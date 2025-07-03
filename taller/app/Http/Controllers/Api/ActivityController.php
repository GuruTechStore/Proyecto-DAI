<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserActivityResource;
use App\Models\UserActivity;
use App\Models\Usuario;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    /**
     * Listar actividades de usuarios con filtros
     */
    public function index(Request $request)
    {
        $this->authorize('viewUserActivities');

        $query = UserActivity::with('usuario');

        // Filtros
        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->has('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->has('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->has('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        if ($request->has('fecha_desde')) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->where('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('accion', 'like', "%{$search}%")
                  ->orWhere('modulo', 'like', "%{$search}%")
                  ->orWhere('detalles', 'like', "%{$search}%")
                  ->orWhereHas('usuario', function($uq) use ($search) {
                      $uq->where('username', 'like', "%{$search}%")
                        ->orWhere('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $allowedSorts = ['id', 'accion', 'modulo', 'created_at'];
        
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($request->per_page ?? 25, 100);
        $activities = $query->paginate($perPage);

        // Log de consulta
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'user_activities_viewed',
            'modulo' => 'actividades',
            'detalles' => json_encode([
                'filters' => $request->only(['usuario_id', 'modulo', 'accion']),
                'results_count' => $activities->total()
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'data' => UserActivityResource::collection($activities),
            'meta' => [
                'pagination' => [
                    'total' => $activities->total(),
                    'per_page' => $activities->perPage(),
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage()
                ],
                'filters' => [
                    'available_modules' => UserActivity::distinct('modulo')->pluck('modulo'),
                    'available_actions' => UserActivity::distinct('accion')->pluck('accion'),
                    'filters_applied' => $request->only(['usuario_id', 'modulo', 'accion'])
                ]
            ]
        ]);
    }

    /**
     * Obtener actividades de un usuario específico
     */
    public function byUser(Request $request, Usuario $user)
    {
        $this->authorize('viewUserActivities');

        $query = UserActivity::where('usuario_id', $user->id);

        // Filtros adicionales
        if ($request->has('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->has('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->has('fecha_desde')) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->where('created_at', '<=', $request->fecha_hasta);
        }

        $perPage = min($request->per_page ?? 20, 100);
        $activities = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Estadísticas del usuario
        $stats = [
            'total_activities' => UserActivity::where('usuario_id', $user->id)->count(),
            'activities_last_7_days' => UserActivity::where('usuario_id', $user->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'activities_last_30_days' => UserActivity::where('usuario_id', $user->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'most_used_modules' => UserActivity::where('usuario_id', $user->id)
                ->selectRaw('modulo, COUNT(*) as count')
                ->groupBy('modulo')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->pluck('count', 'modulo'),
            'most_common_actions' => UserActivity::where('usuario_id', $user->id)
                ->selectRaw('accion, COUNT(*) as count')
                ->groupBy('accion')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->pluck('count', 'accion'),
            'peak_hours' => UserActivity::where('usuario_id', $user->id)
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->pluck('count', 'hour'),
            'last_activity' => UserActivity::where('usuario_id', $user->id)
                ->latest()
                ->first()?->created_at
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nombres' => $user->nombres,
                    'apellidos' => $user->apellidos,
                    'email' => $user->email
                ],
                'activities' => UserActivityResource::collection($activities),
                'stats' => $stats
            ]
        ]);
    }

    /**
     * Obtener actividades por módulo
     */
    public function byModule(Request $request, $module)
    {
        $this->authorize('viewUserActivities');

        $query = UserActivity::where('modulo', $module)->with('usuario');

        // Filtros adicionales
        if ($request->has('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->has('fecha_desde')) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->where('created_at', '<=', $request->fecha_hasta);
        }

        $perPage = min($request->per_page ?? 25, 100);
        $activities = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Estadísticas del módulo
        $moduleStats = [
            'total_activities' => UserActivity::where('modulo', $module)->count(),
            'activities_last_7_days' => UserActivity::where('modulo', $module)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'unique_users' => UserActivity::where('modulo', $module)
                ->distinct('usuario_id')
                ->count(),
            'most_active_users' => UserActivity::where('modulo', $module)
                ->with('usuario:id,username,nombres,apellidos')
                ->selectRaw('usuario_id, COUNT(*) as count')
                ->groupBy('usuario_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'actions_distribution' => UserActivity::where('modulo', $module)
                ->selectRaw('accion, COUNT(*) as count')
                ->groupBy('accion')
                ->orderBy('count', 'desc')
                ->pluck('count', 'accion'),
            'daily_activity' => UserActivity::where('modulo', $module)
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'module' => $module,
                'activities' => UserActivityResource::collection($activities),
                'stats' => $moduleStats,
                'available_actions' => UserActivity::where('modulo', $module)
                    ->distinct('accion')
                    ->pluck('accion')
            ]
        ]);
    }

    /**
     * Dashboard de actividades con métricas generales
     */
    public function dashboard(Request $request)
    {
        $this->authorize('viewActivityDashboard');

        $timeRange = $request->time_range ?? '7d';
        $startDate = $this->getStartDateFromRange($timeRange);

        // Métricas principales
        $metrics = [
            'total_activities' => UserActivity::where('created_at', '>=', $startDate)->count(),
            'unique_active_users' => UserActivity::where('created_at', '>=', $startDate)
                ->distinct('usuario_id')
                ->count(),
            'total_users' => Usuario::where('activo', true)->count(),
            'avg_activities_per_user' => $this->getAverageActivitiesPerUser($startDate),
            'most_active_hour' => $this->getMostActiveHour($startDate),
            'activity_growth' => $this->getActivityGrowth($startDate)
        ];

        // Actividad por módulos
        $moduleActivity = UserActivity::where('created_at', '>=', $startDate)
            ->selectRaw('modulo, COUNT(*) as count, COUNT(DISTINCT usuario_id) as unique_users')
            ->groupBy('modulo')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'module' => $item->modulo,
                    'activities' => $item->count,
                    'unique_users' => $item->unique_users,
                    'avg_per_user' => $item->unique_users > 0 ? round($item->count / $item->unique_users, 2) : 0
                ];
            });

        // Usuarios más activos
        $mostActiveUsers = UserActivity::where('created_at', '>=', $startDate)
            ->with('usuario:id,username,nombres,apellidos,email')
            ->selectRaw('usuario_id, COUNT(*) as activities_count, COUNT(DISTINCT modulo) as modules_used')
            ->groupBy('usuario_id')
            ->orderBy('activities_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($activity) {
                return [
                    'user' => $activity->usuario,
                    'activities_count' => $activity->activities_count,
                    'modules_used' => $activity->modules_used,
                    'last_activity' => UserActivity::where('usuario_id', $activity->usuario_id)
                        ->latest()
                        ->first()?->created_at
                ];
            });

        // Tendencias diarias
        $dailyTrends = UserActivity::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as activities, COUNT(DISTINCT usuario_id) as unique_users')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function($day) {
                return [
                    'date' => $day->date,
                    'activities' => $day->activities,
                    'unique_users' => $day->unique_users,
                    'avg_per_user' => $day->unique_users > 0 ? round($day->activities / $day->unique_users, 2) : 0
                ];
            });

        // Patrones de uso por hora
        $hourlyPatterns = UserActivity::where('created_at', '>=', $startDate)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour');

        // Actividades recientes importantes
        $recentImportantActivities = UserActivity::where('created_at', '>=', now()->subHours(24))
            ->whereIn('accion', [
                'user_created', 'user_deleted', 'role_assigned', 'role_removed',
                'password_changed', '2fa_enabled', '2fa_disabled', 'user_blocked'
            ])
            ->with('usuario:id,username,nombres,apellidos')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Comparativa con período anterior
        $previousStartDate = $startDate->copy()->sub(now()->diff($startDate));
        $previousMetrics = [
            'total_activities' => UserActivity::where('created_at', '>=', $previousStartDate)
                ->where('created_at', '<', $startDate)
                ->count(),
            'unique_active_users' => UserActivity::where('created_at', '>=', $previousStartDate)
                ->where('created_at', '<', $startDate)
                ->distinct('usuario_id')
                ->count()
        ];

        // Log de consulta del dashboard
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'activity_dashboard_viewed',
            'modulo' => 'actividades',
            'detalles' => json_encode([
                'time_range' => $timeRange,
                'total_activities' => $metrics['total_activities']
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => $metrics,
                'previous_metrics' => $previousMetrics,
                'module_activity' => $moduleActivity,
                'most_active_users' => $mostActiveUsers,
                'daily_trends' => $dailyTrends,
                'hourly_patterns' => $hourlyPatterns,
                'recent_important_activities' => UserActivityResource::collection($recentImportantActivities),
                'time_range' => $timeRange,
                'period_comparison' => [
                    'activities_change' => $this->calculatePercentageChange(
                        $previousMetrics['total_activities'], 
                        $metrics['total_activities']
                    ),
                    'users_change' => $this->calculatePercentageChange(
                        $previousMetrics['unique_active_users'], 
                        $metrics['unique_active_users']
                    )
                ]
            ]
        ]);
    }

    /**
     * Exportar actividades
     */
    public function export(Request $request)
    {
        $this->authorize('exportUserActivities');

        $validator = Validator::make($request->all(), [
            'format' => 'required|in:csv,json',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'usuario_id' => 'nullable|exists:usuarios,id',
            'modulo' => 'nullable|string',
            'accion' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parámetros de exportación inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = UserActivity::with('usuario:id,username,nombres,apellidos,email');

        // Aplicar filtros
        if ($request->fecha_desde) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->where('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->usuario_id) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->modulo) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->accion) {
            $query->where('accion', $request->accion);
        }

        // Limitar a 10,000 registros para evitar problemas de memoria
        $activities = $query->orderBy('created_at', 'desc')->limit(10000)->get();

        // Log de exportación
        SecurityLog::create([
            'evento' => 'user_activities_exported',
            'usuario_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'format' => $request->format,
                'records_count' => $activities->count(),
                'filters' => $request->only(['fecha_desde', 'fecha_hasta', 'usuario_id', 'modulo', 'accion'])
            ]),
            'nivel_riesgo' => 'low'
        ]);

        switch ($request->format) {
            case 'json':
                return response()->json([
                    'success' => true,
                    'data' => UserActivityResource::collection($activities),
                    'meta' => [
                        'exported_at' => now(),
                        'exported_by' => auth()->user()->username,
                        'total_records' => $activities->count(),
                        'filters_applied' => $request->only(['fecha_desde', 'fecha_hasta', 'usuario_id', 'modulo', 'accion'])
                    ]
                ]);

            case 'csv':
                $csvData = $this->generateActivityCSV($activities);
                return response($csvData)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="user_activities_' . now()->format('Y-m-d_H-i-s') . '.csv"');
        }
    }

    /**
     * Obtener resumen de actividad por usuario
     */
    public function userSummary(Request $request)
    {
        $this->authorize('viewUserActivities');

        $timeRange = $request->time_range ?? '30d';
        $startDate = $this->getStartDateFromRange($timeRange);

        $userSummaries = Usuario::where('activo', true)
            ->with(['roles:id,name,display_name'])
            ->get()
            ->map(function($user) use ($startDate) {
                $activities = UserActivity::where('usuario_id', $user->id)
                    ->where('created_at', '>=', $startDate);

                $lastActivity = UserActivity::where('usuario_id', $user->id)
                    ->latest()
                    ->first();

                return [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'nombres' => $user->nombres,
                        'apellidos' => $user->apellidos,
                        'email' => $user->email,
                        'roles' => $user->roles->pluck('display_name')
                    ],
                    'activity_stats' => [
                        'total_activities' => $activities->count(),
                        'unique_modules' => $activities->distinct('modulo')->count(),
                        'unique_actions' => $activities->distinct('accion')->count(),
                        'last_activity' => $lastActivity?->created_at,
                        'last_action' => $lastActivity?->accion,
                        'last_module' => $lastActivity?->modulo,
                        'days_since_last_activity' => $lastActivity 
                            ? now()->diffInDays($lastActivity->created_at) 
                            : null,
                        'is_active_user' => $activities->count() > 0
                    ],
                    'top_modules' => $activities->selectRaw('modulo, COUNT(*) as count')
                        ->groupBy('modulo')
                        ->orderBy('count', 'desc')
                        ->limit(3)
                        ->pluck('count', 'modulo')
                ];
            })
            ->sortByDesc('activity_stats.total_activities')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'user_summaries' => $userSummaries,
                'summary_stats' => [
                    'total_users' => $userSummaries->count(),
                    'active_users' => $userSummaries->where('activity_stats.is_active_user', true)->count(),
                    'inactive_users' => $userSummaries->where('activity_stats.is_active_user', false)->count(),
                    'avg_activities_per_user' => $userSummaries->avg('activity_stats.total_activities'),
                    'time_range' => $timeRange
                ]
            ]
        ]);
    }

    /**
     * Detectar patrones de uso anómalos
     */
    public function detectAnomalies(Request $request)
    {
        $this->authorize('viewUserActivities');

        $timeRange = $request->time_range ?? '7d';
        $startDate = $this->getStartDateFromRange($timeRange);

        $anomalies = [
            'unusual_hour_activity' => $this->detectUnusualHourActivity($startDate),
            'excessive_activity' => $this->detectExcessiveActivity($startDate),
            'module_access_anomalies' => $this->detectModuleAccessAnomalies($startDate),
            'inactive_users_sudden_activity' => $this->detectInactiveUsersSuddenActivity($startDate),
            'repeated_failed_actions' => $this->detectRepeatedFailedActions($startDate)
        ];

        // Log detección de anomalías
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'anomaly_detection_run',
            'modulo' => 'actividades',
            'detalles' => json_encode([
                'time_range' => $timeRange,
                'anomalies_found' => array_sum(array_map('count', $anomalies))
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'anomalies' => $anomalies,
                'summary' => [
                    'total_anomalies' => array_sum(array_map('count', $anomalies)),
                    'time_range' => $timeRange,
                    'detection_date' => now()
                ]
            ]
        ]);
    }

    // Métodos auxiliares privados

    private function getStartDateFromRange($range)
    {
        switch ($range) {
            case '1d': return now()->subDay();
            case '7d': return now()->subDays(7);
            case '30d': return now()->subDays(30);
            case '90d': return now()->subDays(90);
            case '24h': return now()->subHours(24);
            default: return now()->subDays(7);
        }
    }

    private function getAverageActivitiesPerUser($startDate)
    {
        $totalUsers = Usuario::where('activo', true)->count();
        if ($totalUsers === 0) return 0;
        
        $totalActivities = UserActivity::where('created_at', '>=', $startDate)->count();
        return round($totalActivities / $totalUsers, 2);
    }

    private function getMostActiveHour($startDate)
    {
        $hourActivity = UserActivity::where('created_at', '>=', $startDate)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();

        return $hourActivity ? $hourActivity->hour : null;
    }

    private function getActivityGrowth($startDate)
    {
        $currentPeriod = UserActivity::where('created_at', '>=', $startDate)->count();
        $previousStart = $startDate->copy()->sub(now()->diff($startDate));
        $previousPeriod = UserActivity::where('created_at', '>=', $previousStart)
            ->where('created_at', '<', $startDate)
            ->count();

        if ($previousPeriod === 0) return $currentPeriod > 0 ? 100 : 0;
        
        return round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 2);
    }

    private function calculatePercentageChange($old, $new)
    {
        if ($old === 0) return $new > 0 ? 100 : 0;
        return round((($new - $old) / $old) * 100, 2);
    }

    private function generateActivityCSV($activities)
    {
        $headers = [
            'ID', 'Usuario', 'Nombres', 'Apellidos', 'Email', 'Acción', 'Módulo',
            'IP Address', 'User Agent', 'Fecha', 'Detalles'
        ];

        $csv = implode(',', $headers) . "\n";

        foreach ($activities as $activity) {
            $row = [
                $activity->id,
                $activity->usuario?->username ?? 'N/A',
                $activity->usuario?->nombres ?? 'N/A',
                $activity->usuario?->apellidos ?? 'N/A',
                $activity->usuario?->email ?? 'N/A',
                $activity->accion,
                $activity->modulo,
                $activity->ip_address,
                str_replace(',', ';', $activity->user_agent),
                $activity->created_at->format('Y-m-d H:i:s'),
                str_replace(',', ';', $activity->detalles)
            ];

            $csv .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }

        return $csv;
    }

    private function detectUnusualHourActivity($startDate)
    {
        // Actividad fuera de horario laboral (antes de 6 AM o después de 10 PM)
        return UserActivity::where('created_at', '>=', $startDate)
            ->whereRaw('HOUR(created_at) < 6 OR HOUR(created_at) > 22')
            ->with('usuario:id,username,nombres,apellidos')
            ->selectRaw('usuario_id, COUNT(*) as count')
            ->groupBy('usuario_id')
            ->having('count', '>', 5)
            ->get();
    }

    private function detectExcessiveActivity($startDate)
    {
        // Usuarios con actividad excesiva (más de 100 actividades por día)
        $avgDaily = UserActivity::where('created_at', '>=', $startDate)
            ->count() / max(1, now()->diffInDays($startDate));
        
        $threshold = $avgDaily * 3; // 3 veces el promedio

        return UserActivity::where('created_at', '>=', $startDate)
            ->with('usuario:id,username,nombres,apellidos')
            ->selectRaw('usuario_id, COUNT(*) as count')
            ->groupBy('usuario_id')
            ->having('count', '>', $threshold)
            ->get();
    }

    private function detectModuleAccessAnomalies($startDate)
    {
        // Usuarios accediendo a módulos que normalmente no usan
        return UserActivity::where('created_at', '>=', $startDate)
            ->with('usuario:id,username,nombres,apellidos')
            ->selectRaw('usuario_id, modulo, COUNT(*) as count')
            ->groupBy('usuario_id', 'modulo')
            ->having('count', '>', 20) // Muchas actividades en módulo específico
            ->get()
            ->groupBy('usuario_id');
    }

    private function detectInactiveUsersSuddenActivity($startDate)
    {
        // Usuarios que no tenían actividad previa y de repente están muy activos
        $inactiveUsers = Usuario::whereDoesntHave('userActivities', function($q) use ($startDate) {
            $q->where('created_at', '<', $startDate->copy()->subDays(30));
        })->pluck('id');

        return UserActivity::where('created_at', '>=', $startDate)
            ->whereIn('usuario_id', $inactiveUsers)
            ->with('usuario:id,username,nombres,apellidos')
            ->selectRaw('usuario_id, COUNT(*) as count')
            ->groupBy('usuario_id')
            ->having('count', '>', 10)
            ->get();
    }

    private function detectRepeatedFailedActions($startDate)
    {
        // Detección basada en patrones de seguridad 
        return UserActivity::where('created_at', '>=', $startDate)
            ->where('accion', 'like', '%failed%')
            ->orWhere('accion', 'like', '%error%')
            ->with('usuario:id,username,nombres,apellidos')
            ->selectRaw('usuario_id, accion, COUNT(*) as count')
            ->groupBy('usuario_id', 'accion')
            ->having('count', '>', 5)
            ->get();
    }
}