<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\Reparacion;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\DetalleVenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    public function getVentasReporte($fechaInicio, $fechaFin, $vendedorId = null)
    {
        $query = Venta::with(['empleado', 'cliente', 'detalleVentas.producto'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin]);
            
        if ($vendedorId) {
            $query->where('empleado_id', $vendedorId);
        }
        
        $ventas = $query->get();
        
        return [
            'ventas' => $ventas,
            'resumen' => [
                'total_ventas' => $ventas->count(),
                'monto_total' => $ventas->sum('total'),
                'promedio_venta' => $ventas->avg('total'),
                'mayor_venta' => $ventas->max('total'),
                'menor_venta' => $ventas->min('total'),
            ],
            'por_vendedor' => $ventas->groupBy('empleado.id')->map(function ($grupo) {
                return [
                    'vendedor' => $grupo->first()->empleado->nombres . ' ' . $grupo->first()->empleado->apellidos,
                    'cantidad' => $grupo->count(),
                    'total' => $grupo->sum('total'),
                ];
            }),
            'por_dia' => $ventas->groupBy(function ($venta) {
                return $venta->created_at->format('Y-m-d');
            })->map(function ($grupo, $fecha) {
                return [
                    'fecha' => $fecha,
                    'cantidad' => $grupo->count(),
                    'total' => $grupo->sum('total'),
                ];
            })->values(),
        ];
    }
    
    public function getInventarioReporte($categoriaId = null, $estadoStock = null)
    {
        $query = Producto::with(['categoria']);
        
        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }
        
        if ($estadoStock) {
            switch ($estadoStock) {
                case 'bajo':
                    $query->whereRaw('stock_actual <= stock_minimo');
                    break;
                case 'agotado':
                    $query->where('stock_actual', 0);
                    break;
                case 'disponible':
                    $query->where('stock_actual', '>', 0);
                    break;
            }
        }
        
        $productos = $query->get();
        
        return [
            'productos' => $productos,
            'resumen' => [
                'total_productos' => $productos->count(),
                'valor_inventario' => $productos->sum(function ($p) {
                    return $p->stock_actual * $p->precio_compra;
                }),
                'productos_bajo_stock' => $productos->filter(function ($p) {
                    return $p->stock_actual <= $p->stock_minimo;
                })->count(),
                'productos_agotados' => $productos->where('stock_actual', 0)->count(),
            ],
            'por_categoria' => $productos->groupBy('categoria.nombre')->map(function ($grupo) {
                return [
                    'cantidad' => $grupo->count(),
                    'valor' => $grupo->sum(function ($p) {
                        return $p->stock_actual * $p->precio_compra;
                    }),
                ];
            }),
        ];
    }

    public function getReparacionesReporte($fechaInicio, $fechaFin, $tecnicoId = null, $estado = null)
    {
        $query = Reparacion::with(['cliente', 'empleado', 'equipo'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin]);
            
        if ($tecnicoId) {
            $query->where('empleado_id', $tecnicoId);
        }
        
        if ($estado) {
            $query->where('estado', $estado);
        }
        
        $reparaciones = $query->get();
        
        return [
            'reparaciones' => $reparaciones,
            'resumen' => [
                'total_reparaciones' => $reparaciones->count(),
                'completadas' => $reparaciones->where('estado', 'completada')->count(),
                'pendientes' => $reparaciones->whereIn('estado', ['recibido', 'diagnosticando', 'reparando'])->count(),
                'monto_total' => $reparaciones->sum('costo_final'),
                'tiempo_promedio' => $this->calcularTiempoPromedioReparacion($reparaciones),
            ],
            'por_tecnico' => $reparaciones->groupBy('empleado.id')->map(function ($grupo) {
                return [
                    'tecnico' => $grupo->first()->empleado->nombres . ' ' . $grupo->first()->empleado->apellidos,
                    'cantidad' => $grupo->count(),
                    'completadas' => $grupo->where('estado', 'completada')->count(),
                    'monto_total' => $grupo->sum('costo_final'),
                ];
            }),
            'por_estado' => $reparaciones->groupBy('estado')->map(function ($grupo, $estado) {
                return [
                    'estado' => ucfirst($estado),
                    'cantidad' => $grupo->count(),
                    'porcentaje' => round(($grupo->count() / $reparaciones->count()) * 100, 2),
                ];
            }),
        ];
    }

    public function getFinancierosReporte($fechaInicio, $fechaFin, $tipoAnalisis)
    {
        $ventas = Venta::whereBetween('created_at', [$fechaInicio, $fechaFin])->get();
        $reparaciones = Reparacion::whereBetween('created_at', [$fechaInicio, $fechaFin])->get();
        
        $ingresoVentas = $ventas->sum('total');
        $ingresoReparaciones = $reparaciones->sum('costo_final');
        $totalIngresos = $ingresoVentas + $ingresoReparaciones;
        
        $data = [
            'resumen_general' => [
                'total_ingresos' => $totalIngresos,
                'ingresos_ventas' => $ingresoVentas,
                'ingresos_reparaciones' => $ingresoReparaciones,
                'total_ventas' => $ventas->count(),
                'total_reparaciones' => $reparaciones->count(),
            ],
            'desglose_por_mes' => $this->getIngresosPorMes($fechaInicio, $fechaFin),
        ];
        
        if ($tipoAnalisis === 'rentabilidad') {
            $data['analisis_rentabilidad'] = $this->getAnalisisRentabilidad($ventas, $reparaciones);
        }
        
        if ($tipoAnalisis === 'flujo_caja') {
            $data['flujo_caja'] = $this->getFlujoCaja($fechaInicio, $fechaFin);
        }
        
        return $data;
    }
    
    public function getVentasMesActual()
    {
        return Venta::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
    }
    
    public function getReparacionesPendientes()
    {
        return Reparacion::whereIn('estado', ['recibido', 'diagnosticando', 'reparando'])
            ->count();
    }
    
    public function getProductosBajoStock()
    {
        return Producto::whereRaw('stock_actual <= stock_minimo')->count();
    }
    
    public function getClientesNuevosMes()
    {
        return Cliente::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }
    
    public function getTopProductos($limit = 10)
    {
        return DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->select('productos.nombre', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'))
            ->groupBy('productos.id', 'productos.nombre')
            ->orderBy('total_vendido', 'desc')
            ->limit($limit)
            ->get();
    }
    
    public function getVentasPorDiaUltimos30()
    {
        return Venta::selectRaw('DATE(created_at) as fecha, SUM(total) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->fecha => $item->total];
            });
    }

    public function getResumenFinanciero()
    {
        $hoy = now();
        $mesActual = $hoy->copy()->startOfMonth();
        $mesAnterior = $hoy->copy()->subMonth()->startOfMonth();
        
        return [
            'ventas_mes_actual' => Venta::where('created_at', '>=', $mesActual)->sum('total'),
            'ventas_mes_anterior' => Venta::whereBetween('created_at', [$mesAnterior, $mesActual])->sum('total'),
            'reparaciones_mes_actual' => Reparacion::where('created_at', '>=', $mesActual)->sum('costo_final'),
            'reparaciones_mes_anterior' => Reparacion::whereBetween('created_at', [$mesAnterior, $mesActual])->sum('costo_final'),
        ];
    }

    public function getRendimientoTecnicos()
    {
        return Empleado::whereHas('roles', function($query) {
                $query->whereIn('name', ['Técnico', 'Técnico Senior']);
            })
            ->withCount([
                'reparaciones as total_reparaciones',
                'reparaciones as completadas' => function($query) {
                    $query->where('estado', 'completada');
                }
            ])
            ->get()
            ->map(function($tecnico) {
                return [
                    'nombre' => $tecnico->nombres . ' ' . $tecnico->apellidos,
                    'total_reparaciones' => $tecnico->total_reparaciones,
                    'completadas' => $tecnico->completadas,
                    'eficiencia' => $tecnico->total_reparaciones > 0 
                        ? round(($tecnico->completadas / $tecnico->total_reparaciones) * 100, 2) 
                        : 0,
                ];
            });
    }

    public function getVentasChartData($periodo)
    {
        $fechaInicio = match($periodo) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => now()->subDays(30),
        };
        
        $ventas = Venta::selectRaw('DATE(created_at) as fecha, SUM(total) as total, COUNT(*) as cantidad')
            ->where('created_at', '>=', $fechaInicio)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        
        $labels = [];
        $totales = [];
        $cantidades = [];
        
        $current = $fechaInicio->copy();
        while ($current <= now()) {
            $fechaStr = $current->format('Y-m-d');
            $labels[] = $current->format('d/m');
            
            $venta = $ventas->firstWhere('fecha', $fechaStr);
            $totales[] = $venta ? (float) $venta->total : 0;
            $cantidades[] = $venta ? (int) $venta->cantidad : 0;
            
            $current->addDay();
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Monto de Ventas (S/.)',
                    'data' => $totales,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Cantidad de Ventas',
                    'data' => $cantidades,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.1,
                ]
            ]
        ];
    }

    public function getReparacionesStats()
    {
        $total = Reparacion::count();
        $pendientes = Reparacion::whereIn('estado', ['recibido', 'diagnosticando', 'reparando'])->count();
        $completadas = Reparacion::where('estado', 'completada')->count();
        $entregadas = Reparacion::where('estado', 'entregada')->count();
        
        return [
            'total' => $total,
            'pendientes' => $pendientes,
            'completadas' => $completadas,
            'entregadas' => $entregadas,
            'eficiencia' => $total > 0 ? round(($completadas / $total) * 100, 2) : 0,
        ];
    }

    public function getInventarioAlerts()
    {
        $productosBajoStock = Producto::whereRaw('stock_actual <= stock_minimo')
            ->select('nombre', 'stock_actual', 'stock_minimo')
            ->get();
            
        $productosAgotados = Producto::where('stock_actual', 0)
            ->select('nombre', 'stock_actual')
            ->get();
        
        return [
            'bajo_stock' => $productosBajoStock,
            'agotados' => $productosAgotados,
            'total_alertas' => $productosBajoStock->count() + $productosAgotados->count(),
        ];
    }

    // Métodos auxiliares privados
    private function calcularTiempoPromedioReparacion($reparaciones)
    {
        $completadas = $reparaciones->where('estado', 'completada')
            ->filter(function($r) {
                return $r->fecha_entrega && $r->fecha_ingreso;
            });
            
        if ($completadas->isEmpty()) {
            return 0;
        }
        
        $tiempoTotal = $completadas->sum(function($r) {
            return Carbon::parse($r->fecha_entrega)->diffInDays(Carbon::parse($r->fecha_ingreso));
        });
        
        return round($tiempoTotal / $completadas->count(), 2);
    }

    private function getAnalisisRentabilidad($ventas, $reparaciones)
    {
        // Calcular costos y márgenes básicos
        $costoVentas = $ventas->sum(function($venta) {
            return $venta->detalleVentas->sum(function($detalle) {
                return $detalle->cantidad * $detalle->producto->precio_compra;
            });
        });
        
        $margenVentas = $ventas->sum('total') - $costoVentas;
        $margenReparaciones = $reparaciones->sum('costo_final') * 0.7; // Asumiendo 70% de margen
        
        return [
            'margen_ventas' => $margenVentas,
            'margen_reparaciones' => $margenReparaciones,
            'margen_total' => $margenVentas + $margenReparaciones,
            'rentabilidad_ventas' => $ventas->sum('total') > 0 ? round(($margenVentas / $ventas->sum('total')) * 100, 2) : 0,
            'rentabilidad_reparaciones' => $reparaciones->sum('costo_final') > 0 ? round(($margenReparaciones / $reparaciones->sum('costo_final')) * 100, 2) : 0,
        ];
    }

    private function getIngresosPorMes($fechaInicio, $fechaFin)
    {
        $ventas = DB::table('ventas')
            ->selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, SUM(total) as total_ventas')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('año', 'mes')
            ->get();
            
        $reparaciones = DB::table('reparaciones')
            ->selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, SUM(costo_final) as total_reparaciones')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('año', 'mes')
            ->get();
        
        $resultado = [];
        foreach ($ventas as $venta) {
            $key = $venta->año . '-' . str_pad($venta->mes, 2, '0', STR_PAD_LEFT);
            $resultado[$key] = [
                'periodo' => $key,
                'ventas' => $venta->total_ventas,
                'reparaciones' => 0,
            ];
        }
        
        foreach ($reparaciones as $reparacion) {
            $key = $reparacion->año . '-' . str_pad($reparacion->mes, 2, '0', STR_PAD_LEFT);
            if (isset($resultado[$key])) {
                $resultado[$key]['reparaciones'] = $reparacion->total_reparaciones;
            } else {
                $resultado[$key] = [
                    'periodo' => $key,
                    'ventas' => 0,
                    'reparaciones' => $reparacion->total_reparaciones,
                ];
            }
        }
        
        return array_values($resultado);
    }

    private function getFlujoCaja($fechaInicio, $fechaFin)
    {
        // Implementar lógica básica de flujo de caja
        $ingresos = Venta::whereBetween('created_at', [$fechaInicio, $fechaFin])->sum('total') +
                   Reparacion::whereBetween('created_at', [$fechaInicio, $fechaFin])->sum('costo_final');
        
        return [
            'ingresos_totales' => $ingresos,
            'periodo' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
            'promedio_diario' => $ingresos / Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)),
        ];
    }
}