<?php
// app/Models/Venta.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'codigo_venta',
        'cliente_id',
        'empleado_id',
        'fecha',
        'tipo_documento',
        'numero_boleta',
        'subtotal',
        'descuento',
        'total',
        'metodo_pago',
        'estado',
        'creado_por',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function empleadoCreador()
    {
        return $this->belongsTo(Empleado::class, 'creado_por');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function garantias()
    {
        return $this->hasMany(Garantia::class);
    }

    // Scopes
    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }
        return $query->whereDate('fecha', $fechaInicio);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    public function scopePorMetodoPago($query, $metodo)
    {
        return $query->where('metodo_pago', $metodo);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('codigo_venta', 'ILIKE', "%{$termino}%")
              ->orWhere('numero_boleta', 'ILIKE', "%{$termino}%")
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
    public function getTotalCalculadoAttribute()
    {
        return $this->detalles->sum('subtotal') - $this->descuento;
    }

    public function getCantidadProductosAttribute()
    {
        return $this->detalles->sum('cantidad');
    }

    public function getEstadoBadgeAttribute()
    {
        $badges = [
            'pendiente' => ['color' => 'yellow', 'text' => 'Pendiente'],
            'completado' => ['color' => 'green', 'text' => 'Completado'],  
            'cancelado' => ['color' => 'red', 'text' => 'Cancelado'],      
            'anulado' => ['color' => 'gray', 'text' => 'Anulado'],        
        ];

        return $badges[$this->estado] ?? ['color' => 'gray', 'text' => 'Sin estado'];
    }
}