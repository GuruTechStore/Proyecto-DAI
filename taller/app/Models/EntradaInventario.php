<?php
// app/Models/EntradaInventario.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntradaInventario extends Model
{
    use HasFactory;

    protected $table = 'entradas_inventario';

    protected $fillable = [
        'codigo_entrada',
        'proveedor_id',
        'fecha',
        'tipo_movimiento',
        'total',
        'numero_factura',
        'observaciones',
        'creado_por',
    ];
    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function usuarioCreador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }
    public function detalles()
    {
        return $this->hasMany(DetalleEntrada::class, 'entrada_id');
    }

    // Scopes
    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        }
        return $query->whereDate('fecha', $fechaInicio);
    }

    public function scopePorProveedor($query, $proveedorId)
    {
        return $query->where('proveedor_id', $proveedorId);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_movimiento', $tipo);
    }

    // Accessors
    public function getTotalCalculadoAttribute()
    {
        return $this->detalles->collect()->sum(function ($detalle) {
            return $detalle->cantidad * $detalle->precio_unitario;
        });
    }
}
