<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class PasswordHistory extends Model
{
    use HasFactory;
    
    protected $table = 'password_history';
    
    protected $fillable = [
        'usuario_id',
        'password_hash'
    ];

    protected $hidden = [
        'password_hash'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * RELACIONES
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * SCOPES
     */
    public function scopeRecientes($query, int $limit = 5)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeDelUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeEnRango($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    /**
     * MÉTODOS ESTÁTICOS PRINCIPALES
     */
    public static function registrarPassword(int $usuarioId, string $password): self
    {
        // Crear nuevo registro
        $registro = self::create([
            'usuario_id' => $usuarioId,
            'password_hash' => Hash::make($password)
        ]);

        // Limpiar historial antiguo automáticamente
        self::limpiarHistorial($usuarioId, 5);

        return $registro;
    }

    public static function limpiarHistorial(int $usuarioId, int $mantener = 5): int
    {
        $idsAMantener = self::where('usuario_id', $usuarioId)
                             ->orderBy('created_at', 'desc')
                             ->limit($mantener)
                             ->pluck('id');

        return self::where('usuario_id', $usuarioId)
                    ->whereNotIn('id', $idsAMantener)
                    ->delete();
    }

    public static function passwordFueUsada(int $usuarioId, string $password, int $limit = 5): bool
    {
        $recentPasswords = self::where('usuario_id', $usuarioId)
                                ->orderBy('created_at', 'desc')
                                ->limit($limit)
                                ->pluck('password_hash');

        foreach ($recentPasswords as $hash) {
            if (Hash::check($password, $hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * MÉTODOS DE ESTADÍSTICAS
     */
    public static function getEstadisticas(int $usuarioId): array
    {
        $history = self::where('usuario_id', $usuarioId)
                        ->orderBy('created_at', 'desc')
                        ->get();

        $cambiosEsteAno = $history->where('created_at', '>=', now()->startOfYear())->count();
        $ultimoCambio = $history->first()?->created_at;
        $primerRegistro = $history->last()?->created_at;

        // Calcular promedio de días entre cambios
        $promedioDias = null;
        if ($history->count() > 1 && $ultimoCambio && $primerRegistro) {
            $totalDias = $ultimoCambio->diffInDays($primerRegistro);
            $promedioDias = $totalDias / ($history->count() - 1);
        }

        return [
            'total_cambios' => $history->count(),
            'ultimo_cambio' => $ultimoCambio,
            'primer_registro' => $primerRegistro,
            'cambios_este_ano' => $cambiosEsteAno,
            'promedio_dias_entre_cambios' => $promedioDias ? round($promedioDias, 1) : null,
            'passwords_almacenadas' => min($history->count(), 5), // Máximo que guardamos
        ];
    }

    public static function getHistorialCompleto(int $usuarioId, int $limit = 10): \Illuminate\Support\Collection
    {
        return self::where('usuario_id', $usuarioId)
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get(['id', 'created_at'])
                   ->map(function($registro) {
                       return [
                           'id' => $registro->id,
                           'fecha_cambio' => $registro->created_at,
                           'tiempo_transcurrido' => $registro->created_at->diffForHumans(),
                           'hace_dias' => $registro->created_at->diffInDays(now()),
                       ];
                   });
    }

    /**
     * MÉTODOS DE VALIDACIÓN
     */
    public static function validarNuevaPassword(int $usuarioId, string $newPassword, array $opciones = []): array
    {
        $limiteBusqueda = $opciones['limite_historial'] ?? 5;
        $minimoCaracteres = $opciones['minimo_caracteres'] ?? 8;
        
        $errores = [];
        $advertencias = [];

        // Validar longitud mínima
        if (strlen($newPassword) < $minimoCaracteres) {
            $errores[] = "La contraseña debe tener al menos {$minimoCaracteres} caracteres";
        }

        // Validar que no sea una contraseña recientemente usada
        if (self::passwordFueUsada($usuarioId, $newPassword, $limiteBusqueda)) {
            $errores[] = "No puedes usar una de tus últimas {$limiteBusqueda} contraseñas";
        }

        // Validar complejidad básica
        if (!preg_match('/[A-Z]/', $newPassword)) {
            $advertencias[] = "Se recomienda incluir al menos una letra mayúscula";
        }

        if (!preg_match('/[a-z]/', $newPassword)) {
            $advertencias[] = "Se recomienda incluir al menos una letra minúscula";
        }

        if (!preg_match('/\d/', $newPassword)) {
            $advertencias[] = "Se recomienda incluir al menos un número";
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)) {
            $advertencias[] = "Se recomienda incluir al menos un símbolo especial";
        }

        // Verificar frecuencia de cambios
        $ultimoCambio = self::where('usuario_id', $usuarioId)
                             ->orderBy('created_at', 'desc')
                             ->first();

        if ($ultimoCambio && $ultimoCambio->created_at->diffInDays(now()) < 1) {
            $advertencias[] = "Has cambiado tu contraseña recientemente";
        }

        return [
            'valida' => empty($errores),
            'errores' => $errores,
            'advertencias' => $advertencias,
            'puntaje_fortaleza' => self::calcularPuntajeFortaleza($newPassword)
        ];
    }

    /**
     * MÉTODOS DE UTILIDAD
     */
    private static function calcularPuntajeFortaleza(string $password): array
    {
        $puntaje = 0;
        $criterios = [];

        // Longitud
        if (strlen($password) >= 8) {
            $puntaje += 25;
            $criterios['longitud'] = true;
        }

        // Mayúsculas
        if (preg_match('/[A-Z]/', $password)) {
            $puntaje += 25;
            $criterios['mayusculas'] = true;
        }

        // Minúsculas
        if (preg_match('/[a-z]/', $password)) {
            $puntaje += 25;
            $criterios['minusculas'] = true;
        }

        // Números
        if (preg_match('/\d/', $password)) {
            $puntaje += 15;
            $criterios['numeros'] = true;
        }

        // Símbolos especiales
        if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $puntaje += 10;
            $criterios['simbolos'] = true;
        }

        $nivel = match(true) {
            $puntaje >= 90 => 'Muy Fuerte',
            $puntaje >= 70 => 'Fuerte',
            $puntaje >= 50 => 'Media',
            $puntaje >= 30 => 'Débil',
            default => 'Muy Débil'
        };

        $color = match($nivel) {
            'Muy Fuerte' => 'green',
            'Fuerte' => 'blue',
            'Media' => 'yellow',
            'Débil' => 'orange',
            'Muy Débil' => 'red'
        };

        return [
            'puntaje' => $puntaje,
            'nivel' => $nivel,
            'color' => $color,
            'criterios_cumplidos' => $criterios
        ];
    }

    public static function limpiarHistorialGlobal(int $diasAntiguedad = 365): int
    {
        return self::where('created_at', '<', now()->subDays($diasAntiguedad))->delete();
    }

    public static function getEstadisticasGlobales(): array
    {
        $totalUsuarios = self::distinct('usuario_id')->count();
        $totalCambios = self::count();
        $cambiosUltimoMes = self::where('created_at', '>=', now()->subMonth())->count();
        
        $promedioGlobal = $totalUsuarios > 0 ? $totalCambios / $totalUsuarios : 0;

        return [
            'usuarios_con_historial' => $totalUsuarios,
            'total_cambios_registrados' => $totalCambios,
            'cambios_ultimo_mes' => $cambiosUltimoMes,
            'promedio_cambios_por_usuario' => round($promedioGlobal, 2),
            'usuarios_activos_mes' => self::where('created_at', '>=', now()->subMonth())
                                          ->distinct('usuario_id')
                                          ->count()
        ];
    }

    /**
     * BOOT METHOD
     */
    protected static function boot()
    {
        parent::boot();

        // Limpiar automáticamente al crear nuevos registros
        static::created(function ($passwordHistory) {
            // Mantener solo los últimos 5 registros por usuario
            self::limpiarHistorial($passwordHistory->usuario_id, 5);
        });
    }
}