<?php

namespace App\Filament\Widgets;

use App\Models\SecurityLog;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SecurityWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Intentos de login fallidos en las últimas 24 horas
        $loginsFallidos = SecurityLog::where('action', 'login_failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // Usuarios activos (último login en los últimos 7 días)
        $usuariosActivos = User::where('last_login_at', '>=', now()->subDays(7))->count();

        // Sesiones bloqueadas por rate limiting
        $sesionesBloqueadas = SecurityLog::where('action', 'login_blocked')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // IPs únicas que han accedido hoy
        $ipsUnicas = SecurityLog::where('action', 'login_success')
            ->whereDate('created_at', today())
            ->distinct('ip_address')
            ->count();

        return [
            Stat::make('Logins Fallidos (24h)', $loginsFallidos)
                ->description($loginsFallidos > 10 ? 'Revisar actividad sospechosa' : 'Actividad normal')
                ->descriptionIcon($loginsFallidos > 10 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($loginsFallidos > 10 ? 'danger' : ($loginsFallidos > 5 ? 'warning' : 'success')),

            Stat::make('Usuarios Activos (7d)', $usuariosActivos)
                ->description('Usuarios con sesión reciente')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('IPs Bloqueadas (24h)', $sesionesBloqueadas)
                ->description($sesionesBloqueadas > 0 ? 'IPs bloqueadas por rate limiting' : 'Sin bloqueos')
                ->descriptionIcon($sesionesBloqueadas > 0 ? 'heroicon-m-shield-exclamation' : 'heroicon-m-shield-check')
                ->color($sesionesBloqueadas > 0 ? 'warning' : 'success'),

            Stat::make('IPs Únicas Hoy', $ipsUnicas)
                ->description('Direcciones IP diferentes')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Gerente']) && auth()->user()->can('seguridad.ver');
    }
}