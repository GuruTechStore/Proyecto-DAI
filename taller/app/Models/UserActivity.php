<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserActivity extends Model
{
    protected $table = 'user_activities';
    
    protected $fillable = [
        'usuario_id',
        'modulo',
        'accion',
        'ruta',
        'fecha',
        'ultima_actividad',
        'ip',
        'user_agent',
        'contador_accesos',
        'datos_sesion'
    ];

    protected $casts = [
        'fecha' => 'date',
        'ultima_actividad' => 'datetime',
        'contador_accesos' => 'integer',
        'datos_sesion' => 'array'
    ];

    // Constantes para módulos
    public const MODULO_DASHBOARD = 'Dashboard';
    public const MODULO_USUARIOS = 'Usuarios';
    public const MODULO_CLIENTES = 'Clientes';
    public const MODULO_PRODUCTOS = 'Productos';
    public const MODULO_REPARACIONES = 'Reparaciones';
    public const MODULO_VENTAS = 'Ventas';
    public const MODULO_EMPLEADOS = 'Empleados';
    public const MODULO_REPORTES = 'Reportes';
    public const MODULO_CONFIGURACION = 'Configuracion';
    public const MODULO_API = 'API';

    /**
     * Relación con Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Scopes para filtrar actividades
     */
    public function scopeRecientes($query, int $days = 30)
    {
        return $query->where('fecha', '>=', now()->subDays($days));
    }

    public function scopePorModulo($query, string $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public function scopeDelUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeHoy($query)
    {
        return $query->where('fecha', today());
    }

    public function scopeActivos($query, int $minutes = 30)
    {
        return $query->where('ultima_actividad', '>=', now()->subMinutes($minutes));
    }

    public function scopePorFecha($query, Carbon $fecha)
    {
        return $query->where('fecha', $fecha->toDateString());
    }

    /**
     * Métodos para gestionar actividad
     */
    public function incrementarAcceso(): void
    {
        $this->increment('contador_accesos');
        $this->update(['ultima_actividad' => now()]);
    }

    public function actualizarDatosSesion(array $datos): void
    {
        $datosActuales = $this->datos_sesion ?? [];
        $this->update([
            'datos_sesion' => array_merge($datosActuales, $datos),
            'ultima_actividad' => now()
        ]);
    }

    /**
     * Métodos estáticos para registrar actividad
     */
    public static function registrarActividad(
        int $usuarioId,
        string $modulo,
        string $accion,
        string $ip,
        string $userAgent,
        ?string $ruta = null,
        array $datosSesion = []
    ): self {
        return self::updateOrCreate(
            [
                'usuario_id' => $usuarioId,
                'modulo' => $modulo,
                'fecha' => today()
            ],
            [
                'accion' => $accion,
                'ruta' => $ruta,
                'ultima_actividad' => now(),
                'ip' => $ip,
                'user_agent' => $userAgent,
                'contador_accesos' => \DB::raw('contador_accesos + 1'),
                'datos_sesion' => $datosSesion
            ]
        );
    }

    /**
     * Obtener usuarios activos
     */
    public static function getUsuariosActivos(int $minutes = 30): \Illuminate\Support\Collection
    {
        return self::activos($minutes)
                   ->with('usuario:id,nombre,email')
                   ->select('usuario_id', 'modulo', 'ultima_actividad', 'ip')
                   ->orderBy('ultima_actividad', 'desc')
                   ->get()
                   ->groupBy('usuario_id');
    }

    /**
     * Estadísticas de actividad
     */
    public static function getStatsForPeriod(int $days = 7): array
    {
        $startDate = now()->subDays($days)->toDateString();
        
        return [
            'usuarios_activos' => self::where('fecha', '>=', $startDate)
                                     ->distinct('usuario_id')
                                     ->count(),
            'total_accesos' => self::where('fecha', '>=', $startDate)
                                  ->sum('contador_accesos'),
            'modulos_populares' => self::where('fecha', '>=', $startDate)
                                      ->selectRaw('modulo, sum(contador_accesos) as total')
                                      ->groupBy('modulo')
                                      ->orderByDesc('total')
                                      ->pluck('total', 'modulo')
                                      ->toArray(),
            'actividad_diaria' => self::where('fecha', '>=', $startDate)
                                     ->selectRaw('fecha, sum(contador_accesos) as total')
                                     ->groupBy('fecha')
                                     ->orderBy('fecha')
                                     ->pluck('total', 'fecha')
                                     ->toArray()
        ];
    }

    /**
     * Detectar sesiones simultáneas sospechosas
     */
    public static function detectarSesionesSospechosas(int $usuarioId): array
    {
        $actividades = self::where('usuario_id', $usuarioId)
                          ->where('ultima_actividad', '>=', now()->subHours(1))
                          ->select('ip', 'ultima_actividad', 'modulo')
                          ->get();

        $ipsUnicas = $actividades->pluck('ip')->unique();
        
        return [
            'ips_diferentes' => $ipsUnicas->count(),
            'es_sospechoso' => $ipsUnicas->count() > 2,
            'ips' => $ipsUnicas->toArray(),
            'ultima_actividad' => $actividades->max('ultima_actividad')
        ];
    }

    /**
     * Limpiar actividades antiguas
     */
    public static function limpiarActividades(int $days = 90): int
    {
        return self::where('fecha', '<', now()->subDays($days)->toDateString())->delete();
    }

    /**
     * Obtener resumen de actividad de un usuario
     */
    public static function getResumenUsuario(int $usuarioId, int $days = 30): array
    {
        $startDate = now()->subDays($days)->toDateString();
        
        $actividades = self::where('usuario_id', $usuarioId)
                          ->where('fecha', '>=', $startDate)
                          ->get();

        return [
            'dias_activos' => $actividades->pluck('fecha')->unique()->count(),
            'total_accesos' => $actividades->sum('contador_accesos'),
            'modulos_usados' => $actividades->pluck('modulo')->unique()->toArray(),
            'ips_utilizadas' => $actividades->pluck('ip')->unique()->toArray(),
            'ultimo_acceso' => $actividades->max('ultima_actividad'),
            'modulo_favorito' => $actividades->groupBy('modulo')
                                           ->map(fn($group) => $group->sum('contador_accesos'))
                                           ->sortDesc()
                                           ->keys()
                                           ->first()
        ];
    }
}
