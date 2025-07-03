<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Si la request espera JSON (API), no redirigir
        if ($request->expectsJson()) {
            return null;
        }

        // Redirigir a la página de login
        return route('login');
    }

    /**
     * Handle unauthenticated user
     */
    protected function unauthenticated($request, array $guards)
    {
        // Si es una request de API
        if ($request->expectsJson()) {
            abort(response()->json([
                'message' => 'No autenticado.',
                'error' => 'Unauthenticated'
            ], 401));
        }

        // Para requests web, redirigir al login
        throw new \Illuminate\Auth\AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        $this->unauthenticated($request, $guards);
    }
}