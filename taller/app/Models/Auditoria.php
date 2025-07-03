<?php
// app/Models/Auditoria.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditoria';

    protected $fillable = [
        'usuario_id',
        'operacion',
        'tabla',
        'registro_id',
        'datos_anteriores',
        'datos_nuevos',
        'ip',
        'user_agent',
        'ruta',
        'controlador',
    ];    
    protected $casts = [
        'datos_anteriores' => 'json',
        'datos_nuevos' => 'json',
        'registro_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    // Scopes
    public function scopePorTabla($query, $tabla)
    {
        return $query->where('tabla', $tabla);
    }
    public function scopePorOperacion($query, $operacion)
    {
        return $query->where('operacion', $operacion);
    }

    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorFecha($query, $fechaInicio, $fechaFin = null)
    {
        if ($fechaFin) {
            return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }
        return $query->whereDate('created_at', $fechaInicio);
    }
    public function scopePorRegistro($query, $tabla, $registroId)
    {
        return $query->where('tabla', $tabla)
                    ->where('registro_id', $registroId);
    }
    public function scopeRecientes($query, $horas = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($horas));
    }
    // Accessors
    public function getCambiosRealizadosAttribute()
    {
        if (!$this->datos_anteriores || !$this->datos_nuevos) {
            return [];
        }

        $cambios = [];
        $anteriores = $this->datos_anteriores;
        $nuevos = $this->datos_nuevos;

        foreach ($nuevos as $campo => $valorNuevo) {
            $valorAnterior = $anteriores[$campo] ?? null;
            
            if ($valorAnterior !== $valorNuevo) {
                $cambios[$campo] = [
                    'anterior' => $valorAnterior,
                    'nuevo' => $valorNuevo
                ];
            }
        }

        return $cambios;
    }

    public function getDescripcionOperacionAttribute()
    {
        $operaciones = [
            'CREATE' => 'Creó',
            'UPDATE' => 'Modificó',
            'DELETE' => 'Eliminó',
            'RESTORE' => 'Restauró',
        ];

        return $operaciones[$this->operacion] ?? $this->operacion;
    }

    // Método estático para crear auditoría
    public static function registrar($tabla, $operacion, $registroId, $datosAnteriores = null, $datosNuevos = null, $contexto = null)
    {
        return static::create([
            'tabla' => $tabla,
            'operacion' => $operacion,
            'registro_id' => $registroId,
            'usuario_id' => auth()->id(),
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'ruta' => request()->path(),
            'controlador' => $contexto,
        ]);
    }
}