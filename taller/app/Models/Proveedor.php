<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre',
        'ruc',
        'contacto',
        'telefono',
        'email',
        'direccion',
        'banco',
        'numero_cuenta',
        'tipo_cuenta',
        'observaciones',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relación con productos
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }

    /**
     * Scope para proveedores activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para buscar proveedores
     */
    public function scopeBuscar($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
              ->orWhere('ruc', 'like', "%{$search}%")
              ->orWhere('contacto', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Accessor para nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ($this->ruc ? " (RUC: {$this->ruc})" : '');
    }

    /**
     * Verificar si tiene productos asociados
     */
    public function tieneProductos()
    {
        return $this->productos()->exists();
    }

    /**
     * Obtener valor total de productos
     */
    public function getValorInventarioAttribute()
    {
        return $this->productos()->sum(\DB::raw('precio * stock'));
    }
}