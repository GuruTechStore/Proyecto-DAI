<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament-panels::pages.dashboard';

    protected static ?string $title = 'Panel de Control';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;

    public function getHeading(): string
    {
        $user = auth()->user();
        $greeting = $this->getGreeting();
        
        return "{$greeting}, {$user->name}!";
    }

    public function getSubheading(): string
    {
        return 'Bienvenido al sistema de gestión empresarial. Aquí tienes un resumen de la actividad reciente.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
        ];
    }

    public function getWidgets(): array
    {
        $user = auth()->user();
        $widgets = [];

        // Widgets disponibles según permisos
        if ($user->can('ventas.ver')) {
            $widgets[] = \App\Filament\Widgets\VentasChart::class;
        }

        if ($user->can('reparaciones.ver')) {
            $widgets[] = \App\Filament\Widgets\ReparacionesWidget::class;
        }

        if ($user->can('productos.ver')) {
            $widgets[] = \App\Filament\Widgets\InventarioWidget::class;
        }

        // Widgets de seguridad solo para roles específicos
        if ($user->hasRole(['Super Admin', 'Gerente'])) {
            $widgets[] = \App\Filament\Widgets\SecurityWidget::class;
            $widgets[] = \App\Filament\Widgets\ActivityWidget::class;
        }

        return $widgets;
    }

    public function getColumns(): int | string | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
        ];
    }

    private function getGreeting(): string
    {
        $hour = now()->hour;

        if ($hour < 12) {
            return 'Buenos días';
        } elseif ($hour < 18) {
            return 'Buenas tardes';
        } else {
            return 'Buenas noches';
        }
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Gerente', 'Supervisor']);
    }
}