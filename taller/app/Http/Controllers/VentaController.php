<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
        $this->middleware('permission:ventas.ver')->only(['index', 'show']);
        $this->middleware('permission:ventas.crear')->only(['create', 'store']);
        $this->middleware('permission:ventas.editar')->only(['edit', 'update']);
        $this->middleware('permission:ventas.eliminar')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort_unless(auth()->user()->can('ventas.ver'), 403);
        
        try {
            $ventas = Venta::with(['cliente', 'detalles.producto'])
                ->latest()
                ->paginate(10);
            
            return view('modules.ventas.index', compact('ventas'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Error al cargar las ventas: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(auth()->user()->can('ventas.crear'), 403);
        
        try {
            $clientes = Cliente::orderBy('nombre')->get();
            $productos = Producto::where('stock', '>', 0)->orderBy('nombre')->get();
            
            return view('modules.ventas.create', compact('clientes', 'productos'));
        } catch (\Exception $e) {
            return redirect()->route('ventas.index')
                ->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('ventas.crear'), 403);
        
        // Verificar empleado
        $empleadoCheck = $this->requireEmpleado();
        if ($empleadoCheck) return $empleadoCheck;
        
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Crear la venta con empleado
            $venta = Venta::create([
                'cliente_id' => $request->cliente_id,
                'empleado_id' => $this->getEmpleadoId(),
                'creado_por' => auth()->id(),
                'codigo_venta' => $this->generateOperationCode('V', 'ventas'),
                'fecha' => now(),
                'subtotal' => 0,
                'total' => 0,
            ]);

            $subtotal = 0;

            // Crear los detalles y actualizar stock
            foreach ($request->productos as $productoData) {
                $producto = Producto::findOrFail($productoData['producto_id']);
                
                // Verificar stock
                if ($producto->stock < $productoData['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$producto->nombre}");
                }

                // Crear detalle
                $detalle = DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $productoData['cantidad'],
                    'precio_unitario' => $productoData['precio_unitario'],
                    'subtotal' => $productoData['cantidad'] * $productoData['precio_unitario'],
                ]);

                // Actualizar stock
                $producto->decrement('stock', $productoData['cantidad']);

                $subtotal += $detalle->subtotal;
            }

            // Actualizar totales de la venta
            $venta->update([
                'subtotal' => $subtotal,
                'total' => $subtotal, // Sin impuestos por ahora
            ]);

            // Log de actividad
            if (function_exists('activity')) {
                activity()
                    ->performedOn($venta)
                    ->causedBy(auth()->user())
                    ->withProperties(['modulo' => 'ventas'])
                    ->log('Venta creada: ' . $venta->numero_factura);
            }

            DB::commit();

            return redirect()->route('ventas.show', $venta)
                ->with('success', 'Venta registrada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error al registrar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        abort_unless(auth()->user()->can('ventas.ver'), 403);
        
        try {
            $venta->load(['cliente', 'empleado', 'detalles.producto', 'createdBy']);
            return view('modules.ventas.show', compact('venta'));
        } catch (\Exception $e) {
            return redirect()->route('ventas.index')
                ->with('error', 'Error al cargar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venta $venta)
    {
        abort_unless(auth()->user()->can('ventas.editar'), 403);
        
        try {
            $clientes = Cliente::orderBy('nombre')->get();
            $productos = Producto::orderBy('nombre')->get();
            $venta->load(['cliente', 'detalles.producto']);
            
            return view('modules.ventas.edit', compact('venta', 'clientes', 'productos'));
        } catch (\Exception $e) {
            return redirect()->route('ventas.index')
                ->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        abort_unless(auth()->user()->can('ventas.editar'), 403);
        
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Restaurar stock de productos anteriores
            foreach ($venta->detalles as $detalle) {
                $detalle->producto->increment('stock', $detalle->cantidad);
                $detalle->delete();
            }

            // Actualizar venta
            $venta->update([
                'cliente_id' => $request->cliente_id,
            ]);

            $subtotal = 0;

            // Crear nuevos detalles
            foreach ($request->productos as $productoData) {
                $producto = Producto::findOrFail($productoData['producto_id']);
                
                if ($producto->stock < $productoData['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$producto->nombre}");
                }

                $detalle = DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $productoData['cantidad'],
                    'precio_unitario' => $productoData['precio_unitario'],
                    'subtotal' => $productoData['cantidad'] * $productoData['precio_unitario'],
                ]);

                $producto->decrement('stock', $productoData['cantidad']);
                $subtotal += $detalle->subtotal;
            }

            $venta->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            // Log de actividad
            if (function_exists('activity')) {
                activity()
                    ->performedOn($venta)
                    ->causedBy(auth()->user())
                    ->withProperties(['modulo' => 'ventas'])
                    ->log('Venta actualizada: ' . $venta->numero_factura);
            }

            DB::commit();

            return redirect()->route('ventas.show', $venta)
                ->with('success', 'Venta actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error al actualizar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venta $venta)
    {
        abort_unless(auth()->user()->can('ventas.eliminar'), 403);
        
        try {
            DB::beginTransaction();

            // Restaurar stock
            foreach ($venta->detalles as $detalle) {
                $detalle->producto->increment('stock', $detalle->cantidad);
            }

            // Eliminar detalles y venta
            $venta->detalles()->delete();
            $venta->delete();

            // Log de actividad
            if (function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties(['modulo' => 'ventas'])
                    ->log('Venta eliminada: ' . $venta->numero_factura);
            }

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta eliminada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Search ventas
     */
    public function search(Request $request)
    {
        abort_unless(auth()->user()->can('ventas.ver'), 403);
        
        $query = $request->get('q');
        
        $ventas = Venta::with(['cliente', 'empleado', 'detalles.producto', 'createdBy'])
            ->whereHas('cliente', function ($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                  ->orWhere('apellido', 'like', "%{$query}%");
            })
            ->orWhere('numero_factura', 'like', "%{$query}%")
            ->latest()
            ->paginate(10);

        return view('modules.ventas.index', compact('ventas', 'query'));
    }

    /**
     * Export ventas to Excel
     */
    public function exportExcel()
    {
        abort_unless(auth()->user()->can('ventas.exportar'), 403);
        
        // Implementar exportación si es necesario
        return back()->with('info', 'Funcionalidad de exportación en desarrollo');
    }

    /**
     * Stock validation for AJAX
     */
    public function stockBajo()
    {
        abort_unless(auth()->user()->can('productos.ver'), 403);
        
        $productos = Producto::whereRaw('stock <= stock_minimo')->get();
        return response()->json($productos);
    }

    /**
     * Sin stock for AJAX
     */
    public function sinStock()


    {
        abort_unless(auth()->user()->can('productos.ver'), 403);
        
        $productos = Producto::where('stock', 0)->get();
        return response()->json($productos);
    }
}

