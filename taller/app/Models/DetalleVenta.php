<?php
// app/Models/DetalleVenta.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_ventas';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'descuento',
        'garantia_dias',
        'subtotal',
    ];
    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'garantia_dias' => 'integer',
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ]; 
   // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Accessors
    public function getSubtotalAttribute()
    {
        return ($this->cantidad * $this->precio_unitario) - $this->descuento;
    }

    public function getTotalSinDescuentoAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }

    public function getPorcentajeDescuentoAttribute()
    {
        if ($this->total_sin_descuento > 0) {
            return ($this->descuento / $this->total_sin_descuento) * 100;
        }
        return 0;
    }
}