<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Empleado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empleados';

    protected $fillable = [
        'dni',
        'nombres',
        'apellidos',
        'telefono',
        'email',
        'especialidad',
        'fecha_contratacion',
        'salario',
        'direccion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_contratacion' => 'date',
        'salario' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relación con reparaciones
     */
    public function reparaciones()
    {
        return $this->hasMany(Reparacion::class, 'empleado_id');
    }

    /**
     * Relación con ventas asociadas (como vendedor)
     */
    public function ventasAsociadas()
    {
        return $this->hasMany(Venta::class, 'empleado_id');
    }

    /**
     * Relación con ventas realizadas (alias)
     */
    public function ventas()
    {
        return $this->ventasAsociadas();
    }

    /**
     * Scope para empleados activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
  public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'empleado_id');
    }
    /**
     * Scope para buscar empleados
     */
    public function scopeBuscar($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nombres', 'like', "%{$search}%")
              ->orWhere('apellidos', 'like', "%{$search}%")
              ->orWhere('dni', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('especialidad', 'like', "%{$search}%")
              ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$search}%"]);
        });
    }

    /**
     * Accessor para nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }

    /**
     * Accessor para iniciales
     */
    public function getInicialesAttribute()
    {
        $nombres = explode(' ', $this->nombres);
        $apellidos = explode(' ', $this->apellidos);
        
        $iniciales = '';
        if (isset($nombres[0])) $iniciales .= substr($nombres[0], 0, 1);
        if (isset($apellidos[0])) $iniciales .= substr($apellidos[0], 0, 1);
        
        return strtoupper($iniciales);
    }

    /**
     * Obtener años de antigüedad
     */
 public function getAntiguedadAttribute()
    {
        return $this->fecha_contratacion
            ? $this->fecha_contratacion->diffInYears() 
            : 0;
    }
    /**
     * Verificar si es técnico
     */
    public function esTecnico()
    {
        return !empty($this->especialidad);
    }

    /**
     * Obtener reparaciones pendientes
     */
    public function reparacionesPendientes()
    {
        return $this->reparaciones()
            ->whereIn('estado', ['recibido', 'diagnostico', 'en_proceso', 'esperando_repuestos']);
    }

    /**
     * Obtener reparaciones completadas
     */
    public function reparacionesCompletadas()
    {
        return $this->reparaciones()->where('estado', 'completada');
    }

    /**
     * Calcular rendimiento del empleado
     */
    public function calcularRendimiento($periodo = 30)
    {
        $reparacionesCompletadas = $this->reparacionesCompletadas()
            ->where('created_at', '>=', now()->subDays($periodo))
            ->count();
            
        $ventasRealizadas = $this->ventasAsociadas()
            ->where('created_at', '>=', now()->subDays($periodo))
            ->count();
            
        return [
            'reparaciones' => $reparacionesCompletadas,
            'ventas' => $ventasRealizadas,
            'periodo_dias' => $periodo
        ];
    }
}