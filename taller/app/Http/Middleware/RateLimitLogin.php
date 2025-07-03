<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitLogin
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, 5)) {
            $seconds = $this->limiter->availableIn($key);
            
            return response()->json([
                'message' => 'Demasiados intentos de inicio de sesión. Intente nuevamente en ' . $seconds . ' segundos.',
                'retry_after' => $seconds
            ], 429);
        }

        $response = $next($request);

        // Si el login falló, incrementar contador
        if ($response->status() === 422 || $response->status() === 401) {
            $this->limiter->hit($key, 900); // 15 minutos
        } else {
            // Si fue exitoso, limpiar el contador
            $this->limiter->clear($key);
        }

        return $response;
    }

    protected function resolveRequestSignature($request)
    {
        return sha1($request->ip() . '|' . $request->userAgent());
    }
}