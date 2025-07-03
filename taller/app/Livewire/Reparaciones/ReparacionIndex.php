<?php

namespace App\Livewire\Reparaciones;

use App\Models\Reparacion;
use App\Models\Usuario;
use App\Models\EstadoReparacion;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class ReparacionIndex extends Component
{
    use WithPagination;

    // Propiedades de búsqueda y filtros
    public string $search = '';
    public string $estadoFilter = '';
    public string $tecnicoFilter = '';
    public string $prioridadFilter = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public string $sortField = 'fecha_ingreso';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    // Modal de reparación
    public bool $showModal = false;
    public bool $showEstadoModal = false;
    public bool $showAsignarModal = false;
    public ?Reparacion $selectedReparacion = null;

    // Cambio de estado
    #[Rule('required|in:recibido,en_diagnostico,en_reparacion,esperando_repuestos,reparado,entregado,cancelado')]
    public string $nuevoEstado = '';
    
    #[Rule('nullable|string|max:500')]
    public string $observacionesEstado = '';

    // Asignar técnico
    #[Rule('required|exists:usuarios,id')]
    public string $tecnicoId = '';
    
    #[Rule('nullable|string|max:500')]
    public string $observacionesAsignacion = '';

    public function mount()
    {
        abort_unless(auth()->user()->can('reparaciones.ver'), 403);
    }

    public function getReparacionesProperty()
    {
        return Reparacion::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('codigo', 'like', '%' . $this->search . '%')
                      ->orWhere('tipo_equipo', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%')
                      ->orWhere('modelo', 'like', '%' . $this->search . '%')
                      ->orWhere('problema_reportado', 'like', '%' . $this->search . '%')
                      ->orWhereHas('cliente', function ($cliente) {
                          $cliente->where('nombre', 'like', '%' . $this->search . '%')
                                  ->orWhere('telefono', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->estadoFilter, function ($query) {
                $query->where('estado', $this->estadoFilter);
            })
            ->when($this->tecnicoFilter, function ($query) {
                $query->where('tecnico_id', $this->tecnicoFilter);
            })
            ->when($this->prioridadFilter, function ($query) {
                $query->where('prioridad', $this->prioridadFilter);
            })
            ->when($this->fechaDesde, function ($query) {
                $query->whereDate('fecha_ingreso', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                $query->whereDate('fecha_ingreso', '<=', $this->fechaHasta);
            })
            ->with(['cliente', 'tecnico'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getTecnicosProperty()
    {
        return Usuario::whereHas('roles', function($query) {
            $query->whereIn('name', ['tecnico', 'tecnico-senior', 'supervisor']);
        })->where('activo', true)->orderBy('nombres')->get();
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

    public function updatedTecnicoFilter()
    {
        $this->resetPage();
    }

    public function updatedPrioridadFilter()
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

    public function showReparacion(Reparacion $reparacion)
    {
        $this->selectedReparacion = $reparacion->load([
            'cliente', 
            'tecnico', 
            'estadosReparacion.usuario',
            'diagnosticos.usuario'
        ]);
        $this->showModal = true;
    }

    public function openEstadoModal(Reparacion $reparacion)
    {
        abort_unless(auth()->user()->can('reparaciones.asignar'), 403);
        
        $this->selectedReparacion = $reparacion;
        $this->nuevoEstado = $reparacion->estado;
        $this->observacionesEstado = '';
        $this->showEstadoModal = true;
    }

    public function openAsignarModal(Reparacion $reparacion)
    {
        abort_unless(auth()->user()->can('reparaciones.asignar'), 403);
        
        $this->selectedReparacion = $reparacion;
        $this->tecnicoId = $reparacion->tecnico_id ?? '';
        $this->observacionesAsignacion = '';
        $this->showAsignarModal = true;
    }

    public function cambiarEstado()
    {
        abort_unless(auth()->user()->can('reparaciones.asignar'), 403);
        
       $this->validate([
            'nuevoEstado' => 'required|in:recibido,diagnosticando,reparando,completado,entregado,cancelado',  // ✅ Estados consistentes con BD
            'observacionesEstado' => 'nullable|string|max:500',
       ]);

        DB::transaction(function () {
            $estadoAnterior = $this->selectedReparacion->estado;
            
            $this->selectedReparacion->update([
                'estado' => $this->nuevoEstado,
                'fecha_entrega' => $this->nuevoEstado === 'entregado' ? now() : null,
            ]);
            
            // Registrar cambio de estado
            EstadoReparacion::create([
                'reparacion_id' => $this->selectedReparacion->id,
                'estado' => $this->nuevoEstado,
                'fecha_cambio' => now(),
                'usuario_id' => auth()->id(),
                'observaciones' => $this->observacionesEstado ?? 'Cambio de estado',
            ]);
            
            // Log de actividad
            activity()
                ->performedOn($this->selectedReparacion)
                ->causedBy(auth()->user())
                ->withProperties([
                    'modulo' => 'reparaciones',
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $this->nuevoEstado
                ])
                ->log('Estado cambiado en reparación: ' . $this->selectedReparacion->codigo);
        });

        $this->closeEstadoModal();
        session()->flash('success', 'Estado actualizado correctamente');
    }

    public function asignarTecnico()
    {
        abort_unless(auth()->user()->can('reparaciones.asignar'), 403);
        
        $this->validate([
            'tecnicoId' => 'required|exists:usuarios,id',
            'observacionesAsignacion' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () {
            $this->selectedReparacion->update([
                'tecnico_id' => $this->tecnicoId,
                'estado' => $this->selectedReparacion->estado === 'recibido' ? 'asignado' : $this->selectedReparacion->estado
            ]);
            
            // Registrar cambio de estado si era necesario
            if ($this->selectedReparacion->estado === 'asignado') {
                EstadoReparacion::create([
                    'reparacion_id' => $this->selectedReparacion->id,
                    'estado' => 'asignado',
                    'fecha_cambio' => now(),
                    'usuario_id' => auth()->id(),
                    'observaciones' => $this->observacionesAsignacion ?? 'Técnico asignado',
                ]);
            }
            
            // Log de actividad
            activity()
                ->performedOn($this->selectedReparacion)
                ->causedBy(auth()->user())
                ->withProperties([
                    'modulo' => 'reparaciones',
                    'tecnico_asignado' => $this->tecnicoId
                ])
                ->log('Técnico asignado a reparación: ' . $this->selectedReparacion->codigo);
        });

        $this->closeAsignarModal();
        session()->flash('success', 'Técnico asignado correctamente');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedReparacion = null;
    }

    public function closeEstadoModal()
    {
        $this->showEstadoModal = false;
        $this->selectedReparacion = null;
        $this->reset(['nuevoEstado', 'observacionesEstado']);
        $this->resetErrorBag();
    }

    public function closeAsignarModal()
    {
        $this->showAsignarModal = false;
        $this->selectedReparacion = null;
        $this->reset(['tecnicoId', 'observacionesAsignacion']);
        $this->resetErrorBag();
    }

    public function getEstadoBadgeClass($estado)
    {
        return match($estado) {
            'recibido' => 'bg-gray-100 text-gray-800',
            'en_diagnostico' => 'bg-blue-100 text-blue-800',
            'en_reparacion' => 'bg-yellow-100 text-yellow-800',
            'esperando_repuestos' => 'bg-orange-100 text-orange-800',
            'reparado' => 'bg-green-100 text-green-800',
            'entregado' => 'bg-green-100 text-green-800',
            'cancelado' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getPrioridadBadgeClass($prioridad)
    {
        return match($prioridad) {
            'baja' => 'bg-gray-100 text-gray-800',
            'media' => 'bg-yellow-100 text-yellow-800',
            'alta' => 'bg-orange-100 text-orange-800',
            'urgente' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // Event listeners
    #[On('reparacion-created')]
    public function refreshReparaciones()
    {
        $this->resetPage();
        session()->flash('success', 'Reparación creada correctamente');
    }

    public function render()
    {
        return view('livewire.reparaciones.reparacion-index', [
            'reparaciones' => $this->reparaciones,
            'tecnicos' => $this->tecnicos,
        ]);
    }
}