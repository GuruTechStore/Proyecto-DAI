<?php

namespace App\Traits;

use App\Models\SecurityLog;
use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSecurityLogs
{
    /**
     * Relación con Security Logs
     */
    public function securityLogs(): HasMany
    {
        return $this->hasMany(SecurityLog::class, 'usuario_id');
    }

    /**
     * Relación con User Activities
     */
    public function userActivities(): HasMany
    {
        return $this->hasMany(UserActivity::class, 'usuario_id');
    }

    /**
     * Obtener logs de seguridad por tipo
     */
    public function getSecurityLogs(array $types = [], int $days = 30)
    {
        $query = $this->securityLogs()
                     ->recientes($days)
                     ->orderBy('created_at', 'desc');

        if (!empty($types)) {
            $query->whereIn('tipo', $types);
        }

        return $query->get();
    }

    /**
     * Verificar si tiene eventos de seguridad recientes
     */
    public function hasRecentSecurityEvents(array $types = [], int $minutes = 60): bool
    {
        $query = $this->securityLogs()
                     ->where('created_at', '>=', now()->subMinutes($minutes));

        if (!empty($types)) {
            $query->whereIn('tipo', $types);
        }

        return $query->exists();
    }

    /**
     * Obtener intentos de login fallidos
     */
    public function getFailedLoginAttempts(int $hours = 24): int
    {
        return $this->securityLogs()
                    ->porTipo(SecurityLog::TIPO_FAILED_LOGIN)
                    ->where('created_at', '>=', now()->subHours($hours))
                    ->count();
    }

    /**
     * Obtener último login exitoso
     */
    public function getLastSuccessfulLogin()
    {
        return $this->securityLogs()
                    ->porTipo(SecurityLog::TIPO_SUCCESSFUL_LOGIN)
                    ->latest('created_at')
                    ->first();
    }

    /**
     * Obtener actividad reciente del usuario
     */
    public function getRecentActivity(int $days = 7)
    {
        return $this->userActivities()
                    ->recientes($days)
                    ->orderBy('ultima_actividad', 'desc')
                    ->get();
    }

    /**
     * Obtener resumen de seguridad
     */
    public function getSecuritySummary(int $days = 7): array
    {
        $securityLogs = $this->getSecurityLogs([], $days);
        $activities = $this->getRecentActivity($days);
        
        $summary = [
            'total_security_events' => $securityLogs->count(),
            'failed_logins' => $securityLogs->where('tipo', SecurityLog::TIPO_FAILED_LOGIN)->count(),
            'successful_logins' => $securityLogs->where('tipo', SecurityLog::TIPO_SUCCESSFUL_LOGIN)->count(),
            'critical_events' => $securityLogs->where('severity', SecurityLog::SEVERITY_CRITICAL)->count(),
            'unique_ips' => $securityLogs->pluck('ip')->unique()->count(),
            'last_activity' => $activities->max('ultima_actividad'),
            'active_days' => $activities->pluck('fecha')->unique()->count(),
            'total_accesses' => $activities->sum('contador_accesos')
        ];

        return $summary;
    }

    /**
     * Detectar si la cuenta está comprometida
     */
    public function isCompromised(): bool
    {
        // Criterios para determinar compromiso
        $recentHours = 2;
        
        // Múltiples IPs en poco tiempo
        $recentLogs = $this->securityLogs()
                          ->where('created_at', '>=', now()->subHours($recentHours))
                          ->get();
        
        $uniqueIps = $recentLogs->pluck('ip')->unique();
        if ($uniqueIps->count() > 3) {
            return true;
        }

        // Muchos intentos fallidos seguidos de login exitoso
        $failedAttempts = $this->getFailedLoginAttempts(1);
        $recentSuccess = $this->hasRecentSecurityEvents([SecurityLog::TIPO_SUCCESSFUL_LOGIN], 30);
        
        if ($failedAttempts > 10 && $recentSuccess) {
            return true;
        }

        // Eventos críticos recientes
        if ($this->hasRecentSecurityEvents([SecurityLog::TIPO_SUSPICIOUS_PATTERN], 30)) {
            return true;
        }

        return false;
    }

    /**
     * Bloquear usuario por seguridad
     */
    public function blockForSecurity(string $reason = 'Actividad sospechosa detectada')
    {
        $this->update([
            'bloqueado' => true,
            'fecha_bloqueo' => now(),
            'razon_bloqueo' => $reason
        ]);

        // Registrar evento de seguridad
        SecurityLog::logUserBlocked(
            $this->id,
            $reason,
            request()->ip(),
            request()->userAgent()
        );
    }

    /**
     * Desbloquear usuario
     */
    public function unblockUser(string $reason = 'Desbloqueado por administrador')
    {
        $this->update([
            'bloqueado' => false,
            'fecha_bloqueo' => null,
            'razon_bloqueo' => null
        ]);

        // Registrar evento
        SecurityLog::create([
            'tipo' => SecurityLog::TIPO_USER_UNBLOCKED,
            'descripcion' => 'Usuario desbloqueado',
            'usuario_id' => $this->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'severity' => SecurityLog::SEVERITY_INFO,
            'datos_adicionales' => [
                'reason' => $reason,
                'unblocked_by' => auth()->id()
            ]
        ]);
    }
}

?>