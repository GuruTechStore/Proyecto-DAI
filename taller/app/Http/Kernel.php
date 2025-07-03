<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        
        // ===== MIDDLEWARE PERSONALIZADOS DEL SISTEMA =====
        
        // Middleware de seguridad y actividad
        'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        'track.activity' => \App\Http\Middleware\TrackUserActivity::class,
        'check.blocked' => \App\Http\Middleware\CheckIfUserIsBlocked::class,
        'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
        'two.factor' => \App\Http\Middleware\TwoFactorAuthentication::class,
        
        // Middleware de roles y permisos (Spatie Laravel Permission)
        'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
        
        // Middleware de seguridad adicional
        'security.log' => \App\Http\Middleware\SecurityLogMiddleware::class,
        'rate.limit.strict' => \App\Http\Middleware\StrictRateLimit::class,
        'ip.whitelist' => \App\Http\Middleware\IpWhitelist::class,
        'session.security' => \App\Http\Middleware\SessionSecurity::class,
        
        // Middleware de auditoría
        'audit.log' => \App\Http\Middleware\AuditLogMiddleware::class,
        'data.protection' => \App\Http\Middleware\DataProtection::class,
        
        // Middleware de validación de módulos
        'module.access' => \App\Http\Middleware\ModuleAccess::class,
        'feature.flag' => \App\Http\Middleware\FeatureFlag::class,
        
        // Middleware de API
        'api.auth' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        'api.throttle' => \App\Http\Middleware\ApiThrottle::class,
        'api.version' => \App\Http\Middleware\ApiVersion::class,
        
        // Middleware de localización
        'locale' => \App\Http\Middleware\SetLocale::class,
        'timezone' => \App\Http\Middleware\SetTimezone::class,
        
        // Middleware de contenido
        'cors.custom' => \App\Http\Middleware\CustomCors::class,
        'sanitize.input' => \App\Http\Middleware\SanitizeInput::class,
        'validate.json' => \App\Http\Middleware\ValidateJsonRequest::class,
        
        // Middleware de performance
        'cache.response' => \App\Http\Middleware\CacheResponse::class,
        'compress.response' => \App\Http\Middleware\CompressResponse::class,
        
        // Middleware de desarrollo (solo en modo local)
        'debug.bar' => \App\Http\Middleware\DebugBar::class,
        'query.log' => \App\Http\Middleware\QueryLog::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces non-global middleware to always be in the given order.
     *
     * @var string[]
     */
    protected $middlewarePriority = [
        \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
        
        // Prioridad de middlewares personalizados
        \App\Http\Middleware\SecurityLogMiddleware::class,
        \App\Http\Middleware\EnsureUserIsActive::class,
        \App\Http\Middleware\CheckIfUserIsBlocked::class,
        \App\Http\Middleware\TwoFactorAuthentication::class,
        \App\Http\Middleware\ForcePasswordChange::class,
        \App\Http\Middleware\TrackUserActivity::class,
        \Spatie\Permission\Middlewares\RoleMiddleware::class,
        \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
    ];

    /**
     * Bootstrap any application services for middleware.
     */
    public function bootstrap(): void
    {
        parent::bootstrap();
        
        // Configuraciones adicionales para middleware personalizado
        $this->configureSecurityMiddleware();
        $this->configurePerformanceMiddleware();
    }

    /**
     * Configurar middleware de seguridad.
     */
    protected function configureSecurityMiddleware(): void
    {
        // Configurar rate limiting dinámico basado en roles
        if (config('app.env') === 'production') {
            $this->app['router']->aliasMiddleware('throttle.admin', function ($request, $next) {
                $user = $request->user();
                $limit = $user && $user->hasRole('Super Admin') ? 1000 : 100;
                return app(\Illuminate\Routing\Middleware\ThrottleRequests::class)
                    ->handle($request, $next, $limit, 1);
            });
        }
    }

    /**
     * Configurar middleware de performance.
     */
    protected function configurePerformanceMiddleware(): void
    {
        // Configuraciones específicas de performance
        if (config('app.env') !== 'local') {
            // En producción, habilitar compresión y cache
            $this->middlewareGroups['web'][] = 'compress.response';
            $this->middlewareGroups['api'][] = 'cache.response';
        }
    }
}