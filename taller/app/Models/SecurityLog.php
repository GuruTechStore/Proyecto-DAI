<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SecurityLog extends Model
{
    protected $table = 'security_logs';
    
    // Deshabilitar updated_at ya que solo necesitamos created_at
    public const UPDATED_AT = null;
    
    protected $fillable = [
        'tipo',
        'descripcion',
        'usuario_id',
        'ip',
        'user_agent',
        'datos_adicionales',
        'severity',
        'procesado'
    ];

    protected $casts = [
        'datos_adicionales' => 'array',
        'severity' => 'integer',
        'procesado' => 'boolean',
        'created_at' => 'datetime'
    ];

    // Constantes para tipos de eventos
    public const TIPO_FAILED_LOGIN = 'failed_login';
    public const TIPO_SUCCESSFUL_LOGIN = 'successful_login';
    public const TIPO_USER_BLOCKED = 'user_blocked';
    public const TIPO_USER_UNBLOCKED = 'user_unblocked';
    public const TIPO_SUSPICIOUS_PATTERN = 'suspicious_pattern';
    public const TIPO_IP_BLOCKED = 'ip_blocked';
    public const TIPO_RATE_LIMIT = 'rate_limit_exceeded';
    public const TIPO_TOKEN_INVALID = 'invalid_token';
    public const TIPO_UNAUTHORIZED = 'unauthorized_access';

    // Constantes para severity
    public const SEVERITY_INFO = 1;
    public const SEVERITY_WARNING = 2;
    public const SEVERITY_ERROR = 3;
    public const SEVERITY_CRITICAL = 4;

    /**
     * Relación con Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Scopes para filtrar logs
     */
    public function scopeRecientes($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorSeverity($query, int $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeCriticos($query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    public function scopeDelUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeDesdeIP($query, string $ip)
    {
        return $query->where('ip', $ip);
    }

    public function scopeNoProcesados($query)
    {
        return $query->where('procesado', false);
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Métodos auxiliares
     */
    public function getSeverityTextAttribute(): string
    {
        return match($this->severity) {
            self::SEVERITY_INFO => 'INFO',
            self::SEVERITY_WARNING => 'WARNING',
            self::SEVERITY_ERROR => 'ERROR',
            self::SEVERITY_CRITICAL => 'CRITICAL',
            default => 'UNKNOWN'
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            self::SEVERITY_INFO => 'blue',
            self::SEVERITY_WARNING => 'yellow',
            self::SEVERITY_ERROR => 'orange',
            self::SEVERITY_CRITICAL => 'red',
            default => 'gray'
        };
    }

    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    public function marcarComoProcesado(): bool
    {
        return $this->update(['procesado' => true]);
    }

    /**
     * Métodos estáticos para crear logs específicos
     */
    public static function logFailedLogin(string $email, string $ip, string $userAgent, array $additionalData = []): self
    {
        return self::create([
            'tipo' => self::TIPO_FAILED_LOGIN,
            'descripcion' => 'Intento de login fallido',
            'ip' => $ip,
            'user_agent' => $userAgent,
            'severity' => self::SEVERITY_WARNING,
            'datos_adicionales' => array_merge(['email' => $email], $additionalData)
        ]);
    }

    public static function logSuccessfulLogin(int $usuarioId, string $ip, string $userAgent): self
    {
        return self::create([
            'tipo' => self::TIPO_SUCCESSFUL_LOGIN,
            'descripcion' => 'Login exitoso',
            'usuario_id' => $usuarioId,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'severity' => self::SEVERITY_INFO
        ]);
    }

    public static function logUserBlocked(int $usuarioId, string $reason, string $ip, string $userAgent): self
    {
        return self::create([
            'tipo' => self::TIPO_USER_BLOCKED,
            'descripcion' => 'Usuario bloqueado por seguridad',
            'usuario_id' => $usuarioId,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'severity' => self::SEVERITY_CRITICAL,
            'datos_adicionales' => ['reason' => $reason]
        ]);
    }

    public static function logSuspiciousPattern(string $pattern, string $type, string $ip, string $userAgent, array $additionalData = []): self
    {
        return self::create([
            'tipo' => self::TIPO_SUSPICIOUS_PATTERN,
            'descripcion' => 'Patrón sospechoso detectado',
            'ip' => $ip,
            'user_agent' => $userAgent,
            'severity' => self::SEVERITY_ERROR,
            'datos_adicionales' => array_merge([
                'pattern' => $pattern,
                'type' => $type
            ], $additionalData)
        ]);
    }

    /**
     * Estadísticas rápidas
     */
    public static function getStatsForPeriod(int $days = 7): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'total' => self::where('created_at', '>=', $startDate)->count(),
            'por_tipo' => self::where('created_at', '>=', $startDate)
                             ->selectRaw('tipo, count(*) as total')
                             ->groupBy('tipo')
                             ->pluck('total', 'tipo')
                             ->toArray(),
            'por_severity' => self::where('created_at', '>=', $startDate)
                                 ->selectRaw('severity, count(*) as total')
                                 ->groupBy('severity')
                                 ->pluck('total', 'severity')
                                 ->toArray(),
            'ips_top' => self::where('created_at', '>=', $startDate)
                            ->selectRaw('ip, count(*) as total')
                            ->groupBy('ip')
                            ->orderByDesc('total')
                            ->limit(10)
                            ->pluck('total', 'ip')
                            ->toArray()
        ];
    }
}