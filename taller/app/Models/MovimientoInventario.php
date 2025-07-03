<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'usuario_id',
        'venta_id',
        'entrada_inventario_id',
        'reparacion_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'motivo',
        'observaciones',
        'documento_referencia',
        'costo_unitario',
        'precio_unitario',
        'lote',
        'fecha_vencimiento',
        'ubicacion_origen',
        'ubicacion_destino',
        'referencia_tipo',
        'referencia_id',
        'fecha_movimiento',
    ];

    protected $casts = [
        'fecha_movimiento' => 'datetime',
        'fecha_vencimiento' => 'date',
        'costo_unitario' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'cantidad' => 'integer',
        'stock_anterior' => 'integer',
        'stock_nuevo' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * RELACIONES
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function entradaInventario(): BelongsTo
    {
        return $this->belongsTo(EntradaInventario::class);
    }

    public function reparacion(): BelongsTo
    {
        return $this->belongsTo(Reparacion::class);
    }

    /**
     * SCOPES
     */
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    public function scopeSalidas($query)
    {
        return $query->where('tipo', 'salida');
    }

    public function scopeAjustes($query)
    {
        return $query->where('tipo', 'ajuste');
    }

    public function scopePorProducto($query, $productoId)
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_movimiento', [$fechaInicio, $fechaFin]);
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_movimiento', today());
    }

    /**
     * ACCESSORS
     */
    public function getTipoTextoAttribute()
    {
        return match($this->tipo) {
            'entrada' => 'Entrada',
            'salida' => 'Salida',
            'ajuste' => 'Ajuste',
            'transferencia' => 'Transferencia',
            default => 'Desconocido'
        };
    }

    public function getTipoColorAttribute()
    {
        return match($this->tipo) {
            'entrada' => 'green',
            'salida' => 'red',
            'ajuste' => 'blue',
            'transferencia' => 'purple',
            default => 'gray'
        };
    }

    public function getDiferenciaStockAttribute()
    {
        return $this->stock_nuevo - $this->stock_anterior;
    }

    public function getValorMovimientoAttribute()
    {
        $precio = $this->precio_unitario ?? $this->costo_unitario ?? 0;
        return $this->cantidad * $precio;
    }

    /**
     * MÉTODO PRINCIPAL PARA REGISTRAR MOVIMIENTOS
     */
    public static function registrar(array $datos)
    {
        // Obtener producto
        $producto = Producto::findOrFail($datos['producto_id']);
        $stockAnterior = $producto->stock;

        // Calcular nuevo stock
        $nuevoStock = match($datos['tipo']) {
            'entrada' => $stockAnterior + $datos['cantidad'],
            'salida' => $stockAnterior - $datos['cantidad'],
            'ajuste' => $datos['cantidad'],
            'transferencia' => $stockAnterior,
            default => throw new \Exception('Tipo de movimiento no válido: ' . $datos['tipo'])
        };

        // Validar que no quede stock negativo
        if ($nuevoStock < 0 && $datos['tipo'] !== 'ajuste') {
            throw new \Exception("Stock insuficiente. Stock actual: {$stockAnterior}, cantidad solicitada: {$datos['cantidad']}");
        }

        // Preparar datos del movimiento
        $datosMovimiento = array_merge($datos, [
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $nuevoStock,
            'fecha_movimiento' => $datos['fecha_movimiento'] ?? now(),
            'usuario_id' => $datos['usuario_id'] ?? auth()->id(),
        ]);

        // Crear el movimiento en una transacción
        return \DB::transaction(function () use ($producto, $nuevoStock, $datosMovimiento) {
            // Actualizar stock del producto (excepto en transferencias)
            if ($datosMovimiento['tipo'] !== 'transferencia') {
                $producto->update(['stock' => $nuevoStock]);
            }

            // Crear el registro del movimiento
            return self::create($datosMovimiento);
        });
    }

    /**
     * MÉTODOS DE CONVENIENCIA
     */
    public static function entrada($productoId, $cantidad, $motivo, $usuarioId = null, $datos = [])
    {
        return self::registrar(array_merge([
            'producto_id' => $productoId,
            'tipo' => 'entrada',
            'cantidad' => $cantidad,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId ?? auth()->id(),
        ], $datos));
    }

    public static function salida($productoId, $cantidad, $motivo, $usuarioId = null, $datos = [])
    {
        return self::registrar(array_merge([
            'producto_id' => $productoId,
            'tipo' => 'salida',
            'cantidad' => $cantidad,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId ?? auth()->id(),
        ], $datos));
    }

    public static function ajuste($productoId, $nuevoStock, $motivo, $usuarioId = null, $datos = [])
    {
        return self::registrar(array_merge([
            'producto_id' => $productoId,
            'tipo' => 'ajuste',
            'cantidad' => $nuevoStock,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId ?? auth()->id(),
        ], $datos));
    }
}