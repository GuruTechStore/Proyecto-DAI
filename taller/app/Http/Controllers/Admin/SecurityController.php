<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityLog;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SecurityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
    }

    /**
     * Dashboard de seguridad
     */
    public function dashboard()
    {
        abort_unless(auth()->user()->can('seguridad.ver'), 403);

        // Estadísticas generales
        $stats = [
            'failed_logins_today' => SecurityLog::where('tipo', 'failed_login')
                ->whereDate('created_at', today())
                ->count(),
            'successful_logins_today' => SecurityLog::where('tipo', 'successful_login')
                ->whereDate('created_at', today())
                ->count(),
            'blocked_users' => Usuario::whereNotNull('blocked_until')
                ->where('blocked_until', '>', now())
                ->count(),
            'active_sessions' => $this->getActiveSessionsCount(),
            'suspicious_activity' => SecurityLog::where('severity', 'critical')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];

        // Actividad reciente
        $recentActivity = SecurityLog::with('usuario')
            ->whereIn('tipo', ['failed_login', 'successful_login', 'user_blocked', 'suspicious_activity'])
            ->latest()
            ->take(20)
            ->get();

        // IPs más activas
        $topIps = SecurityLog::selectRaw('ip, COUNT(*) as attempts')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('ip')
            ->orderByDesc('attempts')
            ->take(10)
            ->get();

        // Intentos fallidos por hora (últimas 24 horas)
        $failedLoginsByHour = SecurityLog::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('tipo', 'failed_login')
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->hour => $item->count];
            });

        // Completar las horas faltantes con 0
        $hourlyData = collect(range(0, 23))->mapWithKeys(function($hour) use ($failedLoginsByHour) {
            return [$hour => $failedLoginsByHour->get($hour, 0)];
        });

        return view('admin.security.dashboard', compact(
            'stats', 
            'recentActivity', 
            'topIps', 
            'hourlyData'
        ));
    }

    /**
     * Logs de seguridad
     */
    public function logs(Request $request)
    {
        abort_unless(auth()->user()->can('seguridad.ver'), 403);

        $query = SecurityLog::with('usuario');

        // Filtros
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('ip')) {
            $query->where('ip', 'like', "%{$request->ip}%");
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                  ->orWhere('ip', 'like', "%{$search}%")
                  ->orWhereHas('usuario', function($userQuery) use ($search) {
                      $userQuery->where('username', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $logs = $query->paginate(25)->withQueryString();

        // Datos para filtros
        $tipos = SecurityLog::distinct()->pluck('tipo');
        $severities = SecurityLog::distinct()->pluck('severity');
        $usuarios = Usuario::select('id', 'username', 'email')->get();

        if ($request->expectsJson()) {
            return response()->json([
                'logs' => $logs,
                'tipos' => $tipos,
                'severities' => $severities,
                'usuarios' => $usuarios
            ]);
        }

        return view('admin.security.logs', compact('logs', 'tipos', 'severities', 'usuarios'));
    }

    /**
     * Sesiones activas
     */
    public function activeSessions(Request $request)
    {
        abort_unless(auth()->user()->can('seguridad.ver'), 403);

        // Obtener sesiones de la base de datos (Laravel sessions)
        $sessions = DB::table('sessions')
            ->select([
                'id',
                'user_id',
                'ip_address',
                'user_agent',
                'last_activity',
                'payload'
            ])
            ->whereNotNull('user_id')
            ->orderByDesc('last_activity')
            ->get()
            ->map(function($session) {
                $usuario = Usuario::find($session->user_id);
                
                return [
                    'id' => $session->id,
                    'usuario' => $usuario ? [
                        'id' => $usuario->id,
                        'username' => $usuario->username,
                        'email' => $usuario->email
                    ] : null,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $this->parseUserAgent($session->user_agent),
                    'last_activity' => Carbon::createFromTimestamp($session->last_activity),
                    'is_current' => $session->id === session()->getId()
                ];
            });

        $stats = [
            'total_sessions' => $sessions->count(),
            'unique_users' => $sessions->pluck('usuario.id')->filter()->unique()->count(),
            'sessions_today' => $sessions->filter(function($session) {
                return $session['last_activity']->isToday();
            })->count()
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'sessions' => $sessions,
                'stats' => $stats
            ]);
        }

        return view('admin.security.sessions', compact('sessions', 'stats'));
    }

    /**
     * Revocar sesión
     */
    public function revokeSession(Request $request, $sessionId)
    {
        abort_unless(auth()->user()->can('seguridad.administrar'), 403);

        // Verificar que no revoque su propia sesión
        if ($sessionId === session()->getId()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes revocar tu propia sesión'
            ], 422);
        }

        // Obtener información de la sesión antes de eliminarla
        $sessionData = DB::table('sessions')
            ->where('id', $sessionId)
            ->first();

        if (!$sessionData) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión no encontrada'
            ], 404);
        }

        // Eliminar la sesión
        DB::table('sessions')->where('id', $sessionId)->delete();

        // Log de la acción
        SecurityLog::create([
            'tipo' => 'session_revoked',
            'descripcion' => 'Sesión revocada por administrador',
            'usuario_id' => $sessionData->user_id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'severity' => 'medium',
            'datos_adicionales' => [
                'revoked_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'session_id' => $sessionId,
                'target_ip' => $sessionData->ip_address
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesión revocada exitosamente'
        ]);
    }

    /**
     * Intentos fallidos de login
     */
    public function failedLogins(Request $request)
    {
        abort_unless(auth()->user()->can('seguridad.ver'), 403);

        $query = SecurityLog::where('tipo', 'failed_login');

        // Filtros
        if ($request->filled('ip')) {
            $query->where('ip', 'like', "%{$request->ip}%");
        }

        if ($request->filled('email')) {
            $query->whereJsonContains('datos_adicionales->email', $request->email);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $failedLogins = $query->latest()
            ->paginate(25)
            ->withQueryString();

        // Estadísticas
        $stats = [
            'total_today' => SecurityLog::where('tipo', 'failed_login')
                ->whereDate('created_at', today())
                ->count(),
            'unique_ips_today' => SecurityLog::where('tipo', 'failed_login')
                ->whereDate('created_at', today())
                ->distinct('ip')
                ->count(),
            'most_targeted_emails' => SecurityLog::where('tipo', 'failed_login')
                ->where('created_at', '>=', now()->subDays(7))
                ->get()
                ->pluck('datos_adicionales.email')
                ->filter()
                ->countBy()
                ->sortDesc()
                ->take(5)
        ];

        return view('admin.security.failed-logins', compact('failedLogins', 'stats'));
    }

    /**
     * Bloquear IP
     */
    public function blockIP(Request $request)
    {
        abort_unless(auth()->user()->can('seguridad.administrar'), 403);

        $request->validate([
            'ip' => 'required|ip',
            'duration' => 'required|integer|min:1|max:43200', // max 30 días en minutos
            'reason' => 'required|string|max:255'
        ]);

        $blockedUntil = now()->addMinutes($request->duration);

        // Guardar en cache
        Cache::put("blocked_ip_{$request->ip}", [
            'blocked_until' => $blockedUntil,
            'reason' => $request->reason,
            'blocked_by' => auth()->user()->username
        ], $blockedUntil);

        // Log de la acción
        SecurityLog::create([
            'tipo' => 'ip_blocked',
            'descripcion' => 'IP bloqueada por administrador',
            'ip' => $request->ip,
            'user_agent' => $request->userAgent(),
            'severity' => 'high',
            'datos_adicionales' => [
                'blocked_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'reason' => $request->reason,
                'duration_minutes' => $request->duration,
                'blocked_until' => $blockedUntil->toISOString()
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => "IP {$request->ip} bloqueada hasta {$blockedUntil->format('d/m/Y H:i')}"
        ]);
    }

    /**
     * Obtener número de sesiones activas
     */
    private function getActiveSessionsCount()
    {
        return DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
            ->count();
    }

    /**
     * Parsear User Agent para mostrar información útil
     */
    private function parseUserAgent($userAgent)
    {
        $agent = [
            'browser' => 'Desconocido',
            'platform' => 'Desconocido',
            'device' => 'Desktop'
        ];

        // Detectar navegador
        if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            $agent['browser'] = 'Chrome ' . explode('.', $matches[1])[0];
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            $agent['browser'] = 'Firefox ' . explode('.', $matches[1])[0];
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
            $agent['browser'] = 'Safari';
        } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
            $agent['browser'] = 'Edge ' . explode('.', $matches[1])[0];
        }

        // Detectar plataforma
        if (strpos($userAgent, 'Windows') !== false) {
            $agent['platform'] = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $agent['platform'] = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $agent['platform'] = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $agent['platform'] = 'Android';
            $agent['device'] = 'Mobile';
        } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $agent['platform'] = 'iOS';
            $agent['device'] = strpos($userAgent, 'iPad') !== false ? 'Tablet' : 'Mobile';
        }

        return $agent;
    }

    /**
     * Exportar logs de seguridad
     */
    public function exportLogs(Request $request)
    {
        abort_unless(auth()->user()->can('seguridad.ver'), 403);

        $query = SecurityLog::with('usuario');

        // Aplicar los mismos filtros que en logs()
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'security_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Headers CSV
            fputcsv($file, [
                'Fecha',
                'Tipo',
                'Severidad',
                'Usuario',
                'IP',
                'Descripción',
                'User Agent',
                'Datos Adicionales'
            ]);

            // Datos
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->tipo,
                    $log->severity,
                    $log->usuario ? $log->usuario->username : 'N/A',
                    $log->ip,
                    $log->descripcion,
                    $log->user_agent,
                    json_encode($log->datos_adicionales)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Limpiar logs antiguos
     */
    public function cleanOldLogs(Request $request)
    {
        abort_unless(auth()->user()->can('seguridad.administrar'), 403);

        $request->validate([
            'days' => 'required|integer|min:30|max:365' // Entre 30 días y 1 año
        ]);

        $cutoffDate = now()->subDays($request->days);
        
        $deletedCount = SecurityLog::where('created_at', '<', $cutoffDate)->delete();

        // Log de la acción
        SecurityLog::create([
            'tipo' => 'logs_cleaned',
            'descripcion' => 'Logs de seguridad limpiados',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'severity' => 'medium',
            'datos_adicionales' => [
                'cleaned_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'cutoff_date' => $cutoffDate->toISOString(),
                'deleted_count' => $deletedCount
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deletedCount} registros anteriores a {$cutoffDate->format('d/m/Y')}"
        ]);
    }

    /**
     * Obtener estadísticas de seguridad para API
     */
    public function getSecurityStats()
    {
        abort_unless(auth()->user()->can('seguridad.ver'), 403);

        return response()->json([
            'failed_logins_24h' => SecurityLog::where('tipo', 'failed_login')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'successful_logins_24h' => SecurityLog::where('tipo', 'successful_login')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'blocked_users' => Usuario::whereNotNull('blocked_until')
                ->where('blocked_until', '>', now())
                ->count(),
            'active_sessions' => $this->getActiveSessionsCount(),
            'critical_events_7d' => SecurityLog::where('severity', 'critical')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ]);
    }
}