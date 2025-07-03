<?php
// app/Models/AjusteInventario.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AjusteInventario extends Model
{
    use HasFactory;

    protected $table = 'ajustes_inventario';

    protected $fillable = [
        'producto_id',
        'tipo_ajuste',
        'cantidad_anterior',
        'diferencia',
        'observaciones',
        'realizado_por',
    ];
    protected $casts = [
        'cantidad_anterior' => 'integer',
        'diferencia' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'realizado_por');
    }
    public static function crear($datos)
    {
        if (!auth()->check()) {
            throw new \Exception('Usuario no autenticado');
        }
        
        $datos['realizado_por'] = $datos['realizado_por'] ?? auth()->id();
        
        return self::create($datos);
      }   
    
        // Scopes
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_ajuste', $tipo);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }
        return $query->whereDate('created_at', $fechaInicio);
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    // Accessors
    public function getCantidadNuevaAttribute()
    {
        return $this->cantidad_anterior + $this->diferencia;
    }
}