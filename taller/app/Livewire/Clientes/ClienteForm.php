<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\Attributes\Rule;

class ClienteForm extends Component
{
    public ?Cliente $cliente = null;
    public bool $isEditing = false;

    #[Rule('required|string|max:100', message: 'El nombre es obligatorio y debe tener máximo 100 caracteres')]
    public string $nombre = '';
    
    #[Rule('nullable|string|max:100', message: 'El apellido debe tener máximo 100 caracteres')]
    public string $apellido = '';
    
    #[Rule('required|string|max:20', message: 'El teléfono es obligatorio y debe tener máximo 20 caracteres')]
    public string $telefono = '';
    
    #[Rule('nullable|email|max:100', message: 'Debe ser un email válido con máximo 100 caracteres')]
    public string $email = '';
            
    public function mount(?Cliente $cliente = null)
    {
        if ($cliente && $cliente->exists) {
            $this->isEditing = true;
            $this->cliente = $cliente;
            $this->nombre = $cliente->nombre;
            $this->apellido = $cliente->apellido ?? '';
            $this->telefono = $cliente->telefono;
            $this->email = $cliente->email ?? '';
        }
        
        // Verificar permisos
        if ($this->isEditing) {
            abort_unless(auth()->user()->can('clientes.editar'), 403);
        } else {
            abort_unless(auth()->user()->can('clientes.crear'), 403);
        }
    }

    public function save()
    {
        // Validar datos básicos
        $this->validate();
        
        // Validación adicional para email único
        if ($this->email) {
            $query = Cliente::where('email', $this->email);
            if ($this->isEditing) {
                $query->where('id', '!=', $this->cliente->id);
            }
            
            if ($query->exists()) {
                $this->addError('email', 'Este email ya está registrado por otro cliente.');
                return;
            }
        }

        $data = [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido ?: null,
            'telefono' => $this->telefono,
            'email' => $this->email ?: null,
        ];

        if ($this->isEditing) {
            $this->cliente->update($data);
            
            // Log de actividad
            activity()
                ->performedOn($this->cliente)
                ->causedBy(auth()->user())
                ->withProperties(['modulo' => 'clientes'])
                ->log('Cliente actualizado: ' . $this->cliente->nombre);
            
            session()->flash('success', 'Cliente actualizado correctamente');
            return redirect()->route('clientes.index');
            
        } else {
            $cliente = Cliente::create($data);
            
            // Log de actividad
            activity()
                ->performedOn($cliente)
                ->causedBy(auth()->user())
                ->withProperties(['modulo' => 'clientes'])
                ->log('Cliente creado: ' . $cliente->nombre);
            
            $this->dispatch('cliente-created');
            session()->flash('success', 'Cliente creado correctamente');
            return redirect()->route('clientes.index');
        }
    }

    public function cancel()
    {
        return redirect()->route('clientes.index');
    }

    public function render()
    {
        return view('livewire.clientes.cliente-form');
    }
}