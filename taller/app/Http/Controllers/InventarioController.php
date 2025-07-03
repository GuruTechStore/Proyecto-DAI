<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\MovimientoStock;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
        $this->middleware('permission:inventario.ver')->only(['index']);
        $this->middleware('permission:inventario.ajustar')->only(['ajustar']);
        $this->middleware('permission:inventario.entradas')->only(['entrada']);
    }

    public function index()
    {
        abort_unless(auth()->user()->can('inventario.ver'), 403);
        
        return view('modules.inventario.index');
    }

    public function ajustar(Request $request)
    {
        abort_unless(auth()->user()->can('inventario.ajustar'), 403);
        
        // Verificar empleado
        if (!auth()->user()->empleado_id) {
            return back()->with('error', 'Usuario no asociado a un empleado');
        }   
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo_ajuste' => 'required|in:entrada,salida,ajuste',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated) {
            $producto = Producto::find($validated['producto_id']);
            $stockAnterior = $producto->stock_actual;
            
            // Calcular nuevo stock
            switch ($validated['tipo_ajuste']) {
                case 'entrada':
                    $nuevoStock = $stockAnterior + $validated['cantidad'];
                    break;
                case 'salida':
                    $nuevoStock = max(0, $stockAnterior - $validated['cantidad']);
                    break;
                case 'ajuste':
                    $nuevoStock = $validated['cantidad'];
                    $validated['cantidad'] = abs($nuevoStock - $stockAnterior);
                    break;
            }
            
            // Actualizar stock del producto
            $producto->update(['stock_actual' => $nuevoStock]);
            
            // Registrar movimiento
            MovimientoStock::create([
                'producto_id' => $producto->id,
                'tipo_movimiento' => $validated['tipo_ajuste'],
                'cantidad' => $validated['cantidad'],
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $nuevoStock,
                'motivo' => $validated['motivo'],
                'observaciones' => $validated['observaciones'],
                'usuario_id' => auth()->id(),
                'fecha' => now(),
            ]);
            
            // Log de actividad
            activity()
                ->performedOn($producto)
                ->causedBy(auth()->user())
                ->withProperties([
                    'modulo' => 'inventario',
                    'tipo_ajuste' => $validated['tipo_ajuste'],
                    'cantidad' => $validated['cantidad'],
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $nuevoStock
                ])
                ->log('Ajuste de inventario: ' . $producto->nombre);
        });

        return back()->with('success', 'Inventario ajustado correctamente');
    }

    public function entrada(Request $request)
    {
        abort_unless(auth()->user()->can('inventario.entradas'), 403);
        
        $validated = $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
            'observaciones' => 'nullable|string|max:500',
            'documento_referencia' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['productos'] as $productoData) {
                $producto = Producto::find($productoData['id']);
                $stockAnterior = $producto->stock_actual;
                $nuevoStock = $stockAnterior + $productoData['cantidad'];
                
                // Actualizar stock
                $producto->update(['stock_actual' => $nuevoStock]);
                
                // Registrar movimiento
                MovimientoStock::create([
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => 'entrada',
                    'cantidad' => $productoData['cantidad'],
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $nuevoStock,
                    'motivo' => $validated['motivo'],
                    'observaciones' => $validated['observaciones'],
                    'documento_referencia' => $validated['documento_referencia'],
                    'usuario_id' => auth()->id(),
                    'fecha' => now(),
                ]);
            }
            
            // Log de actividad
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'modulo' => 'inventario',
                    'tipo_movimiento' => 'entrada_masiva',
                    'productos_count' => count($validated['productos'])
                ])
                ->log('Entrada masiva de inventario');
        });

        return back()->with('success', 'Entrada de inventario registrada correctamente');
    }

    public function reporteBajoStock()
    {
        abort_unless(auth()->user()->can('inventario.ver'), 403);
        
        $productos = Producto::whereRaw('stock_actual <= stock_minimo')
            ->where('activo', true)
            ->with(['categoria'])
            ->orderBy('stock_actual', 'asc')
            ->get();
        
        return view('modules.inventario.bajo-stock', compact('productos'));
    }

    public function reporteMovimientos(Request $request)
    {
        abort_unless(auth()->user()->can('inventario.ver'), 403);
        
        $query = MovimientoStock::with(['producto', 'usuario']);
        
        if ($request->fecha_desde) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        
        if ($request->fecha_hasta) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }
        
        if ($request->tipo_movimiento) {
            $query->where('tipo_movimiento', $request->tipo_movimiento);
        }
        
        if ($request->producto_id) {
            $query->where('producto_id', $request->producto_id);
        }
        
        $movimientos = $query->orderBy('fecha', 'desc')->paginate(50);
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();
        
        return view('modules.inventario.movimientos', compact('movimientos', 'productos'));
    }
}