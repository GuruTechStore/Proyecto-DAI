<?php
// app/Models/ComponenteReparacion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponenteReparacion extends Model
{
    use HasFactory;

    protected $table = 'componentes_reparacion';

    protected $fillable = [
        'reparacion_id',
        'nombre_componente',
        'estado',
        'accion_realizada',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function reparacion()
    {
        return $this->belongsTo(Reparacion::class);
    }

    // Scopes
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorComponente($query, $componente)
    {
        return $query->where('nombre_componente', $componente);
    }

    public function scopeReparados($query)
    {
        return $query->where('estado', 'reparado');
    }

    public function scopeReemplazados($query)
    {
        return $query->where('estado', 'reemplazado');
    }
}