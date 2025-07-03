<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
    }

    /**
     * Mostrar lista de proveedores
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('proveedores.ver'), 403);

        $query = Proveedor::query()->withCount(['productos']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('ruc', 'like', "%{$search}%")
                  ->orWhere('contacto', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['nombre', 'ruc', 'contacto', 'email', 'telefono', 'created_at', 'productos_count'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $proveedores = $query->paginate(15)->withQueryString();

        // Estadísticas
        $stats = [
            'total' => Proveedor::count(),
            'activos' => Proveedor::where('activo', true)->count(),
            'inactivos' => Proveedor::where('activo', false)->count(),
            'con_productos' => Proveedor::has('productos')->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'proveedores' => $proveedores,
                'stats' => $stats
            ]);
        }

        return view('modules.proveedores.index', compact('proveedores', 'stats'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        abort_unless(auth()->user()->can('proveedores.crear'), 403);
        
        return view('modules.proveedores.create');
    }

    /**
     * Guardar nuevo proveedor
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('proveedores.crear'), 403);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:20|unique:proveedores,ruc',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:proveedores,email',
            'direccion' => 'nullable|string|max:500',
            'banco' => 'nullable|string|max:255',
            'numero_cuenta' => 'nullable|string|max:50',
            'tipo_cuenta' => 'nullable|string|in:corriente,ahorros,detraccion',
            'observaciones' => 'nullable|string|max:1000',
            'activo' => 'boolean'
        ]);

        $validated['activo'] = $request->boolean('activo', true);

        $proveedor = Proveedor::create($validated);

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'modulo' => 'proveedores',
            'accion' => 'crear_proveedor',
            'ruta' => $request->fullUrl(),
            'fecha' => today(),
            'ultima_actividad' => now(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'contador_accesos' => 1,
            'datos_sesion' => [
                'proveedor_id' => $proveedor->id,
                'nombre' => $proveedor->nombre,
                'descripcion' => "Proveedor '{$proveedor->nombre}' creado"
            ]
        ]);
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado exitosamente',
                'proveedor' => $proveedor->load('productos')
            ], 201);
        }

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado exitosamente');
    }

    /**
     * Mostrar detalles del proveedor
     */
    public function show(Proveedor $proveedor)
    {
        abort_unless(auth()->user()->can('proveedores.ver'), 403);

        $proveedor->load([
            'productos' => function($query) {
                $query->with('categoria')
                      ->latest()
                      ->take(10);
            }
        ]);

        // Estadísticas del proveedor
        $stats = [
            'productos_total' => $proveedor->productos()->count(),
            'productos_activos' => $proveedor->productos()->where('activo', true)->count(),
            'productos_stock_bajo' => $proveedor->productos()
                ->whereRaw('stock <= stock_minimo')
                ->count(),
            'valor_inventario' => $proveedor->productos()
                ->selectRaw('SUM(precio * stock) as total')
                ->value('total') ?: 0,
            'antiguedad_dias' => now()->diffInDays($proveedor->created_at),
        ];

        // Productos más vendidos del proveedor
        $topProductos = $proveedor->productos()
            ->withCount('detalleVentas')
            ->orderByDesc('detalle_ventas_count')
            ->take(5)
            ->get();

        if (request()->expectsJson()) {
            return response()->json([
                'proveedor' => $proveedor,
                'stats' => $stats,
                'top_productos' => $topProductos
            ]);
        }

        return view('modules.proveedores.show', compact('proveedor', 'stats', 'topProductos'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Proveedor $proveedor)
    {
        abort_unless(auth()->user()->can('proveedores.editar'), 403);
        
        return view('modules.proveedores.edit', compact('proveedor'));
    }

    /**
     * Actualizar proveedor
     */
    public function update(Request $request, Proveedor $proveedor)
    {
        abort_unless(auth()->user()->can('proveedores.editar'), 403);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:20|unique:proveedores,ruc,' . $proveedor->id,
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:proveedores,email,' . $proveedor->id,
            'direccion' => 'nullable|string|max:500',
            'banco' => 'nullable|string|max:255',
            'numero_cuenta' => 'nullable|string|max:50',
            'tipo_cuenta' => 'nullable|string|in:corriente,ahorros,detraccion',
            'observaciones' => 'nullable|string|max:1000',
            'activo' => 'boolean'
        ]);

        $validated['activo'] = $request->boolean('activo', $proveedor->activo);

        $proveedor->update($validated);

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'crear_proveedor',
            'modulo' => 'proveedores',
            'descripcion' => "Proveedor '{$proveedor->nombre}' creado",
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_adicionales' => [
                'proveedor_id' => $proveedor->id,
                'nombre' => $proveedor->nombre
            ]
        ]);
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado exitosamente',
                'proveedor' => $proveedor->fresh()
            ]);
        }

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente');
    }

    /**
     * Eliminar proveedor
     */
    public function destroy(Proveedor $proveedor)
    {
        abort_unless(auth()->user()->can('proveedores.eliminar'), 403);

        // Verificar si tiene productos asociados
        if ($proveedor->productos()->exists()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el proveedor porque tiene productos asociados'
                ], 422);
            }

            return back()->with('error', 'No se puede eliminar el proveedor porque tiene productos asociados');
        }

        $nombre = $proveedor->nombre;
        $proveedor->delete();

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'eliminar_proveedor',
            'modulo' => 'proveedores',
            'descripcion' => "Proveedor '{$nombre}' eliminado",
            'url' => request()->fullUrl(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'datos_adicionales' => [
                'proveedor_nombre' => $nombre
            ]
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado exitosamente'
            ]);
        }

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado exitosamente');
    }

    /**
     * Ver productos del proveedor
     */
    public function productos(Proveedor $proveedor, Request $request)
    {
        abort_unless(auth()->user()->can('proveedores.ver'), 403);

        $query = $proveedor->productos()->with(['categoria']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        if ($request->filled('stock')) {
            switch ($request->stock) {
                case 'bajo':
                    $query->whereRaw('stock <= stock_minimo');
                    break;
                case 'agotado':
                    $query->where('stock', 0);
                    break;
                case 'disponible':
                    $query->where('stock', '>', 0);
                    break;
            }
        }

        $productos = $query->paginate(15)->withQueryString();

        // Categorías para filtro
        $categorias = $proveedor->productos()
            ->with('categoria')
            ->get()
            ->pluck('categoria')
            ->filter()
            ->unique('id');

        // Estadísticas de productos
        $statsProductos = [
            'total' => $proveedor->productos()->count(),
            'activos' => $proveedor->productos()->where('activo', true)->count(),
            'stock_bajo' => $proveedor->productos()->whereRaw('stock <= stock_minimo')->count(),
            'agotados' => $proveedor->productos()->where('stock', 0)->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'proveedor' => $proveedor,
                'productos' => $productos,
                'categorias' => $categorias,
                'stats' => $statsProductos
            ]);
        }

        return view('modules.proveedores.productos', compact(
            'proveedor', 
            'productos', 
            'categorias', 
            'statsProductos'
        ));
    }

    /**
     * Ver historial con el proveedor (en lugar de compras)
     */
    public function compras(Proveedor $proveedor, Request $request)
    {
        abort_unless(auth()->user()->can('proveedores.ver'), 403);

        // Por ahora solo mostrar información básica
        // Si más adelante quieres implementar compras, aquí va la lógica
        
        $historial = [
            'productos_suministrados' => $proveedor->productos()->count(),
            'valor_total_productos' => $proveedor->valor_inventario,
            'ultimo_producto_agregado' => $proveedor->productos()->latest()->first(),
            'fecha_registro' => $proveedor->created_at
        ];
        
        return view('modules.proveedores.historial', compact('proveedor', 'historial'));
    }

    /**
     * Eliminar múltiples proveedores
     */
    public function bulkDelete(Request $request)
    {
        abort_unless(auth()->user()->can('proveedores.eliminar'), 403);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:proveedores,id'
        ]);

        $proveedores = Proveedor::whereIn('id', $request->ids)->get();
        $deleted = 0;
        $errors = [];

        foreach ($proveedores as $proveedor) {
            if ($proveedor->productos()->exists()) {
                $errors[] = "El proveedor '{$proveedor->nombre}' tiene productos asociados";
                continue;
            }

            $proveedor->delete();
            $deleted++;
        }

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'eliminar_proveedores_masivo',
            'modulo' => 'proveedores',
            'descripcion' => "Eliminación masiva de proveedores: {$deleted} eliminados",
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_adicionales' => [
                'eliminados' => $deleted,
                'errores' => count($errors),
                'ids' => $request->ids
            ]
        ]);

        $message = "Se eliminaron {$deleted} proveedores";
        if (!empty($errors)) {
            $message .= ". Errores: " . implode(', ', $errors);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'deleted' => $deleted,
            'errors' => $errors
        ]);
    }

    /**
     * Exportar proveedores a Excel
     */
    public function exportExcel(Request $request)
    {
        abort_unless(auth()->user()->can('proveedores.ver'), 403);

        $query = Proveedor::query();

        // Aplicar filtros
        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        $proveedores = $query->get();

        $filename = 'proveedores_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($proveedores) {
            $file = fopen('php://output', 'w');
            
            // Headers CSV
            fputcsv($file, [
                'ID',
                'Nombre',
                'RUC',
                'Contacto',
                'Teléfono',
                'Email',
                'Dirección',
                'Banco',
                'Número de Cuenta',
                'Tipo de Cuenta',
                'Estado',
                'Fecha de Registro'
            ]);

            // Datos
            foreach ($proveedores as $proveedor) {
                fputcsv($file, [
                    $proveedor->id,
                    $proveedor->nombre,
                    $proveedor->ruc,
                    $proveedor->contacto,
                    $proveedor->telefono,
                    $proveedor->email,
                    $proveedor->direccion,
                    $proveedor->banco,
                    $proveedor->numero_cuenta,
                    $proveedor->tipo_cuenta,
                    $proveedor->activo ? 'Activo' : 'Inactivo',
                    $proveedor->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'exportar_proveedores',
            'modulo' => 'proveedores',
            'descripcion' => 'Exportación de proveedores a Excel',
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_adicionales' => [
                'total_registros' => $proveedores->count(),
                'filename' => $filename
            ]
        ]);

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Buscar proveedores (API)
     */
    public function search(Request $request)
    {
        abort_unless(auth()->user()->can('proveedores.ver'), 403);

        $query = Proveedor::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('ruc', 'like', "%{$search}%")
                  ->orWhere('contacto', 'like', "%{$search}%");
            });
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $proveedores = $query->where('activo', true)
            ->select('id', 'nombre', 'ruc', 'contacto', 'telefono', 'email')
            ->limit(20)
            ->get()
            ->map(function($proveedor) {
                return [
                    'id' => $proveedor->id,
                    'text' => $proveedor->nombre,
                    'nombre' => $proveedor->nombre,
                    'ruc' => $proveedor->ruc,
                    'contacto' => $proveedor->contacto,
                    'email' => $proveedor->email
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $proveedores
        ]);
    }

    /**
     * Activar/Desactivar proveedor
     */
    public function toggleStatus(Proveedor $proveedor)
    {
        abort_unless(auth()->user()->can('proveedores.editar'), 403);

        $proveedor->update(['activo' => !$proveedor->activo]);
        
        $status = $proveedor->activo ? 'activado' : 'desactivado';

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'cambiar_estado_proveedor',
            'modulo' => 'proveedores',
            'descripcion' => "Proveedor '{$proveedor->nombre}' {$status}",
            'url' => request()->fullUrl(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'datos_adicionales' => [
                'proveedor_id' => $proveedor->id,
                'nombre' => $proveedor->nombre,
                'nuevo_estado' => $proveedor->activo
            ]
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Proveedor {$status} exitosamente",
                'activo' => $proveedor->activo
            ]);
        }

        return back()->with('success', "Proveedor {$status} exitosamente");
    }

    /**
     * Obtener estadísticas del proveedor
     */
    public function getStats(Proveedor $proveedor)
    {
        abort_unless(auth()->user()->can('proveedores.ver'), 403);

        $stats = [
            'productos' => [
                'total' => $proveedor->productos()->count(),
                'activos' => $proveedor->productos()->where('activo', true)->count(),
                'stock_bajo' => $proveedor->productos()->whereRaw('stock <= stock_minimo')->count(),
                'agotados' => $proveedor->productos()->where('stock', 0)->count(),
            ],
            'inventario' => [
                'valor_total' => $proveedor->productos()
                    ->selectRaw('SUM(precio * stock) as total')
                    ->value('total') ?: 0,
                'stock_total' => $proveedor->productos()->sum('stock'),
            ],
            'actividad' => [
                'productos_agregados_mes' => $proveedor->productos()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'ultima_actualizacion' => $proveedor->updated_at,
                'antiguedad_dias' => now()->diffInDays($proveedor->created_at),
            ]
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Obtener proveedores activos para selects
     */
    public function activos()
    {
        abort_unless(auth()->user()->can('proveedores.ver'), 403);

        $proveedores = Proveedor::where('activo', true)
            ->select('id', 'nombre', 'ruc', 'contacto')
            ->orderBy('nombre')
            ->get()
            ->map(function($proveedor) {
                return [
                    'id' => $proveedor->id,
                    'nombre' => $proveedor->nombre,
                    'ruc' => $proveedor->ruc,
                    'contacto' => $proveedor->contacto
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $proveedores
        ]);
    }

    /**
     * Generar reporte de proveedores
     */
    public function generateReport(Request $request)
    {
        abort_unless(auth()->user()->can('proveedores.ver'), 403);

        $request->validate([
            'formato' => 'required|in:pdf,excel',
            'incluir' => 'array|in:productos,estadisticas,contacto'
        ]);

        $proveedores = Proveedor::with(['productos' => function($query) {
            $query->select('proveedor_id', 'nombre', 'stock', 'precio');
        }])->get();

        $incluir = $request->get('incluir', []);

        $data = [
            'proveedores' => $proveedores,
            'incluir_productos' => in_array('productos', $incluir),
            'incluir_estadisticas' => in_array('estadisticas', $incluir),
            'incluir_contacto' => in_array('contacto', $incluir),
            'fecha_generacion' => now(),
            'generado_por' => auth()->user()->username
        ];

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'generar_reporte_proveedores',
            'modulo' => 'proveedores',
            'descripcion' => 'Reporte de proveedores generado',
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_adicionales' => [
                'formato' => $request->formato,
                'incluir' => $incluir,
                'total_proveedores' => $proveedores->count()
            ]
        ]);

        if ($request->formato === 'pdf') {
            // Generar PDF
            // return PDF::loadView('reports.proveedores', $data)->download('proveedores.pdf');
            return response()->json([
                'message' => 'Generación de PDF en desarrollo'
            ]);
        } else {
            // Generar Excel
            return $this->exportExcel($request);
        }
    }
}