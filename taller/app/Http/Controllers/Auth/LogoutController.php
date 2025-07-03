<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SecurityLog;
use App\Models\Usuario; 

class LogoutController extends Controller
{
    /**
     * Cerrar sesión del usuario
     */
    public function logout(Request $request)
    {
        // Registrar la actividad antes de cerrar sesión
        $user = Auth::user();
        
        if ($user) {
            // Actualizar último acceso antes del logout si el método existe
            if (method_exists($user, 'updateLastAccess')) {
                $user->updateLastAccess();
            }
            
            // Log de logout
            $this->logLogout($request, $user, 'manual_logout');
        }

        // Realizar logout
        Auth::logout();
        
        // Invalidar la sesión
        $request->session()->invalidate();
        
        // Regenerar token CSRF
        $request->session()->regenerateToken();

        // Si es petición AJAX o API
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sesión cerrada exitosamente',
                'logout' => true
            ]);
        }

        // Redirigir a la página de login con mensaje
        return redirect()->route('login')
            ->with('success', 'Sesión cerrada exitosamente');
    }

    /**
     * Logout de todos los dispositivos (API)
     */
    public function logoutAllDevices(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            // Eliminar todos los tokens de API del usuario si usa Sanctum
            if (method_exists($user, 'tokens')) {
                $tokenCount = $user->tokens()->count();
                $user->tokens()->delete();
                
                // Log de actividad
                $this->logLogout($request, $user, 'logout_all_devices', [
                    'tokens_revoked' => $tokenCount
                ]);
            } else {
                $this->logLogout($request, $user, 'logout_all_devices');
            }
        }

        // Realizar logout de la sesión actual
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Sesión cerrada en todos los dispositivos',
                'logout' => true
            ]);
        }

        return redirect()->route('login')
            ->with('success', 'Sesión cerrada en todos los dispositivos');
    }

    /**
     * Logout específico de un token API
     */
    public function logoutToken(Request $request)
    {
        $request->validate([
            'token_id' => 'required|integer|exists:personal_access_tokens,id'
        ]);

        $user = Auth::user();
        $tokenId = $request->input('token_id');

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // Verificar que el token pertenece al usuario actual
        if (method_exists($user, 'tokens')) {
            $token = $user->tokens()->where('id', $tokenId)->first();

            if (!$token) {
                return response()->json([
                    'message' => 'Token no encontrado o no autorizado'
                ], 404);
            }

            // Eliminar el token específico
            $tokenName = $token->name;
            $token->delete();

            $this->logLogout($request, $user, 'logout_specific_token', [
                'token_id' => $tokenId,
                'token_name' => $tokenName
            ]);

            return response()->json([
                'message' => 'Token eliminado exitosamente'
            ]);
        }

        return response()->json([
            'message' => 'Funcionalidad no disponible'
        ], 400);
    }

    /**
     * Forzar logout de usuario (solo para administradores)
     */
    public function forceLogout(Request $request, $userId)
    {
        $currentUser = Auth::user();
        
        // Verificar permisos de administrador
        if (!$currentUser || !$this->canManageUsers($currentUser)) {
            return response()->json([
                'message' => 'No tiene permisos para realizar esta acción'
            ], 403);
        }

        $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        $targetUser = Usuario::findOrFail($userId); // CAMBIADO: Usar modelo User
        
        // No permitir que se fuerce logout a sí mismo
        if ($targetUser->id === Auth::id()) {
            return response()->json([
                'message' => 'No puede forzar su propio logout'
            ], 422);
        }

        $tokenCount = 0;
        // Eliminar todos los tokens del usuario objetivo
        if (method_exists($targetUser, 'tokens')) {
            $tokenCount = $targetUser->tokens()->count();
            $targetUser->tokens()->delete();
        }

        // Log de actividad administrativa
        SecurityLog::create([
            'tipo' => 'forced_logout',
            'descripcion' => 'Logout forzado por administrador',
            'usuario_id' => $targetUser->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'severity' => 'warning',
            'datos_adicionales' => [
                'admin_user_id' => $currentUser->id,
                'admin_username' => $this->getUserIdentifier($currentUser),
                'target_user_id' => $targetUser->id,
                'target_username' => $this->getUserIdentifier($targetUser),
                'reason' => $request->input('reason'),
                'tokens_revoked' => $tokenCount
            ]
        ]);

        return response()->json([
            'message' => "Sesión de {$this->getUserIdentifier($targetUser)} cerrada forzosamente",
            'target_user' => $this->getUserIdentifier($targetUser)
        ]);
    }

    /**
     * Obtener lista de sesiones activas del usuario
     */
    public function getActiveSessions(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no autenticado'
            ], 401);
        }
        
        $sessions = [];
        
        // Obtener tokens activos si usa Sanctum
        if (method_exists($user, 'tokens')) {
            $tokens = $user->tokens()
                ->select(['id', 'name', 'created_at', 'last_used_at'])
                ->get()
                ->map(function ($token) use ($user) {
                    $isCurrent = false;
                    
                    // Verificar si es el token actual
                    if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                        $isCurrent = $token->id === $user->currentAccessToken()->id;
                    }
                    
                    return [
                        'id' => $token->id,
                        'device_name' => $token->name,
                        'created_at' => $token->created_at,
                        'last_used_at' => $token->last_used_at,
                        'is_current' => $isCurrent,
                        'type' => 'api_token'
                    ];
                });
            
            $sessions = $tokens->toArray();
        }
        
        // Agregar sesión web actual si existe
        if ($request->session()->has('_token')) {
            $sessions[] = [
                'id' => 'web_session',
                'device_name' => 'Sesión Web',
                'created_at' => now(),
                'last_used_at' => now(),
                'is_current' => true,
                'type' => 'web_session'
            ];
        }

        return response()->json([
            'sessions' => $sessions,
            'total' => count($sessions)
        ]);
    }

    /**
     * Verificar si la sesión sigue siendo válida
     */
    public function checkSession(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'valid' => false,
                'message' => 'Sesión no válida'
            ], 401);
        }

        $user = Auth::user();

        // Verificar si el usuario está activo
        if (!$this->isUserActive($user)) {
            Auth::logout();
            $request->session()->invalidate();
            
            return response()->json([
                'valid' => false,
                'message' => 'Usuario inactivo'
            ], 401);
        }

        return response()->json([
            'valid' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'last_access' => $this->getUserLastAccess($user)
            ]
        ]);
    }

    /**
     * Verificar si el usuario puede gestionar otros usuarios
     */
    private function canManageUsers($user): bool
    {
        // Si usa Spatie Permission
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole(['Super Admin', 'Admin', 'Gerente']);
        }
        
        // Si usa campo 'rol' o 'role'
        $userRole = $user->role ?? $user->rol ?? '';
        if ($userRole) {
            return in_array(strtolower($userRole), ['super admin', 'admin', 'gerente']);
        }
        
        // Si usa permisos específicos
        if (method_exists($user, 'can')) {
            return $user->can('manage_users') || $user->can('usuarios.gestionar');
        }
        
        return false;
    }

    /**
     * Verificar si el usuario está activo
     */
    private function isUserActive($user): bool
    {
        // Verificar campos comunes para estado activo
        if (property_exists($user, 'active')) {
            return $user->active && !($user->blocked ?? false);
        }
        
        if (property_exists($user, 'activo')) {
            return $user->activo && !($user->bloqueado ?? false);
        }
        
        // Si no hay campos específicos, verificar si el usuario existe y tiene email verificado
        return $user->exists && ($user->email_verified_at !== null || !config('auth.verification', false));
    }

    /**
     * Obtener identificador del usuario (nombre o email)
     */
    private function getUserIdentifier($user): string
    {
        return $user->name ?? $user->username ?? $user->email;
    }

    /**
     * Obtener último acceso del usuario
     */
    private function getUserLastAccess($user)
    {
        return $user->last_login_at ?? 
               $user->ultimo_acceso ?? 
               $user->updated_at;
    }

    /**
     * Log de logout
     */
    private function logLogout(Request $request, $user, string $type, array $additional = []): void
    {
        try {
            SecurityLog::create([
                'tipo' => $type,
                'descripcion' => $this->getLogoutDescription($type),
                'usuario_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'severity' => 'info',
                'datos_adicionales' => array_merge([
                    'username' => $this->getUserIdentifier($user),
                    'timestamp' => now()
                ], $additional)
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log logout: ' . $e->getMessage());
        }
    }

    /**
     * Obtener descripción del tipo de logout
     */
    private function getLogoutDescription(string $type): string
    {
        return match($type) {
            'manual_logout' => 'Usuario cerró sesión manualmente',
            'logout_all_devices' => 'Usuario cerró sesión en todos los dispositivos',
            'logout_specific_token' => 'Usuario eliminó token específico',
            'forced_logout' => 'Logout forzado por administrador',
            'session_expired' => 'Sesión expirada',
            'inactive_logout' => 'Logout por inactividad',
            default => 'Logout del usuario'
        };
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['checkSession']);
    }
}