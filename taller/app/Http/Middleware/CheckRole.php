<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder.');
        }

        $user = Auth::user();

        // Verificar si el usuario está activo
        if (!$user->isActive()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Su cuenta está inactiva. Contacte al administrador.');
        }

        // Si no se especifican roles, solo verificar autenticación y estado activo
        if (empty($roles)) {
            return $next($request);
        }

        // Super Admin siempre tiene acceso
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguno de los roles requeridos
        $hasRequiredRole = false;
        
        foreach ($roles as $role) {
            // Permitir verificación por tipo de usuario o por rol de Spatie
            if ($user->tipo_usuario === $role || $user->hasRole($role)) {
                $hasRequiredRole = true;
                break;
            }
        }

        if (!$hasRequiredRole) {
            // Si es una petición AJAX, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No tiene permisos suficientes para realizar esta acción.',
                    'required_roles' => $roles
                ], 403);
            }

            // Para peticiones web, redirigir con mensaje
            return redirect()->back()->with('error', 'No tiene permisos suficientes para acceder a esta sección.');
        }

        return $next($request);
    }

    /**
     * Verificar roles múltiples con operador AND
     */
    public function handleWithAllRoles(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->isActive()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Su cuenta está inactiva.');
        }

        // Super Admin siempre tiene acceso
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verificar que tenga TODOS los roles requeridos
        foreach ($roles as $role) {
            if (!($user->tipo_usuario === $role || $user->hasRole($role))) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'No tiene todos los permisos requeridos.',
                        'required_roles' => $roles
                    ], 403);
                }

                return redirect()->back()->with('error', 'No tiene todos los permisos requeridos para esta acción.');
            }
        }

        return $next($request);
    }

    /**
     * Verificar roles con jerarquía
     */
    public function handleWithHierarchy(Request $request, Closure $next, $minimumRole): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->isActive()) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Su cuenta está inactiva.');
        }

        // Definir jerarquía de roles
        $roleHierarchy = [
            'Super Admin' => 100,
            'Admin' => 80,
            'Supervisor' => 60,
            'Jefe' => 60,
            'Gerente' => 60,
            'Empleado Senior' => 40,
            'Empleado' => 20,
            'Usuario' => 10
        ];

        $userLevel = $roleHierarchy[$user->tipo_usuario] ?? 0;
        $requiredLevel = $roleHierarchy[$minimumRole] ?? 0;

        if ($userLevel < $requiredLevel) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Nivel de autorización insuficiente.',
                    'required_level' => $minimumRole
                ], 403);
            }

            return redirect()->back()->with('error', 'Nivel de autorización insuficiente.');
        }

        return $next($request);
    }
}