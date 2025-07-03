<?php
// app/Models/ProductoReparacion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoReparacion extends Model
{
    use HasFactory;

    protected $table = 'productos_reparacion';

    protected $fillable = [
        'reparacion_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'descripcion',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function reparacion()
    {
        return $this->belongsTo(Reparacion::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Accessors
    public function getSubtotalAttribute()
    {
        return $this->cantidad * $this->precio_unitario;
    }
}