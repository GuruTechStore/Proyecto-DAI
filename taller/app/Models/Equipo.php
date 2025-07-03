<?php
// app/Models/Equipo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';

    protected $fillable = [
        'cliente_id',
        'tipo',
        'marca',
        'modelo',
        'imei',
        'caracteristicas',
        'altavoz',
        'microfono',
        'zocalo',
        'camara',
        'pantalla',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function reparaciones()
    {
        return $this->hasMany(Reparacion::class);
    }

    // Scopes
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('tipo', 'LIKE', "%{$termino}%")
            ->orWhere('marca', 'LIKE', "%{$termino}%")
            ->orWhere('modelo', 'LIKE', "%{$termino}%")
            ->orWhere('imei', 'LIKE', "%{$termino}%");
        });
    }
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorMarca($query, $marca)
    {
        return $query->where('marca', $marca);
    }

    public function scopePorCliente($query, $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    // Accessors
    public function getDescripcionCompletaAttribute()
    {
        return "{$this->tipo} {$this->marca} {$this->modelo}";
    }

    public function getComponentesAttribute()
    {
        return [
            'altavoz' => $this->altavoz,
            'microfono' => $this->microfono,
            'zocalo' => $this->zocalo,
            'camara' => $this->camara,
            'pantalla' => $this->pantalla,
        ];
    }
}