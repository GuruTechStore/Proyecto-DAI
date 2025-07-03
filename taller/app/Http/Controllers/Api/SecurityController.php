<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SecurityLogResource;
use App\Models\SecurityLog;
use App\Models\UserActivity;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SecurityController extends Controller
{
    /**
     * Listar logs de seguridad con filtros avanzados
     */
    public function index(Request $request)
    {
        $this->authorize('viewSecurityLogs');

        $query = SecurityLog::with('usuario');

        // Filtros
        if ($request->has('evento')) {
            $query->where('evento', $request->evento);
        }

        if ($request->has('nivel_riesgo')) {
            $query->where('nivel_riesgo', $request->nivel_riesgo);
        }

        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->has('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        if ($request->has('resuelto')) {
            $query->where('resuelto', $request->resuelto === 'true');
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
                $q->where('evento', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('detalles', 'like', "%{$search}%")
                  ->orWhereHas('usuario', function($uq) use ($search) {
                      $uq->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $allowedSorts = ['id', 'evento', 'nivel_riesgo', 'created_at', 'ip_address'];
        
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($request->per_page ?? 20, 100);
        $logs = $query->paginate($perPage);

        // Log de consulta
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'security_logs_viewed',
            'modulo' => 'seguridad',
            'detalles' => json_encode([
                'filters' => $request->only(['evento', 'nivel_riesgo', 'usuario_id', 'resuelto']),
                'results_count' => $logs->total()
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'data' => SecurityLogResource::collection($logs),
            'meta' => [
                'pagination' => [
                    'total' => $logs->total(),
                    'per_page' => $logs->perPage(),
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage()
                ],
                'filters' => [
                    'available_events' => SecurityLog::distinct('evento')->pluck('evento'),
                    'risk_levels' => ['low', 'medium', 'high', 'critical'],
                    'filters_applied' => $request->only(['evento', 'nivel_riesgo', 'usuario_id', 'resuelto'])
                ]
            ]
        ]);
    }

    /**
     * Mostrar log de seguridad específico
     */
    public function show(SecurityLog $log)
    {
        $this->authorize('viewSecurityLogs');

        $log->load('usuario');

        // Buscar logs relacionados (mismo usuario, IP o evento en un rango de tiempo)
        $relatedLogs = SecurityLog::where('id', '!=', $log->id)
            ->where(function($q) use ($log) {
                $q->where('usuario_id', $log->usuario_id)
                  ->orWhere('ip_address', $log->ip_address)
                  ->orWhere('evento', $log->evento);
            })
            ->where('created_at', '>=', $log->created_at->subHours(2))
            ->where('created_at', '<=', $log->created_at->addHours(2))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Información adicional de contexto
        $context = [
            'user_recent_activity' => null,
            'ip_geolocation' => $this->getIPInfo($log->ip_address),
            'user_agent_info' => $this->parseUserAgent($log->user_agent),
            'risk_score' => $this->calculateRiskScore($log)
        ];

        if ($log->usuario_id) {
            $context['user_recent_activity'] = UserActivity::where('usuario_id', $log->usuario_id)
                ->where('created_at', '>=', $log->created_at->subHour())
                ->where('created_at', '<=', $log->created_at->addHour())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'log' => new SecurityLogResource($log),
                'related_logs' => SecurityLogResource::collection($relatedLogs),
                'context' => $context
            ]
        ]);
    }

    /**
     * Marcar log como resuelto
     */
    public function resolve(Request $request, SecurityLog $log)
    {
        $this->authorize('resolveSecurityLogs');

        $validator = Validator::make($request->all(), [
            'notas_resolucion' => 'nullable|string|max:1000',
            'accion_tomada' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($log->resuelto) {
            return response()->json([
                'success' => false,
                'message' => 'Este log ya fue marcado como resuelto'
            ], 400);
        }

        $log->update([
            'resuelto' => true,
            'resuelto_por' => auth()->id(),
            'resuelto_en' => now(),
            'notas_resolucion' => $request->notas_resolucion,
            'accion_tomada' => $request->accion_tomada
        ]);

        // Log de la resolución
        SecurityLog::create([
            'evento' => 'security_log_resolved',
            'usuario_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'resolved_log_id' => $log->id,
                'original_event' => $log->evento,
                'notas_resolucion' => $request->notas_resolucion,
                'accion_tomada' => $request->accion_tomada
            ]),
            'nivel_riesgo' => 'low'
        ]);

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'security_log_resolved',
            'modulo' => 'seguridad',
            'detalles' => json_encode([
                'log_id' => $log->id,
                'original_event' => $log->evento,
                'risk_level' => $log->nivel_riesgo
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Log marcado como resuelto correctamente',
            'data' => new SecurityLogResource($log->fresh(['usuario']))
        ]);
    }

    /**
     * Dashboard de seguridad con métricas
     */
    public function dashboard(Request $request)
    {
        $this->authorize('viewSecurityDashboard');

        $timeRange = $request->time_range ?? '7d'; // 1d, 7d, 30d, 90d
        $startDate = $this->getStartDateFromRange($timeRange);

        // Métricas principales
        $metrics = [
            'total_events' => SecurityLog::where('created_at', '>=', $startDate)->count(),
            'critical_events' => SecurityLog::where('created_at', '>=', $startDate)
                ->where('nivel_riesgo', 'critical')->count(),
            'high_risk_events' => SecurityLog::where('created_at', '>=', $startDate)
                ->where('nivel_riesgo', 'high')->count(),
            'unresolved_events' => SecurityLog::where('created_at', '>=', $startDate)
                ->where('resuelto', false)
                ->whereIn('nivel_riesgo', ['high', 'critical'])
                ->count(),
            'unique_users_affected' => SecurityLog::where('created_at', '>=', $startDate)
                ->whereNotNull('usuario_id')
                ->distinct('usuario_id')
                ->count(),
            'unique_ips' => SecurityLog::where('created_at', '>=', $startDate)
                ->distinct('ip_address')
                ->count()
        ];

        // Eventos por tipo
        $eventsByType = SecurityLog::where('created_at', '>=', $startDate)
            ->selectRaw('evento, nivel_riesgo, COUNT(*) as count')
            ->groupBy('evento', 'nivel_riesgo')
            ->orderBy('count', 'desc')
            ->get()
            ->groupBy('evento')
            ->map(function($events, $evento) {
                return [
                    'evento' => $evento,
                    'total' => $events->sum('count'),
                    'by_risk' => $events->groupBy('nivel_riesgo')->map->sum('count')
                ];
            })
            ->values()
            ->take(10);

        // Tendencias por día
        $dailyTrends = SecurityLog::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, nivel_riesgo, COUNT(*) as count')
            ->groupBy('date', 'nivel_riesgo')
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(function($dayEvents, $date) {
                return [
                    'date' => $date,
                    'total' => $dayEvents->sum('count'),
                    'critical' => $dayEvents->where('nivel_riesgo', 'critical')->sum('count'),
                    'high' => $dayEvents->where('nivel_riesgo', 'high')->sum('count'),
                    'medium' => $dayEvents->where('nivel_riesgo', 'medium')->sum('count'),
                    'low' => $dayEvents->where('nivel_riesgo', 'low')->sum('count')
                ];
            })
            ->values();

        // Top IPs sospechosas
        $suspiciousIPs = SecurityLog::where('created_at', '>=', $startDate)
            ->whereIn('nivel_riesgo', ['high', 'critical'])
            ->selectRaw('ip_address, COUNT(*) as events_count, MAX(nivel_riesgo) as max_risk')
            ->groupBy('ip_address')
            ->orderBy('events_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($ip) {
                return [
                    'ip_address' => $ip->ip_address,
                    'events_count' => $ip->events_count,
                    'max_risk' => $ip->max_risk,
                    'location' => $this->getIPInfo($ip->ip_address),
                    'latest_event' => SecurityLog::where('ip_address', $ip->ip_address)
                        ->latest()
                        ->first()?->evento
                ];
            });

        // Usuarios con más eventos de seguridad
        $usersWithMostEvents = SecurityLog::where('created_at', '>=', $startDate)
            ->whereNotNull('usuario_id')
            ->with('usuario:id,username,email,nombres,apellidos')
            ->selectRaw('usuario_id, COUNT(*) as events_count')
            ->groupBy('usuario_id')
            ->orderBy('events_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($userLog) {
                return [
                    'user' => $userLog->usuario,
                    'events_count' => $userLog->events_count,
                    'latest_event' => SecurityLog::where('usuario_id', $userLog->usuario_id)
                        ->latest()
                        ->first()?->evento
                ];
            });

        // Eventos críticos recientes no resueltos
        $criticalUnresolved = SecurityLog::where('created_at', '>=', $startDate)
            ->where('nivel_riesgo', 'critical')
            ->where('resuelto', false)
            ->with('usuario:id,username,email')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Alertas de seguridad (patrones sospechosos)
        $alerts = $this->generateSecurityAlerts($startDate);

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'security_dashboard_viewed',
            'modulo' => 'seguridad',
            'detalles' => json_encode([
                'time_range' => $timeRange,
                'total_events' => $metrics['total_events']
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => $metrics,
                'events_by_type' => $eventsByType,
                'daily_trends' => $dailyTrends,
                'suspicious_ips' => $suspiciousIPs,
                'users_with_most_events' => $usersWithMostEvents,
                'critical_unresolved' => SecurityLogResource::collection($criticalUnresolved),
                'security_alerts' => $alerts,
                'time_range' => $timeRange,
                'generated_at' => now()
            ]
        ]);
    }

    /**
     * Generar reporte de seguridad
     */
    public function export(Request $request)
    {
        $this->authorize('exportSecurityLogs');

        $validator = Validator::make($request->all(), [
            'format' => 'required|in:csv,json,pdf',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'nivel_riesgo' => 'nullable|in:low,medium,high,critical',
            'evento' => 'nullable|string',
            'include_resolved' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parámetros de exportación inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = SecurityLog::with('usuario');

        // Aplicar filtros
        if ($request->fecha_desde) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->fecha_hasta) {
            $query->where('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->nivel_riesgo) {
            $query->where('nivel_riesgo', $request->nivel_riesgo);
        }

        if ($request->evento) {
            $query->where('evento', $request->evento);
        }

        if (!$request->include_resolved) {
            $query->where('resuelto', false);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // Log de exportación
        SecurityLog::create([
            'evento' => 'security_logs_exported',
            'usuario_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'format' => $request->format,
                'records_count' => $logs->count(),
                'filters' => $request->only(['fecha_desde', 'fecha_hasta', 'nivel_riesgo', 'evento'])
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        switch ($request->format) {
            case 'json':
                return response()->json([
                    'success' => true,
                    'data' => SecurityLogResource::collection($logs),
                    'meta' => [
                        'exported_at' => now(),
                        'exported_by' => auth()->user()->username,
                        'total_records' => $logs->count()
                    ]
                ]);

            case 'csv':
                $csvData = $this->generateCSV($logs);
                return response($csvData)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="security_logs_' . now()->format('Y-m-d_H-i-s') . '.csv"');

            case 'pdf':
                // Aquí implementarías la generación de PDF
                return response()->json([
                    'success' => false,
                    'message' => 'Exportación PDF no implementada aún'
                ], 501);
        }
    }

    /**
     * Obtener estadísticas de eventos de seguridad
     */
    public function stats(Request $request)
    {
        $this->authorize('viewSecurityLogs');

        $timeRange = $request->time_range ?? '30d';
        $startDate = $this->getStartDateFromRange($timeRange);

        $stats = [
            'overview' => [
                'total_events' => SecurityLog::where('created_at', '>=', $startDate)->count(),
                'events_by_risk' => SecurityLog::where('created_at', '>=', $startDate)
                    ->selectRaw('nivel_riesgo, COUNT(*) as count')
                    ->groupBy('nivel_riesgo')
                    ->pluck('count', 'nivel_riesgo'),
                'resolved_percentage' => $this->getResolvedPercentage($startDate),
                'avg_resolution_time' => $this->getAverageResolutionTime($startDate)
            ],
            'trends' => [
                'daily_average' => SecurityLog::where('created_at', '>=', $startDate)
                    ->count() / max(1, now()->diffInDays($startDate)),
                'growth_rate' => $this->getGrowthRate($startDate),
                'peak_hours' => $this->getPeakHours($startDate)
            ],
            'top_events' => SecurityLog::where('created_at', '>=', $startDate)
                ->selectRaw('evento, COUNT(*) as count')
                ->groupBy('evento')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('count', 'evento'),
            'geographic_distribution' => $this->getGeographicDistribution($startDate),
            'user_impact' => [
                'affected_users' => SecurityLog::where('created_at', '>=', $startDate)
                    ->whereNotNull('usuario_id')
                    ->distinct('usuario_id')
                    ->count(),
                'repeat_offenders' => $this->getRepeatOffenders($startDate)
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'meta' => [
                'time_range' => $timeRange,
                'start_date' => $startDate,
                'generated_at' => now()
            ]
        ]);
    }

    /**
     * Buscar patrones sospechosos
     */
    public function detectPatterns(Request $request)
    {
        $this->authorize('viewSecurityLogs');

        $timeRange = $request->time_range ?? '24h';
        $startDate = $this->getStartDateFromRange($timeRange);

        $patterns = [
            'brute_force_attempts' => $this->detectBruteForce($startDate),
            'suspicious_ip_activity' => $this->detectSuspiciousIPs($startDate),
            'privilege_escalation' => $this->detectPrivilegeEscalation($startDate),
            'unusual_access_patterns' => $this->detectUnusualAccess($startDate),
            'failed_login_patterns' => $this->detectFailedLoginPatterns($startDate)
        ];

        // Log detección de patrones
        SecurityLog::create([
            'evento' => 'pattern_detection_run',
            'usuario_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'time_range' => $timeRange,
                'patterns_found' => array_sum(array_map('count', $patterns))
            ]),
            'nivel_riesgo' => 'low'
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'patterns' => $patterns,
                'summary' => [
                    'total_patterns' => array_sum(array_map('count', $patterns)),
                    'critical_patterns' => count($patterns['brute_force_attempts']) + count($patterns['privilege_escalation']),
                    'time_range' => $timeRange
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

    private function getIPInfo($ip)
    {
        // Implementación básica - en producción usarías un servicio real
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return ['country' => 'Local', 'city' => 'Localhost'];
        }
        
        // Aquí integrarías con un servicio como IPInfo, MaxMind, etc.
        return ['country' => 'Unknown', 'city' => 'Unknown'];
    }

    private function parseUserAgent($userAgent)
    {
        // Parsing básico del User Agent
        $info = [
            'browser' => 'Unknown',
            'platform' => 'Unknown',
            'device' => 'Desktop'
        ];

        if (str_contains($userAgent, 'Mobile')) {
            $info['device'] = 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet')) {
            $info['device'] = 'Tablet';
        }

        return $info;
    }

    private function calculateRiskScore($log)
    {
        $score = 0;
        
        switch ($log->nivel_riesgo) {
            case 'critical': $score += 100; break;
            case 'high': $score += 75; break;
            case 'medium': $score += 50; break;
            case 'low': $score += 25; break;
        }

        // Factores adicionales
        if (str_contains($log->evento, 'failed')) $score += 10;
        if (str_contains($log->evento, 'blocked')) $score += 15;
        if (str_contains($log->evento, 'suspicious')) $score += 20;

        return min($score, 100);
    }

    private function generateSecurityAlerts($startDate)
    {
        $alerts = [];

        // Alertas de múltiples fallos de login
        $failedLogins = SecurityLog::where('created_at', '>=', $startDate)
            ->where('evento', 'login_failed')
            ->selectRaw('ip_address, COUNT(*) as count')
            ->groupBy('ip_address')
            ->having('count', '>', 10)
            ->get();

        foreach ($failedLogins as $login) {
            $alerts[] = [
                'type' => 'multiple_failed_logins',
                'severity' => 'high',
                'message' => "IP {$login->ip_address} ha tenido {$login->count} intentos fallidos de login",
                'ip_address' => $login->ip_address,
                'count' => $login->count
            ];
        }

        // Alertas de cuentas bloqueadas
        $blockedAccounts = SecurityLog::where('created_at', '>=', $startDate)
            ->where('evento', 'account_auto_locked')
            ->count();

        if ($blockedAccounts > 5) {
            $alerts[] = [
                'type' => 'multiple_account_blocks',
                'severity' => 'critical',
                'message' => "{$blockedAccounts} cuentas han sido bloqueadas automáticamente",
                'count' => $blockedAccounts
            ];
        }

        return $alerts;
    }

    private function generateCSV($logs)
    {
        $headers = [
            'ID', 'Evento', 'Usuario', 'Email', 'IP Address', 'User Agent',
            'Nivel Riesgo', 'Resuelto', 'Fecha Creación', 'Detalles'
        ];

        $csv = implode(',', $headers) . "\n";

        foreach ($logs as $log) {
            $row = [
                $log->id,
                $log->evento,
                $log->usuario?->username ?? 'N/A',
                $log->usuario?->email ?? 'N/A',
                $log->ip_address,
                str_replace(',', ';', $log->user_agent),
                $log->nivel_riesgo,
                $log->resuelto ? 'Sí' : 'No',
                $log->created_at->format('Y-m-d H:i:s'),
                str_replace(',', ';', $log->detalles)
            ];

            $csv .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }

        return $csv;
    }

    private function getResolvedPercentage($startDate)
    {
        $total = SecurityLog::where('created_at', '>=', $startDate)->count();
        if ($total === 0) return 0;
        
        $resolved = SecurityLog::where('created_at', '>=', $startDate)
            ->where('resuelto', true)
            ->count();
            
        return round(($resolved / $total) * 100, 2);
    }

    private function getAverageResolutionTime($startDate)
    {
        $resolvedLogs = SecurityLog::where('created_at', '>=', $startDate)
            ->where('resuelto', true)
            ->whereNotNull('resuelto_en')
            ->get();

        if ($resolvedLogs->isEmpty()) return 0;

        $totalMinutes = $resolvedLogs->sum(function($log) {
            return $log->created_at->diffInMinutes($log->resuelto_en);
        });

        return round($totalMinutes / $resolvedLogs->count(), 2);
    }

    private function getGrowthRate($startDate)
    {
        $currentPeriod = SecurityLog::where('created_at', '>=', $startDate)->count();
        $previousStart = $startDate->copy()->sub(now()->diff($startDate));
        $previousPeriod = SecurityLog::where('created_at', '>=', $previousStart)
            ->where('created_at', '<', $startDate)
            ->count();

        if ($previousPeriod === 0) return $currentPeriod > 0 ? 100 : 0;
        
        return round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 2);
    }

    private function getPeakHours($startDate)
    {
        return SecurityLog::where('created_at', '>=', $startDate)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->pluck('count', 'hour');
    }

    private function getGeographicDistribution($startDate)
    {
        $ips = SecurityLog::where('created_at', '>=', $startDate)
            ->selectRaw('ip_address, COUNT(*) as count')
            ->groupBy('ip_address')
            ->orderBy('count', 'desc')
            ->limit(20)
            ->get();

        return $ips->map(function($ip) {
            return [
                'ip' => $ip->ip_address,
                'count' => $ip->count,
                'location' => $this->getIPInfo($ip->ip_address)
            ];
        });
    }

    private function getRepeatOffenders($startDate)
    {
        return SecurityLog::where('created_at', '>=', $startDate)
            ->whereNotNull('usuario_id')
            ->whereIn('nivel_riesgo', ['high', 'critical'])
            ->with('usuario:id,username,email')
            ->selectRaw('usuario_id, COUNT(*) as count')
            ->groupBy('usuario_id')
            ->having('count', '>', 3)
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }

    private function detectBruteForce($startDate)
    {
        return SecurityLog::where('created_at', '>=', $startDate)
            ->where('evento', 'login_failed')
            ->selectRaw('ip_address, usuario_id, COUNT(*) as attempts')
            ->groupBy('ip_address', 'usuario_id')
            ->having('attempts', '>', 5)
            ->with('usuario:id,username,email')
            ->get();
    }

    private function detectSuspiciousIPs($startDate)
    {
        return SecurityLog::where('created_at', '>=', $startDate)
            ->selectRaw('ip_address, COUNT(DISTINCT evento) as different_events, COUNT(*) as total_events')
            ->groupBy('ip_address')
            ->having('different_events', '>', 5)
            ->orHaving('total_events', '>', 50)
            ->get();
    }

    private function detectPrivilegeEscalation($startDate)
    {
        return SecurityLog::where('created_at', '>=', $startDate)
            ->whereIn('evento', ['role_assigned', 'permission_granted', 'user_elevated'])
            ->with('usuario:id,username,email')
            ->get();
    }

    private function detectUnusualAccess($startDate)
    {
        // Detectar accesos fuera del horario habitual
        return SecurityLog::where('created_at', '>=', $startDate)
            ->where('evento', 'login_success')
            ->whereRaw('HOUR(created_at) < 6 OR HOUR(created_at) > 22')
            ->with('usuario:id,username,email')
            ->get();
    }

    private function detectFailedLoginPatterns($startDate)
    {
        return SecurityLog::where('created_at', '>=', $startDate)
            ->where('evento', 'login_failed')
            ->selectRaw('usuario_id, ip_address, COUNT(*) as attempts, MIN(created_at) as first_attempt, MAX(created_at) as last_attempt')
            ->groupBy('usuario_id', 'ip_address')
            ->having('attempts', '>', 3)
            ->with('usuario:id,username,email')
            ->get();
    }
}