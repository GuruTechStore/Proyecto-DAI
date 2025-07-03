<?php
// app/Models/Reparacion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reparacion extends Model
{
    use HasFactory;

    protected $table = 'reparaciones';

    protected $fillable = [
        'codigo_ticket',
        'cliente_id',
        'equipo_id',
        'empleado_id',
        'estado',
        'problema_reportado',
        'diagnostico',
        'solucion',
        'observaciones',
        'costo_estimado',
        'costo_final',
        'creado_por',
        'fecha_ingreso',
        'fecha_entrega',
    ];

    protected $casts = [
        'costo_estimado' => 'decimal:2',
        'costo_final' => 'decimal:2',
        'fecha_ingreso' => 'datetime',
        'fecha_entrega' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function empleadoCreador()
    {
        return $this->belongsTo(Empleado::class, 'creado_por');
    }

    public function componentes()
    {
        return $this->hasMany(ComponenteReparacion::class);
    }

    public function productos()
    {
        return $this->hasMany(ProductoReparacion::class);
    }

    public function garantias()
    {
        return $this->hasMany(Garantia::class);
    }

    // Scopes
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeEnProceso($query)
    {
        return $query->whereIn('estado', ['recibido', 'diagnosticando', 'reparando']);
    }

    public function scopeTerminadas($query)
    {
        return $query->whereIn('estado', ['completada', 'entregada']);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin]);
        }
        return $query->whereDate('fecha_ingreso', $fechaInicio);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('codigo_ticket', 'ILIKE', "%{$termino}%")
              ->orWhere('problema_reportado', 'ILIKE', "%{$termino}%")
              ->orWhereHas('cliente', function ($clienteQuery) use ($termino) {
                  $clienteQuery->where('nombre', 'ILIKE', "%{$termino}%")
                              ->orWhere('apellido', 'ILIKE', "%{$termino}%");
              });
        });
    }

    public function scopePorEmpleado($query, $empleadoId)
    {
        return $query->where('empleado_id', $empleadoId);
    }

    // Accessors
    public function getDiasTranscurridosAttribute()
    {
        $fechaFin = $this->fecha_entrega ?? now();
        return $this->fecha_ingreso->diffInDays($fechaFin);
    }

    public function getEstadoBadgeAttribute()
    {
        $badges = [
            'recibido' => ['color' => 'blue', 'text' => 'Recibido'],
            'diagnosticando' => ['color' => 'yellow', 'text' => 'Diagnosticando'],
            'reparando' => ['color' => 'orange', 'text' => 'Reparando'],
            'completada' => ['color' => 'green', 'text' => 'Completada'],
            'entregada' => ['color' => 'gray', 'text' => 'Entregada'],
            'cancelada' => ['color' => 'red', 'text' => 'Cancelada'],
        ];

        return $badges[$this->estado] ?? ['color' => 'gray', 'text' => 'Sin estado'];
    }

    public function getCostoTotalProductosAttribute()
    {
        return $this->productos()
            ->selectRaw('SUM(cantidad * precio_unitario) as total')
            ->value('total') ?? 0;
    }
}