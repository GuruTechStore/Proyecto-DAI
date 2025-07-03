<?php

namespace App\Livewire\Productos;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\MovimientoStock;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class ProductoIndex extends Component
{
    use WithPagination;

    // Propiedades de búsqueda y filtros
    public string $search = '';
    public string $categoriaFilter = '';
    public string $stockFilter = '';
    public string $sortField = 'nombre';
    public string $sortDirection = 'asc';
    public int $perPage = 15;
    public array $selectedProductos = [];
    public bool $selectAll = false;

    // Modal de producto
    public bool $showModal = false;
    public bool $showStockModal = false;
    public ?Producto $selectedProducto = null;

    // Ajuste de stock
    #[Rule('required|in:entrada,salida,ajuste')]
    public string $tipoMovimiento = 'entrada';
    
    #[Rule('required|integer|min:1')]
    public int $cantidad = 1;
    
    #[Rule('required|string|max:255')]
    public string $motivo = '';
    
    #[Rule('nullable|string|max:500')]
    public string $observaciones = '';

    public function mount()
    {
        abort_unless(auth()->user()->can('productos.ver'), 403);
    }

    public function getProductosProperty()
    {
        return Producto::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('codigo', 'like', '%' . $this->search . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoriaFilter, function ($query) {
                $query->where('categoria_id', $this->categoriaFilter);
            })
            ->when($this->stockFilter, function ($query) {
                switch ($this->stockFilter) {
                    case 'sin_stock':
                        $query->where('stock_actual', 0);
                        break;
                    case 'bajo_stock':
                        $query->whereRaw('stock_actual <= stock_minimo AND stock_actual > 0');
                        break;
                    case 'stock_normal':
                        $query->whereRaw('stock_actual > stock_minimo');
                        break;
                }
            })
            ->with(['categoria', 'proveedor'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getCategoriasProperty()
    {
        return Categoria::where('activa', true)->orderBy('nombre')->get();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoriaFilter()
    {
        $this->resetPage();
    }

    public function updatedStockFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedProductos = $this->productos->pluck('id')->toArray();
        } else {
            $this->selectedProductos = [];
        }
    }

    public function showProducto(Producto $producto)
    {
        $this->selectedProducto = $producto->load(['categoria', 'proveedor', 'movimientosStock.usuario']);
        $this->showModal = true;
    }

    public function openStockModal(Producto $producto)
    {
        abort_unless(auth()->user()->can('inventario.ajustar'), 403);
        
        $this->selectedProducto = $producto;
        $this->showStockModal = true;
        $this->reset(['tipoMovimiento', 'cantidad', 'motivo', 'observaciones']);
        $this->tipoMovimiento = 'entrada';
        $this->cantidad = 1;
    }

    public function ajustarStock()
    {
        abort_unless(auth()->user()->can('inventario.ajustar'), 403);
        
        $this->validate();

        DB::transaction(function () {
            $producto = $this->selectedProducto;
            $stockAnterior = $producto->stock_actual;
            
            // Calcular nuevo stock
            switch ($this->tipoMovimiento) {
                case 'entrada':
                    $nuevoStock = $stockAnterior + $this->cantidad;
                    break;
                case 'salida':
                    $nuevoStock = max(0, $stockAnterior - $this->cantidad);
                    break;
                case 'ajuste':
                    $nuevoStock = $this->cantidad;
                    $this->cantidad = abs($nuevoStock - $stockAnterior);
                    break;
            }
            
            // Actualizar stock del producto
            $producto->update(['stock_actual' => $nuevoStock]);
            
            // Registrar movimiento
            MovimientoStock::create([
                'producto_id' => $producto->id,
                'tipo_movimiento' => $this->tipoMovimiento,
                'cantidad' => $this->cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $nuevoStock,
                'motivo' => $this->motivo,
                'observaciones' => $this->observaciones,
                'usuario_id' => auth()->id(),
                'fecha' => now(),
            ]);
            
            // Log de actividad
            activity()
                ->performedOn($producto)
                ->causedBy(auth()->user())
                ->withProperties([
                    'modulo' => 'inventario',
                    'tipo_movimiento' => $this->tipoMovimiento,
                    'cantidad' => $this->cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $nuevoStock
                ])
                ->log('Ajuste de stock: ' . $producto->nombre);
        });

        $this->closeStockModal();
        session()->flash('success', 'Stock ajustado correctamente');
    }

    public function deleteProducto(Producto $producto)
    {
        abort_unless(auth()->user()->can('productos.eliminar'), 403);
        
        try {
            // Verificar si tiene movimientos de stock
            if ($producto->movimientosStock()->count() > 0) {
                session()->flash('error', 'No se puede eliminar el producto. Tiene movimientos de stock registrados.');
                return;
            }
            
            $nombre = $producto->nombre;
            $producto->delete();
            
            // Log de actividad
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['modulo' => 'productos', 'producto_eliminado' => $nombre])
                ->log('Producto eliminado: ' . $nombre);
                
            session()->flash('success', 'Producto eliminado correctamente');
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar el producto. Tiene registros asociados.');
        }
    }

    public function deleteSelected()
    {
        abort_unless(auth()->user()->can('productos.eliminar'), 403);
        
        $deletedCount = 0;
        $errorCount = 0;
        
        foreach ($this->selectedProductos as $productoId) {
            try {
                $producto = Producto::find($productoId);
                if ($producto && $producto->movimientosStock()->count() === 0) {
                    $producto->delete();
                    $deletedCount++;
                } else {
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
            }
        }
        
        $this->selectedProductos = [];
        $this->selectAll = false;
        
        if ($deletedCount > 0) {
            session()->flash('success', "Se eliminaron {$deletedCount} productos correctamente.");
        }
        
        if ($errorCount > 0) {
            session()->flash('warning', "{$errorCount} productos no se pudieron eliminar porque tienen registros asociados.");
        }
    }

    public function toggleEstado(Producto $producto)
    {
        abort_unless(auth()->user()->can('productos.editar'), 403);
        
        $producto->update(['activo' => !$producto->activo]);
        
        $estado = $producto->activo ? 'activado' : 'desactivado';
        
        // Log de actividad
        activity()
            ->performedOn($producto)
            ->causedBy(auth()->user())
            ->withProperties(['modulo' => 'productos', 'nuevo_estado' => $producto->activo])
            ->log("Producto {$estado}: " . $producto->nombre);
        
        session()->flash('success', "Producto {$estado} correctamente");
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedProducto = null;
    }

    public function closeStockModal()
    {
        $this->showStockModal = false;
        $this->selectedProducto = null;
        $this->reset(['tipoMovimiento', 'cantidad', 'motivo', 'observaciones']);
        $this->resetErrorBag();
    }

    public function getStockStatusClass($producto)
    {
        if ($producto->stock_actual == 0) {
            return 'bg-red-100 text-red-800';
        } elseif ($producto->stock_actual <= $producto->stock_minimo) {
            return 'bg-yellow-100 text-yellow-800';
        } else {
            return 'bg-green-100 text-green-800';
        }
    }

    public function getStockStatusText($producto)
    {
        if ($producto->stock_actual == 0) {
            return 'Sin stock';
        } elseif ($producto->stock_actual <= $producto->stock_minimo) {
            return 'Bajo stock';
        } else {
            return 'Stock normal';
        }
    }

    // Event listeners
    #[On('producto-created')]
    public function refreshProductos()
    {
        $this->resetPage();
        session()->flash('success', 'Producto creado correctamente');
    }

    #[On('producto-updated')]
    public function handleProductoUpdated()
    {
        $this->resetPage();
        session()->flash('success', 'Producto actualizado correctamente');
    }

    public function render()
    {
        return view('livewire.productos.producto-index', [
            'productos' => $this->productos,
            'categorias' => $this->categorias,
        ]);
    }
}