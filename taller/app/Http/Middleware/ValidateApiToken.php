<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\SecurityLog;

class ValidateApiToken
{
    public function handle(Request $request, Closure $next)
    {
        // Aplicar rate limiting
        $this->applyRateLimit($request);

        // Validar token si está presente
        if ($request->bearerToken()) {
            $this->validateToken($request);
        }

        $response = $next($request);

        // Log successful API access
        if (Auth::guard('sanctum')->check()) {
            $this->logApiAccess($request);
        }

        return $response;
    }

    protected function applyRateLimit(Request $request)
    {
        $key = $this->getRateLimitKey($request);
        $maxAttempts = config('security.rate_limit.attempts', 60);
        $decayMinutes = config('security.rate_limit.decay_minutes', 1);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $this->logRateLimitExceeded($request, $key);
            
            abort(429, 'Too Many Requests');
        }

        RateLimiter::hit($key, $decayMinutes * 60);
    }

    protected function getRateLimitKey(Request $request): string
    {
        $user = Auth::guard('sanctum')->user();
        
        if ($user) {
            return 'api_rate_limit:user:' . $user->id;
        }

        return 'api_rate_limit:ip:' . $request->ip();
    }

    protected function validateToken(Request $request)
    {
        $token = $request->bearerToken();
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            $this->logUnauthorizedAccess($request, 'Invalid token');
            abort(401, 'Unauthorized');
        }

        // Verificar expiración personalizada
        if ($this->isTokenExpired($accessToken)) {
            $accessToken->delete();
            $this->logUnauthorizedAccess($request, 'Token expired');
            abort(401, 'Token expired');
        }

        // Verificar capacidades del token
        if (!$this->hasRequiredAbilities($accessToken, $request)) {
            $this->logUnauthorizedAccess($request, 'Insufficient token abilities');
            abort(403, 'Insufficient permissions');
        }
    }

    protected function isTokenExpired(PersonalAccessToken $token): bool
    {
        if (!$token->expires_at) {
            return false;
        }

        return $token->expires_at->isPast();
    }

    protected function hasRequiredAbilities(PersonalAccessToken $token, Request $request): bool
    {
        $route = $request->route();
        if (!$route) return true;

        $requiredAbility = $this->getRequiredAbilityForRoute($route);
        
        if (!$requiredAbility) return true;

        return $token->can($requiredAbility);
    }

    protected function getRequiredAbilityForRoute($route): ?string
    {
        $action = $route->getActionMethod();
        $controller = class_basename($route->getController());

        // Mapear controladores y acciones a capacidades
        $abilityMap = [
            'UsuarioController' => [
                'index' => 'users:read',
                'show' => 'users:read',
                'store' => 'users:create',
                'update' => 'users:update',
                'destroy' => 'users:delete'
            ],
            'ClienteController' => [
                'index' => 'clients:read',
                'show' => 'clients:read',
                'store' => 'clients:create',
                'update' => 'clients:update',
                'destroy' => 'clients:delete'
            ],
        ];

        return $abilityMap[$controller][$action] ?? null;
    }

    protected function logApiAccess(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            
            // Registrar en user_activities para tracking
            \App\Models\UserActivity::registrarActividad(
                $user->id,
                \App\Models\UserActivity::MODULO_API,
                $request->method() . ' ' . $request->path(),
                $request->ip(),
                $request->userAgent(),
                $request->fullUrl(),
                [
                    'api_endpoint' => true,
                    'response_time' => microtime(true) - LARAVEL_START
                ]
            );

        } catch (\Exception $e) {
            \Log::error('API access logging error: ' . $e->getMessage());
        }
    }

    protected function logUnauthorizedAccess(Request $request, string $reason)
    {
        try {
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_UNAUTHORIZED,
                'descripcion' => "Acceso no autorizado a API: {$reason}",
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'severity' => SecurityLog::SEVERITY_WARNING,
                'datos_adicionales' => [
                    'endpoint' => $request->fullUrl(),
                    'method' => $request->method(),
                    'token' => substr($request->bearerToken() ?? '', 0, 10) . '...',
                    'reason' => $reason
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Security event logging error: ' . $e->getMessage());
        }
    }

    protected function logRateLimitExceeded(Request $request, string $key)
    {
        try {
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_RATE_LIMIT,
                'descripcion' => 'Límite de peticiones API excedido',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'severity' => SecurityLog::SEVERITY_WARNING,
                'datos_adicionales' => [
                    'rate_limit_key' => $key,
                    'endpoint' => $request->fullUrl(),
                    'method' => $request->method()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Rate limit logging error: ' . $e->getMessage());
        }
    }
}