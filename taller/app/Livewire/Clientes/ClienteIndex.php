<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use Livewire\Attributes\On;

class ClienteIndex extends Component
{
    use WithPagination;

    // Propiedades de búsqueda y filtros
    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;
    public array $selectedClientes = [];
    public bool $selectAll = false;

    // Modal de cliente
    public bool $showModal = false;
    public ?Cliente $selectedCliente = null;

    // Formulario de edición rápida
    #[Rule('required|string|max:100')]
    public string $nombre = '';
    
    #[Rule('nullable|string|max:100')]
    public string $apellido = '';
    
    #[Rule('required|string|max:20')]
    public string $telefono = '';
    
    #[Rule('nullable|email|max:100')]
    public string $email = '';

    #[Rule('nullable|string|max:255')]
    public string $direccion = '';

    #[Rule('nullable|string|max:20')]
    public string $dni = '';

    // Lifecycle hooks
    public function mount()
    {
        abort_unless(auth()->user()->can('clientes.ver'), 403);
    }

    // Computed properties
    public function getClientesProperty()
    {
        return Cliente::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido', 'like', '%' . $this->search . '%')
                      ->orWhere('telefono', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('dni', 'like', '%' . $this->search . '%');
                });
            })
            ->withCount(['equipos', 'reparaciones', 'ventas'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // Actions
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

    public function showCliente(Cliente $cliente)
    {
        $this->selectedCliente = $cliente;
        $this->showModal = true;
    }

    public function editCliente(Cliente $cliente)
    {
        abort_unless(auth()->user()->can('clientes.editar'), 403);
        
        $this->selectedCliente = $cliente;
        $this->nombre = $cliente->nombre;
        $this->apellido = $cliente->apellido ?? '';
        $this->telefono = $cliente->telefono;
        $this->email = $cliente->email ?? '';
        $this->direccion = $cliente->direccion ?? '';
        $this->dni = $cliente->dni ?? '';
        $this->showModal = true;
    }

    public function saveCliente()
    {
        abort_unless(auth()->user()->can('clientes.editar'), 403);
        
        // Validación dinámica para email y DNI únicos
        $rules = $this->rules;
        if ($this->selectedCliente) {
            $rules['email'] = 'nullable|email|max:100|unique:clientes,email,' . $this->selectedCliente->id;
            $rules['dni'] = 'nullable|string|max:20|unique:clientes,dni,' . $this->selectedCliente->id;
        }
        
        $this->validate($rules);

        $this->selectedCliente->update([
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'dni' => $this->dni,
        ]);

        $this->dispatch('cliente-updated');
        $this->closeModal();
        
        session()->flash('success', 'Cliente actualizado correctamente');
    }

    public function deleteCliente(Cliente $cliente)
    {
        abort_unless(auth()->user()->can('clientes.eliminar'), 403);
        
        try {
            $cliente->delete();
            
            // Log de actividad
            activity()
                ->performedOn($cliente)
                ->causedBy(auth()->user())
                ->log('Cliente eliminado');
                
            session()->flash('success', 'Cliente eliminado correctamente');
        } catch (\Exception $e) {
            session()->flash('error', 'No se puede eliminar el cliente. Tiene registros asociados.');
        }
    }

    public function deleteSelected()
    {
        abort_unless(auth()->user()->can('clientes.eliminar'), 403);
        
        Cliente::whereIn('id', $this->selectedClientes)->delete();
        $this->selectedClientes = [];
        $this->selectAll = false;
        
        session()->flash('success', 'Clientes eliminados correctamente');
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedClientes = $this->clientes->pluck('id')->toArray();
        } else {
            $this->selectedClientes = [];
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedCliente = null;
        $this->reset(['nombre', 'apellido', 'telefono', 'email', 'direccion', 'dni']);
    }

    // Event listeners
    #[On('cliente-created')]
    public function refreshClientes()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.clientes.cliente-index', [
            'clientes' => $this->clientes,
        ]);
    }
}