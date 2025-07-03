<?php

// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Verificar notificaciones automáticas cada hora
        $schedule->command('notifications:check')
                 ->hourly()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Limpieza del sistema cada domingo a las 2:00 AM
        $schedule->command('system:cleanup --days=90')
                 ->weekly()
                 ->sundays()
                 ->at('02:00')
                 ->withoutOverlapping();

        // Limpieza más agresiva cada mes (datos muy antiguos)
        $schedule->command('system:cleanup --days=365')
                 ->monthly()
                 ->withoutOverlapping();

        // Verificar solo stock bajo cada 6 horas
        $schedule->command('notifications:check --stock')
                 ->everySixHours()
                 ->withoutOverlapping();

        // Verificar contraseñas expiradas cada lunes
        $schedule->command('notifications:check --passwords')
                 ->weekly()
                 ->mondays()
                 ->at('09:00');

        // Limpiar cache de configuraciones cada día
        $schedule->call(function () {
            \App\Models\Setting::clearCache();
        })->daily()->at('03:00');

        // Backup automático si está habilitado
        $schedule->call(function () {
            $backupHabilitado = \App\Models\Setting::get('backup_automatico_habilitado', false);
            $frecuencia = \App\Models\Setting::get('backup_frecuencia', 'semanal');
            
            if ($backupHabilitado) {
                // Aquí puedes implementar tu lógica de backup
                // Por ejemplo: Artisan::call('backup:run');
                \Log::info('Backup automático ejecutado');
            }
        })->cron($this->getBackupCron());

        // Generar reportes automáticos para administradores
        $schedule->call(function () {
            $this->generarReportesDiarios();
        })->dailyAt('07:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Obtener expresión cron para backup según frecuencia configurada
     */
    private function getBackupCron()
    {
        $frecuencia = \App\Models\Setting::get('backup_frecuencia', 'semanal');
        
        return match($frecuencia) {
            'diario' => '0 1 * * *',      // Cada día a la 1:00 AM
            'semanal' => '0 1 * * 0',     // Cada domingo a la 1:00 AM
            'mensual' => '0 1 1 * *',     // El día 1 de cada mes a la 1:00 AM
            default => '0 1 * * 0'        // Por defecto semanal
        };
    }

    /**
     * Generar reportes diarios automáticos
     */
    private function generarReportesDiarios()
    {
        try {
            // Estadísticas de ventas del día anterior
            $ayer = now()->subDay();
            $ventasAyer = \App\Models\Venta::whereDate('fecha', $ayer)->count();
            $totalVentas = \App\Models\Venta::whereDate('fecha', $ayer)->sum('total');
            
            // Productos con stock bajo
            $productosStockBajo = \App\Models\Producto::stockBajo()->count();
            
            // Reparaciones pendientes
            $reparacionesPendientes = \App\Models\Reparacion::enProceso()->count();
            
            // Notificar a administradores
            $mensaje = "Reporte diario:\n";
            $mensaje .= "• Ventas de ayer: {$ventasAyer} por S/ " . number_format($totalVentas, 2) . "\n";
            $mensaje .= "• Productos con stock bajo: {$productosStockBajo}\n";
            $mensaje .= "• Reparaciones pendientes: {$reparacionesPendientes}";
            
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->enviarAPorRoles(
                ['Super Admin', 'Gerente'],
                \App\Models\Notificacion::TIPO_SISTEMA,
                'Reporte Diario del Sistema',
                $mensaje,
                [
                    'prioridad' => \App\Models\Notificacion::PRIORIDAD_BAJA,
                    'entidad' => 'sistema'
                ]
            );
            
        } catch (\Exception $e) {
            \Log::error('Error generando reporte diario: ' . $e->getMessage());
        }
    }
}