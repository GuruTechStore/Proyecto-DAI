<?php
// app/Models/DetalleEntrada.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleEntrada extends Model
{
    use HasFactory;

    protected $table = 'detalle_entradas';

    protected $fillable = [
        'entrada_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function entrada()
    {
        return $this->belongsTo(EntradaInventario::class, 'entrada_id');
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