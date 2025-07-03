<?php

namespace App\Http\Controllers;

use App\Models\Garantia;
use App\Models\Reparacion;
use App\Models\Venta;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GarantiaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
        $this->middleware('permission:garantias.ver')->only(['index', 'show']);
        $this->middleware('permission:garantias.crear')->only(['create', 'store']);
        $this->middleware('permission:garantias.editar')->only(['edit', 'update']);
        $this->middleware('permission:garantias.procesar')->only(['procesar']);
    }

    public function index()
    {
        abort_unless(auth()->user()->can('garantias.ver'), 403);
        
        return view('modules.garantias.index');
    }

    public function create()
    {
        abort_unless(auth()->user()->can('garantias.crear'), 403);
        
        $reparaciones = Reparacion::where('estado', 'entregado')
            ->whereDoesntHave('garantia')
            ->with(['cliente'])
            ->orderBy('fecha_entrega', 'desc')
            ->get();
        
        return view('modules.garantias.create', compact('reparaciones'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('garantias.crear'), 403);
        
        $validated = $request->validate([
            'reparacion_id' => 'required|exists:reparaciones,id',
            'tipo_garantia' => 'required|in:reparacion,repuesto,total',
            'dias_garantia' => 'required|integer|min:1|max:365',
            'descripcion_garantia' => 'required|string|max:500',
            'condiciones' => 'nullable|string',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $reparacion = Reparacion::find($validated['reparacion_id']);
        
        // Verificar que la reparación no tenga garantía
        if ($reparacion->garantia) {
            return back()->with('error', 'Esta reparación ya tiene una garantía asignada');
        }
        
        // Calcular fecha de vencimiento
        $fechaInicio = $reparacion->fecha_entrega ?? now();
        $fechaVencimiento = Carbon::parse($fechaInicio)->addDays($validated['dias_garantia']);
        
        $garantia = Garantia::create([
            'reparacion_id' => $validated['reparacion_id'],
            'cliente_id' => $reparacion->cliente_id,
            'tipo_garantia' => $validated['tipo_garantia'],
            'dias_garantia' => $validated['dias_garantia'],
            'fecha_inicio' => $fechaInicio,
            'fecha_vencimiento' => $fechaVencimiento,
            'descripcion_garantia' => $validated['descripcion_garantia'],
            'condiciones' => $validated['condiciones'],
            'observaciones' => $validated['observaciones'],
            'estado' => 'vigente',
            'usuario_id' => auth()->id(),
        ]);
        
        // Log de actividad
        activity()
            ->performedOn($garantia)
            ->causedBy(auth()->user())
            ->withProperties(['modulo' => 'garantias'])
            ->log('Garantía creada para reparación: ' . $reparacion->codigo);

        return redirect()->route('garantias.index')
            ->with('success', 'Garantía creada correctamente');
    }

    public function show(Garantia $garantia)
    {
        abort_unless(auth()->user()->can('garantias.ver'), 403);
        
        $garantia->load(['reparacion', 'cliente', 'usuario', 'reclamosGarantia.usuario']);
        
        return view('modules.garantias.show', compact('garantia'));
    }

    public function edit(Garantia $garantia)
    {
        abort_unless(auth()->user()->can('garantias.editar'), 403);
        
        if ($garantia->estado === 'usado' || $garantia->estado === 'vencido') {
            return back()->with('error', 'No se puede editar una garantía usada o vencida');
        }
        
        return view('modules.garantias.edit', compact('garantia'));
    }

    public function update(Request $request, Garantia $garantia)
    {
        abort_unless(auth()->user()->can('garantias.editar'), 403);
        
        if ($garantia->estado === 'usado' || $garantia->estado === 'vencido') {
            return back()->with('error', 'No se puede editar una garantía usada o vencida');
        }
        
        $validated = $request->validate([
            'tipo_garantia' => 'required|in:reparacion,repuesto,total',
            'dias_garantia' => 'required|integer|min:1|max:365',
            'descripcion_garantia' => 'required|string|max:500',
            'condiciones' => 'nullable|string',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Recalcular fecha de vencimiento si cambiaron los días
        if ($garantia->dias_garantia !== $validated['dias_garantia']) {
            $fechaVencimiento = Carbon::parse($garantia->fecha_inicio)->addDays($validated['dias_garantia']);
            $validated['fecha_vencimiento'] = $fechaVencimiento;
        }
        
        $garantia->update($validated);
        
        // Log de actividad
        activity()
            ->performedOn($garantia)
            ->causedBy(auth()->user())
            ->withProperties(['modulo' => 'garantias'])
            ->log('Garantía actualizada: ' . $garantia->reparacion->codigo);

        return redirect()->route('garantias.show', $garantia)
            ->with('success', 'Garantía actualizada correctamente');
    }

    public function procesar(Request $request, Garantia $garantia)
    {
        abort_unless(auth()->user()->can('garantias.procesar'), 403);
        
        if ($garantia->estado !== 'vigente') {
            return back()->with('error', 'Solo se pueden procesar garantías vigentes');
        }
        
        if ($garantia->fecha_vencimiento < now()) {
            return back()->with('error', 'La garantía ya está vencida');
        }
        
        $validated = $request->validate([
            'tipo_reclamo' => 'required|in:reparacion_defectuosa,repuesto_defectuoso,problema_nuevo',
            'descripcion_problema' => 'required|string|max:1000',
            'accion_tomada' => 'required|in:reparacion_gratuita,cambio_repuesto,devolucion_dinero,garantia_extendida',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Crear reclamo de garantía
        $garantia->reclamosGarantia()->create([
            'tipo_reclamo' => $validated['tipo_reclamo'],
            'descripcion_problema' => $validated['descripcion_problema'],
            'accion_tomada' => $validated['accion_tomada'],
            'observaciones' => $validated['observaciones'],
            'fecha_reclamo' => now(),
            'usuario_id' => auth()->id(),
        ]);
        
        // Actualizar estado de la garantía
        $garantia->update([
            'estado' => 'usado',
            'fecha_uso' => now(),
        ]);
        
        // Log de actividad
        activity()
            ->performedOn($garantia)
            ->causedBy(auth()->user())
            ->withProperties([
                'modulo' => 'garantias',
                'tipo_reclamo' => $validated['tipo_reclamo'],
                'accion_tomada' => $validated['accion_tomada']
            ])
            ->log('Garantía procesada: ' . $garantia->reparacion->codigo);

        return back()->with('success', 'Garantía procesada correctamente');
    }

    public function verificar(Request $request)
    {
        $request->validate([
            'codigo_reparacion' => 'required|string',
        ]);
        
        $reparacion = Reparacion::where('codigo', $request->codigo_reparacion)->first();
        
        if (!$reparacion) {
            return back()->with('error', 'No se encontró la reparación con ese código');
        }
        
        $garantia = $reparacion->garantia;
        
        if (!$garantia) {
            return back()->with('error', 'Esta reparación no tiene garantía asignada');
        }
        
        // Verificar estado de la garantía
        if ($garantia->fecha_vencimiento < now() && $garantia->estado === 'vigente') {
            $garantia->update(['estado' => 'vencido']);
        }
        
        return view('modules.garantias.verificar', compact('garantia'));
    }

    public function reporteVencimientos()
    {
        abort_unless(auth()->user()->can('garantias.ver'), 403);
        
        // Garantías que vencen en los próximos 30 días
        $garantiasPorVencer = Garantia::where('estado', 'vigente')
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])
            ->with(['reparacion', 'cliente'])
            ->orderBy('fecha_vencimiento')
            ->get();
        
        // Garantías vencidas no actualizadas
        $garantiasVencidas = Garantia::where('estado', 'vigente')
            ->where('fecha_vencimiento', '<', now())
            ->with(['reparacion', 'cliente'])
            ->orderBy('fecha_vencimiento')
            ->get();
        
        return view('modules.garantias.vencimientos', compact('garantiasPorVencer', 'garantiasVencidas'));
    }
}