<?php

namespace App\Http\Controllers;

use App\Models\Reparacion;
use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\Usuario;
use App\Models\EstadoReparacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReparacionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
        $this->middleware('permission:reparaciones.ver')->only(['index', 'show']);
        $this->middleware('permission:reparaciones.crear')->only(['create', 'store']);
        $this->middleware('permission:reparaciones.editar')->only(['edit', 'update']);
        $this->middleware('permission:reparaciones.asignar')->only(['asignarTecnico', 'cambiarEstado']);
    }

    public function index()
    {
        abort_unless(auth()->user()->can('reparaciones.ver'), 403);
        
        return view('modules.reparaciones.index');
    }

    public function create()
    {
        abort_unless(auth()->user()->can('reparaciones.crear'), 403);
        
        $clientes = Cliente::orderBy('nombre')->get();
        $tecnicos = \App\Models\Empleado::whereHas('usuario.roles', function($query) {
            $query->whereIn('name', ['tecnico', 'tecnico-senior', 'supervisor']);
        })->where('activo', true)->get();        
        return view('modules.reparaciones.create', compact('clientes', 'tecnicos'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('reparaciones.crear'), 403);
        
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'equipo_id' => 'nullable|exists:equipos,id',
            'tipo_equipo' => 'required|string|max:100',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'problema_reportado' => 'required|string',
            'observaciones_iniciales' => 'nullable|string',
            'empleado_id' => 'nullable|exists:empleados,id',
            'prioridad' => 'required|in:baja,media,alta,urgente',
            'costo_estimado' => 'nullable|numeric|min:0',
            'fecha_estimada' => 'nullable|date|after:today',
        ]);

        DB::transaction(function () use ($validated) {
        // Verificar empleado
        $user = auth()->user();
        if (!$user->empleado_id) {
            throw new \Exception('Usuario no asociado a un empleado');
        }
        
        // Generar código único
        $validated['codigo_ticket'] = $this->generateOperationCode('R', 'reparaciones');
        $validated['creado_por'] = $user->id;
        $validated['fecha_ingreso'] = now();
        $validated['estado'] = 'recibido';
            
            $reparacion = Reparacion::create($validated);
            
            // Crear el estado inicial
            EstadoReparacion::create([
                'reparacion_id' => $reparacion->id,
                'estado' => 'recibido',
                'fecha_cambio' => now(),
                'usuario_id' => auth()->id(),
                'observaciones' => 'Reparación registrada en el sistema',
            ]);
            
            // Log de actividad
            activity()
                ->performedOn($reparacion)
                ->causedBy(auth()->user())
                ->withProperties(['modulo' => 'reparaciones'])
                ->log('Reparación creada: ' . $reparacion->codigo);
        });

        return redirect()->route('reparaciones.index')
            ->with('success', 'Reparación registrada correctamente');
    }

    public function show(Reparacion $reparacion)
    {
        abort_unless(auth()->user()->can('reparaciones.ver'), 403);
        
        $reparacion->load([
            'cliente', 
            'equipo', 
            'empleado',
            'createdBy',
            'estadosReparacion.usuario',
            'diagnosticos.usuario',
            'costosReparacion.usuario'
        ]);
        
        return view('modules.reparaciones.show', compact('reparacion'));
    }

    public function edit(Reparacion $reparacion)
    {
        abort_unless(auth()->user()->can('reparaciones.editar'), 403);
        
        $clientes = Cliente::orderBy('nombre')->get();
        $tecnicos = Usuario::whereHas('roles', function($query) {
            $query->whereIn('name', ['tecnico', 'tecnico-senior', 'supervisor']);
        })->where('activo', true)->get();
        
        return view('modules.reparaciones.edit', compact('reparacion', 'clientes', 'tecnicos'));
    }

    public function update(Request $request, Reparacion $reparacion)
    {
        abort_unless(auth()->user()->can('reparaciones.editar'), 403);
        
        $validated = $request->validate([
            'tipo_equipo' => 'required|string|max:100',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'problema_reportado' => 'required|string',
            'observaciones_iniciales' => 'nullable|string',
            'prioridad' => 'required|in:baja,media,alta,urgente',
            'costo_estimado' => 'nullable|numeric|min:0',
            'fecha_estimada' => 'nullable|date|after:today',
        ]);

        $reparacion->update($validated);
        
        // Log de actividad
        activity()
            ->performedOn($reparacion)
            ->causedBy(auth()->user())
            ->withProperties(['modulo' => 'reparaciones'])
            ->log('Reparación actualizada: ' . $reparacion->codigo);

        return redirect()->route('reparaciones.show', $reparacion)
            ->with('success', 'Reparación actualizada correctamente');
    }

    public function asignarTecnico(Request $request, Reparacion $reparacion)
    {
        abort_unless(auth()->user()->can('reparaciones.asignar'), 403);
        
        $validated = $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $reparacion) {
            $reparacion->update([
                'empleado_id' => $validated['empleado_id'],
                'estado' => 'asignado'
            ]);
            
            // Registrar cambio de estado
            EstadoReparacion::create([
                'reparacion_id' => $reparacion->id,
                'estado' => 'asignado',
                'fecha_cambio' => now(),
                'usuario_id' => auth()->id(),
                'observaciones' => $validated['observaciones'] ?? 'Técnico asignado',
            ]);
            
            // Log de actividad
            activity()
                ->performedOn($reparacion)
                ->causedBy(auth()->user())
                ->withProperties([
                    'modulo' => 'reparaciones',
                    'tecnico_asignado' => $validated['tecnico_id']
                ])
                ->log('Técnico asignado a reparación: ' . $reparacion->codigo);
        });

        return back()->with('success', 'Técnico asignado correctamente');
    }

    public function cambiarEstado(Request $request, Reparacion $reparacion)
    {
        abort_unless(auth()->user()->can('reparaciones.asignar'), 403);
        
        $validated = $request->validate([
            'estado' => 'required|in:recibido,en_diagnostico,en_reparacion,esperando_repuestos,reparado,entregado,cancelado',
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $reparacion) {
            $estadoAnterior = $reparacion->estado;
            
            $reparacion->update([
                'estado' => $validated['estado'],
                'fecha_entrega' => $validated['estado'] === 'entregado' ? now() : null,
            ]);
            
            // Registrar cambio de estado
            EstadoReparacion::create([
                'reparacion_id' => $reparacion->id,
                'estado' => $validated['estado'],
                'fecha_cambio' => now(),
                'usuario_id' => auth()->id(),
                'observaciones' => $validated['observaciones'] ?? 'Cambio de estado',
            ]);
            
            // Log de actividad
            activity()
                ->performedOn($reparacion)
                ->causedBy(auth()->user())
                ->withProperties([
                    'modulo' => 'reparaciones',
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $validated['estado']
                ])
                ->log('Estado cambiado en reparación: ' . $reparacion->codigo);
        });

        return back()->with('success', 'Estado actualizado correctamente');
    }
}