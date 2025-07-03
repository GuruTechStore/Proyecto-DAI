<?php
// app/Models/Garantia.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Garantia extends Model
{
    use HasFactory;

    protected $table = 'garantias';

    protected $fillable = [
        'codigo_garantia',
        'tipo_garantia',
        'reparacion_id',
        'producto_id',
        'venta_id',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'condiciones',
        'estado',
    ];
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    // Relaciones
    public function reparacion()
    {
        return $this->belongsTo(Reparacion::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Scopes
    public function scopeVigentes($query)
    {
        return $query->where('estado', 'vigente')
                    ->where('fecha_fin', '>=', now()->format('Y-m-d'));
    }
    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vigente')
                    ->where('fecha_fin', '<', now()->format('Y-m-d'));
    }
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_garantia', $tipo);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopeProximasAVencer($query, $dias = 30)
    {
        $fechaLimite = now()->addDays($dias)->format('Y-m-d');
        return $query->where('estado', 'vigente')
                    ->where('fecha_fin', '<=', $fechaLimite)
                    ->where('fecha_fin', '>=', now()->format('Y-m-d'));
    }
    // Accessors
    public function getFechaVencimientoAttribute()
    {
        return $this->fecha_fin;
    }
    public function getDiasRestantesAttribute()
    {
        $fechaVencimiento = $this->fecha_vencimiento;
        $hoy = now();
        
        if ($fechaVencimiento->isPast()) {
            return 0;
        }
        
        return $hoy->diffInDays($fechaVencimiento);
    }

    public function getEstaVigente()
    {
        return $this->estado === 'vigente' && $this->fecha_fin >= now()->format('Y-m-d');
    }
    public function getEstadoBadgeAttribute()
    {
        if ($this->estado === 'vigente' && $this->fecha_vencimiento->isFuture()) {
            return ['color' => 'green', 'text' => 'Vigente'];
        } elseif ($this->estado === 'vigente' && $this->fecha_vencimiento->isPast()) {
            return ['color' => 'red', 'text' => 'Vencida'];
        } elseif ($this->estado === 'utilizada') {
            return ['color' => 'blue', 'text' => 'Utilizada'];
        } elseif ($this->estado === 'cancelada') {
            return ['color' => 'gray', 'text' => 'Cancelada'];
        }
        
        return ['color' => 'gray', 'text' => 'Sin estado'];
    }
}