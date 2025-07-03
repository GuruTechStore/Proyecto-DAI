<?php

namespace App\Filament\Widgets;

use App\Models\Venta;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class VentasChart extends ChartWidget
{
    protected static ?string $heading = 'Ventas de los Últimos 30 Días';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = '30';

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $startDate = now()->subDays($days);
        
        // Obtener datos de ventas agrupados por día
        $data = Venta::selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as cantidad')
            ->whereBetween('created_at', [$startDate, now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Crear array completo de fechas
        $labels = [];
        $ventas = [];
        $cantidades = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d/m');
            
            if ($data->has($date)) {
                $ventas[] = (float) $data[$date]->total;
                $cantidades[] = (int) $data[$date]->cantidad;
            } else {
                $ventas[] = 0;
                $cantidades[] = 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventas Diarias (S/.)',
                    'data' => $ventas,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Cantidad de Ventas',
                    'data' => $cantidades,
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => false,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Ventas (S/.)',
                    ],
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Cantidad',
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Fecha',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.datasetIndex === 0) {
                                label += "S/. " + context.parsed.y.toFixed(2);
                            } else {
                                label += context.parsed.y + " ventas";
                            }
                            return label;
                        }',
                    ],
                ],
            ],
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Últimos 7 días',
            '15' => 'Últimos 15 días',
            '30' => 'Últimos 30 días',
            '60' => 'Últimos 2 meses',
            '90' => 'Últimos 3 meses',
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->can('ventas.ver');
    }
}