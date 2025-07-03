<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\UserActivity;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check()) {
            $this->trackActivity($request);
            $this->checkConcurrentSessions();
            $this->checkInactiveUsers();
        }

        return $response;
    }

    protected function trackActivity(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Actualizar último login en tabla usuarios
            $user->update(['ultimo_login' => now()]);

            // Registrar actividad en user_activities
            $modulo = $this->getModuleFromRoute($request);
            $accion = $request->method() . ' ' . $request->path();
            
            UserActivity::registrarActividad(
                $user->id,
                $modulo,
                $accion,
                $request->ip(),
                $request->userAgent(),
                $request->fullUrl(),
                [
                    'session_id' => session()->getId(),
                    'referer' => $request->header('referer'),
                    'method' => $request->method()
                ]
            );

        } catch (\Exception $e) {
            \Log::error('Track activity error: ' . $e->getMessage());
        }
    }

    protected function getModuleFromRoute(Request $request): string
    {
        $path = $request->path();
        $segments = explode('/', $path);
        
        // Mapear rutas a módulos usando constantes del modelo
        $moduleMap = [
            'dashboard' => UserActivity::MODULO_DASHBOARD,
            'usuarios' => UserActivity::MODULO_USUARIOS,
            'clientes' => UserActivity::MODULO_CLIENTES,
            'productos' => UserActivity::MODULO_PRODUCTOS,
            'reparaciones' => UserActivity::MODULO_REPARACIONES,
            'ventas' => UserActivity::MODULO_VENTAS,
            'empleados' => UserActivity::MODULO_EMPLEADOS,
            'reportes' => UserActivity::MODULO_REPORTES,
            'configuracion' => UserActivity::MODULO_CONFIGURACION,
            'api' => UserActivity::MODULO_API
        ];

        foreach ($segments as $segment) {
            if (isset($moduleMap[$segment])) {
                return $moduleMap[$segment];
            }
        }

        return 'General';
    }

    protected function checkConcurrentSessions()
    {
        try {
            $user = Auth::user();
            $sessionLimit = config('security.session_concurrent_limit', 3);
            
            // Contar sesiones activas
            $activeSessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('last_activity', '>', now()->subMinutes(config('session.lifetime', 120))->timestamp)
                ->count();

            if ($activeSessions > $sessionLimit) {
                // Log evento de seguridad usando SecurityLog
                \App\Models\SecurityLog::create([
                    'tipo' => 'concurrent_sessions_exceeded',
                    'descripcion' => "Usuario excedió límite de sesiones concurrentes",
                    'usuario_id' => $user->id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'severity' => \App\Models\SecurityLog::SEVERITY_WARNING,
                    'datos_adicionales' => [
                        'sesiones_activas' => $activeSessions,
                        'limite' => $sessionLimit
                    ]
                ]);

                // Opcional: Cerrar sesiones más antiguas
                $this->closeOldestSessions($user->id, $sessionLimit);
            }

        } catch (\Exception $e) {
            \Log::error('Concurrent sessions check error: ' . $e->getMessage());
        }
    }

    protected function closeOldestSessions(int $userId, int $limit)
    {
        $oldSessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderBy('last_activity', 'asc')
            ->limit(DB::table('sessions')->where('user_id', $userId)->count() - $limit + 1)
            ->pluck('id');

        DB::table('sessions')->whereIn('id', $oldSessions)->delete();
    }

    protected function checkInactiveUsers()
    {
        try {
            // Ejecutar solo ocasionalmente para no sobrecargar
            if (rand(1, 100) <= 5) { // 5% de probabilidad
                $inactiveUsers = Usuario::where('ultimo_login', '<', now()->subDays(30))
                    ->where('bloqueado', false)
                    ->get();

                foreach ($inactiveUsers as $user) {
                    $user->update(['bloqueado' => true]);
                    
                    \App\Models\SecurityLog::create([
                        'tipo' => \App\Models\SecurityLog::TIPO_USER_BLOCKED,
                        'descripcion' => 'Usuario bloqueado por inactividad',
                        'usuario_id' => $user->id,
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'severity' => \App\Models\SecurityLog::SEVERITY_INFO,
                        'datos_adicionales' => [
                            'reason' => 'inactive_30_days',
                            'ultimo_login' => $user->ultimo_login
                        ]
                    ]);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Inactive users check error: ' . $e->getMessage());
        }
    }
}