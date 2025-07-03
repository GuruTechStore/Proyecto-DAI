<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
    }

    /**
     * Mostrar actividad del sistema
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('actividad.ver'), 403);

        $query = UserActivity::with(['usuario']);

        // Filtros
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('ip')) {
            $query->where('ip', 'like', "%{$request->ip}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
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

        $activities = $query->paginate(25)->withQueryString();

        // Datos para filtros
        $usuarios = Usuario::select('id', 'username', 'email')->get();
        $acciones = UserActivity::distinct()->pluck('accion')->filter();
        $modulos = UserActivity::distinct()->pluck('modulo')->filter();

        // Estadísticas
        $stats = [
            'total_activities' => UserActivity::count(),
            'activities_today' => UserActivity::whereDate('created_at', today())->count(),
            'unique_users_today' => UserActivity::whereDate('created_at', today())
                ->distinct('usuario_id')
                ->count(),
            'most_active_user' => $this->getMostActiveUser(),
            'top_actions' => $this->getTopActions(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'activities' => $activities,
                'usuarios' => $usuarios,
                'acciones' => $acciones,
                'modulos' => $modulos,
                'stats' => $stats
            ]);
        }

        return view('admin.activity.index', compact(
            'activities', 
            'usuarios', 
            'acciones', 
            'modulos', 
            'stats'
        ));
    }

    /**
     * Actividad de un usuario específico
     */
    public function userActivity(Request $request, Usuario $user)
    {
        abort_unless(auth()->user()->can('actividad.ver'), 403);

        $query = UserActivity::where('usuario_id', $user->id);

        // Filtros
        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $activities = $query->latest()->paginate(20)->withQueryString();

        // Estadísticas del usuario
        $userStats = [
            'total_activities' => UserActivity::where('usuario_id', $user->id)->count(),
            'activities_today' => UserActivity::where('usuario_id', $user->id)
                ->whereDate('created_at', today())
                ->count(),
            'activities_this_week' => UserActivity::where('usuario_id', $user->id)
                ->where('created_at', '>=', now()->startOfWeek())
                ->count(),
            'activities_this_month' => UserActivity::where('usuario_id', $user->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'last_activity' => UserActivity::where('usuario_id', $user->id)
                ->latest()
                ->first()?->created_at,
            'most_used_modules' => UserActivity::where('usuario_id', $user->id)
                ->selectRaw('modulo, COUNT(*) as count')
                ->groupBy('modulo')
                ->orderByDesc('count')
                ->take(5)
                ->get(),
        ];

        // Actividad por día (últimos 30 días)
        $dailyActivity = UserActivity::where('usuario_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->date => $item->count];
            });

        // Completar días faltantes con 0
        $last30Days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $last30Days[$date] = $dailyActivity->get($date, 0);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'user' => $user,
                'activities' => $activities,
                'stats' => $userStats,
                'daily_activity' => $last30Days
            ]);
        }

        return view('admin.activity.user', compact(
            'user', 
            'activities', 
            'userStats', 
            'last30Days'
        ));
    }

    /**
     * Exportar actividad
     */
    public function export(Request $request)
    {
        abort_unless(auth()->user()->can('actividad.ver'), 403);

        $query = UserActivity::with('usuario');

        // Aplicar filtros
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        $activities = $query->orderBy('created_at', 'desc')->get();

        $filename = 'user_activity_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // Headers CSV
            fputcsv($file, [
                'Fecha',
                'Usuario',
                'Email',
                'Acción',
                'Módulo',
                'Descripción',
                'URL',
                'IP',
                'User Agent'
            ]);

            // Datos
            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->created_at->format('Y-m-d H:i:s'),
                    $activity->usuario ? $activity->usuario->username : 'N/A',
                    $activity->usuario ? $activity->usuario->email : 'N/A',
                    $activity->accion,
                    $activity->modulo,
                    $activity->descripcion,
                    $activity->url,
                    $activity->ip,
                    $activity->user_agent
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Limpiar actividad antigua
     */
    public function clearOldActivity(Request $request)
    {
        abort_unless(auth()->user()->can('actividad.administrar'), 403);

        $request->validate([
            'days' => 'required|integer|min:30|max:365' // Entre 30 días y 1 año
        ]);

        $cutoffDate = now()->subDays($request->days);
        
        $deletedCount = UserActivity::where('created_at', '<', $cutoffDate)->delete();

        // Log de la acción
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'limpiar_actividad',
            'modulo' => 'admin',
            'descripcion' => "Actividad anterior a {$cutoffDate->format('Y-m-d')} eliminada",
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_adicionales' => [
                'cutoff_date' => $cutoffDate->toISOString(),
                'deleted_count' => $deletedCount
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => "Se eliminaron {$deletedCount} registros de actividad anteriores a {$cutoffDate->format('d/m/Y')}"
        ]);
    }

    /**
     * Obtener estadísticas en tiempo real
     */
    public function getRealtimeStats()
    {
        abort_unless(auth()->user()->can('actividad.ver'), 403);

        return response()->json([
            'online_users' => $this->getOnlineUsersCount(),
            'activities_last_hour' => UserActivity::where('created_at', '>=', now()->subHour())->count(),
            'most_active_module' => $this->getMostActiveModule(),
            'recent_activities' => UserActivity::with('usuario')
                ->latest()
                ->take(5)
                ->get()
                ->map(function($activity) {
                    return [
                        'usuario' => $activity->usuario ? $activity->usuario->username : 'Sistema',
                        'accion' => $activity->accion,
                        'modulo' => $activity->modulo,
                        'descripcion' => $activity->descripcion,
                        'created_at' => $activity->created_at->diffForHumans()
                    ];
                })
        ]);
    }

    /**
     * Obtener usuario más activo
     */
    private function getMostActiveUser()
    {
        $result = UserActivity::with('usuario')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('usuario_id, COUNT(*) as count')
            ->groupBy('usuario_id')
            ->orderByDesc('count')
            ->first();

        return $result ? [
            'usuario' => $result->usuario->username ?? 'N/A',
            'count' => $result->count
        ] : null;
    }

    /**
     * Obtener acciones más comunes
     */
    private function getTopActions()
    {
        return UserActivity::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('accion, COUNT(*) as count')
            ->groupBy('accion')
            ->orderByDesc('count')
            ->take(5)
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->accion => $item->count];
            });
    }

    /**
     * Obtener usuarios online (actividad en los últimos 15 minutos)
     */
    private function getOnlineUsersCount()
    {
        return UserActivity::where('created_at', '>=', now()->subMinutes(15))
            ->distinct('usuario_id')
            ->count();
    }

    /**
     * Obtener módulo más activo
     */
    private function getMostActiveModule()
    {
        $result = UserActivity::where('created_at', '>=', now()->subDay())
            ->selectRaw('modulo, COUNT(*) as count')
            ->groupBy('modulo')
            ->orderByDesc('count')
            ->first();

        return $result ? $result->modulo : null;
    }

    /**
     * Obtener actividad por horas del día
     */
    public function getHourlyActivity(Request $request)
    {
        abort_unless(auth()->user()->can('actividad.ver'), 403);

        $date = $request->get('date', today());

        $hourlyData = UserActivity::whereDate('created_at', $date)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->hour => $item->count];
            });

        // Completar horas faltantes con 0
        $completeDayData = collect(range(0, 23))->mapWithKeys(function($hour) use ($hourlyData) {
            return [$hour => $hourlyData->get($hour, 0)];
        });

        return response()->json([
            'date' => $date,
            'hourly_data' => $completeDayData
        ]);
    }

    /**
     * Obtener top usuarios por actividad
     */
    public function getTopUsers(Request $request)
    {
        abort_unless(auth()->user()->can('actividad.ver'), 403);

        $days = $request->get('days', 7);
        $limit = $request->get('limit', 10);

        $topUsers = UserActivity::with('usuario')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('usuario_id, COUNT(*) as activities_count')
            ->groupBy('usuario_id')
            ->orderByDesc('activities_count')
            ->take($limit)
            ->get()
            ->map(function($item) {
                return [
                    'usuario' => $item->usuario ? [
                        'id' => $item->usuario->id,
                        'username' => $item->usuario->username,
                        'email' => $item->usuario->email
                    ] : null,
                    'activities_count' => $item->activities_count
                ];
            });

        return response()->json([
            'period_days' => $days,
            'top_users' => $topUsers
        ]);
    }
}