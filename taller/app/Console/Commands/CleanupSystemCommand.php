<?php

// app/Console/Commands/CleanupSystemCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notificacion;
use App\Models\PasswordHistory;
use App\Models\UserActivity;
use App\Models\SecurityLog;
use App\Services\NotificationService;

class CleanupSystemCommand extends Command
{
    protected $signature = 'system:cleanup 
                           {--days=90 : Días de antigüedad para limpiar}
                           {--notifications : Limpiar solo notificaciones}
                           {--passwords : Limpiar solo historial de contraseñas}
                           {--activities : Limpiar solo actividades de usuarios}
                           {--security : Limpiar solo logs de seguridad}
                           {--all : Limpiar todo}
                           {--dry-run : Mostrar qué se limpiaría sin ejecutar}';

    protected $description = 'Limpia datos antiguos del sistema para optimizar rendimiento';

    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $dias = $this->option('days');
        $dryRun = $this->option('dry-run');
        
        $this->info("🧹 Iniciando limpieza del sistema (datos de {$dias} días o más)");
        
        if ($dryRun) {
            $this->warn("🔍 MODO DRY-RUN: Solo se mostrarán los datos que se eliminarían");
        }

        $resultados = [];

        // Determinar qué limpiar
        $limpiarTodo = $this->option('all') || (!$this->option('notifications') && 
                                               !$this->option('passwords') && 
                                               !$this->option('activities') && 
                                               !$this->option('security'));

        if ($limpiarTodo || $this->option('notifications')) {
            $resultados['notificaciones'] = $this->limpiarNotificaciones($dias, $dryRun);
        }

        if ($limpiarTodo || $this->option('passwords')) {
            $resultados['passwords'] = $this->limpiarPasswordHistory($dias, $dryRun);
        }

        if ($limpiarTodo || $this->option('activities')) {
            $resultados['actividades'] = $this->limpiarUserActivities($dias, $dryRun);
        }

        if ($limpiarTodo || $this->option('security')) {
            $resultados['security_logs'] = $this->limpiarSecurityLogs($dias, $dryRun);
        }

        // Mostrar resumen
        $this->mostrarResumen($resultados, $dryRun);

        if (!$dryRun) {
            $this->info("✅ Limpieza completada exitosamente");
            
            // Notificar a administradores
            $this->notificarLimpiezaCompletada($resultados);
        } else {
            $this->info("ℹ️ Ejecuta sin --dry-run para realizar la limpieza");
        }

        return 0;
    }

    private function limpiarNotificaciones($dias, $dryRun)
    {
        $this->line("📢 Procesando notificaciones...");
        
        $query = Notificacion::where('fecha_creacion', '<', now()->subDays($dias))
                             ->where('leida', true)
                             ->where('resuelta', true);

        $count = $query->count();
        
        if ($dryRun) {
            $this->info("   Se eliminarían {$count} notificaciones");
            return ['eliminadas' => 0, 'candidatas' => $count];
        }

        $eliminadas = $query->delete();
        $this->info("   ✅ {$eliminadas} notificaciones eliminadas");
        
        return ['eliminadas' => $eliminadas, 'candidatas' => $count];
    }

    private function limpiarPasswordHistory($dias, $dryRun)
    {
        $this->line("🔒 Procesando historial de contraseñas...");
        
        if ($dryRun) {
            $count = PasswordHistory::where('created_at', '<', now()->subDays($dias))->count();
            $this->info("   Se eliminarían {$count} registros de historial");
            return ['eliminadas' => 0, 'candidatas' => $count];
        }

        $eliminadas = PasswordHistory::limpiarHistorialGlobal($dias);
        $this->info("   ✅ {$eliminadas} registros de historial eliminados");
        
        return ['eliminadas' => $eliminadas, 'candidatas' => $eliminadas];
    }

    private function limpiarUserActivities($dias, $dryRun)
    {
        $this->line("👥 Procesando actividades de usuarios...");
        
        if ($dryRun) {
            $count = UserActivity::where('created_at', '<', now()->subDays($dias))->count();
            $this->info("   Se eliminarían {$count} registros de actividad");
            return ['eliminadas' => 0, 'candidatas' => $count];
        }

        $eliminadas = UserActivity::limpiarActividades($dias);
        $this->info("   ✅ {$eliminadas} registros de actividad eliminados");
        
        return ['eliminadas' => $eliminadas, 'candidatas' => $eliminadas];
    }

    private function limpiarSecurityLogs($dias, $dryRun)
    {
        $this->line("🛡️ Procesando logs de seguridad...");
        
        $query = SecurityLog::where('created_at', '<', now()->subDays($dias))
                            ->where('severity', '!=', SecurityLog::SEVERITY_CRITICAL);

        $count = $query->count();
        
        if ($dryRun) {
            $this->info("   Se eliminarían {$count} logs de seguridad (manteniendo críticos)");
            return ['eliminadas' => 0, 'candidatas' => $count];
        }

        $eliminadas = $query->delete();
        $this->info("   ✅ {$eliminadas} logs de seguridad eliminados (críticos mantenidos)");
        
        return ['eliminadas' => $eliminadas, 'candidatas' => $count];
    }

    private function mostrarResumen($resultados, $dryRun)
    {
        $this->newLine();
        $this->info("📊 RESUMEN DE LIMPIEZA");
        $this->line("======================");

        $totalEliminadas = 0;
        $totalCandidatas = 0;

        foreach ($resultados as $tipo => $data) {
            $eliminadas = $data['eliminadas'];
            $candidatas = $data['candidatas'];
            
            $totalEliminadas += $eliminadas;
            $totalCandidatas += $candidatas;

            $tipoFormateado = ucfirst(str_replace('_', ' ', $tipo));
            
            if ($dryRun) {
                $this->line("• {$tipoFormateado}: {$candidatas} registros candidatos");
            } else {
                $this->line("• {$tipoFormateado}: {$eliminadas} registros eliminados");
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->warn("TOTAL: {$totalCandidatas} registros serían eliminados");
        } else {
            $this->info("TOTAL: {$totalEliminadas} registros eliminados");
        }

        // Mostrar espacio liberado estimado
        $espacioEstimado = $this->calcularEspacioLiberado($totalEliminadas);
        $this->line("Espacio estimado liberado: {$espacioEstimado}");
    }

    private function calcularEspacioLiberado($registros)
    {
        // Estimación aproximada: 1KB por registro promedio
        $bytesLiberados = $registros * 1024;
        
        if ($bytesLiberados < 1024 * 1024) {
            return round($bytesLiberados / 1024, 2) . ' KB';
        } elseif ($bytesLiberados < 1024 * 1024 * 1024) {
            return round($bytesLiberados / (1024 * 1024), 2) . ' MB';
        } else {
            return round($bytesLiberados / (1024 * 1024 * 1024), 2) . ' GB';
        }
    }

    private function notificarLimpiezaCompletada($resultados)
    {
        $totalEliminados = array_sum(array_column($resultados, 'eliminadas'));
        
        if ($totalEliminados > 0) {
            $this->notificationService->enviarAPorRoles(
                ['Super Admin', 'Gerente'],
                \App\Models\Notificacion::TIPO_SISTEMA,
                'Limpieza del Sistema Completada',
                "Se completó la limpieza automática del sistema. Total de registros eliminados: {$totalEliminados}",
                [
                    'prioridad' => \App\Models\Notificacion::PRIORIDAD_BAJA,
                    'entidad' => 'sistema'
                ]
            );
        }
    }
}