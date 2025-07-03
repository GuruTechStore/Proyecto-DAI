<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Auditoria;
use App\Models\SecurityLog;
use App\Models\UserActivity;

class CleanExpiredSessions extends Command
{
    protected $signature = 'security:clean-sessions 
                          {--dry-run : Solo mostrar qué se eliminaría sin hacerlo}
                          {--sessions-days=7 : Días para mantener sesiones}
                          {--audit-days=365 : Días para mantener auditoría}
                          {--security-days=90 : Días para mantener logs de seguridad}
                          {--activity-days=90 : Días para mantener actividad de usuarios}';

    protected $description = 'Limpia sesiones expiradas, tokens vencidos y logs antiguos';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $sessionsDays = (int) $this->option('sessions-days');
        $auditDays = (int) $this->option('audit-days');
        $securityDays = (int) $this->option('security-days');
        $activityDays = (int) $this->option('activity-days');

        $this->info('Iniciando limpieza de seguridad...');
        
        if ($isDryRun) {
            $this->warn('MODO DRY-RUN: Solo se mostrarán las estadísticas, no se eliminará nada.');
        }

        $totalCleaned = 0;

        // 1. Limpiar sesiones expiradas
        $totalCleaned += $this->cleanExpiredSessions($sessionsDays, $isDryRun);

        // 2. Revocar tokens Sanctum vencidos
        $totalCleaned += $this->cleanExpiredTokens($isDryRun);

        // 3. Limpiar logs de auditoría antiguos
        $totalCleaned += $this->cleanOldAuditLogs($auditDays, $isDryRun);

        // 4. Limpiar security logs antiguos
        $totalCleaned += $this->cleanOldSecurityLogs($securityDays, $isDryRun);

        // 5. Limpiar actividad de usuarios antigua
        $totalCleaned += $this->cleanOldUserActivity($activityDays, $isDryRun);

        // 6. Optimizar tablas
        if (!$isDryRun && $totalCleaned > 0) {
            $this->optimizeTables();
        }

        $this->info("Limpieza completada. Total de registros procesados: {$totalCleaned}");
        
        return Command::SUCCESS;
    }

    protected function cleanExpiredSessions(int $days, bool $isDryRun): int
    {
        $this->info("🧹 Limpiando sesiones expiradas (>{$days} días)...");
        
        $cutoffDate = now()->subDays($days);
        $cutoffTimestamp = $cutoffDate->timestamp;
        
        $query = DB::table('sessions')
                   ->where('last_activity', '<', $cutoffTimestamp);
        
        $count = $query->count();
        
        if ($count > 0) {
            $this->line("   Sesiones a eliminar: {$count}");
            
            if (!$isDryRun) {
                $deleted = $query->delete();
                $this->info("   ✅ Eliminadas: {$deleted} sesiones");
                return $deleted;
            }
        } else {
            $this->line("   ✅ No hay sesiones expiradas para eliminar");
        }
        
        return $count;
    }

    protected function cleanExpiredTokens(bool $isDryRun): int
    {
        $this->info("🔑 Limpiando tokens Sanctum vencidos...");
        
        $query = PersonalAccessToken::where('expires_at', '<', now())
                                   ->orWhere('last_used_at', '<', now()->subDays(30));
        
        $count = $query->count();
        
        if ($count > 0) {
            $this->line("   Tokens a eliminar: {$count}");
            
            if (!$isDryRun) {
                $deleted = $query->delete();
                $this->info("   ✅ Eliminados: {$deleted} tokens");
                return $deleted;
            }
        } else {
            $this->line("   ✅ No hay tokens vencidos para eliminar");
        }
        
        return $count;
    }

    protected function cleanOldAuditLogs(int $days, bool $isDryRun): int
    {
        $this->info("📋 Limpiando logs de auditoría antiguos (>{$days} días)...");
        
        $cutoffDate = now()->subDays($days);
        $query = Auditoria::where('created_at', '<', $cutoffDate);
        
        $count = $query->count();
        
        if ($count > 0) {
            $this->line("   Registros de auditoría a eliminar: {$count}");
            
            if (!$isDryRun) {
                $deleted = 0;
                $query->chunk(1000, function ($audits) use (&$deleted) {
                    $ids = $audits->pluck('id');
                    $deleted += Auditoria::whereIn('id', $ids)->delete();
                });
                
                $this->info("   ✅ Eliminados: {$deleted} registros de auditoría");
                return $deleted;
            }
        } else {
            $this->line("   ✅ No hay logs de auditoría antiguos para eliminar");
        }
        
        return $count;
    }

    protected function cleanOldSecurityLogs(int $days, bool $isDryRun): int
    {
        $this->info("🔒 Limpiando security logs antiguos (>{$days} días)...");
        
        $cutoffDate = now()->subDays($days);
        $query = SecurityLog::where('created_at', '<', $cutoffDate);
        
        $count = $query->count();
        
        if ($count > 0) {
            $this->line("   Security logs a eliminar: {$count}");
            
            if (!$isDryRun) {
                $deleted = 0;
                $query->chunk(1000, function ($logs) use (&$deleted) {
                    $ids = $logs->pluck('id');
                    $deleted += SecurityLog::whereIn('id', $ids)->delete();
                });
                
                $this->info("   ✅ Eliminados: {$deleted} security logs");
                return $deleted;
            }
        } else {
            $this->line("   ✅ No hay security logs antiguos para eliminar");
        }
        
        return $count;
    }

    protected function cleanOldUserActivity(int $days, bool $isDryRun): int
    {
        $this->info("👤 Limpiando actividad de usuarios antigua (>{$days} días)...");
        
        $cutoffDate = now()->subDays($days);
        $query = UserActivity::where('fecha', '<', $cutoffDate->toDateString());
        
        $count = $query->count();
        
        if ($count > 0) {
            $this->line("   Registros de actividad a eliminar: {$count}");
            
            if (!$isDryRun) {
                $deleted = $query->delete();
                $this->info("   ✅ Eliminados: {$deleted} registros de actividad");
                return $deleted;
            }
        } else {
            $this->line("   ✅ No hay actividad antigua para eliminar");
        }
        
        return $count;
    }

    protected function optimizeTables()
    {
        $this->info("🔧 Optimizando tablas...");
        
        $tables = ['sessions', 'personal_access_tokens', 'auditoria', 'security_logs', 'user_activities'];
        
        foreach ($tables as $table) {
            try {
                DB::statement("OPTIMIZE TABLE {$table}");
                $this->line("   ✅ Tabla {$table} optimizada");
            } catch (\Exception $e) {
                $this->warn("   ⚠️  Error optimizando tabla {$table}: " . $e->getMessage());
            }
        }
    }
}
