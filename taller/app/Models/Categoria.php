<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'orden',
    ];
    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'activo' => true,
        'orden' => 0,
    ];

    /**
     * Relaciones
     */
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function productosActivos()
    {
        return $this->hasMany(Producto::class)->where('activo', true);
    }

    /**
     * Scopes
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('descripcion', 'like', "%{$termino}%");
        });
    }

    public function scopeConProductos($query)
    {
        return $query->has('productos');
    }

    public function scopeSinProductos($query)
    {
        return $query->doesntHave('productos');
    }

    /**
     * Accessors
     */
    public function getEstadoTextAttribute()
    {
        return $this->activo ? 'Activa' : 'Inactiva';
    }

    public function getEstadoColorAttribute()
    {
        return $this->activo ? 'green' : 'red';
    }
    /**
     * Métodos auxiliares
     */
    public function puedeEliminarse()
    {
        return $this->productos()->count() === 0;
    }

    public function getTotalProductos()
    {
        return $this->productos()->count();
    }

    public function getTotalProductosActivos()
    {
        return $this->productosActivos()->count();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Al crear una categoría, asignar el siguiente orden disponible
        static::creating(function ($categoria) {
            if (is_null($categoria->orden)) {
                $categoria->orden = static::max('orden') + 1;
            }
        });
    }
}