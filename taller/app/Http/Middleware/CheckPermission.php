<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $permission = null): Response
    {
        // Si no hay usuario autenticado, redirigir al login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Si es Super Admin, permitir todo
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // Si no se especifica permiso, solo verificar autenticación
        if (!$permission) {
            return $next($request);
        }

        // Verificar si el usuario tiene el permiso específico
        if (!$user->can($permission)) {
            // Si es una request AJAX, devolver JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No tienes permisos para realizar esta acción.',
                    'error' => 'Insufficient permissions'
                ], 403);
            }

            // Para requests web, redirigir con mensaje
            return redirect()->route('dashboard')
                ->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}

// ===== app/Http/Middleware/ModulePermissions.php =====

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModulePermissions
{
    /**
     * Map of routes to required permissions
     */
    private $routePermissions = [
        // Clientes
        'clientes.index' => 'clientes.ver',
        'clientes.show' => 'clientes.ver',
        'clientes.create' => 'clientes.crear',
        'clientes.store' => 'clientes.crear',
        'clientes.edit' => 'clientes.editar',
        'clientes.update' => 'clientes.editar',
        'clientes.destroy' => 'clientes.eliminar',

        // Productos
        'productos.index' => 'productos.ver',
        'productos.show' => 'productos.ver',
        'productos.create' => 'productos.crear',
        'productos.store' => 'productos.crear',
        'productos.edit' => 'productos.editar',
        'productos.update' => 'productos.editar',
        'productos.destroy' => 'productos.eliminar',

        // Categorías
        'categorias.index' => 'categorias.ver',
        'categorias.show' => 'categorias.ver',
        'categorias.create' => 'categorias.crear',
        'categorias.store' => 'categorias.crear',
        'categorias.edit' => 'categorias.editar',
        'categorias.update' => 'categorias.editar',
        'categorias.destroy' => 'categorias.eliminar',

        // Proveedores
        'proveedores.index' => 'proveedores.ver',
        'proveedores.show' => 'proveedores.ver',
        'proveedores.create' => 'proveedores.crear',
        'proveedores.store' => 'proveedores.crear',
        'proveedores.edit' => 'proveedores.editar',
        'proveedores.update' => 'proveedores.editar',
        'proveedores.destroy' => 'proveedores.eliminar',

        // Reparaciones
        'reparaciones.index' => 'reparaciones.ver',
        'reparaciones.show' => 'reparaciones.ver',
        'reparaciones.create' => 'reparaciones.crear',
        'reparaciones.store' => 'reparaciones.crear',
        'reparaciones.edit' => 'reparaciones.editar',
        'reparaciones.update' => 'reparaciones.editar',
        'reparaciones.destroy' => 'reparaciones.eliminar',

        // Ventas
        'ventas.index' => 'ventas.ver',
        'ventas.show' => 'ventas.ver',
        'ventas.create' => 'ventas.crear',
        'ventas.store' => 'ventas.crear',
        'ventas.edit' => 'ventas.editar',
        'ventas.update' => 'ventas.editar',
        'ventas.destroy' => 'ventas.eliminar',

        // Empleados
        'empleados.index' => 'empleados.ver',
        'empleados.show' => 'empleados.ver',
        'empleados.create' => 'empleados.crear',
        'empleados.store' => 'empleados.crear',
        'empleados.edit' => 'empleados.editar',
        'empleados.update' => 'empleados.editar',
        'empleados.destroy' => 'empleados.eliminar',

        // Reportes
        'reportes.index' => 'reportes.ver',
        'reportes.ventas' => 'reportes.ventas',
        'reportes.productos' => 'reportes.productos',
        'reportes.reparaciones' => 'reportes.reparaciones',
        'reportes.clientes' => 'reportes.clientes',
        'reportes.empleados' => 'reportes.empleados',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Solo aplicar a usuarios autenticados
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $routeName = $request->route()->getName();

        // Super Admin siempre puede continuar
        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        // Dashboard siempre accesible para usuarios autenticados
        if ($routeName === 'dashboard') {
            return $next($request);
        }

        // Verificar permiso específico para la ruta
        if (isset($this->routePermissions[$routeName])) {
            $requiredPermission = $this->routePermissions[$routeName];
            
            if (!$user->can($requiredPermission)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'No tienes permisos para realizar esta acción.',
                        'required_permission' => $requiredPermission
                    ], 403);
                }

                return redirect()->route('dashboard')
                    ->with('error', 'No tienes permisos para acceder a esta sección.');
            }
        }

        return $next($request);
    }
}