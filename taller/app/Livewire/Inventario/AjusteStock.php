<?php

namespace App\Livewire\Inventario;

use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\AjusteInventario;
use Livewire\Component;
use Livewire\Attributes\Rule;

class AjusteStock extends Component
{
    public ?Producto $producto = null;
    
    #[Rule('required|in:entrada,salida,correccion')]
    public string $tipoAjuste = 'entrada';
    
    #[Rule('required|integer|min:1')]
    public int $cantidad = 1;
    
    #[Rule('required|string|max:500')]
    public string $observaciones = '';

    // Para búsqueda de producto
    public string $buscarProducto = '';
    public array $productosEncontrados = [];
    public bool $mostrarProductos = false;

    public function mount(?Producto $producto = null)
    {
        abort_unless(auth()->user()->can('inventario.ajustar'), 403);
        
        if ($producto) {
            $this->producto = $producto;
            $this->buscarProducto = $producto->nombre . ' (' . $producto->codigo . ')';
        }
    }

    public function updatedBuscarProducto()
    {
        if (strlen($this->buscarProducto) >= 2) {
            $this->productosEncontrados = Producto::where(function($query) {
                $query->where('nombre', 'like', '%' . $this->buscarProducto . '%')
                      ->orWhere('codigo', 'like', '%' . $this->buscarProducto . '%');
            })
            ->where('activo', true)
            ->limit(10)
            ->get()
            ->toArray();
            
            $this->mostrarProductos = count($this->productosEncontrados) > 0;
        } else {
            $this->productosEncontrados = [];
            $this->mostrarProductos = false;
        }
    }

    public function seleccionarProducto($productoId)
    {
        $this->producto = Producto::find($productoId);
        if ($this->producto) {
            $this->buscarProducto = $this->producto->nombre . ' (' . $this->producto->codigo . ')';
            $this->mostrarProductos = false;
            $this->productosEncontrados = [];
        }
    }

    public function save()
    {
        $this->validate([
            'producto' => 'required',
            'tipoAjuste' => 'required|in:entrada,salida,correccion',
            'cantidad' => 'required|integer|min:1',
            'observaciones' => 'required|string|max:500',
        ]);

        if (!$this->producto) {
            session()->flash('error', 'Debe seleccionar un producto');
            return;
        }

        try {
            $stockAnterior = $this->producto->stock_actual;
            
            // Calcular nuevo stock
            switch ($this->tipoAjuste) {
                case 'entrada':
                    $nuevoStock = $stockAnterior + $this->cantidad;
                    $diferencia = $this->cantidad;
                    break;
                case 'salida':
                    $nuevoStock = max(0, $stockAnterior - $this->cantidad);
                    $diferencia = -$this->cantidad;
                    break;
                case 'correccion':
                    $nuevoStock = $this->cantidad;
                    $diferencia = $nuevoStock - $stockAnterior;
                    break;
            }
            
            // Actualizar producto
            $this->producto->update(['stock_actual' => $nuevoStock]);
            
            // Registrar ajuste
            AjusteInventario::create([
                'producto_id' => $this->producto->id,
                'tipo_ajuste' => $this->tipoAjuste,
                'cantidad_anterior' => $stockAnterior,
                'diferencia' => $diferencia,
                'observaciones' => $this->observaciones,
                'usuario_id' => auth()->user()->empleado->id ?? auth()->id(),
            ]);
            
            // Registrar movimiento
            MovimientoInventario::create([
                'producto_id' => $this->producto->id,
                'usuario_id' => auth()->user()->id,
                'tipo' => 'ajuste',
                'cantidad' => abs($diferencia),
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $nuevoStock,
                'motivo' => "Ajuste manual: {$this->tipoAjuste}",
                'observaciones' => $this->observaciones,
                'fecha_movimiento' => now(),
            ]);
            
            activity()
                ->performedOn($this->producto)
                ->causedBy(auth()->user())
                ->log("Ajuste de inventario: {$this->tipoAjuste}");

            session()->flash('success', 'Ajuste registrado correctamente');
            
            // Reset form
            $this->reset(['tipoAjuste', 'cantidad', 'observaciones', 'buscarProducto']);
            $this->producto = null;
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al registrar el ajuste: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.inventario.ajuste-stock');
    }
}