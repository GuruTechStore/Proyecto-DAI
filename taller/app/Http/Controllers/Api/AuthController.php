<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Resources\Auth\UserProfileResource;
use App\Models\Usuario;
use App\Models\SecurityLog;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    /**
     * Login con Sanctum
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
            'two_factor_code' => 'nullable|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Rate limiting por IP
        $key = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            SecurityLog::create([
                'evento' => 'login_rate_limited',
                'usuario_id' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'email' => $request->email,
                    'attempts' => 5,
                    'retry_after' => $seconds
                ]),
                'nivel_riesgo' => 'high'
            ]);

            return response()->json([
                'success' => false,
                'message' => "Demasiados intentos. Intenta nuevamente en {$seconds} segundos."
            ], 429);
        }

        $user = Usuario::where('email', $request->email)->first();

        // Verificar si el usuario existe
        if (!$user) {
            RateLimiter::hit($key, 300); // 5 minutos
            
            SecurityLog::create([
                'evento' => 'login_failed',
                'usuario_id' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'email' => $request->email,
                    'reason' => 'user_not_found'
                ]),
                'nivel_riesgo' => 'medium'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Verificar si está bloqueado
        if ($user->blocked_until && $user->blocked_until > now()) {
            SecurityLog::create([
                'evento' => 'login_blocked_attempt',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'blocked_until' => $user->blocked_until,
                    'reason' => $user->blocked_reason
                ]),
                'nivel_riesgo' => 'high'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cuenta bloqueada hasta: ' . $user->blocked_until->format('Y-m-d H:i:s'),
                'data' => [
                    'blocked_until' => $user->blocked_until,
                    'reason' => $user->blocked_reason
                ]
            ], 403);
        }

        // Verificar si está activo
        if (!$user->activo) {
            SecurityLog::create([
                'evento' => 'login_inactive_attempt',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode(['account_status' => 'inactive']),
                'nivel_riesgo' => 'medium'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cuenta desactivada. Contacta al administrador.'
            ], 403);
        }

        // Verificar contraseña
        if (!Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 300);
            
            SecurityLog::create([
                'evento' => 'login_failed',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode(['reason' => 'invalid_password']),
                'nivel_riesgo' => 'medium'
            ]);

            // Incrementar intentos fallidos
            $this->incrementFailedAttempts($user);

            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Verificar 2FA si está habilitado
        if ($user->two_factor_secret) {
            if (!$request->two_factor_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código de autenticación de dos factores requerido',
                    'requires_2fa' => true,
                    'user_id' => $user->id
                ], 200);
            }

            if (!$this->verify2FACode($user, $request->two_factor_code)) {
                SecurityLog::create([
                    'evento' => '2fa_verification_failed',
                    'usuario_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'detalles' => json_encode(['reason' => 'invalid_2fa_code']),
                    'nivel_riesgo' => 'high'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Código de autenticación inválido'
                ], 401);
            }
        }

        // Verificar si requiere cambio de contraseña
        if ($user->force_password_change || $this->passwordRequiresChange($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Debes cambiar tu contraseña antes de continuar',
                'requires_password_change' => true,
                'user_id' => $user->id
            ], 200);
        }

        // Login exitoso
        RateLimiter::clear($key);
        
        // Crear token con abilities basados en roles
        $abilities = $this->getUserAbilities($user);
        $tokenName = $request->remember ? 'auth-token-remember' : 'auth-token';
        $token = $user->createToken($tokenName, $abilities);

        // Configurar expiración del token
        $expiresAt = $request->remember 
            ? now()->addDays(30) 
            : now()->addHours(config('sanctum.expiration', 8));
        
        $token->accessToken->update(['expires_at' => $expiresAt]);

        // Resetear intentos fallidos
        $user->update([
            'failed_login_attempts' => 0,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);

        // Log login exitoso
        SecurityLog::create([
            'evento' => 'login_success',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'remember' => $request->remember ?? false,
                'token_expires_at' => $expiresAt,
                'user_agent_parsed' => $this->parseUserAgent($request->userAgent())
            ]),
            'nivel_riesgo' => 'low'
        ]);

        // Log actividad
        UserActivity::create([
            'usuario_id' => $user->id,
            'accion' => 'login',
            'modulo' => 'auth',
            'detalles' => json_encode([
                'login_method' => $user->two_factor_secret ? '2fa' : 'password',
                'remember' => $request->remember ?? false
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => new LoginResource((object)[
                'user' => $user->load('roles', 'permissions'),
                'token' => $token->plainTextToken,
                'expires_at' => $expiresAt
            ])
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = auth()->user();
        $token = $request->user()->currentAccessToken();

        // Log logout
        SecurityLog::create([
            'evento' => 'logout',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'token_name' => $token->name,
                'session_duration' => now()->diffInMinutes($token->created_at)
            ]),
            'nivel_riesgo' => 'low'
        ]);

        UserActivity::create([
            'usuario_id' => $user->id,
            'accion' => 'logout',
            'modulo' => 'auth',
            'detalles' => json_encode(['manual_logout' => true]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Revocar token actual
        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = auth()->user();
        $currentToken = $request->user()->currentAccessToken();

        // Verificar si el token está cerca de expirar
        if ($currentToken->expires_at && $currentToken->expires_at > now()->addHour()) {
            return response()->json([
                'success' => false,
                'message' => 'El token aún es válido por más de una hora'
            ], 400);
        }

        // Crear nuevo token
        $abilities = $this->getUserAbilities($user);
        $newToken = $user->createToken('refreshed-token', $abilities);
        $expiresAt = now()->addHours(config('sanctum.expiration', 8));
        $newToken->accessToken->update(['expires_at' => $expiresAt]);

        // Revocar token anterior
        $currentToken->delete();

        // Log refresh
        SecurityLog::create([
            'evento' => 'token_refreshed',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'old_token_id' => $currentToken->id,
                'new_expires_at' => $expiresAt
            ]),
            'nivel_riesgo' => 'low'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Token renovado exitosamente',
            'data' => [
                'token' => $newToken->plainTextToken,
                'expires_at' => $expiresAt,
                'user' => new UserProfileResource($user)
            ]
        ]);
    }

    /**
     * Obtener información del usuario actual
     */
    public function me(Request $request)
    {
        $user = auth()->user();
        $user->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'data' => new UserProfileResource($user)
        ]);
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'confirmed', PasswordRule::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            SecurityLog::create([
                'evento' => 'password_change_failed',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode(['reason' => 'invalid_current_password']),
                'nivel_riesgo' => 'medium'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
            'force_password_change' => false
        ]);

        // Revocar otros tokens (opcional)
        if ($request->revoke_other_sessions) {
            $currentTokenId = $request->user()->currentAccessToken()->id;
            $user->tokens()->where('id', '!=', $currentTokenId)->delete();
        }

        SecurityLog::create([
            'evento' => 'password_changed',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'changed_by' => 'self',
                'revoked_other_sessions' => $request->revoke_other_sessions ?? false
            ]),
            'nivel_riesgo' => 'low'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }

    /**
     * Solicitar reset de contraseña
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:usuarios,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email no válido o no registrado',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Usuario::where('email', $request->email)->first();

        SecurityLog::create([
            'evento' => 'password_reset_requested',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode(['email' => $request->email]),
            'nivel_riesgo' => 'low'
        ]);

        // Aquí implementarías el envío del email
        // Por ahora retornamos éxito

        return response()->json([
            'success' => true,
            'message' => 'Si el email existe, recibirás un enlace de recuperación'
        ]);
    }

    /**
     * Reset de contraseña
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:usuarios,email',
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Aquí validarías el token de reset
        // Por simplicidad, asumimos que es válido

        $user = Usuario::where('email', $request->email)->first();
        
        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'force_password_change' => false
        ]);

        // Revocar todos los tokens
        $user->tokens()->delete();

        SecurityLog::create([
            'evento' => 'password_reset_completed',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode(['reset_method' => 'email_token']),
            'nivel_riesgo' => 'medium'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña restablecida correctamente'
        ]);
    }

    /**
     * Revocar todos los tokens del usuario
     */
    public function revokeAllTokens(Request $request)
    {
        $user = auth()->user();
        $currentTokenId = $request->user()->currentAccessToken()->id;
        
        // Revocar todos excepto el actual
        $revokedCount = $user->tokens()->where('id', '!=', $currentTokenId)->count();
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        SecurityLog::create([
            'evento' => 'all_tokens_revoked',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'revoked_count' => $revokedCount,
                'kept_current' => true
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$revokedCount} sesiones revocadas correctamente"
        ]);
    }

    /**
     * Verificar estado de la sesión
     */
    public function sessionStatus(Request $request)
    {
        $user = auth()->user();
        $token = $request->user()->currentAccessToken();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserProfileResource($user),
                'session' => [
                    'token_name' => $token->name,
                    'created_at' => $token->created_at,
                    'expires_at' => $token->expires_at,
                    'last_used_at' => $token->last_used_at,
                    'abilities' => $token->abilities,
                    'active_sessions' => $user->tokens()->count()
                ],
                'security' => [
                    'requires_2fa' => !is_null($user->two_factor_secret),
                    'email_verified' => !is_null($user->email_verified_at),
                    'password_expires' => $this->passwordRequiresChange($user),
                    'account_locked' => $user->blocked_until && $user->blocked_until > now()
                ]
            ]
        ]);
    }

    // Métodos auxiliares privados

    /**
     * Obtener habilidades del usuario para el token
     */
    private function getUserAbilities($user)
    {
        $abilities = ['*']; // Por defecto todas las habilidades
        
        // Puedes personalizar las habilidades basadas en roles
        $userRoles = $user->roles->pluck('name')->toArray();
        
        if (in_array('Super Admin', $userRoles)) {
            $abilities = ['*'];
        } elseif (in_array('Gerente', $userRoles)) {
            $abilities = ['read', 'write', 'manage-users', 'view-reports'];
        } elseif (in_array('Supervisor', $userRoles)) {
            $abilities = ['read', 'write', 'view-reports'];
        } else {
            $abilities = ['read'];
        }

        return $abilities;
    }

    /**
     * Incrementar intentos fallidos de login
     */
    private function incrementFailedAttempts($user)
    {
        $attempts = $user->failed_login_attempts + 1;
        $user->update(['failed_login_attempts' => $attempts]);

        // Bloquear si supera 5 intentos
        if ($attempts >= 5) {
            $user->update([
                'blocked_until' => now()->addMinutes(30),
                'blocked_reason' => 'Demasiados intentos fallidos de login'
            ]);

            SecurityLog::create([
                'evento' => 'account_auto_locked',
                'usuario_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'detalles' => json_encode([
                    'failed_attempts' => $attempts,
                    'locked_until' => now()->addMinutes(30)
                ]),
                'nivel_riesgo' => 'high'
            ]);
        }
    }

    /**
     * Verificar código 2FA
     */
    private function verify2FACode($user, $code)
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        // Aquí implementarías la verificación real con Google2FA
        // Por simplicidad, aceptamos cualquier código de 6 dígitos
        return strlen($code) === 6 && is_numeric($code);
    }

    /**
     * Verificar si la contraseña requiere cambio
     */
    private function passwordRequiresChange($user)
    {
        if ($user->force_password_change) {
            return true;
        }

        if (!$user->password_changed_at) {
            return true;
        }

        // Contraseñas expiran después de 90 días
        return $user->password_changed_at->diffInDays(now()) > 90;
    }

    /**
     * Parsear User Agent
     */
    private function parseUserAgent($userAgent)
    {
        // Implementación básica de parsing de User Agent
        $parsed = [
            'platform' => 'Unknown',
            'browser' => 'Unknown',
            'version' => 'Unknown'
        ];

        if (str_contains($userAgent, 'Windows')) {
            $parsed['platform'] = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $parsed['platform'] = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $parsed['platform'] = 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            $parsed['platform'] = 'Android';
        } elseif (str_contains($userAgent, 'iOS')) {
            $parsed['platform'] = 'iOS';
        }

        if (str_contains($userAgent, 'Chrome')) {
            $parsed['browser'] = 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            $parsed['browser'] = 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            $parsed['browser'] = 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            $parsed['browser'] = 'Edge';
        }

        return $parsed;
    }

    /**
     * Obtener límite de rate por rol
     */
    private function getRoleRateLimit()
    {
        $user = auth()->user();
        
        if (!$user) return '50,1'; // Default para no autenticados
        
        if ($user->hasRole('Super Admin')) return 'none';
        if ($user->hasRole(['Gerente', 'Supervisor'])) return '200,1';
        if ($user->hasRole(['Técnico Senior', 'Técnico', 'Vendedor Senior', 'Vendedor'])) return '100,1';
        
        return '50,1'; // Empleado
    }
}