<?php

namespace App\Filament\Widgets;

use App\Models\Reparacion;
use Filament\Widgets\ChartWidget;

class ReparacionesWidget extends ChartWidget
{
    protected static ?string $heading = 'Estado de Reparaciones';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $estados = Reparacion::selectRaw('estado, COUNT(*) as count')
            ->groupBy('estado')
            ->pluck('count', 'estado')
            ->toArray();

        $labels = [];
        $data = [];
        $backgroundColor = [];

        $estadosConfig = [
            'recibido' => ['label' => 'Recibido', 'color' => '#6B7280'],
            'diagnosticando' => ['label' => 'Diagnosticando', 'color' => '#F59E0B'],
            'reparando' => ['label' => 'Reparando', 'color' => '#3B82F6'],
            'completado' => ['label' => 'Completado', 'color' => '#10B981'],
            'cancelado' => ['label' => 'Cancelado', 'color' => '#EF4444'],
        ];

        foreach ($estadosConfig as $estado => $config) {
            if (isset($estados[$estado]) && $estados[$estado] > 0) {
                $labels[] = $config['label'];
                $data[] = $estados[$estado];
                $backgroundColor[] = $config['color'];
            }
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 20,
                        'usePointStyle' => true,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ": " + context.parsed + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
            'cutout' => '50%',
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->can('reparaciones.ver');
    }
}