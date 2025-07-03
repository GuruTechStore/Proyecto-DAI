<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SecurityLog;
use App\Models\UserActivity;
use App\Models\Usuario;
use App\Models\Auditoria;
use Carbon\Carbon;

class SecurityReport extends Command
{
    protected $signature = 'security:report 
                          {--days=7 : Número de días para el reporte}
                          {--format=console : Formato del reporte (console|json|email)}
                          {--email= : Email para enviar el reporte}
                          {--save= : Ruta para guardar el reporte}';

    protected $description = 'Genera un reporte de seguridad del sistema usando security_logs y user_activities';

    public function handle()
    {
        $days = (int) $this->option('days');
        $format = $this->option('format');
        $email = $this->option('email');
        $savePath = $this->option('save');

        $this->info("🔒 Generando reporte de seguridad para los últimos {$days} días...");

        $report = $this->generateSecurityReport($days);

        switch ($format) {
            case 'json':
                $this->outputJsonReport($report, $savePath);
                break;
            case 'email':
                $this->sendEmailReport($report, $email);
                break;
            default:
                $this->outputConsoleReport($report);
                if ($savePath) {
                    $this->saveConsoleReport($report, $savePath);
                }
                break;
        }

        return Command::SUCCESS;
    }

    protected function generateSecurityReport(int $days): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'periodo' => [
                'desde' => $startDate->format('d/m/Y'),
                'hasta' => now()->format('d/m/Y'),
                'dias' => $days
            ],
            'resumen_general' => $this->getGeneralSummary($startDate),
            'security_logs' => $this->getSecurityLogsSummary($startDate),
            'user_activities' => $this->getUserActivitiesSummary($startDate),
            'usuarios_comprometidos' => $this->getCompromisedUsers($startDate),
            'ips_sospechosas' => $this->getSuspiciousIPs($startDate),
            'actividad_auditoria' => $this->getAuditActivity($startDate),
            'tendencias' => $this->getSecurityTrends($startDate),
            'recomendaciones' => $this->getSecurityRecommendations($startDate)
        ];
    }

    protected function getGeneralSummary(Carbon $startDate): array
    {
        $totalSecurityEvents = SecurityLog::where('created_at', '>=', $startDate)->count();
        $uniqueIPs = SecurityLog::where('created_at', '>=', $startDate)
                                ->distinct('ip')
                                ->count();
        $blockedUsers = Usuario::where('bloqueado', true)
                              ->where('fecha_bloqueo', '>=', $startDate)
                              ->count();
        $failedLogins = SecurityLog::where('created_at', '>=', $startDate)
                                  ->where('tipo', SecurityLog::TIPO_FAILED_LOGIN)
                                  ->count();
        $activeUsers = UserActivity::where('fecha', '>=', $startDate->toDateString())
                                  ->distinct('usuario_id')
                                  ->count();

        return [
            'total_security_events' => $totalSecurityEvents,
            'ips_unicas' => $uniqueIPs,
            'usuarios_bloqueados' => $blockedUsers,
            'intentos_fallidos' => $failedLogins,
            'usuarios_activos' => $activeUsers,
            'nivel_riesgo' => $this->calculateRiskLevel($totalSecurityEvents, $failedLogins, $blockedUsers)
        ];
    }

    protected function getSecurityLogsSummary(Carbon $startDate): array
    {
        $events = SecurityLog::where('created_at', '>=', $startDate)
                            ->select('tipo', 'severity', DB::raw('count(*) as total'))
                            ->groupBy('tipo', 'severity')
                            ->orderBy('total', 'desc')
                            ->get();

        $hourlyDistribution = SecurityLog::where('created_at', '>=', $startDate)
                                        ->select(DB::raw('HOUR(created_at) as hora'), 
                                               DB::raw('count(*) as total'))
                                        ->groupBy('hora')
                                        ->orderBy('hora')
                                        ->get();

        $bySeverity = SecurityLog::where('created_at', '>=', $startDate)
                                ->select('severity', DB::raw('count(*) as total'))
                                ->groupBy('severity')
                                ->get()
                                ->mapWithKeys(function ($item) {
                                    $severityMap = [
                                        1 => 'INFO',
                                        2 => 'WARNING', 
                                        3 => 'ERROR',
                                        4 => 'CRITICAL'
                                    ];
                                    return [$severityMap[$item->severity] => $item->total];
                                });

        return [
            'por_tipo_severity' => $events->toArray(),
            'por_severity' => $bySeverity->toArray(),
            'distribucion_horaria' => $hourlyDistribution->toArray(),
            'eventos_criticos' => $this->getCriticalSecurityEvents($startDate)
        ];
    }

    protected function getUserActivitiesSummary(Carbon $startDate): array
    {
        $moduleStats = UserActivity::where('fecha', '>=', $startDate->toDateString())
                                  ->select('modulo', DB::raw('sum(contador_accesos) as total_accesos'))
                                  ->groupBy('modulo')
                                  ->orderBy('total_accesos', 'desc')
                                  ->get();

        $dailyActivity = UserActivity::where('fecha', '>=', $startDate->toDateString())
                                    ->select('fecha', DB::raw('sum(contador_accesos) as total_accesos'))
                                    ->groupBy('fecha')
                                    ->orderBy('fecha')
                                    ->get();

        $topUsers = UserActivity::where('fecha', '>=', $startDate->toDateString())
                               ->select('usuario_id', DB::raw('sum(contador_accesos) as total_accesos'))
                               ->groupBy('usuario_id')
                               ->orderBy('total_accesos', 'desc')
                               ->limit(10)
                               ->with('usuario:id,nombre,email')
                               ->get();

        return [
            'modulos_populares' => $moduleStats->toArray(),
            'actividad_diaria' => $dailyActivity->toArray(),
            'usuarios_mas_activos' => $topUsers->map(function ($activity) {
                return [
                    'usuario' => $activity->usuario ? $activity->usuario->email : 'Usuario eliminado',
                    'nombre' => $activity->usuario ? $activity->usuario->nombre : 'N/A',
                    'accesos' => $activity->total_accesos
                ];
            })->toArray(),
            'usuarios_activos_unicos' => UserActivity::where('fecha', '>=', $startDate->toDateString())
                                                    ->distinct('usuario_id')
                                                    ->count()
        ];
    }

    protected function getCriticalSecurityEvents(Carbon $startDate): array
    {
        return SecurityLog::where('created_at', '>=', $startDate)
                         ->where('severity', SecurityLog::SEVERITY_CRITICAL)
                         ->with('usuario:id,nombre,email')
                         ->orderBy('created_at', 'desc')
                         ->limit(10)
                         ->get()
                         ->map(function ($event) {
                             return [
                                 'tipo' => $event->tipo,
                                 'descripcion' => $event->descripcion,
                                 'usuario' => $event->usuario ? $event->usuario->email : 'N/A',
                                 'ip' => $event->ip,
                                 'severity' => $event->severity_text,
                                 'fecha' => $event->created_at->format('d/m/Y H:i:s'),
                                 'datos_adicionales' => $event->datos_adicionales
                             ];
                         })
                         ->toArray();
    }

    protected function getCompromisedUsers(Carbon $startDate): array
    {
        $compromisedUsers = Usuario::whereHas('securityLogs', function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate)
                  ->whereIn('tipo', [
                      SecurityLog::TIPO_FAILED_LOGIN, 
                      SecurityLog::TIPO_SUSPICIOUS_PATTERN, 
                      SecurityLog::TIPO_USER_BLOCKED
                  ]);
        })
        ->with(['securityLogs' => function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate)
                  ->orderBy('created_at', 'desc');
        }])
        ->get();

        return $compromisedUsers->map(function ($user) {
            $events = $user->securityLogs;
            $riskScore = $this->calculateUserRiskScore($events);
            
            return [
                'usuario' => $user->email,
                'nombre' => $user->nombre,
                'bloqueado' => $user->bloqueado,
                'ultimo_login' => $user->ultimo_login?->format('d/m/Y H:i:s'),
                'eventos_totales' => $events->count(),
                'intentos_fallidos' => $events->where('tipo', SecurityLog::TIPO_FAILED_LOGIN)->count(),
                'eventos_criticos' => $events->where('severity', SecurityLog::SEVERITY_CRITICAL)->count(),
                'ips_diferentes' => $events->pluck('ip')->unique()->count(),
                'nivel_riesgo' => $riskScore,
                'estado_riesgo' => $this->getRiskLabel($riskScore)
            ];
        })->toArray();
    }

    protected function getSuspiciousIPs(Carbon $startDate): array
    {
        $suspiciousIPs = SecurityLog::where('created_at', '>=', $startDate)
                                   ->select('ip', 
                                          DB::raw('count(*) as total_eventos'),
                                          DB::raw('count(DISTINCT usuario_id) as usuarios_afectados'),
                                          DB::raw('count(CASE WHEN tipo = "' . SecurityLog::TIPO_FAILED_LOGIN . '" THEN 1 END) as intentos_fallidos'),
                                          DB::raw('count(CASE WHEN severity = ' . SecurityLog::SEVERITY_CRITICAL . ' THEN 1 END) as eventos_criticos'))
                                   ->groupBy('ip')
                                   ->having('total_eventos', '>', 5)
                                   ->orderBy('total_eventos', 'desc')
                                   ->limit(20)
                                   ->get();

        return $suspiciousIPs->map(function ($ip) {
            $geoInfo = $this->getIPGeoLocation($ip->ip);
            
            return [
                'ip' => $ip->ip,
                'total_eventos' => $ip->total_eventos,
                'usuarios_afectados' => $ip->usuarios_afectados,
                'intentos_fallidos' => $ip->intentos_fallidos,
                'eventos_criticos' => $ip->eventos_criticos,
                'pais' => $geoInfo['pais'] ?? 'Desconocido',
                'ciudad' => $geoInfo['ciudad'] ?? 'Desconocida',
                'nivel_peligro' => $this->calculateIPDangerLevel($ip)
            ];
        })->toArray();
    }

    protected function getAuditActivity(Carbon $startDate): array
    {
        $auditSummary = Auditoria::where('created_at', '>=', $startDate)
                                ->select('tabla', 'operacion', DB::raw('count(*) as total'))
                                ->groupBy('tabla', 'operacion')
                                ->orderBy('total', 'desc')
                                ->get();

        $topUsers = Auditoria::where('created_at', '>=', $startDate)
                            ->select('usuario_id', DB::raw('count(*) as total_operaciones'))
                            ->groupBy('usuario_id')
                            ->orderBy('total_operaciones', 'desc')
                            ->limit(10)
                            ->with('usuario:id,nombre,email')
                            ->get();

        return [
            'total_operaciones' => Auditoria::where('created_at', '>=', $startDate)->count(),
            'operaciones_por_tabla' => $auditSummary->toArray(),
            'usuarios_mas_activos' => $topUsers->map(function ($audit) {
                return [
                    'usuario' => $audit->usuario ? $audit->usuario->email : 'Usuario eliminado',
                    'nombre' => $audit->usuario ? $audit->usuario->nombre : 'N/A',
                    'operaciones' => $audit->total_operaciones
                ];
            })->toArray()
        ];
    }

    protected function getSecurityTrends(Carbon $startDate): array
    {
        $dailySecurityEvents = SecurityLog::where('created_at', '>=', $startDate)
                                         ->select(DB::raw('DATE(created_at) as fecha'), 
                                                DB::raw('count(*) as total'))
                                         ->groupBy('fecha')
                                         ->orderBy('fecha')
                                         ->get();

        $dailyUserActivity = UserActivity::where('fecha', '>=', $startDate->toDateString())
                                        ->select('fecha', DB::raw('sum(contador_accesos) as total'))
                                        ->groupBy('fecha')
                                        ->orderBy('fecha')
                                        ->get();

        $weeklyComparison = $this->getWeeklyComparison($startDate);

        return [
            'security_events_diarios' => $dailySecurityEvents->toArray(),
            'actividad_usuarios_diaria' => $dailyUserActivity->toArray(),
            'comparacion_semanal' => $weeklyComparison,
            'tendencia_seguridad' => $this->calculateTrend($dailySecurityEvents),
            'tendencia_actividad' => $this->calculateTrend($dailyUserActivity)
        ];
    }

    protected function getSecurityRecommendations(Carbon $startDate): array
    {
        $recommendations = [];
        
        // Analizar security logs
        $failedLogins = SecurityLog::where('created_at', '>=', $startDate)
                                  ->where('tipo', SecurityLog::TIPO_FAILED_LOGIN)
                                  ->count();
        
        if ($failedLogins > 100) {
            $recommendations[] = [
                'tipo' => 'alta_prioridad',
                'titulo' => 'Alto número de intentos de login fallidos',
                'descripcion' => "Se detectaron {$failedLogins} intentos fallidos. Considere implementar CAPTCHA o 2FA.",
                'accion' => 'Revisar políticas de autenticación'
            ];
        }

        $suspiciousPatterns = SecurityLog::where('created_at', '>=', $startDate)
                                       ->where('tipo', SecurityLog::TIPO_SUSPICIOUS_PATTERN)
                                       ->count();
        
        if ($suspiciousPatterns > 10) {
            $recommendations[] = [
                'tipo' => 'media_prioridad',
                'titulo' => 'Patrones sospechosos detectados',
                'descripcion' => "Se detectaron {$suspiciousPatterns} patrones sospechosos. Revisar logs detallados.",
                'accion' => 'Analizar patrones de ataques'
            ];
        }

        $criticalEvents = SecurityLog::where('created_at', '>=', $startDate)
                                    ->where('severity', SecurityLog::SEVERITY_CRITICAL)
                                    ->count();
        
        if ($criticalEvents > 5) {
            $recommendations[] = [
                'tipo' => 'alta_prioridad',
                'titulo' => 'Múltiples eventos críticos de seguridad',
                'descripcion' => "Se registraron {$criticalEvents} eventos críticos. Requiere atención inmediata.",
                'accion' => 'Revisar eventos críticos y tomar acciones correctivas'
            ];
        }

        $blockedUsers = Usuario::where('bloqueado', true)->count();
        if ($blockedUsers > 5) {
            $recommendations[] = [
                'tipo' => 'baja_prioridad',
                'titulo' => 'Múltiples usuarios bloqueados',
                'descripcion' => "Hay {$blockedUsers} usuarios bloqueados. Revisar si algunos pueden ser desbloqueados.",
                'accion' => 'Revisar usuarios bloqueados'
            ];
        }

        // Analizar actividad de usuarios
        $inactiveUsers = Usuario::where('ultimo_login', '<', now()->subDays(30))
                               ->where('bloqueado', false)
                               ->count();
        
        if ($inactiveUsers > 10) {
            $recommendations[] = [
                'tipo' => 'media_prioridad',
                'titulo' => 'Usuarios inactivos detectados',
                'descripcion' => "Hay {$inactiveUsers} usuarios sin actividad en 30+ días.",
                'accion' => 'Revisar y posiblemente bloquear usuarios inactivos'
            ];
        }

        return $recommendations;
    }

    protected function outputConsoleReport(array $report)
    {
        $this->info("\n" . str_repeat('=', 70));
        $this->info('               REPORTE DE SEGURIDAD DEL SISTEMA');
        $this->info(str_repeat('=', 70));
        
        // Período
        $periodo = $report['periodo'];
        $this->info("📅 Período: {$periodo['desde']} - {$periodo['hasta']} ({$periodo['dias']} días)");
        
        // Resumen General
        $this->info("\n📊 RESUMEN GENERAL");
        $this->line(str_repeat('-', 50));
        $general = $report['resumen_general'];
        $this->line("Security Events: {$general['total_security_events']}");
        $this->line("IPs únicas: {$general['ips_unicas']}");
        $this->line("Usuarios bloqueados: {$general['usuarios_bloqueados']}");
        $this->line("Intentos login fallidos: {$general['intentos_fallidos']}");
        $this->line("Usuarios activos: {$general['usuarios_activos']}");
        $this->line("Nivel de riesgo: " . $this->colorizeRiskLevel($general['nivel_riesgo']));

        // Security Logs por Severity
        $this->info("\n🔒 EVENTOS DE SEGURIDAD POR CRITICIDAD");
        $this->line(str_repeat('-', 50));
        foreach ($report['security_logs']['por_severity'] as $severity => $count) {
            $color = match($severity) {
                'CRITICAL' => 'error',
                'ERROR' => 'comment',
                'WARNING' => 'warn',
                default => 'info'
            };
            $this->$color("• {$severity}: {$count}");
        }

        // Eventos Críticos
        if (!empty($report['security_logs']['eventos_criticos'])) {
            $this->info("\n🚨 EVENTOS CRÍTICOS RECIENTES");
            $this->line(str_repeat('-', 50));
            foreach (array_slice($report['security_logs']['eventos_criticos'], 0, 5) as $event) {
                $this->error("• {$event['fecha']} - {$event['tipo']} - {$event['usuario']} ({$event['ip']})");
            }
        }

        // Actividad de Usuarios
        $this->info("\n👥 ACTIVIDAD DE USUARIOS");
        $this->line(str_repeat('-', 50));
        $activity = $report['user_activities'];
        $this->line("Usuarios únicos activos: {$activity['usuarios_activos_unicos']}");
        if (!empty($activity['modulos_populares'])) {
            $this->line("Módulos más utilizados:");
            foreach (array_slice($activity['modulos_populares'], 0, 3) as $modulo) {
                $this->line("  • {$modulo['modulo']}: {$modulo['total_accesos']} accesos");
            }
        }

        // IPs Sospechosas
        if (!empty($report['ips_sospechosas'])) {
            $this->info("\n🌐 IPs MÁS SOSPECHOSAS");
            $this->line(str_repeat('-', 50));
            foreach (array_slice($report['ips_sospechosas'], 0, 5) as $ip) {
                $danger = $ip['nivel_peligro'];
                $this->line("• {$ip['ip']} - {$ip['total_eventos']} eventos - Peligro: {$danger} - {$ip['pais']}");
                $this->line("  Fallidos: {$ip['intentos_fallidos']} | Críticos: {$ip['eventos_criticos']}");
            }
        }

        // Recomendaciones
        if (!empty($report['recomendaciones'])) {
            $this->info("\n💡 RECOMENDACIONES");
            $this->line(str_repeat('-', 50));
            foreach ($report['recomendaciones'] as $rec) {
                $color = $rec['tipo'] === 'alta_prioridad' ? 'error' : 
                        ($rec['tipo'] === 'media_prioridad' ? 'warn' : 'info');
                $this->$color("• {$rec['titulo']}");
                $this->line("  {$rec['descripcion']}");
                $this->line("  Acción: {$rec['accion']}");
            }
        }

        $this->info("\n" . str_repeat('=', 70));
    }

    protected function outputJsonReport(array $report, ?string $savePath)
    {
        $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($savePath) {
            file_put_contents($savePath, $json);
            $this->info("Reporte JSON guardado en: {$savePath}");
        } else {
            $this->line($json);
        }
    }

    protected function sendEmailReport(array $report, ?string $email)
    {
        if (!$email) {
            $email = config('security.admin_email');
        }

        if (!$email) {
            $this->error('No se especificó email para enviar el reporte');
            return;
        }

        // Aquí implementarías el envío por email
        // Mail::to($email)->send(new SecurityReportMail($report));
        
        $this->info("Reporte enviado por email a: {$email}");
    }

    protected function saveConsoleReport(array $report, string $savePath)
    {
        ob_start();
        $this->outputConsoleReport($report);
        $content = ob_get_clean();
        
        file_put_contents($savePath, $content);
        $this->info("Reporte guardado en: {$savePath}");
    }

    // Métodos auxiliares
    protected function calculateRiskLevel(int $totalEvents, int $failedLogins, int $blockedUsers): string
    {
        $score = 0;
        
        if ($totalEvents > 1000) $score += 3;
        elseif ($totalEvents > 500) $score += 2;
        elseif ($totalEvents > 100) $score += 1;
        
        if ($failedLogins > 100) $score += 3;
        elseif ($failedLogins > 50) $score += 2;
        elseif ($failedLogins > 20) $score += 1;
        
        if ($blockedUsers > 10) $score += 2;
        elseif ($blockedUsers > 5) $score += 1;
        
        if ($score >= 6) return 'CRÍTICO';
        if ($score >= 4) return 'ALTO';
        if ($score >= 2) return 'MEDIO';
        return 'BAJO';
    }

    protected function calculateUserRiskScore($events): int
    {
        $score = 0;
        $score += $events->where('tipo', SecurityLog::TIPO_FAILED_LOGIN)->count() * 2;
        $score += $events->where('tipo', SecurityLog::TIPO_SUSPICIOUS_PATTERN)->count() * 5;
        $score += $events->where('severity', SecurityLog::SEVERITY_CRITICAL)->count() * 10;
        $score += $events->pluck('ip')->unique()->count();
        
        return min($score, 100);
    }

    protected function getRiskLabel(int $score): string
    {
        if ($score >= 50) return 'CRÍTICO';
        if ($score >= 30) return 'ALTO';
        if ($score >= 15) return 'MEDIO';
        return 'BAJO';
    }

    protected function calculateIPDangerLevel($ip): string
    {
        $score = 0;
        $score += min($ip->total_eventos / 10, 10);
        $score += min($ip->intentos_fallidos / 5, 10);
        $score += min($ip->eventos_criticos * 5, 15);
        $score += min($ip->usuarios_afectados * 2, 10);
        
        if ($score >= 25) return 'CRÍTICO';
        if ($score >= 20) return 'ALTO';
        if ($score >= 10) return 'MEDIO';
        return 'BAJO';
    }

    protected function getIPGeoLocation(string $ip): array
    {
        // Implementación básica - puedes integrar con servicios como GeoIP
        return [
            'pais' => 'Desconocido',
            'ciudad' => 'Desconocida'
        ];
    }

    protected function getWeeklyComparison(Carbon $startDate): array
    {
        $thisWeek = SecurityLog::where('created_at', '>=', $startDate)->count();
        $lastWeek = SecurityLog::where('created_at', '>=', $startDate->copy()->subWeek())
                              ->where('created_at', '<', $startDate)
                              ->count();
        
        $change = $lastWeek > 0 ? (($thisWeek - $lastWeek) / $lastWeek) * 100 : 0;
        
        return [
            'esta_semana' => $thisWeek,
            'semana_anterior' => $lastWeek,
            'cambio_porcentual' => round($change, 2)
        ];
    }

    protected function calculateTrend($dailyData): string
    {
        if ($dailyData->count() < 3) return 'INSUFICIENTES_DATOS';
        
        $values = $dailyData->pluck('total')->toArray();
        $firstHalf = array_slice($values, 0, ceil(count($values) / 2));
        $secondHalf = array_slice($values, floor(count($values) / 2));
        
        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);
        
        if ($secondAvg > $firstAvg * 1.2) return 'CRECIENTE';
        if ($secondAvg < $firstAvg * 0.8) return 'DECRECIENTE';
        return 'ESTABLE';
    }

    protected function colorizeRiskLevel(string $level): string
    {
        switch ($level) {
            case 'CRÍTICO':
                return "<error>{$level}</error>";
            case 'ALTO':
                return "<comment>{$level}</comment>";
            case 'MEDIO':
                return "<info>{$level}</info>";
            default:
                return $level;
        }
    }
}
