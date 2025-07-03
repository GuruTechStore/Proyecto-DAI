<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'categoria_id',
        'proveedor_id',
        'precio_compra',
        'precio_venta',
        'stock',
        'stock_minimo',
        'unidad_medida',
        'ubicacion',
        'imagen_url',
        'garantia_dias',
        'activo'
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'garantia_dias' => 'integer',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * RELACIONES
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    public function ventas()
    {
        return $this->hasManyThrough(Venta::class, DetalleVenta::class, 'producto_id', 'id', 'id', 'venta_id');
    }

    public function ajustesInventario()
    {
        return $this->hasMany(AjusteInventario::class, 'producto_id');
    }

    // ✅ NUEVA RELACIÓN CON MOVIMIENTOS
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    public function productosReparacion()
    {
        return $this->hasMany(ProductoReparacion::class, 'producto_id');
    }

    /**
     * SCOPES
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeStockBajo($query)
    {
        return $query->whereRaw('stock <= stock_minimo');
    }

    public function scopeSinStock($query)
    {
        return $query->where('stock', 0);
    }

    public function scopeBuscar($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nombre', 'like', "%{$search}%")
              ->orWhere('codigo', 'like', "%{$search}%")
              ->orWhere('descripcion', 'like', "%{$search}%");
        });
    }

    public function scopeCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    public function scopeProveedor($query, $proveedorId)
    {
        return $query->where('proveedor_id', $proveedorId);
    }

    /**
     * MÉTODOS DE VALIDACIÓN
     */
    public function tieneStock($cantidad = 1)
    {
        return $this->stock >= $cantidad;
    }

    public function esStockMinimo()
    {
        return $this->stock <= $this->stock_minimo;
    }

    public function debeAlertarStock()
    {
        $alertarStockBajo = Setting::get('inventario_alertas_stock_bajo', true);
        return $alertarStockBajo && $this->esStockMinimo();
    }

    /**
     * ACCESSORS
     */
    public function getMargenGananciaAttribute()
    {
        if ($this->precio_compra > 0 && $this->precio_venta > 0) {
            return (($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100;
        }
        return 0;
    }

    public function getValorInventarioAttribute()
    {
        return ($this->precio_venta ?? $this->precio_compra) * $this->stock;
    }

    public function getImagenUrlCompleteAttribute()
    {
        return $this->imagen_url ? asset('storage/' . $this->imagen_url) : asset('images/producto-default.png');
    }

    public function getEstadoStockAttribute()
    {
        if ($this->stock <= 0) {
            return ['texto' => 'Sin Stock', 'color' => 'red', 'nivel' => 'critico'];
        } elseif ($this->esStockMinimo()) {
            return ['texto' => 'Stock Bajo', 'color' => 'yellow', 'nivel' => 'bajo'];
        } else {
            return ['texto' => 'Stock Normal', 'color' => 'green', 'nivel' => 'normal'];
        }
    }

    /**
     * ✅ MÉTODOS ACTUALIZADOS PARA USAR MOVIMIENTOS
     */
    public function reducirStock($cantidad, $motivo = 'venta', $usuarioId = null, $datos = [])
    {
        if (!$this->tieneStock($cantidad)) {
            throw new \Exception("Stock insuficiente. Disponible: {$this->stock}, solicitado: {$cantidad}");
        }

        $datosMovimiento = array_merge($datos, [
            'producto_id' => $this->id,
            'tipo' => 'salida',
            'cantidad' => $cantidad,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId ?? auth()->id()
        ]);

        $movimiento = MovimientoInventario::registrar($datosMovimiento);

        // Verificar si debe alertar stock bajo
        if ($this->fresh()->debeAlertarStock()) {
            $this->enviarAlertaStockBajo();
        }

        return $movimiento;
    }

    public function aumentarStock($cantidad, $motivo = 'compra', $usuarioId = null, $datos = [])
    {
        $datosMovimiento = array_merge($datos, [
            'producto_id' => $this->id,
            'tipo' => 'entrada',
            'cantidad' => $cantidad,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId ?? auth()->id()
        ]);

        return MovimientoInventario::registrar($datosMovimiento);
    }

    public function ajustarStock($nuevoStock, $motivo = 'ajuste manual', $usuarioId = null, $datos = [])
    {
        $datosMovimiento = array_merge($datos, [
            'producto_id' => $this->id,
            'tipo' => 'ajuste',
            'cantidad' => $nuevoStock,
            'motivo' => $motivo,
            'usuario_id' => $usuarioId ?? auth()->id()
        ]);

        $movimiento = MovimientoInventario::registrar($datosMovimiento);

        // Verificar alertas después del ajuste
        if ($this->fresh()->debeAlertarStock()) {
            $this->enviarAlertaStockBajo();
        }

        return $movimiento;
    }

    /**
     * ✅ NUEVO MÉTODO PARA ALERTAS DE STOCK
     */
    public function enviarAlertaStockBajo()
    {
        // Obtener usuarios que deben recibir alertas
        $usuariosAlerta = Usuario::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Gerente', 'Supervisor']);
        })->pluck('id');

        // Crear notificaciones
        foreach ($usuariosAlerta as $usuarioId) {
            Notificacion::crear(
                Notificacion::TIPO_STOCK_BAJO,
                $usuarioId,
                'Stock Bajo: ' . $this->nombre,
                "El producto '{$this->nombre}' (Código: {$this->codigo}) tiene stock bajo: {$this->stock} unidades disponibles (Mínimo: {$this->stock_minimo})",
                [
                    'prioridad' => $this->stock <= 0 ? Notificacion::PRIORIDAD_CRITICA : Notificacion::PRIORIDAD_ALTA,
                    'entidad' => 'producto',
                    'entidad_id' => $this->id,
                    'enlace' => route('productos.show', $this->id)
                ]
            );
        }
    }

    /**
     * MÉTODOS DE REPORTES Y ESTADÍSTICAS
     */
    public function getMovimientosRecientes($dias = 30)
    {
        return $this->movimientos()
                   ->where('fecha_movimiento', '>=', now()->subDays($dias))
                   ->orderBy('fecha_movimiento', 'desc')
                   ->get();
    }

    public function getEstadisticasMovimientos($dias = 30)
    {
        $movimientos = $this->getMovimientosRecientes($dias);

        return [
            'total_entradas' => $movimientos->where('tipo', 'entrada')->sum('cantidad'),
            'total_salidas' => $movimientos->where('tipo', 'salida')->sum('cantidad'),
            'total_ajustes' => $movimientos->where('tipo', 'ajuste')->count(),
            'ultimo_movimiento' => $movimientos->first()?->fecha_movimiento,
            'rotacion' => $this->calcularRotacion($dias)
        ];
    }

    private function calcularRotacion($dias)
    {
        $totalSalidas = $this->movimientos()
                            ->where('tipo', 'salida')
                            ->where('fecha_movimiento', '>=', now()->subDays($dias))
                            ->sum('cantidad');

        $stockPromedio = ($this->stock + $this->stock_minimo) / 2;
        
        return $stockPromedio > 0 ? round($totalSalidas / $stockPromedio, 2) : 0;
    }

    /**
     * MÉTODOS ESTÁTICOS
     */
    public static function generarCodigo($categoria = null)
    {
        $prefijo = Setting::get('productos_codigo_prefijo', 'PROD');
        $ultimo = self::latest('id')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        
        if ($categoria) {
            $prefijo .= '-' . strtoupper(substr($categoria, 0, 3));
        }
        
        return $prefijo . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public static function getProductosStockBajo()
    {
        return self::stockBajo()->activos()->with(['categoria', 'proveedor'])->get();
    }

    public static function getProductosMasVendidos($dias = 30, $limite = 10)
    {
        return self::select('productos.*')
                   ->join('movimientos_inventario', 'productos.id', '=', 'movimientos_inventario.producto_id')
                   ->where('movimientos_inventario.tipo', 'salida')
                   ->where('movimientos_inventario.motivo', 'venta')
                   ->where('movimientos_inventario.fecha_movimiento', '>=', now()->subDays($dias))
                   ->groupBy('productos.id')
                   ->orderByRaw('SUM(movimientos_inventario.cantidad) DESC')
                   ->limit($limite)
                   ->get();
    }

    /**
     * BOOT METHOD
     */
    protected static function boot()
    {
        parent::boot();

        // Validar al crear/actualizar
        static::saving(function ($producto) {
            // Generar código si no existe
            if (empty($producto->codigo)) {
                $producto->codigo = self::generarCodigo($producto->categoria?->nombre);
            }

            // Validar precio de venta mayor que compra
            if ($producto->precio_venta && $producto->precio_compra && 
                $producto->precio_venta <= $producto->precio_compra) {
                throw new \Exception('El precio de venta debe ser mayor al precio de compra');
            }
        });

        // Registrar movimiento de ajuste al actualizar stock manualmente
        static::updated(function ($producto) {
            if ($producto->isDirty('stock')) {
                $stockAnterior = $producto->getOriginal('stock');
                $stockNuevo = $producto->stock;
                
                // Solo registrar si el cambio no fue por un movimiento ya registrado
                if (!$producto->skip_movement_log) {
                    MovimientoInventario::create([
                        'producto_id' => $producto->id,
                        'usuario_id' => auth()->id(),
                        'tipo' => 'ajuste',
                        'cantidad' => $stockNuevo,
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'motivo' => 'Ajuste manual',
                        'fecha_movimiento' => now()
                    ]);
                }
            }
        });
    }
}