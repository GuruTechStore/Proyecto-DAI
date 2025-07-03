<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario está autenticado, verificar si está activo
        if (auth()->check()) {
            $user = auth()->user();
            
            // Verificar si el usuario tiene la propiedad 'activo'
            if (property_exists($user, 'activo') && !$user->activo) {
                auth()->logout();
                
                return redirect()->route('login')
                    ->with('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
            }
            
            // Verificar si el usuario está bloqueado (blocked_until)
            if (property_exists($user, 'blocked_until') && $user->blocked_until && $user->blocked_until > now()) {
                auth()->logout();
                
                return redirect()->route('login')
                    ->with('error', 'Tu cuenta está temporalmente bloqueada.');
            }
        }

        return $next($request);
    }
}