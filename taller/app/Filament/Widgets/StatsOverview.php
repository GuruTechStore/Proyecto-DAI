<?php

namespace App\Filament\Widgets;

use App\Models\Venta;
use App\Models\Reparacion;
use App\Models\Producto;
use App\Models\Cliente;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        $stats = [];

        // Ventas del día - Solo si tiene permisos
        if ($user->can('ventas.ver')) {
            $ventasHoy = Venta::whereDate('created_at', today())->sum('total');
            $ventasAyer = Venta::whereDate('created_at', today()->subDay())->sum('total');
            $cambioVentas = $ventasAyer > 0 ? (($ventasHoy - $ventasAyer) / $ventasAyer) * 100 : 0;
            
            $stats[] = Stat::make('Ventas Hoy', 'S/. ' . number_format($ventasHoy, 2))
                ->description($cambioVentas >= 0 ? '+' . number_format($cambioVentas, 1) . '% vs ayer' : number_format($cambioVentas, 1) . '% vs ayer')
                ->descriptionIcon($cambioVentas >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($cambioVentas >= 0 ? 'success' : 'danger')
                ->chart([
                    $ventasAyer,
                    $ventasHoy,
                ]);
        }

        // Reparaciones pendientes - Solo si tiene permisos
        if ($user->can('reparaciones.ver')) {
            $reparacionesPendientes = Reparacion::whereIn('estado', ['recibido', 'diagnosticando', 'reparando'])->count();
            $reparacionesCompletadas = Reparacion::where('estado', 'completado')
                ->whereDate('created_at', today())
                ->count();
            
            $stats[] = Stat::make('Reparaciones Pendientes', $reparacionesPendientes)
                ->description($reparacionesCompletadas . ' completadas hoy')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color($reparacionesPendientes > 10 ? 'warning' : 'success');
        }

        // Productos con stock bajo - Solo si tiene permisos
        if ($user->can('productos.ver')) {
            $productosStockBajo = Producto::whereRaw('stock <= stock_minimo')->count();
            $totalProductos = Producto::count();
            $porcentajeStockBajo = $totalProductos > 0 ? ($productosStockBajo / $totalProductos) * 100 : 0;
            
            $stats[] = Stat::make('Productos Stock Bajo', $productosStockBajo)
                ->description(number_format($porcentajeStockBajo, 1) . '% del inventario')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($productosStockBajo > 5 ? 'danger' : ($productosStockBajo > 0 ? 'warning' : 'success'));
        }

        // Nuevos clientes del mes - Solo si tiene permisos
        if ($user->can('clientes.ver')) {
            $clientesEsteMes = Cliente::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $clientesMesAnterior = Cliente::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();
            $cambioClientes = $clientesMesAnterior > 0 ? (($clientesEsteMes - $clientesMesAnterior) / $clientesMesAnterior) * 100 : 0;
            
            $stats[] = Stat::make('Nuevos Clientes', $clientesEsteMes)
                ->description($cambioClientes >= 0 ? '+' . number_format($cambioClientes, 1) . '% vs mes anterior' : number_format($cambioClientes, 1) . '% vs mes anterior')
                ->descriptionIcon($cambioClientes >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($cambioClientes >= 0 ? 'success' : 'danger');
        }

        // Si no tiene permisos para nada, mostrar mensaje básico
        if (empty($stats)) {
            $stats[] = Stat::make('Acceso Limitado', 'Contacta al administrador')
                ->description('No tienes permisos para ver estadísticas')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('gray');
        }

        return $stats;
    }

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user->can('ventas.ver') || 
               $user->can('reparaciones.ver') || 
               $user->can('productos.ver') || 
               $user->can('clientes.ver');
    }
}