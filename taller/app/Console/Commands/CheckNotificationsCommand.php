<?php

// app/Console/Commands/CheckNotificationsCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Models\Setting;

class CheckNotificationsCommand extends Command
{
    protected $signature = 'notifications:check 
                           {--stock : Verificar solo stock bajo}
                           {--passwords : Verificar solo contraseñas expiradas}
                           {--force : Enviar notificaciones aunque ya se hayan enviado hoy}';

    protected $description = 'Verifica y envía notificaciones automáticas del sistema';

    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        $this->info("🔔 Verificando notificaciones automáticas...");

        $resultados = [];

        // Verificar stock bajo si está habilitado
        if ($this->shouldCheckStock()) {
            $resultados['stock'] = $this->verificarStockBajo();
        }

        // Verificar contraseñas expiradas
        if ($this->shouldCheckPasswords()) {
            $resultados['passwords'] = $this->verificarPasswordsExpiradas();
        }

        // Mostrar resumen
        $this->mostrarResumen($resultados);

        return 0;
    }

    private function shouldCheckStock()
    {
        $habilitado = Setting::get('inventario_alertas_stock_bajo', true);
        $soloStock = $this->option('stock');
        $noEspecifico = !$this->option('stock') && !$this->option('passwords');

        return $habilitado && ($soloStock || $noEspecifico);
    }

    private function shouldCheckPasswords()
    {
        $soloPasswords = $this->option('passwords');
        $noEspecifico = !$this->option('stock') && !$this->option('passwords');

        return $soloPasswords || $noEspecifico;
    }

    private function verificarStockBajo()
    {
        $this->line("📦 Verificando productos con stock bajo...");

        try {
            $notificacionesEnviadas = $this->notificationService->verificarYNotificarStockBajo();
            
            if ($notificacionesEnviadas > 0) {
                $this->info("   ✅ {$notificacionesEnviadas} notificaciones de stock bajo enviadas");
            } else {
                $this->line("   ℹ️ No hay productos con stock bajo o ya se notificaron hoy");
            }

            return [
                'exito' => true,
                'enviadas' => $notificacionesEnviadas,
                'mensaje' => "Stock bajo verificado"
            ];

        } catch (\Exception $e) {
            $this->error("   ❌ Error al verificar stock bajo: " . $e->getMessage());
            
            return [
                'exito' => false,
                'enviadas' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    private function verificarPasswordsExpiradas()
    {
        $this->line("🔒 Verificando contraseñas próximas a expirar...");

        try {
            $notificacionesEnviadas = $this->notificationService->verificarPasswordsExpiradas();
            
            if ($notificacionesEnviadas > 0) {
                $this->info("   ✅ {$notificacionesEnviadas} notificaciones de contraseñas enviadas");
            } else {
                $this->line("   ℹ️ No hay contraseñas próximas a expirar o ya se notificaron");
            }

            return [
                'exito' => true,
                'enviadas' => $notificacionesEnviadas,
                'mensaje' => "Contraseñas verificadas"
            ];

        } catch (\Exception $e) {
            $this->error("   ❌ Error al verificar contraseñas: " . $e->getMessage());
            
            return [
                'exito' => false,
                'enviadas' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    private function mostrarResumen($resultados)
    {
        $this->newLine();
        $this->info("📊 RESUMEN DE VERIFICACIÓN");
        $this->line("==========================");

        $totalEnviadas = 0;
        $totalErrores = 0;

        foreach ($resultados as $tipo => $resultado) {
            $tipoFormateado = ucfirst($tipo);
            
            if ($resultado['exito']) {
                $enviadas = $resultado['enviadas'];
                $totalEnviadas += $enviadas;
                $this->line("• {$tipoFormateado}: {$enviadas} notificaciones enviadas");
            } else {
                $totalErrores++;
                $this->line("• {$tipoFormateado}: Error - {$resultado['error']}");
            }
        }

        $this->newLine();
        if ($totalErrores > 0) {
            $this->warn("⚠️ Se encontraron {$totalErrores} errores durante la verificación");
        }
        
        $this->info("✅ Total de notificaciones enviadas: {$totalEnviadas}");
    }
}