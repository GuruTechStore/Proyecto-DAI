<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    /**
     * Mostrar listado de productos
     */
    public function index(Request $request)
    {
        try {
            abort_unless(auth()->user()->can('productos.ver'), 403);

            // Query base con relaciones
            $query = Producto::with(['categoria', 'proveedor']);

            // Filtros de búsqueda
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%");
                });
            }

            // Filtro por categoría
            if ($request->filled('categoria_id') && $request->categoria_id !== '') {
                $query->where('categoria_id', $request->categoria_id);
            }

            // Filtro por proveedor
            if ($request->filled('proveedor_id') && $request->proveedor_id !== '') {
                $query->where('proveedor_id', $request->proveedor_id);
            }

            // Filtro por estado
            if ($request->filled('activo') && $request->activo !== '') {
                $query->where('activo', $request->activo === '1');
            }

            // Filtro por stock
            if ($request->filled('stock_filter')) {
                switch ($request->stock_filter) {
                    case 'bajo':
                        $query->whereRaw('stock <= stock_minimo');
                        break;
                    case 'sin_stock':
                        $query->where('stock', 0);
                        break;
                    case 'normal':
                        $query->whereRaw('stock > stock_minimo');
                        break;
                }
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSorts = ['nombre', 'codigo', 'precio_venta', 'stock', 'created_at'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->latest();
            }

            // Paginación
            $productos = $query->paginate(15)->withQueryString();

            // Datos para filtros
            $categorias = Categoria::where('activa', true)->orderBy('nombre')->get();
            $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();

            // Estadísticas
            $stats = [
                'total' => Producto::count(),
                'activos' => Producto::where('activo', true)->count(),
                'stock_bajo' => Producto::whereRaw('stock <= stock_minimo')->count(),
                'sin_stock' => Producto::where('stock', 0)->count(),
                'valor_inventario' => Producto::selectRaw('SUM(precio_venta * stock) as total')->value('total') ?? 0
            ];

            // Log de actividad
            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'ver_listado_productos',
                'modulo' => 'productos',
                'descripcion' => 'Usuario visualizó el listado de productos',
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'datos_adicionales' => [
                    'filtros' => $request->only(['search', 'categoria_id', 'proveedor_id', 'activo', 'stock_filter']),
                    'total_resultados' => $productos->total()
                ]
            ]);

            // Respuesta JSON para AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'productos' => $productos,
                    'categorias' => $categorias,
                    'proveedores' => $proveedores,
                    'stats' => $stats
                ]);
            }

            return view('modules.productos.index', compact('productos', 'categorias', 'proveedores', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error en ProductoController@index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar los productos: ' . $e->getMessage()
                ], 500);
            }

            return back()->withError('Error al cargar los productos. Verifique la conexión a la base de datos.');
        }
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        try {
            abort_unless(auth()->user()->can('productos.crear'), 403);
            
            $categorias = Categoria::where('activa', true)->orderBy('nombre')->get();
            $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
            
            return view('modules.productos.create', compact('categorias', 'proveedores'));
        } catch (\Exception $e) {
            Log::error('Error en ProductoController@create: ' . $e->getMessage());
            return back()->withError('Error al cargar el formulario de creación.');
        }
    }

    /**
     * Guardar nuevo producto
     */
    public function store(Request $request)
    {
        try {
            abort_unless(auth()->user()->can('productos.crear'), 403);

            $validated = $request->validate([
                'codigo' => 'nullable|string|max:50|unique:productos,codigo',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:1000',
                'categoria_id' => 'required|exists:categorias,id',
                'proveedor_id' => 'nullable|exists:proveedores,id',
                'precio_compra' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'stock_minimo' => 'required|integer|min:0',
                'unidad_medida' => 'nullable|string|max:50',
                'ubicacion' => 'nullable|string|max:100',
                'garantia_dias' => 'nullable|integer|min:0',
                'activo' => 'boolean'
            ]);

            // Generar código automático si no se proporciona
            if (empty($validated['codigo'])) {
                $validated['codigo'] = $this->generarCodigoAutomatico();
            }

            DB::beginTransaction();

            $producto = Producto::create($validated);

            // Log de actividad
            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'crear_producto',
                'modulo' => 'productos',
                'descripcion' => 'Producto creado: ' . $producto->nombre,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'datos_adicionales' => [
                    'producto_id' => $producto->id,
                    'codigo' => $producto->codigo
                ]
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto creado exitosamente',
                    'producto' => $producto->load(['categoria', 'proveedor'])
                ], 201);
            }

            return redirect()->route('productos.index')
                ->with('success', 'Producto creado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ProductoController@store: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'data' => $request->all()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el producto: ' . $e->getMessage()
                ], 500);
            }

            return back()->withError('Error al crear el producto. Intente nuevamente.')->withInput();
        }
    }

    /**
     * Mostrar detalles del producto
     */
    public function show(Producto $producto)
    {
        try {
            abort_unless(auth()->user()->can('productos.ver'), 403);

            $producto->load(['categoria', 'proveedor']);

            // Estadísticas del producto
            $stats = [
                'ventas_total' => $producto->detalleVentas()->sum('cantidad') ?? 0,
                'ventas_mes' => $producto->detalleVentas()
                    ->whereHas('venta', function($q) {
                        $q->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    })
                    ->sum('cantidad') ?? 0,
                'ingresos_total' => $producto->detalleVentas()->sum(DB::raw('cantidad * precio_unitario')) ?? 0,
                'stock_disponible' => $producto->stock,
                'valor_inventario' => ($producto->precio_venta * $producto->stock),
                'margen_ganancia' => $producto->precio_compra > 0 ? 
                    (($producto->precio_venta - $producto->precio_compra) / $producto->precio_compra) * 100 : 0,
            ];

            // Movimientos recientes
            $movimientosRecientes = collect(); // Placeholder hasta que se implemente MovimientoInventario
            if (method_exists($producto, 'movimientos')) {
                $movimientosRecientes = $producto->movimientos()
                    ->with('usuario')
                    ->latest()
                    ->take(10)
                    ->get();
            }

            return view('modules.productos.show', compact('producto', 'stats', 'movimientosRecientes'));

        } catch (\Exception $e) {
            Log::error('Error en ProductoController@show: ' . $e->getMessage());
            return back()->withError('Error al cargar los detalles del producto.');
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Producto $producto)
    {
        try {
            abort_unless(auth()->user()->can('productos.editar'), 403);
            
            $categorias = Categoria::where('activa', true)->orderBy('nombre')->get();
            $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
            
            return view('modules.productos.edit', compact('producto', 'categorias', 'proveedores'));
        } catch (\Exception $e) {
            Log::error('Error en ProductoController@edit: ' . $e->getMessage());
            return back()->withError('Error al cargar el formulario de edición.');
        }
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, Producto $producto)
    {
        try {
            abort_unless(auth()->user()->can('productos.editar'), 403);

            $validated = $request->validate([
                'codigo' => 'nullable|string|max:50|unique:productos,codigo,' . $producto->id,
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:1000',
                'categoria_id' => 'required|exists:categorias,id',
                'proveedor_id' => 'nullable|exists:proveedores,id',
                'precio_compra' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'stock_minimo' => 'required|integer|min:0',
                'unidad_medida' => 'nullable|string|max:50',
                'ubicacion' => 'nullable|string|max:100',
                'garantia_dias' => 'nullable|integer|min:0',
                'activo' => 'boolean'
            ]);

            DB::beginTransaction();

            $producto->update($validated);

            // Log de actividad
            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'actualizar_producto',
                'modulo' => 'productos',
                'descripcion' => 'Producto actualizado: ' . $producto->nombre,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'datos_adicionales' => [
                    'producto_id' => $producto->id,
                    'cambios' => $producto->getChanges()
                ]
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente',
                    'producto' => $producto->load(['categoria', 'proveedor'])
                ]);
            }

            return redirect()->route('productos.index')
                ->with('success', 'Producto actualizado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ProductoController@update: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'producto_id' => $producto->id,
                'data' => $request->all()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el producto: ' . $e->getMessage()
                ], 500);
            }

            return back()->withError('Error al actualizar el producto. Intente nuevamente.')->withInput();
        }
    }

    /**
     * Eliminar producto
     */
    public function destroy(Producto $producto)
    {
        try {
            abort_unless(auth()->user()->can('productos.eliminar'), 403);

            // Verificar si el producto tiene relaciones que impidan su eliminación
            if ($producto->detalleVentas()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el producto porque tiene ventas asociadas.'
                ], 422);
            }

            DB::beginTransaction();

            $nombreProducto = $producto->nombre;
            
            $producto->delete();

            // Log de actividad
            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'eliminar_producto',
                'modulo' => 'productos',
                'descripcion' => 'Producto eliminado: ' . $nombreProducto,
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'datos_adicionales' => [
                    'producto_eliminado' => $nombreProducto
                ]
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en ProductoController@destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar código automático para producto
     */
    private function generarCodigoAutomatico()
    {
        $prefijo = 'PROD';
        $ultimo = Producto::latest('id')->first();
        $numero = $ultimo ? $ultimo->id + 1 : 1;
        
        return $prefijo . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Búsqueda de productos para API/AJAX
     */
    public function search(Request $request)
    {
        try {
            abort_unless(auth()->user()->can('productos.ver'), 403);

            $query = Producto::with(['categoria', 'proveedor']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%");
                });
            }

            if ($request->filled('categoria_id')) {
                $query->where('categoria_id', $request->categoria_id);
            }

            $productos = $query->where('activo', true)
                ->limit(20)
                ->get()
                ->map(function($producto) {
                    return [
                        'id' => $producto->id,
                        'text' => $producto->nombre,
                        'codigo' => $producto->codigo,
                        'nombre' => $producto->nombre,
                        'precio' => $producto->precio_venta,
                        'stock' => $producto->stock,
                        'categoria' => $producto->categoria?->nombre,
                        'tiene_stock' => $producto->stock > 0
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $productos
            ]);

        } catch (\Exception $e) {
            Log::error('Error en ProductoController@search: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda'
            ], 500);
        }
    }
}