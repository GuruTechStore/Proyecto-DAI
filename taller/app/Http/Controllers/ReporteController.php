<?php

namespace App\Http\Controllers;

use App\Services\ReporteService;
use App\Exports\VentasExport;
use App\Exports\ClientesExport;
use App\Exports\ProductosExport;
use App\Exports\ReparacionesExport;
use App\Exports\InventarioExport;
use App\Exports\ReporteGeneralExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReporteController extends Controller
{
    protected ReporteService $reporteService;

    public function __construct(ReporteService $reporteService)
    {
        $this->middleware(['auth', 'active']);
        $this->reporteService = $reporteService;
    }

    public function index()
    {
        abort_unless(auth()->user()->can('reportes.ver'), 403);
        
        $user = auth()->user();
        $availableReports = [];
        
        // Reportes según permisos
        if ($user->can('reportes.ventas')) {
            $availableReports['ventas'] = [
                'title' => 'Reportes de Ventas',
                'description' => 'Análisis de ventas por período, vendedor y producto',
                'icon' => 'chart-bar',
                'color' => 'green',
            ];
        }
        
        if ($user->can('reportes.inventario')) {
            $availableReports['inventario'] = [
                'title' => 'Reportes de Inventario',
                'description' => 'Estado de stock, movimientos y valorización',
                'icon' => 'cube',
                'color' => 'blue',
            ];
        }
        
        if ($user->can('reportes.reparaciones')) {
            $availableReports['reparaciones'] = [
                'title' => 'Reportes de Reparaciones',
                'description' => 'Análisis de reparaciones, tiempos y técnicos',
                'icon' => 'wrench',
                'color' => 'yellow',
            ];
        }
        
        if ($user->can('reportes.financieros')) {
            $availableReports['financieros'] = [
                'title' => 'Reportes Financieros',
                'description' => 'Análisis financiero y rentabilidad',
                'icon' => 'currency-dollar',
                'color' => 'purple',
            ];
        }
        
        return view('reports.index', compact('availableReports'));
    }

    // Reporte de ventas
    // public function ventasReporte(Request $request)
    // {
    //     abort_unless(auth()->user()->can('reportes.ventas'), 403);
        
    //     $validated = $request->validate([
    //         'fecha_inicio' => 'required|date',
    //         'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
    //         'vendedor_id' => 'nullable|exists:empleados,id',
    //         'formato' => 'required|in:pdf,excel,json',
    //     ]);
        
    //     $data = $this->reporteService->getVentasReporte(
    //         $validated['fecha_inicio'],
    //         $validated['fecha_fin'],
    //         $validated['vendedor_id'] ?? null
    //     );
        
    //     switch ($validated['formato']) {
    //         case 'pdf':
    //             $pdf = Pdf::loadView('reports.pdf.ventas', compact('data', 'validated'));
    //             return $pdf->download('reporte-ventas-' . now()->format('Y-m-d') . '.pdf');
                
    //         case 'excel':
    //             return Excel::download(
    //                 new VentasExport($validated['fecha_inicio'], $validated['fecha_fin'], $validated['vendedor_id']),
    //                 'reporte-ventas-' . now()->format('Y-m-d') . '.xlsx'
    //             );
                
    //         case 'json':
    //             return response()->json($data);
    //     }
    // }

    // Reporte de inventario
    public function inventarioReporte(Request $request)
    {
        abort_unless(auth()->user()->can('reportes.inventario'), 403);
        
        $validated = $request->validate([
            'categoria_id' => 'nullable|exists:categorias,id',
            'estado_stock' => 'nullable|in:disponible,bajo,agotado',
            'formato' => 'required|in:pdf,excel,json',
        ]);
        
        $data = $this->reporteService->getInventarioReporte(
            $validated['categoria_id'] ?? null,
            $validated['estado_stock'] ?? null
        );
        
        switch ($validated['formato']) {
            case 'pdf':
                $pdf = Pdf::loadView('reports.pdf.inventario', compact('data', 'validated'));
                return $pdf->download('reporte-inventario-' . now()->format('Y-m-d') . '.pdf');
                
            case 'excel':
                return Excel::download(
                    new ProductosExport($validated['categoria_id'], $validated['estado_stock']),
                    'reporte-inventario-' . now()->format('Y-m-d') . '.xlsx'
                );
                
            case 'json':
                return response()->json($data);
        }
    }

    // Reporte de reparaciones
    public function reparacionesReporte(Request $request)
    {
        abort_unless(auth()->user()->can('reportes.reparaciones'), 403);
        
        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'tecnico_id' => 'nullable|exists:empleados,id',
            'estado' => 'nullable|in:recibido,diagnosticando,reparando,completada,entregada',
            'formato' => 'required|in:pdf,excel,json',
        ]);
        
        $data = $this->reporteService->getReparacionesReporte(
            $validated['fecha_inicio'],
            $validated['fecha_fin'],
            $validated['tecnico_id'] ?? null,
            $validated['estado'] ?? null
        );
        
        switch ($validated['formato']) {
            case 'pdf':
                $pdf = Pdf::loadView('reports.pdf.reparaciones', compact('data', 'validated'));
                return $pdf->download('reporte-reparaciones-' . now()->format('Y-m-d') . '.pdf');
                
            case 'excel':
                return Excel::download(
                    new ReparacionesExport($validated['fecha_inicio'], $validated['fecha_fin'], $validated['tecnico_id'], $validated['estado']),
                    'reporte-reparaciones-' . now()->format('Y-m-d') . '.xlsx'
                );
                
            case 'json':
                return response()->json($data);
        }
    }

    // Reporte financiero
    // public function financierosReporte(Request $request)
    // {
    //     abort_unless(auth()->user()->can('reportes.financieros'), 403);
        
    //     $validated = $request->validate([
    //         'fecha_inicio' => 'required|date',
    //         'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
    //         'tipo_analisis' => 'required|in:general,rentabilidad,flujo_caja',
    //         'formato' => 'required|in:pdf,excel,json',
    //     ]);
        
    //     $data = $this->reporteService->getFinancierosReporte(
    //         $validated['fecha_inicio'],
    //         $validated['fecha_fin'],
    //         $validated['tipo_analisis']
    //     );
        
    //     switch ($validated['formato']) {
    //         case 'pdf':
    //             $pdf = Pdf::loadView('reports.pdf.financieros', compact('data', 'validated'));
    //             return $pdf->download('reporte-financiero-' . now()->format('Y-m-d') . '.pdf');
                
    //         case 'excel':
    //             return Excel::download(
    //                 new ReporteGeneralExport($data, 'financiero'),
    //                 'reporte-financiero-' . now()->format('Y-m-d') . '.xlsx'
    //             );
                
    //         case 'json':
    //             return response()->json($data);
    //     }
    // }

    // Dashboard de reportes con gráficos
    public function dashboard()
    {
        abort_unless(auth()->user()->can('reportes.ver'), 403);
        
        $data = [
            'ventas_mes' => $this->reporteService->getVentasMesActual(),
            'reparaciones_pendientes' => $this->reporteService->getReparacionesPendientes(),
            'productos_bajo_stock' => $this->reporteService->getProductosBajoStock(),
            'clientes_nuevos' => $this->reporteService->getClientesNuevosMes(),
            'top_productos' => $this->reporteService->getTopProductos(),
            'ventas_por_dia' => $this->reporteService->getVentasPorDiaUltimos30(),
            'resumen_financiero' => $this->reporteService->getResumenFinanciero(),
            'rendimiento_empleados' => $this->reporteService->getRendimientoEmpleados(),        ];
        
        return view('reports.dashboard', compact('data'));
    }

    // API endpoints para datos en tiempo real
    public function getVentasChart(Request $request)
    {
        abort_unless(auth()->user()->can('reportes.ventas'), 403);
        
        $periodo = $request->get('periodo', '30d');
        $data = $this->reporteService->getVentasChartData($periodo);
        
        return response()->json($data);
    }

    public function getReparacionesStats(Request $request)
    {
        abort_unless(auth()->user()->can('reportes.reparaciones'), 403);
        
        $data = $this->reporteService->getReparacionesStats();
        
        return response()->json($data);
    }

    public function getInventarioAlerts(Request $request)
    {
        abort_unless(auth()->user()->can('reportes.inventario'), 403);
        
        $data = $this->reporteService->getInventarioAlerts();
        
        return response()->json($data);
    }
    public function ventasDashboard()
{
    abort_unless(auth()->user()->can('reportes.ventas'), 403);
    // Tu lógica aquí
    return view('reports.ventas.dashboard');
}

public function inventarioDashboard()
{
    abort_unless(auth()->user()->can('reportes.inventario'), 403);
    // Tu lógica aquí
    return view('reports.inventario.dashboard');
}

public function reparacionesDashboard()
{
    abort_unless(auth()->user()->can('reportes.reparaciones'), 403);
    // Tu lógica aquí
    return view('reports.reparaciones.dashboard');
}
}