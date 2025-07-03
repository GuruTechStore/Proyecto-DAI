<?php

namespace App\Livewire\Ventas;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\MovimientoStock;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\DB;

class VentaForm extends Component
{
    #[Rule('required|exists:clientes,id', message: 'Seleccione un cliente válido')]
    public string $cliente_id = '';
    
    #[Rule('required|in:boleta,factura,ticket', message: 'Seleccione un tipo de comprobante válido')]
    public string $tipo_comprobante = 'boleta';
    
    #[Rule('required|in:efectivo,tarjeta,transferencia,yape,plin', message: 'Seleccione un método de pago válido')]
    public string $metodo_pago = 'efectivo';
    
    #[Rule('nullable|string|max:500', message: 'Las observaciones deben tener máximo 500 caracteres')]
    public string $observaciones = '';

    // Productos de la venta
    public array $productosVenta = [];
    public string $buscarProducto = '';
    public array $productosDisponibles = [];
    public bool $mostrarProductos = false;

    // Totales
    public float $subtotal = 0;
    public float $igv = 0;
    public float $total = 0;

    public function mount()
    {
        abort_unless(auth()->user()->can('ventas.crear'), 403);
    }

    public function getClientesProperty()
    {
        return Cliente::orderBy('nombre')->get();
    }

    public function updatedBuscarProducto($value)
    {
        if (strlen($value) >= 2) {
            $this->productosDisponibles = Producto::where('activo', true)
                ->where('stock_actual', '>', 0)
                ->where(function ($query) use ($value) {
                    $query->where('nombre', 'like', '%' . $value . '%')
                          ->orWhere('codigo', 'like', '%' . $value . '%');
                })
                ->limit(10)
                ->get()
                ->toArray();
            $this->mostrarProductos = true;
        } else {
            $this->productosDisponibles = [];
            $this->mostrarProductos = false;
        }
    }

    public function agregarProducto($productoId)
    {
        $producto = Producto::find($productoId);
        
        if (!$producto || $producto->stock_actual <= 0) {
            session()->flash('error', 'Producto no disponible o sin stock');
            return;
        }

        // Verificar si ya está en la lista
        $existe = false;
        foreach ($this->productosVenta as $index => $item) {
            if ($item['id'] == $productoId) {
                // Incrementar cantidad si no excede el stock
                if ($item['cantidad'] < $producto->stock_actual) {
                    $this->productosVenta[$index]['cantidad']++;
                    $this->productosVenta[$index]['subtotal'] = 
                        $this->productosVenta[$index]['cantidad'] * $this->productosVenta[$index]['precio'];
                } else {
                    session()->flash('error', 'No hay suficiente stock disponible');
                    return;
                }
                $existe = true;
                break;
            }
        }

        if (!$existe) {
            $this->productosVenta[] = [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio_venta,
                'cantidad' => 1,
                'stock_disponible' => $producto->stock_actual,
                'subtotal' => $producto->precio_venta,
            ];
        }

        $this->calcularTotales();
        $this->buscarProducto = '';
        $this->productosDisponibles = [];
        $this->mostrarProductos = false;
    }

    public function removerProducto($index)
    {
        unset($this->productosVenta[$index]);
        $this->productosVenta = array_values($this->productosVenta);
        $this->calcularTotales();
    }

    public function actualizarCantidad($index, $cantidad)
    {
        if ($cantidad <= 0) {
            $this->removerProducto($index);
            return;
        }

        $producto = $this->productosVenta[$index];
        
        if ($cantidad > $producto['stock_disponible']) {
            session()->flash('error', 'Cantidad excede el stock disponible');
            return;
        }

        $this->productosVenta[$index]['cantidad'] = $cantidad;
        $this->productosVenta[$index]['subtotal'] = $cantidad * $producto['precio'];
        
        $this->calcularTotales();
    }

    public function actualizarPrecio($index, $precio)
    {
        if ($precio < 0) {
            return;
        }

        $this->productosVenta[$index]['precio'] = $precio;
        $this->productosVenta[$index]['subtotal'] = 
            $this->productosVenta[$index]['cantidad'] * $precio;
        
        $this->calcularTotales();
    }

    private function calcularTotales()
    {
        $this->subtotal = array_sum(array_column($this->productosVenta, 'subtotal'));
        $this->igv = $this->subtotal * 0.18;
        $this->total = $this->subtotal + $this->igv;
    }

    public function procesarVenta()
    {
        $this->validate();

        if (empty($this->productosVenta)) {
            $this->addError('productos', 'Debe agregar al menos un producto a la venta');
            return;
        }

        // Validar stock antes de procesar
        foreach ($this->productosVenta as $item) {
            $producto = Producto::find($item['id']);
            if ($producto->stock_actual < $item['cantidad']) {
                $this->addError('productos', "Stock insuficiente para {$producto->nombre}");
                return;
            }
        }

        DB::transaction(function () {
            // Generar número de comprobante
            $numeroComprobante = $this->generarNumeroComprobante();
            
            // Crear la venta
            $venta = Venta::create([
                'cliente_id' => $this->cliente_id,
                'usuario_id' => auth()->id(),
                'numero_comprobante' => $numeroComprobante,
                'tipo_comprobante' => $this->tipo_comprobante,
                'metodo_pago' => $this->metodo_pago,
                'fecha_venta' => now(),
                'estado' => 'completada',
                'observaciones' => $this->observaciones ?: null,
                'subtotal' => $this->subtotal,
                'igv' => $this->igv,
                'total' => $this->total,
            ]);
            
            // Procesar cada producto
            foreach ($this->productosVenta as $item) {
                $producto = Producto::find($item['id']);
                
                // Crear detalle de venta
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $item['subtotal'],
                ]);
                
                // Actualizar stock del producto
                $producto->decrement('stock_actual', $item['cantidad']);
                
                // Registrar movimiento de stock
                MovimientoStock::create([
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => 'salida',
                    'cantidad' => $item['cantidad'],
                    'stock_anterior' => $producto->stock_actual + $item['cantidad'],
                    'stock_nuevo' => $producto->stock_actual,
                    'motivo' => 'Venta - ' . $numeroComprobante,
                    'usuario_id' => auth()->id(),
                    'venta_id' => $venta->id,
                    'fecha' => now(),
                ]);
            }
            
            // Log de actividad
            activity()
                ->performedOn($venta)
                ->causedBy(auth()->user())
                ->withProperties(['modulo' => 'ventas'])
                ->log('Venta procesada: ' . $numeroComprobante);
        });

        $this->dispatch('venta-created');
        session()->flash('success', 'Venta procesada correctamente');
        return redirect()->route('ventas.index');
    }

    private function generarNumeroComprobante()
    {
        $prefijo = match($this->tipo_comprobante) {
            'boleta' => 'B001-',
            'factura' => 'F001-',
            'ticket' => 'T001-',
            default => 'V001-'
        };
        
        $ultimoNumero = Venta::where('tipo_comprobante', $this->tipo_comprobante)
            ->whereYear('created_at', date('Y'))
            ->count();
            
        return $prefijo . str_pad($ultimoNumero + 1, 6, '0', STR_PAD_LEFT);
    }

    public function cancel()
    {
        return redirect()->route('ventas.index');
    }

    public function render()
    {
        return view('livewire.ventas.venta-form', [
            'clientes' => $this->clientes,
        ]);
    }
}