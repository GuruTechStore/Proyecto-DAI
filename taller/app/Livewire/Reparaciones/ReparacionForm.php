<?php

namespace App\Livewire\Reparaciones;

use App\Models\Reparacion;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\Usuario;
use App\Models\EstadoReparacion;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\DB;

class ReparacionForm extends Component
{
    public ?Reparacion $reparacion = null;
    public bool $isEditing = false;

    #[Rule('required|exists:clientes,id', message: 'Seleccione un cliente válido')]
    public string $cliente_id = '';
    
    #[Rule('nullable|exists:equipos,id', message: 'Seleccione un equipo válido')]
    public string $equipo_id = '';
    
    #[Rule('required|string|max:100', message: 'El tipo de equipo es obligatorio')]
    public string $tipo_equipo = '';
    
    #[Rule('nullable|string|max:100', message: 'La marca debe tener máximo 100 caracteres')]
    public string $marca = '';
    
    #[Rule('nullable|string|max:100', message: 'El modelo debe tener máximo 100 caracteres')]
    public string $modelo = '';
    
    #[Rule('nullable|string|max:100', message: 'El número de serie debe tener máximo 100 caracteres')]
    public string $numero_serie = '';
    
    #[Rule('required|string', message: 'Debe describir el problema reportado')]
    public string $problema_reportado = '';
    
    #[Rule('nullable|string', message: 'Las observaciones deben ser texto válido')]
    public string $observaciones_iniciales = '';
    
    #[Rule('nullable|exists:usuarios,id', message: 'Seleccione un técnico válido')]
    public string $tecnico_id = '';
    
    #[Rule('required|in:baja,media,alta,urgente', message: 'Seleccione una prioridad válida')]
    public string $prioridad = 'media';
    
    #[Rule('nullable|numeric|min:0', message: 'El costo estimado debe ser un número mayor o igual a 0')]
    public string $costo_estimado = '';
    
    #[Rule('nullable|date|after:today', message: 'La fecha estimada debe ser posterior a hoy')]
    public string $fecha_estimada = '';

    // Propiedades auxiliares
    public array $equiposCliente = [];
    public bool $mostrarEquipos = false;

    public function mount(?Reparacion $reparacion = null)
    {
        if ($reparacion && $reparacion->exists) {
            $this->isEditing = true;
            $this->reparacion = $reparacion;
            $this->cliente_id = $reparacion->cliente_id;
            $this->equipo_id = $reparacion->equipo_id ?? '';
            $this->tipo_equipo = $reparacion->tipo_equipo;
            $this->marca = $reparacion->marca ?? '';
            $this->modelo = $reparacion->modelo ?? '';
            $this->numero_serie = $reparacion->numero_serie ?? '';
            $this->problema_reportado = $reparacion->problema_reportado;
            $this->observaciones_iniciales = $reparacion->observaciones_iniciales ?? '';
            $this->tecnico_id = $reparacion->tecnico_id ?? '';
            $this->prioridad = $reparacion->prioridad;
            $this->costo_estimado = $reparacion->costo_estimado ?? '';
            $this->fecha_estimada = $reparacion->fecha_estimada ? $reparacion->fecha_estimada->format('Y-m-d') : '';
            
            if ($this->cliente_id) {
                $this->cargarEquiposCliente();
            }
        }
        
        // Verificar permisos
        if ($this->isEditing) {
            abort_unless(auth()->user()->can('reparaciones.editar'), 403);
        } else {
            abort_unless(auth()->user()->can('reparaciones.crear'), 403);
        }
    }

    public function getClientesProperty()
    {
        return Cliente::orderBy('nombre')->get();
    }

    public function getTecnicosProperty()
    {
        return Usuario::whereHas('roles', function($query) {
            $query->whereIn('name', ['tecnico', 'tecnico-senior', 'supervisor']);
        })->where('activo', true)->orderBy('nombres')->get();
    }

    public function updatedClienteId($value)
    {
        if ($value) {
            $this->cargarEquiposCliente();
            $this->mostrarEquipos = true;
        } else {
            $this->equiposCliente = [];
            $this->mostrarEquipos = false;
            $this->equipo_id = '';
        }
    }

    public function updatedEquipoId($value)
    {
        if ($value) {
            $equipo = Equipo::find($value);
            if ($equipo) {
                $this->tipo_equipo = $equipo->tipo;
                $this->marca = $equipo->marca ?? '';
                $this->modelo = $equipo->modelo ?? '';
                $this->numero_serie = $equipo->numero_serie ?? '';
            }
        }
    }

    private function cargarEquiposCliente()
    {
        if ($this->cliente_id) {
            $this->equiposCliente = Equipo::where('cliente_id', $this->cliente_id)
                ->orderBy('tipo')
                ->get()
                ->toArray();
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'cliente_id' => $this->cliente_id,
            'equipo_id' => $this->equipo_id ?: null,
            'tipo_equipo' => $this->tipo_equipo,
            'marca' => $this->marca ?: null,
            'modelo' => $this->modelo ?: null,
            'numero_serie' => $this->numero_serie ?: null,
            'problema_reportado' => $this->problema_reportado,
            'observaciones_iniciales' => $this->observaciones_iniciales ?: null,
            'tecnico_id' => $this->tecnico_id ?: null,
            'prioridad' => $this->prioridad,
            'costo_estimado' => $this->costo_estimado ? (float) $this->costo_estimado : null,
            'fecha_estimada' => $this->fecha_estimada ?: null,
        ];

        if ($this->isEditing) {
            $this->reparacion->update($data);
            
            // Log de actividad
            activity()
                ->performedOn($this->reparacion)
                ->causedBy(auth()->user())
                ->withProperties(['modulo' => 'reparaciones'])
                ->log('Reparación actualizada: ' . $this->reparacion->codigo);
            
            session()->flash('success', 'Reparación actualizada correctamente');
            return redirect()->route('reparaciones.show', $this->reparacion);
            
        } else {
            DB::transaction(function () use ($data) {
                // Generar código único
                $data['codigo'] = 'REP-' . date('Y') . '-' . str_pad(
                    Reparacion::whereYear('created_at', date('Y'))->count() + 1, 
                    4, 
                    '0', 
                    STR_PAD_LEFT
                );
                
                $data['estado'] = 'recibido';
                $data['fecha_ingreso'] = now();
                
                $reparacion = Reparacion::create($data);
                
                // Crear el estado inicial
                EstadoReparacion::create([
                    'reparacion_id' => $reparacion->id,
                    'estado' => 'recibido',
                    'fecha_cambio' => now(),
                    'usuario_id' => auth()->id(),
                    'observaciones' => 'Reparación registrada en el sistema',
                ]);
                
                // Si se asignó técnico, cambiar estado
                if ($this->tecnico_id) {
                    $reparacion->update(['estado' => 'asignado']);
                    
                    EstadoReparacion::create([
                        'reparacion_id' => $reparacion->id,
                        'estado' => 'asignado',
                        'fecha_cambio' => now(),
                        'usuario_id' => auth()->id(),
                        'observaciones' => 'Técnico asignado al momento del registro',
                    ]);
                }
                
                // Log de actividad
                activity()
                    ->performedOn($reparacion)
                    ->causedBy(auth()->user())
                    ->withProperties(['modulo' => 'reparaciones'])
                    ->log('Reparación creada: ' . $reparacion->codigo);
            });
            
            $this->dispatch('reparacion-created');
            session()->flash('success', 'Reparación registrada correctamente');
            return redirect()->route('reparaciones.index');
        }
    }

    public function cancel()
    {
        return redirect()->route('reparaciones.index');
    }

    public function render()
    {
        return view('livewire.reparaciones.reparacion-form', [
            'clientes' => $this->clientes,
            'tecnicos' => $this->tecnicos,
        ]);
    }
}