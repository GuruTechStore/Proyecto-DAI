<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'tipo_notificacion',
        'usuario_destino_id',
        'titulo',
        'mensaje',
        'enlace_accion',
        'entidad_relacionada',
        'entidad_id',
        'leida',
        'resuelta',
        'fecha_creacion',
        'fecha_lectura',
        'fecha_resolucion',
        'prioridad',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'resuelta' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_lectura' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'prioridad' => 'integer',
        'entidad_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes para tipos de notificación
    public const TIPO_STOCK_BAJO = 'stock_bajo';
    public const TIPO_REPARACION_COMPLETADA = 'reparacion_completada';
    public const TIPO_VENTA_REALIZADA = 'venta_realizada';
    public const TIPO_USUARIO_BLOQUEADO = 'usuario_bloqueado';
    public const TIPO_BACKUP_COMPLETADO = 'backup_completado';
    public const TIPO_SISTEMA = 'sistema';

    // Constantes para prioridades
    public const PRIORIDAD_BAJA = 1;
    public const PRIORIDAD_NORMAL = 2;
    public const PRIORIDAD_ALTA = 3;
    public const PRIORIDAD_CRITICA = 4;
    public const PRIORIDAD_URGENTE = 5;

    /**
     * RELACIONES
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_destino_id');
    }

    /**
     * SCOPES
     */
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function scopeLeidas($query)
    {
        return $query->where('leida', true);
    }

    public function scopeNoResueltas($query)
    {
        return $query->where('resuelta', false);
    }

    public function scopeResueltas($query)
    {
        return $query->where('resuelta', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_notificacion', $tipo);
    }

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_destino_id', $usuarioId);
    }

    public function scopeRecientes($query, $horas = 24)
    {
        return $query->where('fecha_creacion', '>=', now()->subHours($horas));
    }

    public function scopeOrdenadaPorPrioridad($query)
    {
        return $query->orderBy('prioridad', 'desc')
                    ->orderBy('fecha_creacion', 'desc');
    }

    public function scopeCriticas($query)
    {
        return $query->whereIn('prioridad', [self::PRIORIDAD_CRITICA, self::PRIORIDAD_URGENTE]);
    }

    /**
     * ACCESSORS
     */
    public function getPrioridadTextoAttribute()
    {
        return match($this->prioridad) {
            self::PRIORIDAD_BAJA => 'Baja',
            self::PRIORIDAD_NORMAL => 'Normal',
            self::PRIORIDAD_ALTA => 'Alta',
            self::PRIORIDAD_CRITICA => 'Crítica',
            self::PRIORIDAD_URGENTE => 'Urgente',
            default => 'Normal'
        };
    }

    public function getPrioridadColorAttribute()
    {
        return match($this->prioridad) {
            self::PRIORIDAD_BAJA => 'green',
            self::PRIORIDAD_NORMAL => 'blue',
            self::PRIORIDAD_ALTA => 'yellow',
            self::PRIORIDAD_CRITICA => 'orange',
            self::PRIORIDAD_URGENTE => 'red',
            default => 'blue'
        };
    }

    public function getTiempoTranscurridoAttribute()
    {
        return $this->fecha_creacion->diffForHumans();
    }

    public function getIconoTipoAttribute()
    {
        return match($this->tipo_notificacion) {
            self::TIPO_STOCK_BAJO => '📦',
            self::TIPO_REPARACION_COMPLETADA => '🔧',
            self::TIPO_VENTA_REALIZADA => '💰',
            self::TIPO_USUARIO_BLOQUEADO => '🚫',
            self::TIPO_BACKUP_COMPLETADO => '💾',
            self::TIPO_SISTEMA => '⚙️',
            default => '📢'
        };
    }

    /**
     * MÉTODOS DE ACCIÓN
     */
    public function marcarComoLeida()
    {
        if (!$this->leida) {
            $this->update([
                'leida' => true,
                'fecha_lectura' => now(),
            ]);
        }
        return $this;
    }

    public function marcarComoResuelta()
    {
        $this->update([
            'resuelta' => true,
            'fecha_resolucion' => now(),
        ]);

        if (!$this->leida) {
            $this->marcarComoLeida();
        }
        
        return $this;
    }

    /**
     * MÉTODOS ESTÁTICOS PARA CREAR NOTIFICACIONES
     */
    public static function crear($tipo, $usuarioId, $titulo, $mensaje, $opciones = [])
    {
        return self::create([
            'tipo_notificacion' => $tipo,
            'usuario_destino_id' => $usuarioId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'enlace_accion' => $opciones['enlace'] ?? null,
            'entidad_relacionada' => $opciones['entidad'] ?? null,
            'entidad_id' => $opciones['entidad_id'] ?? null,
            'prioridad' => $opciones['prioridad'] ?? self::PRIORIDAD_NORMAL,
            'fecha_creacion' => now(),
        ]);
    }

    public static function notificarStockBajo($productoId, $usuariosIds = null)
    {
        $producto = \App\Models\Producto::find($productoId);
        if (!$producto) return;

        $usuarios = $usuariosIds ?? \App\Models\Usuario::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Gerente', 'Supervisor']);
        })->pluck('id');

        foreach ($usuarios as $usuarioId) {
            self::crear(
                self::TIPO_STOCK_BAJO,
                $usuarioId,
                'Stock Bajo Detectado',
                "El producto '{$producto->nombre}' tiene stock bajo ({$producto->stock} unidades)",
                [
                    'prioridad' => self::PRIORIDAD_ALTA,
                    'entidad' => 'producto',
                    'entidad_id' => $producto->id,
                    'enlace' => route('productos.show', $producto->id)
                ]
            );
        }
    }

    public static function notificarReparacionCompletada($reparacionId, $usuarioId = null)
    {
        $reparacion = \App\Models\Reparacion::find($reparacionId);
        if (!$reparacion) return;

        $usuarioDestino = $usuarioId ?? $reparacion->cliente->usuario_id ?? 
                         \App\Models\Usuario::role(['Super Admin', 'Gerente'])->first()->id;

        return self::crear(
            self::TIPO_REPARACION_COMPLETADA,
            $usuarioDestino,
            'Reparación Completada',
            "La reparación {$reparacion->codigo_ticket} ha sido completada",
            [
                'prioridad' => self::PRIORIDAD_NORMAL,
                'entidad' => 'reparacion',
                'entidad_id' => $reparacion->id,
                'enlace' => route('reparaciones.show', $reparacion->id)
            ]
        );
    }

    public static function notificarVentaRealizada($ventaId, $usuariosIds = null)
    {
        $venta = \App\Models\Venta::find($ventaId);
        if (!$venta) return;

        $usuarios = $usuariosIds ?? \App\Models\Usuario::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Gerente', 'Supervisor']);
        })->pluck('id');

        foreach ($usuarios as $usuarioId) {
            self::crear(
                self::TIPO_VENTA_REALIZADA,
                $usuarioId,
                'Nueva Venta Registrada',
                "Se registró una venta por S/ {$venta->total}",
                [
                    'prioridad' => self::PRIORIDAD_NORMAL,
                    'entidad' => 'venta',
                    'entidad_id' => $venta->id,
                    'enlace' => route('ventas.show', $venta->id)
                ]
            );
        }
    }

    /**
     * MÉTODOS DE UTILIDAD
     */
    public static function contarNoLeidas($usuarioId)
    {
        return self::porUsuario($usuarioId)->noLeidas()->count();
    }

    public static function contarCriticas($usuarioId)
    {
        return self::porUsuario($usuarioId)->criticas()->noLeidas()->count();
    }

    public static function limpiarAntiguas($diasAntiguedad = 90)
    {
        return self::where('fecha_creacion', '<', now()->subDays($diasAntiguedad))
                   ->where('leida', true)
                   ->where('resuelta', true)
                   ->delete();
    }
}