<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Auth\CustomLogin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->login(CustomLogin::class)
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Slate,
                'success' => Color::Green,
                'warning' => Color::Orange,
                'danger' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets del dashboard principal
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\VentasChart::class,
                \App\Filament\Widgets\ReparacionesWidget::class,
                \App\Filament\Widgets\InventarioWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                'role:Super Admin|Gerente|Supervisor', // Solo estos roles pueden acceder
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Mi Perfil')
                    ->url('/profile')
                    ->icon('heroicon-o-user-circle'),
                'settings' => MenuItem::make()
                    ->label('Configuración')
                    ->url('/admin/settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->visible(fn () => auth()->user()->hasRole('Super Admin')),
                'security' => MenuItem::make()
                    ->label('Panel de Seguridad')
                    ->url('/admin/security-dashboard')
                    ->icon('heroicon-o-shield-check')
                    ->visible(fn () => auth()->user()->hasRole(['Super Admin', 'Gerente'])),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkMode(false)
            ->brandName('Gestión Empresarial')
            ->brandLogo(asset('images/logo.png'))
            ->favicon(asset('favicon.ico'))
            ->navigationGroups([
                NavigationGroup::make('Gestión de Datos')
                    ->label('Gestión de Datos')
                    ->icon('heroicon-o-folder')
                    ->collapsed(),
                NavigationGroup::make('Operaciones')
                    ->label('Operaciones')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->collapsed(),
                NavigationGroup::make('Reportes')
                    ->label('Reportes')
                    ->icon('heroicon-o-chart-bar')
                    ->collapsed(),
                NavigationGroup::make('Administración')
                    ->label('Administración')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
                NavigationGroup::make('Seguridad')
                    ->label('Seguridad')
                    ->icon('heroicon-o-shield-check')
                    ->collapsed(),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->topNavigation(false)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->breadcrumbs(true)
            ->spa();
    }
}