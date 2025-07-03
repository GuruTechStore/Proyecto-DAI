<?php

namespace App\Livewire\Ventas;

use App\Models\Venta;
use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\On;

class VentaIndex extends Component
{
    use WithPagination;

    // Propiedades de búsqueda y filtros
    public string $search = '';
    public string $estadoFilter = '';
    public string $clienteFilter = '';
    public string $metodoPagoFilter = '';
    public string $tipoComprobanteFilter = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public string $sortField = 'fecha_venta';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    // Modal de venta
    public bool $showModal = false;
    public bool $showAnularModal = false;
    public ?Venta $selectedVenta = null;

    // Anular venta
    #[Rule('required|string|max:500', message: 'Debe especificar el motivo de la anulación')]
    public string $motivoAnulacion = '';

    public function mount()
    {
        abort_unless(auth()->user()->can('ventas.ver'), 403);
    }

    public function getVentasProperty()
    {
        return Venta::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('numero_comprobante', 'like', '%' . $this->search . '%')
                      ->orWhere('observaciones', 'like', '%' . $this->search . '%')
                      ->orWhereHas('cliente', function ($cliente) {
                          $cliente->where('nombre', 'like', '%' . $this->search . '%')
                                  ->orWhere('telefono', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->estadoFilter, function ($query) {
                $query->where('estado', $this->estadoFilter);
            })
            ->when($this->clienteFilter, function ($query) {
                $query->where('cliente_id', $this->clienteFilter);
            })
            ->when($this->metodoPagoFilter, function ($query) {
                $query->where('metodo_pago', $this->metodoPagoFilter);
            })
            ->when($this->tipoComprobanteFilter, function ($query) {
                $query->where('tipo_comprobante', $this->tipoComprobanteFilter);
            })
            ->when($this->fechaDesde, function ($query) {
                $query->whereDate('fecha_venta', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                $query->whereDate('fecha_venta', '<=', $this->fechaHasta);
            })
            ->with(['cliente', 'usuario'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getClientesProperty()
    {
        return Cliente::orderBy('nombre')->get();
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

    public function updatedEstadoFilter()
    {
        $this->resetPage();
    }

    public function updatedClienteFilter()
    {
        $this->resetPage();
    }

    public function updatedMetodoPagoFilter()
    {
        $this->resetPage();
    }

    public function updatedTipoComprobanteFilter()
    {
        $this->resetPage();
    }

    public function updatedFechaDesde()
    {
        $this->resetPage();
    }

    public function updatedFechaHasta()
    {
        $this->resetPage();
    }

    public function showVenta(Venta $venta)
    {
        $this->selectedVenta = $venta->load(['cliente', 'usuario', 'detalles.producto']);
        $this->showModal = true;
    }

    public function openAnularModal(Venta $venta)
    {
        abort_unless(auth()->user()->can('ventas.anular'), 403);
        
        if ($venta->estado === 'anulada') {
            session()->flash('error', 'La venta ya está anulada');
            return;
        }
        
        $this->selectedVenta = $venta;
        $this->motivoAnulacion = '';
        $this->showAnularModal = true;
    }

    public function anularVenta()
    {
        abort_unless(auth()->user()->can('ventas.anular'), 403);
        
        $this->validate([
            'motivoAnulacion' => 'required|string|max:500',
        ]);

        // Verificar que la venta no esté ya anulada
        if ($this->selectedVenta->estado === 'anulada') {
            session()->flash('error', 'La venta ya está anulada');
            $this->closeAnularModal();
            return;
        }

        try {
            app(VentaController::class)->anular(
                request()->merge(['motivo_anulacion' => $this->motivoAnulacion]),
                $this->selectedVenta
            );
            
            $this->closeAnularModal();
            session()->flash('success', 'Venta anulada correctamente');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al anular la venta: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedVenta = null;
    }

    public function closeAnularModal()
    {
        $this->showAnularModal = false;
        $this->selectedVenta = null;
        $this->motivoAnulacion = '';
        $this->resetErrorBag();
    }

    public function getEstadoBadgeClass($estado)
    {
        return match($estado) {
            'pendiente' => 'bg-yellow-100 text-yellow-800',
            'completada' => 'bg-green-100 text-green-800',
            'anulada' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getMetodoPagoBadgeClass($metodo)
    {
        return match($metodo) {
            'efectivo' => 'bg-green-100 text-green-800',
            'tarjeta' => 'bg-blue-100 text-blue-800',
            'transferencia' => 'bg-purple-100 text-purple-800',
            'yape' => 'bg-yellow-100 text-yellow-800',
            'plin' => 'bg-pink-100 text-pink-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getTotalVentas()
    {
        return $this->ventas->where('estado', 'completada')->sum('total');
    }

    public function getCantidadVentas()
    {
        return $this->ventas->where('estado', 'completada')->count();
    }

    // Event listeners
    #[On('venta-created')]
    public function refreshVentas()
    {
        $this->resetPage();
        session()->flash('success', 'Venta procesada correctamente');
    }

    public function render()
    {
        return view('livewire.ventas.venta-index', [
            'ventas' => $this->ventas,
            'clientes' => $this->clientes,
            'totalVentas' => $this->getTotalVentas(),
            'cantidadVentas' => $this->getCantidadVentas(),
        ]);
    }
}