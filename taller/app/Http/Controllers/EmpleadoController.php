<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Reparacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\USuario;
use Illuminate\Support\Facades\Hash;
class EmpleadoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
    }

    /**
     * Mostrar lista de empleados
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('empleados.ver'), 403);

        $query = Empleado::query()->withCount(['reparaciones', 'ventasAsociadas']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('especialidad', 'like', "%{$search}%");
            });
        }

        if ($request->filled('especialidad')) {
            $query->where('especialidad', $request->especialidad);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['nombres', 'apellidos', 'dni', 'especialidad', 'fecha_contratacion', 'created_at', 'reparaciones_count'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $empleados = $query->paginate(15)->withQueryString();

        // Datos adicionales
        $especialidades = Empleado::distinct()->pluck('especialidad')->filter();
        $stats = [
            'total' => Empleado::count(),
            'activos' => Empleado::where('activo', true)->count(),
            'inactivos' => Empleado::where('activo', false)->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'empleados' => $empleados,
                'especialidades' => $especialidades,
                'stats' => $stats
            ]);
        }

        return view('modules.empleados.index', compact('empleados', 'especialidades', 'stats'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        abort_unless(auth()->user()->can('empleados.crear'), 403);
        
        $especialidades = Empleado::distinct()->pluck('especialidad')->filter();
        
        return view('modules.empleados.create', compact('especialidades'));
    }
    public function crearUsuario(Request $request, Empleado $empleado)
    {
        abort_unless(auth()->user()->can('usuarios.crear'), 403);
        
        // Verificar que no tenga usuario ya
        if ($empleado->usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Este empleado ya tiene un usuario del sistema'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Generar username único
            $baseUsername = strtolower(
                substr($empleado->nombres, 0, 1) . 
                explode(' ', $empleado->apellidos)[0]
            );
            
            $username = $baseUsername;
            $counter = 1;
            
            while (Usuario::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }
            
            // Generar contraseña temporal
            $password = 'temp' . rand(1000, 9999);
            
            // Crear usuario
            $usuario = Usuario::create([
                'empleado_id' => $empleado->id,
                'username' => $username,
                'email' => $empleado->email,
                'password' => $password,
                'tipo_usuario' => 'Empleado',
                'activo' => true,
                'force_password_change' => true,
                'created_by' => auth()->id()
            ]);
            
            // Asignar rol básico de empleado
            if (class_exists('\Spatie\Permission\Models\Role')) {
                $role = \Spatie\Permission\Models\Role::where('name', 'Empleado')->first();
                if ($role) {
                    $usuario->assignRole($role);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'username' => $username,
                'password' => $password
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Guardar nuevo empleado
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('empleados.crear'), 403);

        $validated = $request->validate([
            'dni' => 'required|string|max:20|unique:empleados,dni',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100|unique:empleados,email',
            'especialidad' => 'nullable|string|max:100',
            'fecha_contratacion' => 'required|date|before_or_equal:today',
            'salario' => 'nullable|numeric|min:0',
            'direccion' => 'nullable|string|max:255',
            'activo' => 'boolean'
        ]);

        $validated['activo'] = $request->boolean('activo', true);

        $empleado = Empleado::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Empleado creado exitosamente',
                'empleado' => $empleado->load('reparaciones')
            ], 201);
        }

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado creado exitosamente');
    }

        /**
         * Mostrar detalles del empleado
         */
    /**
     * Mostrar detalles del empleado
     */
    public function show(Empleado $empleado)
    {
        abort_unless(auth()->user()->can('empleados.ver'), 403);

        $empleado->load([
            'usuario',
            'reparaciones' => function($query) {
                $query->with(['cliente', 'equipo'])
                    ->latest()
                    ->take(10);
            },
            'ventas' => function($query) {
                $query->with(['cliente'])
                    ->latest()
                    ->take(5);
            }
        ]);
        // Estadísticas del empleado - 
        $stats = [
            'reparaciones_total' => $empleado->reparaciones()->count(),
            'reparaciones_mes' => $empleado->reparaciones()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'reparaciones_pendientes' => $empleado->reparaciones()
                ->whereIn('estado', ['recibido', 'diagnosticando', 'reparando'])
                ->count(),
            'reparaciones_completadas' => $empleado->reparaciones()
                ->where('estado', 'completado')
                ->count(),
            // CAMBIO PRINCIPAL: usar fecha_entrega en lugar de fecha_completado
            'tiempo_promedio' => $empleado->reparaciones()
            ->where('estado', 'completado')
            ->whereNotNull('fecha_entrega')  // ✅ CORRECTO
            ->get()
            ->map(function($reparacion) {
                return $reparacion->fecha_ingreso->diffInDays($reparacion->fecha_entrega);  // ✅ CORRECTO
            })
            ->avg() ?? 0,
            'antiguedad_dias' => now()->diffInDays($empleado->fecha_contratacion),
            'eficiencia' => $this->calcularEficiencia($empleado),
        ];

        // Rendimiento mensual (últimos 6 meses) - CORREGIDO
        $rendimientoMensual = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $count = $empleado->reparaciones()
                ->whereMonth('created_at', $fecha->month)
                ->whereYear('created_at', $fecha->year)
                ->where('estado', 'completado')
                ->count();
            
            $rendimientoMensual[] = [
                'mes' => $fecha->format('M Y'),
                'reparaciones' => $count
            ];
        }

        if (request()->expectsJson()) {
            return response()->json([
                'empleado' => $empleado,
                'stats' => $stats,
                'rendimiento_mensual' => $rendimientoMensual
            ]);
        }

            return view('modules.empleados.show', compact('empleado', 'rendimientoMensual'))
                ->with('estadisticas', $stats);    }

    /**
     * Calcular eficiencia del empleado
     */
    private function calcularEficiencia(Empleado $empleado)
    {
        // Reparaciones completadas en los últimos 30 días
        $completadas = $empleado->reparaciones()
            ->where('estado', 'completado')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Total de reparaciones asignadas en los últimos 30 días  
        $total = $empleado->reparaciones()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        if ($total === 0) {
            return 0;
        }

        return round(($completadas / $total) * 100, 1);
    }
    /**
     * Mostrar formulario de edición
     */
    public function edit(Empleado $empleado)
    {
        abort_unless(auth()->user()->can('empleados.editar'), 403);
        
        $especialidades = Empleado::distinct()->pluck('especialidad')->filter();
        
        return view('modules.empleados.edit', compact('empleado', 'especialidades'));
    }

    /**
     * Actualizar empleado
     */
    public function update(Request $request, Empleado $empleado)
    {
        abort_unless(auth()->user()->can('empleados.editar'), 403);

        $validated = $request->validate([
            'dni' => 'required|string|max:20|unique:empleados,dni,' . $empleado->id,
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100|unique:empleados,email,' . $empleado->id,
            'especialidad' => 'nullable|string|max:100',
            'fecha_contratacion' => 'required|date|before_or_equal:today',
            'salario' => 'nullable|numeric|min:0',
            'direccion' => 'nullable|string|max:255',
            'activo' => 'boolean'
        ]);

        $validated['activo'] = $request->boolean('activo', $empleado->activo);

        $empleado->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Empleado actualizado exitosamente',
                'empleado' => $empleado->fresh()
            ]);
        }

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado actualizado exitosamente');
    }

    /**
     * Eliminar empleado
     */
    public function destroy(Empleado $empleado)
    {
        abort_unless(auth()->user()->can('empleados.eliminar'), 403);

        // Verificar si tiene reparaciones pendientes
        $reparacionesPendientes = $empleado->reparaciones()
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->count();

        if ($reparacionesPendientes > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el empleado porque tiene reparaciones pendientes'
                ], 422);
            }

            return back()->with('error', 'No se puede eliminar el empleado porque tiene reparaciones pendientes');
        }

        $empleado->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Empleado eliminado exitosamente'
            ]);
        }

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado eliminado exitosamente');
    }

    /**
     * Activar/Desactivar empleado
     */
    public function toggleStatus(Empleado $empleado)
    {
        abort_unless(auth()->user()->can('empleados.editar'), 403);

        $empleado->update(['activo' => !$empleado->activo]);
        
        $status = $empleado->activo ? 'activado' : 'desactivado';
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Empleado {$status} exitosamente",
                'activo' => $empleado->activo
            ]);
        }

        return back()->with('success', "Empleado {$status} exitosamente");
    }

    /**
     * Ver reparaciones del empleado
     */
    public function reparaciones(Empleado $empleado, Request $request)
    {
        abort_unless(auth()->user()->can('empleados.ver'), 403);

        $query = $empleado->reparaciones()->with(['cliente', 'producto']);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $reparaciones = $query->latest()->paginate(15)->withQueryString();

        // Estadísticas de las reparaciones filtradas
        $stats = [
            'total' => $query->count(),
            'pendientes' => $empleado->reparaciones()->where('estado', 'recibido')->count(),  // ✅ CORRECTO
            'en_proceso' => $empleado->reparaciones()->whereIn('estado', ['diagnosticando', 'reparando'])->count(),  // ✅ CORRECTO
            'completadas' => $empleado->reparaciones()->where('estado', 'completado')->count(),  // ✅ Sin 'a'
            'canceladas' => $empleado->reparaciones()->where('estado', 'cancelado')->count(),  // ✅ Sin 'a'
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'reparaciones' => $reparaciones,
                'stats' => $stats,
                'empleado' => $empleado
            ]);
        }

        return view('modules.empleados.reparaciones', compact('empleado', 'reparaciones', 'stats'));
    }

    /**
     * Reportes de rendimiento del empleado
     */
public function rendimiento(Empleado $empleado, Request $request)
{
    abort_unless(auth()->user()->can('empleados.ver'), 403);

    $fechaInicio = $request->get('fecha_inicio', now()->subMonths(6));
    $fechaFin = $request->get('fecha_fin', now());

    // Reparaciones por mes - CORREGIDO para PostgreSQL
    $reparacionesPorMes = \App\Models\Reparacion::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mes, COUNT(*) as total")
        ->where('empleado_id', $empleado->id)
        ->whereBetween('created_at', [$fechaInicio, $fechaFin])
        ->groupBy('mes')
        ->orderBy('mes')
        ->get();
    // Tiempo promedio de reparación - CORREGIDO
    $tiempoPromedio = $empleado->reparaciones()
    ->where('estado', 'completado')
    ->whereNotNull('fecha_entrega')  // ✅ CAMBIAR DE fecha_completado
    ->whereBetween('created_at', [$fechaInicio, $fechaFin])
    ->get()
    ->map(function($reparacion) {
        return $reparacion->fecha_ingreso->diffInHours($reparacion->fecha_entrega);  // ✅ CORRECTO
    })
    ->avg();

    // Reparaciones por estado
    $reparacionesPorEstado = $empleado->reparaciones()
        ->selectRaw('estado, COUNT(*) as total')
        ->whereBetween('created_at', [$fechaInicio, $fechaFin])
        ->groupBy('estado')
        ->get();

    $data = [
        'reparaciones_por_mes' => $reparacionesPorMes,
        'tiempo_promedio_horas' => round($tiempoPromedio ?: 0, 2),
        'reparaciones_por_estado' => $reparacionesPorEstado,
        'periodo' => [
            'inicio' => $fechaInicio,
            'fin' => $fechaFin
        ]
    ];

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'data' => $data,
            'empleado' => $empleado
        ]);
    }

    return view('modules.empleados.rendimiento', compact('empleado', 'data'));
}
    /**
     * Buscar empleados (API)
     */
    public function search(Request $request)
    {
        abort_unless(auth()->user()->can('empleados.ver'), 403);

        $query = Empleado::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$search}%"]);
            });
        }

        if ($request->filled('especialidad')) {
            $query->where('especialidad', $request->especialidad);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $empleados = $query->where('activo', true)
            ->select('id', 'nombres', 'apellidos', 'dni', 'especialidad', 'email', 'telefono')
            ->limit(20)
            ->get()
            ->map(function($empleado) {
                return [
                    'id' => $empleado->id,
                    'text' => $empleado->nombres . ' ' . $empleado->apellidos,
                    'nombres' => $empleado->nombres,
                    'apellidos' => $empleado->apellidos,
                    'dni' => $empleado->dni,
                    'especialidad' => $empleado->especialidad,
                    'email' => $empleado->email,
                    'telefono' => $empleado->telefono
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $empleados
        ]);
    }

    /**
     * Obtener empleados activos para selects
     */
    public function activos()
    {
        abort_unless(auth()->user()->can('empleados.ver'), 403);

        $empleados = Empleado::where('activo', true)
            ->select('id', 'nombres', 'apellidos', 'especialidad')
            ->orderBy('nombres')
            ->get()
            ->map(function($empleado) {
                return [
                    'id' => $empleado->id,
                    'nombre_completo' => $empleado->nombres . ' ' . $empleado->apellidos,
                    'especialidad' => $empleado->especialidad
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $empleados
        ]);
    }

    /**
     * Exportar empleados
     */
    public function export(Request $request)
    {
        abort_unless(auth()->user()->can('empleados.ver'), 403);

        $formato = $request->get('formato', 'excel');
        
        // Implementar exportación según formato
        // return Excel::download(new EmpleadosExport(), 'empleados.' . $formato);
        
        return response()->json([
            'message' => 'Funcionalidad de exportación en desarrollo'
        ]);
    }
}