<?php

namespace App\Livewire\Inventario;

use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\AjusteInventario;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;

class InventarioIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filtroStock = '';
    public string $filtroCategoria = '';
    public int $perPage = 15;

    // Modal de ajuste
    public bool $showAjusteModal = false;
    public ?Producto $selectedProducto = null;
    
    #[Rule('required|in:entrada,salida,correccion')]
    public string $tipoAjuste = 'entrada';
    
    #[Rule('required|integer|min:1')]
    public int $cantidad = 1;
    
    #[Rule('required|string|max:500')]
    public string $observaciones = '';

    public function mount()
    {
        abort_unless(auth()->user()->can('inventario.ver'), 403);
    }

    public function getProductosProperty()
    {
        return Producto::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('codigo', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filtroStock === 'sin_stock', function ($query) {
                $query->where('stock_actual', 0);
            })
            ->when($this->filtroStock === 'bajo_stock', function ($query) {
                $query->whereRaw('stock_actual <= stock_minimo AND stock_actual > 0');
            })
            ->when($this->filtroCategoria, function ($query) {
                $query->where('categoria_id', $this->filtroCategoria);
            })
            ->where('activo', true)
            ->with(['categoria'])
            ->orderBy('nombre')
            ->paginate($this->perPage);
    }

    public function abrirAjuste(Producto $producto)
    {
        abort_unless(auth()->user()->can('inventario.ajustar'), 403);
        
        $this->selectedProducto = $producto;
        $this->cantidad = 1;
        $this->observaciones = '';
        $this->showAjusteModal = true;
    }

    public function guardarAjuste()
    {
        abort_unless(auth()->user()->can('inventario.ajustar'), 403);
        
        $this->validate();

        try {
            $stockAnterior = $this->selectedProducto->stock_actual;
            
            // Calcular nuevo stock según tipo de ajuste
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
            
            // Actualizar stock
            $this->selectedProducto->update(['stock_actual' => $nuevoStock]);
            
            // Registrar ajuste
            AjusteInventario::create([
                'producto_id' => $this->selectedProducto->id,
                'tipo_ajuste' => $this->tipoAjuste,
                'cantidad_anterior' => $stockAnterior,
                'diferencia' => $diferencia,
                'observaciones' => $this->observaciones,
                'usuario_id' => auth()->user()->empleado->id ?? auth()->id(),
            ]);
            
            // Registrar movimiento
            MovimientoInventario::create([
                'producto_id' => $this->selectedProducto->id,
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
                ->performedOn($this->selectedProducto)
                ->causedBy(auth()->user())
                ->log("Ajuste de inventario: {$this->tipoAjuste}");

            $this->closeAjusteModal();
            session()->flash('success', 'Ajuste de inventario registrado correctamente');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al registrar el ajuste: ' . $e->getMessage());
        }
    }

    public function closeAjusteModal()
    {
        $this->showAjusteModal = false;
        $this->selectedProducto = null;
        $this->reset(['tipoAjuste', 'cantidad', 'observaciones']);
    }

    public function render()
    {
        return view('livewire.inventario.inventario-index', [
            'productos' => $this->productos,
        ]);
    }
}