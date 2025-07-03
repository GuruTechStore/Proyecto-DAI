<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar el helper de configuraciones
        require_once app_path('Helpers/SettingsHelper.php');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Verificar que la tabla settings existe antes de cargar configuraciones
        if (Schema::hasTable('settings')) {
            try {
                // Compartir configuraciones públicas con todas las vistas
                $publicSettings = Setting::getPublic();
                View::share('settings', $publicSettings);
                
                // Compartir configuraciones específicas comúnmente usadas
                View::share('appName', setting('app_name', config('app.name')));
                View::share('companyName', company_name());
                View::share('companyLogo', company_logo());
                View::share('currencySymbol', currency_symbol());
                View::share('dateFormat', date_format_setting());
                
                // Configurar dinámicamente algunos valores de configuración
                $this->configureDynamicSettings();
                
            } catch (\Exception $e) {
                // Si hay error al cargar configuraciones, usar valores por defecto
                View::share('settings', collect());
                View::share('appName', config('app.name'));
                View::share('companyName', 'Mi Empresa');
                View::share('companyLogo', null);
                View::share('currencySymbol', 'S/');
                View::share('dateFormat', 'd/m/Y');
            }
        }
    }

    /**
     * Configurar valores dinámicos basados en settings
     */
    private function configureDynamicSettings(): void
    {
        // Configurar timezone dinámicamente
        $timezone = setting('sistema_zona_horaria');
        if ($timezone && $timezone !== config('app.timezone')) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }

        // Configurar locale dinámicamente
        $locale = setting('sistema_idioma');
        if ($locale && $locale !== config('app.locale')) {
            config(['app.locale' => $locale]);
            app()->setLocale($locale);
        }

        // Configurar tiempo de sesión dinámicamente
        $sessionLifetime = setting('session_lifetime');
        if ($sessionLifetime && $sessionLifetime !== config('session.lifetime')) {
            config(['session.lifetime' => $sessionLifetime]);
        }

        // Configurar paginación dinámicamente
        $itemsPerPage = setting('sistema_items_por_pagina');
        if ($itemsPerPage) {
            config(['app.pagination.per_page' => $itemsPerPage]);
        }

        // Configurar configuraciones de email dinámicamente
        $mailFromAddress = setting('mail_from_address');
        $mailFromName = setting('mail_from_name');
        
        if ($mailFromAddress) {
            config(['mail.from.address' => $mailFromAddress]);
        }
        
        if ($mailFromName) {
            config(['mail.from.name' => $mailFromName]);
        }
    }
}