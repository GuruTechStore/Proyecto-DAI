<?php
// app/Models/Configuracion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';
    protected $fillable = [
        'clave',
        'valor',
        'tipo',
        'descripcion',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Scopes
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('clave', 'LIKE', "%{$termino}%")
            ->orWhere('descripcion', 'LIKE', "%{$termino}%");
        });
    }
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('clave', 'LIKE', "{$categoria}%");
    }

    // Métodos estáticos para obtener configuraciones
    public static function obtener($clave, $valorPorDefecto = null)
    {
        $config = static::where('clave', $clave)->first();
        return $config ? $config->valor : $valorPorDefecto;
    }
    public static function establecer($clave, $valor, $descripcion = null)
    {
        return static::updateOrCreate(
            ['clave' => $clave],
            [
                'valor' => $valor,
                'descripcion' => $descripcion,
            ]
        );
    }

    public static function obtenerBoolean($clave, $valorPorDefecto = false)
    {
        $valor = static::obtener($clave, $valorPorDefecto);
        return filter_var($valor, FILTER_VALIDATE_BOOLEAN);
    }

    public static function obtenerEntero($clave, $valorPorDefecto = 0)
    {
        $valor = static::obtener($clave, $valorPorDefecto);
        return (int) $valor;
    }

    public static function obtenerDecimal($clave, $valorPorDefecto = 0.0)
    {
        $valor = static::obtener($clave, $valorPorDefecto);
        return (float) $valor;
    }

    public static function obtenerArray($clave, $valorPorDefecto = [])
    {
        $valor = static::obtener($clave);
        
        if ($valor === null) {
            return $valorPorDefecto;
        }

        $decodificado = json_decode($valor, true);
        return is_array($decodificado) ? $decodificado : $valorPorDefecto;
    }

    // Accessors para tipos específicos
    public function getValorBooleanAttribute()
    {
        return filter_var($this->valor, FILTER_VALIDATE_BOOLEAN);
    }

    public function getValorEnteroAttribute()
    {
        return (int) $this->valor;
    }

    public function getValorDecimalAttribute()
    {
        return (float) $this->valor;
    }

    public function getValorArrayAttribute()
    {
        $decodificado = json_decode($this->valor, true);
        return is_array($decodificado) ? $decodificado : [];
    }
}