<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Reparacion;
use App\Models\Venta;
use App\Models\Empleado;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
    }

    public function index()
    {
        $user = auth()->user();
        
        try {
            // Obtener estadísticas principales
            $stats = $this->getMainStats();
            
            // Obtener datos de gráficos
            $chartData = $this->getChartData();
            
            // Obtener actividad reciente
            $actividadReciente = $this->getRecentActivity();
            
            // Obtener alertas del sistema
            $alertas = $this->getSystemAlerts();

            return view('dashboard', compact('stats', 'chartData', 'actividadReciente', 'alertas'));

        } catch (\Exception $e) {
            Log::error('Error en Dashboard: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null
            ]);
            
            // Devolver datos por defecto en caso de error
            return view('dashboard', [
                'stats' => $this->getDefaultStats(),
                'chartData' => $this->getDefaultChartData(),
                'actividadReciente' => [],
                'alertas' => []
            ])->with('error', 'Error al cargar algunas estadísticas del dashboard.');
        }
    }

    /**
     * Obtener estadísticas principales del dashboard
     */
    private function getMainStats()
    {
        return Cache::remember('dashboard_main_stats', 300, function () {
            $currentMonth = Carbon::now()->startOfMonth();
            $previousMonth = Carbon::now()->subMonth()->startOfMonth();
            $today = Carbon::today();

            $stats = [];

            try {
                // Estadísticas básicas - siempre disponibles
                $stats['totalUsuarios'] = Usuario::count();
                $stats['usuariosActivos'] = Usuario::where('activo', true)->count();
                $stats['usuariosConectadosHoy'] = Usuario::whereDate('ultimo_login', $today)->count();

                // Estadísticas de clientes
                if (class_exists(Cliente::class)) {
                    $stats['totalClientes'] = Cliente::count();
                    $stats['clientesActivos'] = Cliente::where('activo', true)->count();
                    $stats['clientesNuevos'] = Cliente::where('created_at', '>=', $currentMonth)->count();
                } else {
                    $stats['totalClientes'] = 0;
                    $stats['clientesActivos'] = 0;
                    $stats['clientesNuevos'] = 0;
                }

                // Estadísticas de productos
                if (class_exists(Producto::class)) {
                    $stats['totalProductos'] = Producto::count();
                    $stats['productosActivos'] = Producto::where('activo', true)->count();
                    $stats['productosBajoStock'] = Producto::whereRaw('stock <= stock_minimo')->count();
                    $stats['productosSinStock'] = Producto::where('stock', '<=', 0)->count();
                    $stats['valorInventario'] = Producto::selectRaw('SUM(precio_venta * stock) as total')->value('total') ?? 0;
                } else {
                    $stats['totalProductos'] = 0;
                    $stats['productosActivos'] = 0;
                    $stats['productosBajoStock'] = 0;
                    $stats['productosSinStock'] = 0;
                    $stats['valorInventario'] = 0;
                }

                // Estadísticas de empleados
                if (class_exists(Empleado::class)) {
                    $stats['totalEmpleados'] = Empleado::count();
                    $stats['empleadosActivos'] = Empleado::where('activo', true)->count();
                } else {
                    $stats['totalEmpleados'] = 0;
                    $stats['empleadosActivos'] = 0;
                }

                // Estadísticas de categorías y proveedores
                if (class_exists(Categoria::class)) {
                    $stats['totalCategorias'] = Categoria::count();
                    $stats['categoriasActivas'] = Categoria::where('activa', true)->count();
                } else {
                    $stats['totalCategorias'] = 0;
                    $stats['categoriasActivas'] = 0;
                }

                if (class_exists(Proveedor::class)) {
                    $stats['totalProveedores'] = Proveedor::count();
                    $stats['proveedoresActivos'] = Proveedor::where('activo', true)->count();
                } else {
                    $stats['totalProveedores'] = 0;
                    $stats['proveedoresActivos'] = 0;
                }

                // Estadísticas de ventas
                if (class_exists(Venta::class)) {
                    $stats['ventasHoy'] = Venta::whereDate('created_at', $today)->sum('total');
                    $stats['ventasMes'] = Venta::where('created_at', '>=', $currentMonth)->sum('total');
                    $stats['ventasMesAnterior'] = Venta::whereBetween('created_at', [$previousMonth, $currentMonth])->sum('total');
                    $stats['totalVentasMes'] = Venta::where('created_at', '>=', $currentMonth)->count();
                    
                    // Calcular crecimiento de ventas
                    if ($stats['ventasMesAnterior'] > 0) {
                        $stats['crecimientoVentas'] = (($stats['ventasMes'] - $stats['ventasMesAnterior']) / $stats['ventasMesAnterior']) * 100;
                    } else {
                        $stats['crecimientoVentas'] = $stats['ventasMes'] > 0 ? 100 : 0;
                    }
                } else {
                    $stats['ventasHoy'] = 0;
                    $stats['ventasMes'] = 0;
                    $stats['ventasMesAnterior'] = 0;
                    $stats['totalVentasMes'] = 0;
                    $stats['crecimientoVentas'] = 0;
                }

                // Estadísticas de reparaciones
                if (class_exists(Reparacion::class)) {
                    $stats['totalReparaciones'] = Reparacion::count();
                    $stats['reparacionesPendientes'] = Reparacion::whereIn('estado', ['pendiente', 'diagnostico'])->count();
                    $stats['reparacionesEnProceso'] = Reparacion::where('estado', 'en_proceso')->count();
                    $stats['reparacionesCompletadas'] = Reparacion::where('estado', 'completado')
                        ->where('created_at', '>=', $currentMonth)->count();
                } else {
                    $stats['totalReparaciones'] = 0;
                    $stats['reparacionesPendientes'] = 0;
                    $stats['reparacionesEnProceso'] = 0;
                    $stats['reparacionesCompletadas'] = 0;
                }

            } catch (\Exception $e) {
                Log::error('Error al obtener estadísticas del dashboard: ' . $e->getMessage());
                // Devolver estadísticas por defecto en caso de error
                return $this->getDefaultStats();
            }

            return $stats;
        });
    }

    /**
     * Obtener datos para gráficos
     */
    private function getChartData()
    {
        return Cache::remember('dashboard_chart_data', 600, function () {
            $chartData = [];

            try {
                // Gráfico de ventas de los últimos 7 días
                if (class_exists(Venta::class)) {
                    $chartData['ventasUltimos7Dias'] = $this->getVentasUltimos7Dias();
                } else {
                    $chartData['ventasUltimos7Dias'] = $this->getEmptyChartData();
                }

                // Gráfico de reparaciones por estado
                if (class_exists(Reparacion::class)) {
                    $chartData['reparacionesPorEstado'] = $this->getReparacionesPorEstado();
                } else {
                    $chartData['reparacionesPorEstado'] = $this->getEmptyPieChartData();
                }

                // Gráfico de productos más vendidos
                if (class_exists(Venta::class) && class_exists(Producto::class)) {
                    $chartData['productosMasVendidos'] = $this->getProductosMasVendidos();
                } else {
                    $chartData['productosMasVendidos'] = [];
                }

            } catch (\Exception $e) {
                Log::error('Error al obtener datos de gráficos: ' . $e->getMessage());
                return $this->getDefaultChartData();
            }

            return $chartData;
        });
    }

    /**
     * Obtener actividad reciente
     */
    private function getRecentActivity()
    {
        try {
            $actividades = [];

            // Ventas recientes
            if (class_exists(Venta::class)) {
                $ventasRecientes = Venta::with(['cliente'])
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(function($venta) {
                        return [
                            'tipo' => 'venta',
                            'descripcion' => 'Venta #' . $venta->id . ' - ' . ($venta->cliente->nombre ?? 'Cliente no especificado'),
                            'monto' => $venta->total,
                            'fecha' => $venta->created_at,
                            'icono' => 'fas fa-shopping-cart',
                            'color' => 'text-green-600'
                        ];
                    });
                
                $actividades = array_merge($actividades, $ventasRecientes->toArray());
            }

            // Reparaciones recientes
            if (class_exists(Reparacion::class)) {
                $reparacionesRecientes = Reparacion::with(['cliente', 'equipo'])
                    ->latest()
                    ->limit(3)
                    ->get()
                    ->map(function($reparacion) {
                        return [
                            'tipo' => 'reparacion',
                            'descripcion' => 'Reparación #' . $reparacion->id . ' - ' . ($reparacion->equipo->tipo ?? 'Equipo'),
                            'estado' => $reparacion->estado,
                            'fecha' => $reparacion->created_at,
                            'icono' => 'fas fa-tools',
                            'color' => 'text-blue-600'
                        ];
                    });
                
                $actividades = array_merge($actividades, $reparacionesRecientes->toArray());
            }

            // Clientes nuevos
            if (class_exists(Cliente::class)) {
                $clientesNuevos = Cliente::latest()
                    ->limit(3)
                    ->get()
                    ->map(function($cliente) {
                        return [
                            'tipo' => 'cliente',
                            'descripcion' => 'Nuevo cliente: ' . $cliente->nombre,
                            'telefono' => $cliente->telefono,
                            'fecha' => $cliente->created_at,
                            'icono' => 'fas fa-user-plus',
                            'color' => 'text-purple-600'
                        ];
                    });
                
                $actividades = array_merge($actividades, $clientesNuevos->toArray());
            }

            // Ordenar por fecha más reciente
            usort($actividades, function($a, $b) {
                return $b['fecha'] <=> $a['fecha'];
            });

            // Limitar a 10 actividades
            return array_slice($actividades, 0, 10);

        } catch (\Exception $e) {
            Log::error('Error al obtener actividad reciente: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener alertas del sistema
     */
    private function getSystemAlerts()
    {
        try {
            $alertas = [];

            // Alertas de stock bajo
            if (class_exists(Producto::class)) {
                $productosStockBajo = Producto::whereRaw('stock <= stock_minimo')
                    ->where('activo', true)
                    ->count();
                
                if ($productosStockBajo > 0) {
                    $alertas[] = [
                        'tipo' => 'warning',
                        'titulo' => 'Stock Bajo',
                        'mensaje' => "{$productosStockBajo} producto(s) con stock bajo o agotado",
                        'enlace' => route('productos.index', ['stock_filter' => 'bajo']),
                        'icono' => 'fas fa-exclamation-triangle'
                    ];
                }
            }

            // Alertas de reparaciones pendientes
            if (class_exists(Reparacion::class)) {
                $reparacionesPendientes = Reparacion::where('estado', 'pendiente')->count();
                
                if ($reparacionesPendientes > 0) {
                    $alertas[] = [
                        'tipo' => 'info',
                        'titulo' => 'Reparaciones Pendientes',
                        'mensaje' => "{$reparacionesPendientes} reparación(es) esperando atención",
                        'enlace' => route('reparaciones.index', ['estado' => 'pendiente']),
                        'icono' => 'fas fa-clock'
                    ];
                }
            }

            return $alertas;

        } catch (\Exception $e) {
            Log::error('Error al obtener alertas del sistema: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Estadísticas por defecto en caso de error
     */
    private function getDefaultStats()
    {
        return [
            'totalUsuarios' => 0,
            'usuariosActivos' => 0,
            'usuariosConectadosHoy' => 0,
            'totalClientes' => 0,
            'clientesActivos' => 0,
            'clientesNuevos' => 0,
            'totalProductos' => 0,
            'productosActivos' => 0,
            'productosBajoStock' => 0,
            'productosSinStock' => 0,
            'valorInventario' => 0,
            'totalEmpleados' => 0,
            'empleadosActivos' => 0,
            'totalCategorias' => 0,
            'categoriasActivas' => 0,
            'totalProveedores' => 0,
            'proveedoresActivos' => 0,
            'ventasHoy' => 0,
            'ventasMes' => 0,
            'ventasMesAnterior' => 0,
            'totalVentasMes' => 0,
            'crecimientoVentas' => 0,
            'totalReparaciones' => 0,
            'reparacionesPendientes' => 0,
            'reparacionesEnProceso' => 0,
            'reparacionesCompletadas' => 0,
        ];
    }

    /**
     * Datos por defecto para gráficos
     */
    private function getDefaultChartData()
    {
        return [
            'ventasUltimos7Dias' => $this->getEmptyChartData(),
            'reparacionesPorEstado' => $this->getEmptyPieChartData(),
            'productosMasVendidos' => []
        ];
    }

    /**
     * Ventas de los últimos 7 días
     */
    private function getVentasUltimos7Dias()
    {
        $dias = [];
        $ventas = [];

        for ($i = 6; $i >= 0; $i--) {
            $fecha = Carbon::today()->subDays($i);
            $dias[] = $fecha->format('d/m');
            
            $ventasDia = Venta::whereDate('created_at', $fecha)->sum('total');
            $ventas[] = $ventasDia;
        }

        return [
            'labels' => $dias,
            'datasets' => [
                [
                    'label' => 'Ventas (S/)',
                    'data' => $ventas,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4
                ]
            ]
        ];
    }

    /**
     * Reparaciones por estado
     */
    private function getReparacionesPorEstado()
    {
        $estados = [
            'pendiente' => 'Pendiente',
            'diagnostico' => 'Diagnóstico',
            'en_proceso' => 'En Proceso',
            'completado' => 'Completado',
            'entregado' => 'Entregado'
        ];

        $labels = [];
        $data = [];
        $colors = ['#EF4444', '#F59E0B', '#3B82F6', '#10B981', '#8B5CF6'];

        foreach ($estados as $estado => $label) {
            $count = Reparacion::where('estado', $estado)->count();
            if ($count > 0) {
                $labels[] = $label;
                $data[] = $count;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($labels))
                ]
            ]
        ];
    }

    /**
     * Productos más vendidos
     */
    private function getProductosMasVendidos()
    {
        if (!class_exists('App\Models\DetalleVenta')) {
            return [];
        }

        return DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->select('productos.nombre', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'))
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Datos vacíos para gráfico de líneas
     */
    private function getEmptyChartData()
    {
        return [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Sin datos',
                    'data' => [],
                    'borderColor' => 'rgb(156, 163, 175)',
                    'backgroundColor' => 'rgba(156, 163, 175, 0.1)'
                ]
            ]
        ];
    }

    /**
     * Datos vacíos para gráfico circular
     */
    private function getEmptyPieChartData()
    {
        return [
            'labels' => ['Sin datos'],
            'datasets' => [
                [
                    'data' => [1],
                    'backgroundColor' => ['#E5E7EB']
                ]
            ]
        ];
    }

    /**
     * Actualizar datos del dashboard (para AJAX)
     */
    public function refreshData(Request $request)
    {
        try {
            // Limpiar cache
            Cache::forget('dashboard_main_stats');
            Cache::forget('dashboard_chart_data');

            $data = [
                'stats' => $this->getMainStats(),
                'chartData' => $this->getChartData(),
                'actividadReciente' => $this->getRecentActivity(),
                'alertas' => $this->getSystemAlerts()
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error al refrescar datos del dashboard: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los datos'
            ], 500);
        }
    }

    /**
     * Obtener notificaciones del usuario
     */
    public function getNotifications(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Si el usuario tiene un sistema de notificaciones, implementar aquí
            $notifications = []; // Placeholder
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener notificaciones: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar notificaciones'
            ], 500);
        }
    }

    /**
     * Marcar notificación como leída
     */
    public function markNotificationRead(Request $request, $id)
    {
        try {
            // Implementar lógica para marcar notificación como leída
            
            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como leída'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al marcar notificación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al marcar la notificación'
            ], 500);
        }
    }
}