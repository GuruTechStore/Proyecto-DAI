<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Exception;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si no hay usuario autenticado, continuar
        if (!Auth::check()) {
            return $next($request);
        }

        try {
            $user = Auth::user();

            // Verificar si el usuario existe y está activo
            if (!$user || !$this->isUserActive($user)) {
                return $this->handleInactiveUser($request);
            }

            // Actualizar último acceso de forma eficiente
            $this->updateLastAccessIfNeeded($user);

            return $next($request);

        } catch (Exception $e) {
            // Log del error pero permitir continuar para evitar bloqueos
            \Log::error('Error in EnsureUserIsActive middleware: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip()
            ]);

            // Si hay error, permitir continuar pero limpiar auth por seguridad
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de autenticación. Por favor, recarga la página.',
                    'code' => 'AUTH_ERROR'
                ], 500);
            }

            return $next($request);
        }
    }

    /**
     * Verificar si el usuario está activo de forma segura
     */
    private function isUserActive($user): bool
    {
        try {
            // Verificar si tiene el método isActive
            if (method_exists($user, 'isActive')) {
                return $user->isActive();
            }

            // Verificar campo activo directamente
            return isset($user->activo) ? (bool) $user->activo : true;

        } catch (Exception $e) {
            \Log::warning('Error checking user active status: ' . $e->getMessage());
            // En caso de error, asumir que está activo para no bloquear
            return true;
        }
    }

    /**
     * Manejar usuario inactivo
     */
    private function handleInactiveUser(Request $request): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Su cuenta está inactiva. Contacte al administrador.',
                'code' => 'USER_INACTIVE',
                'redirect' => route('login')
            ], 403);
        }

        return redirect()->route('login')
            ->with('error', 'Su cuenta está inactiva. Contacte al administrador del sistema.');
    }

    /**
     * Actualizar último acceso de forma eficiente
     */
    private function updateLastAccessIfNeeded($user): void
    {
        try {
            // Usar cache para evitar actualizaciones frecuentes
            $cacheKey = "last_access_updated_{$user->id}";
            
            if (!Cache::has($cacheKey)) {
                // Solo actualizar si han pasado más de 5 minutos
                $lastAccess = $user->ultimo_acceso;
                
                if (!$lastAccess || $lastAccess->diffInMinutes(now()) >= 5) {
                    $user->update(['ultimo_acceso' => now()]);
                }
                
                // Cache por 5 minutos para evitar actualizaciones frecuentes
                Cache::put($cacheKey, true, 300);
            }

        } catch (Exception $e) {
            // Log pero no bloquear la aplicación
            \Log::warning('Error updating last access: ' . $e->getMessage());
        }
    }
}