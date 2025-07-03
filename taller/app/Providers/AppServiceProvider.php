<?php

// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\NotificationService;
use App\Helpers\SettingsHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar el servicio de notificaciones
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        // Registrar el helper de configuraciones
        $this->app->singleton('settings', function ($app) {
            return new SettingsHelper();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Compartir configuraciones públicas con todas las vistas
        View::composer('*', function ($view) {
            if (app()->runningInConsole()) {
                return;
            }

            try {
                $publicSettings = \App\Models\Setting::getPublic();
                $view->with('publicSettings', $publicSettings);
            } catch (\Exception $e) {
                // Si hay error (por ejemplo, tablas no creadas aún), usar valores por defecto
                $view->with('publicSettings', []);
            }
        });

        // Compartir información de empresa con el layout
        View::composer(['layouts.app', 'layouts.guest'], function ($view) {
            if (app()->runningInConsole()) {
                return;
            }

            try {
                $empresaInfo = SettingsHelper::getEmpresaInfo();
                $sistemaConfig = SettingsHelper::getSistemaConfig();
                
                $view->with('empresaInfo', $empresaInfo)
                     ->with('sistemaConfig', $sistemaConfig);
            } catch (\Exception $e) {
                $view->with('empresaInfo', [])
                     ->with('sistemaConfig', []);
            }
        });

        // Compartir notificaciones del usuario autenticado
        View::composer('*', function ($view) {
            if (auth()->check() && !app()->runningInConsole()) {
                try {
                    $notificationService = app(NotificationService::class);
                    $resumenNotificaciones = $notificationService->getResumenPorUsuario(auth()->id());
                    $view->with('userNotifications', $resumenNotificaciones);
                } catch (\Exception $e) {
                    $view->with('userNotifications', [
                        'total_no_leidas' => 0,
                        'criticas' => 0,
                        'recientes' => collect(),
                        'por_tipo' => []
                    ]);
                }
            }
        });

        // Macros para Blade (helpers personalizados)
        \Blade::directive('currency', function ($expression) {
            return "<?php echo App\Helpers\SettingsHelper::formatCurrency($expression); ?>";
        });

        \Blade::directive('dateFormat', function ($expression) {
            return "<?php echo App\Helpers\SettingsHelper::formatDate($expression); ?>";
        });

        \Blade::directive('setting', function ($expression) {
            return "<?php echo App\Helpers\SettingsHelper::get($expression); ?>";
        });

        // Inicializar configuraciones por defecto en el primer boot
        $this->initializeDefaultSettings();

        // Configurar políticas de cache
        $this->configureCachePolicies();
    }

    /**
     * Inicializar configuraciones por defecto
     */
    private function initializeDefaultSettings()
    {
        if (app()->runningInConsole()) {
            return;
        }

        try {
            // Solo inicializar si la tabla existe y está vacía
            if (\Schema::hasTable('settings') && \App\Models\Setting::count() === 0) {
                \App\Models\Setting::initializeDefaults();
            }
        } catch (\Exception $e) {
            // Ignorar errores durante la inicialización (migraciones, etc.)
        }
    }

    /**
     * Configurar políticas de cache para el sistema
     */
    private function configureCachePolicies()
    {
        // Configurar tiempo de vida del cache basado en configuraciones
        $this->app->extend('cache', function ($cache, $app) {
            // Obtener duración del cache desde configuraciones
            try {
                $defaultTtl = \App\Models\Setting::get('sistema_cache_duracion_minutos', 60);
                config(['cache.default_ttl' => $defaultTtl * 60]); // Convertir a segundos
            } catch (\Exception $e) {
                // Usar valor por defecto si hay error
                config(['cache.default_ttl' => 3600]);
            }
            
            return $cache;
        });
    }
}