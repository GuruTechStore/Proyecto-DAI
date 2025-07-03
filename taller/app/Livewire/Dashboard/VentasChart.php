<?php

namespace App\Livewire\Dashboard;

use App\Services\ReporteService;
use Livewire\Component;
use Carbon\Carbon;

class VentasChart extends Component
{
    public string $periodo = '30d'; // 7d, 30d, 90d, 1y
    public array $chartData = [];
    public string $chartType = 'line'; // line, bar
    public bool $loading = false;
    
    protected ReporteService $reporteService;
    
    public function boot(ReporteService $reporteService)
    {
        $this->reporteService = $reporteService;
    }
    
    public function mount()
    {
        abort_unless(auth()->user()->can('reportes.ventas'), 403);
        $this->updateChartData();
    }
    
    public function updatedPeriodo()
    {
        $this->loading = true;
        $this->updateChartData();
        $this->loading = false;
    }
    
    public function updatedChartType()
    {
        $this->dispatch('updateChart', [
            'data' => $this->chartData, 
            'type' => $this->chartType
        ]);
    }
    
    public function refreshData()
    {
        $this->loading = true;
        $this->updateChartData();
        $this->loading = false;
        
        $this->dispatch('notify', [
            'message' => 'Datos actualizados correctamente',
            'type' => 'success'
        ]);
    }
    
    private function updateChartData()
    {
        $fechaInicio = match($this->periodo) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => now()->subDays(30),
        };
        
        $ventas = \App\Models\Venta::selectRaw('DATE(created_at) as fecha, SUM(total) as total, COUNT(*) as cantidad')
            ->where('created_at', '>=', $fechaInicio)
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();
        
        $labels = [];
        $totales = [];
        $cantidades = [];
        
        // Generar fechas completas del período
        $current = $fechaInicio->copy();
        while ($current <= now()) {
            $fechaStr = $current->format('Y-m-d');
            $labels[] = $this->formatearFecha($current);
            
            $venta = $ventas->firstWhere('fecha', $fechaStr);
            $totales[] = $venta ? (float) $venta->total : 0;
            $cantidades[] = $venta ? (int) $venta->cantidad : 0;
            
            $current->addDay();
        }
        
        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Monto de Ventas (S/.)',
                    'data' => $totales,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.1,
                    'yAxisID' => 'y',
                    'fill' => true,
                ],
                [
                    'label' => 'Cantidad de Ventas',
                    'data' => $cantidades,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.1,
                    'yAxisID' => 'y1',
                    'fill' => true,
                ]
            ]
        ];
        
        $this->dispatch('updateChart', [
            'data' => $this->chartData, 
            'type' => $this->chartType
        ]);
    }
    
    private function formatearFecha(Carbon $fecha): string
    {
        return match($this->periodo) {
            '7d', '30d' => $fecha->format('d/m'),
            '90d' => $fecha->format('d/m'),
            '1y' => $fecha->format('M Y'),
            default => $fecha->format('d/m'),
        };
    }
    
    public function exportarDatos()
    {
        $data = [
            'periodo' => $this->periodo,
            'fechas' => $this->chartData['labels'],
            'ventas_monto' => $this->chartData['datasets'][0]['data'],
            'ventas_cantidad' => $this->chartData['datasets'][1]['data'],
        ];
        
        $this->dispatch('download-chart-data', $data);
    }
    
    public function getResumenAttribute()
    {
        if (empty($this->chartData)) {
            return [];
        }
        
        $montos = $this->chartData['datasets'][0]['data'];
        $cantidades = $this->chartData['datasets'][1]['data'];
        
        return [
            'total_monto' => array_sum($montos),
            'total_cantidad' => array_sum($cantidades),
            'promedio_monto' => count($montos) > 0 ? array_sum($montos) / count($montos) : 0,
            'promedio_cantidad' => count($cantidades) > 0 ? array_sum($cantidades) / count($cantidades) : 0,
            'maximo_monto' => max($montos),
            'maximo_cantidad' => max($cantidades),
        ];
    }
    
    public function render()
    {
        return view('livewire.dashboard.ventas-chart', [
            'resumen' => $this->getResumenAttribute(),
        ]);
    }
}