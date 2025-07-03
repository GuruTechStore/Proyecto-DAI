<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Usuario;
use App\Models\SecurityLog;

class LoginController extends Controller
{
    /**
     * Mostrar el formulario de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesar el login - Usando LoginRequest
     */
    public function login(LoginRequest $request)
    {
        try {
            // El LoginRequest ya maneja toda la validación y autenticación
            $request->authenticate();
            
            return $this->sendLoginResponse($request);
            
        } catch (ValidationException $e) {
            return $this->sendFailedLoginResponse($request, $e);
        }
    }

    /**
     * Respuesta exitosa de login
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        
        $user = Auth::user();
        
        // Log successful login
        $this->logSuccessfulLogin($request, $user);
        
        // Limpiar intentos fallidos
        $this->clearLoginAttempts($request);
        
        // Actualizar último acceso
        $this->updateUserLastAccess($user, $request);
        
        // Si es petición AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login exitoso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->display_name ?? $user->username,
                    'email' => $user->email,
                    'role' => $this->getUserRole($user)
                ],
                'redirect' => $this->redirectPath(),
                'requires_2fa' => $this->userHas2FA($user),
                'force_password_change' => session()->has('force_password_change')
            ]);
        }

        // Verificar si debe cambiar contraseña
       if ($user->bloqueado_hasta && $user->bloqueado_hasta->isFuture()) {
            Auth::logout();
            $request->session()->invalidate();
            throw ValidationException::withMessages([
                'email' => 'Cuenta bloqueada hasta ' . $user->bloqueado_hasta->format('d/m/Y H:i')
            ]);
        if ($user->force_password_change || session()->has('force_password_change')) {

        }}


        // Verificar si requiere 2FA
        if ($this->userHas2FA($user)) {
            session(['2fa_required' => true, '2fa_user_id' => $user->id]);
            return redirect()->route('two-factor.challenge');
        }

        // Redirigir a la página solicitada o dashboard
        return redirect()->intended($this->redirectPath())
            ->with('success', '¡Bienvenido de vuelta, ' . ($user->empleado ? $user->empleado->nombres : $user->username) . '!');
    }

    /**
     * Respuesta de login fallido
     */
    protected function sendFailedLoginResponse(Request $request, ?ValidationException $exception = null)
    {
        $message = $exception ? 
                  $exception->getMessage() : 
                  'Las credenciales proporcionadas no coinciden con nuestros registros.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => $exception ? $exception->errors() : ['email' => [$message]]
            ], 422);
        }

        // Para requests web, el LoginRequest ya maneja el ValidationException
        throw $exception ?: ValidationException::withMessages([
            'email' => [$message],
        ]);
    }

    /**
     * Login legacy (mantener compatibilidad)
     */
    public function legacyLogin(Request $request)
    {
        // Validar datos de entrada
        $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ], [
            'login.required' => 'El campo usuario/email es obligatorio.',
            'login.max' => 'El usuario/email no puede tener más de 255 caracteres.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        // Verificar rate limiting
        $this->checkTooManyFailedAttempts($request);

        // Intentar autenticar
        $credentials = $this->getCredentials($request);
        
        if ($this->attemptLogin($credentials, $request)) {
            return $this->sendLoginResponse($request);
        }

        // Login fallido - incrementar intentos
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Obtener credenciales del request
     */
    protected function getCredentials(Request $request): array
    {
        $login = $request->input('login');
        
        // Determinar si es email o username
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        return [
            $field => $login,
            'password' => $request->input('password'),
        ];
    }

    /**
     * Ruta de redirección después del login
     */
    protected function redirectPath(): string
    {
        // Verificar si hay una URL intended
        if (session('url.intended')) {
            return session('url.intended');
        }

        $user = Auth::user();
        
        if (!$user) {
            return route('dashboard');
        }
        
        // Si el usuario tiene roles con Spatie Permission
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('Super Admin')) {
                return route('admin.users.index');
            }
            
            if ($user->hasRole(['Gerente', 'Supervisor'])) {
                return route('admin.users.index');
            }
            
            if ($user->hasRole(['Técnico', 'Vendedor'])) {
                return route('dashboard');
            }
        }
        
        // Fallback usando campos del modelo Usuario
        $userRole = $this->getUserRole($user);
        
        return match(strtolower($userRole)) {
            'admin', 'super admin' => route('admin.users.index'),
            'gerente', 'supervisor' => route('admin.users.index'),
            'técnico', 'vendedor', 'empleado' => route('dashboard'),
            'cliente' => route('dashboard'),
            default => route('dashboard')
        };
    }

    /**
     * Login via API (para Sanctum)
     */
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email', // CORREGIDO: tabla usuarios
            'password' => 'required|string',
            'device_name' => 'required|string',
        ]);

        // Verificar rate limiting para API
        $this->checkTooManyFailedAttempts($request);

        $user = Usuario::where('email', $request->email)->first(); // CORREGIDO: modelo Usuario

        // Verificar usuario bloqueado/inactivo
        if (!$user || $this->isUserBlocked($user) || !$this->isUserActive($user)) {
            $this->logFailedAttempt($request, $request->email, 'api_login_blocked');
            $this->incrementLoginAttempts($request);
            
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Verificar contraseña
        if (!Hash::check($request->password, $user->password)) {
            $this->logFailedAttempt($request, $request->email, 'api_invalid_password', $user->id);
            $this->incrementLoginAttempts($request);
            
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Login exitoso
        $this->logSuccessfulLogin($request, $user, 'api');
        $this->clearLoginAttempts($request);
        $this->updateUserLastAccess($user, $request);

        // Crear token
        $token = $user->createToken($request->device_name);

        return response()->json([
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user->id,
                'name' => $user->display_name ?? $user->username,
                'email' => $user->email,
                'role' => $this->getUserRole($user),
            ],
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ]);
    }

    /**
     * Obtener rol del usuario
     */
    protected function getUserRole($user): string
    {
        if (method_exists($user, 'roles') && $user->roles->isNotEmpty()) {
            return $user->roles->first()->name;
        }
        
        return $user->tipo_usuario ?? 'Usuario';
    }

    /**
     * Verificar si el usuario tiene 2FA habilitado
     */
    protected function userHas2FA($user): bool
    {
        return !empty($user->two_factor_secret);
    }

    /**
     * Verificar si el usuario está activo
     */
    protected function isUserActive($user): bool
    {
        return $user->isActive();
    }

    /**
     * Verificar si el usuario está bloqueado
     */
    protected function isUserBlocked($user): bool
    {
        return isset($user->blocked_until) && $user->blocked_until && $user->blocked_until > now();
    }

    /**
     * Registrar intento exitoso
     */
    protected function logSuccessfulLogin(Request $request, $user, string $type = 'web'): void
    {
        SecurityLog::create([
            'evento' => 'login_success',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'login_type' => $type,
                'timestamp' => now()->toISOString(),
                'success' => true
            ]),
            'nivel_riesgo' => 'low'
        ]);
    }

    /**
     * Registrar intento fallido
     */
    protected function logFailedAttempt(Request $request, string $identifier, string $reason, ?int $userId = null): void
    {
        SecurityLog::create([
            'evento' => 'login_failed',
            'usuario_id' => $userId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'identifier' => $identifier,
                'reason' => $reason,
                'timestamp' => now()->toISOString(),
                'attempts' => $this->getCurrentAttempts($request)
            ]),
            'nivel_riesgo' => 'medium'
        ]);
    }

    /**
     * Actualizar último acceso del usuario
     */
    protected function updateUserLastAccess($user, $request)
    {
        $user->update([
            'ultimo_login' => now(),
            'intentos_fallidos' => 0,
            'bloqueado' => false,
            'bloqueado_hasta' => null,
        ]);
    } 
    /**
     * Verificar too many attempts
     */
    protected function checkTooManyFailedAttempts(Request $request): void
    {
        $key = $this->throttleKey($request);
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            throw ValidationException::withMessages([
                'email' => ["Demasiados intentos de inicio de sesión. Intente nuevamente en {$seconds} segundos."],
            ]);
        }
    }

    /**
     * Incrementar intentos de login
     */
    protected function incrementLoginAttempts(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), 300); // 5 minutos
    }

    /**
     * Limpiar intentos de login
     */
    protected function clearLoginAttempts(Request $request): void
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    /**
     * Obtener clave de throttle
     */
    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email', $request->input('login', ''))) . '|' . $request->ip();
    }

    /**
     * Obtener intentos actuales
     */
    protected function getCurrentAttempts(Request $request): int
    {
        return RateLimiter::attempts($this->throttleKey($request));
    }

    /**
     * Intentar hacer login
     */
    protected function attemptLogin(array $credentials, Request $request): bool
    {
        return Auth::attempt($credentials, $request->boolean('remember'));
    }

    /**
     * Verificar estado de login para API
     */
    public function checkLoginStatus(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            return response()->json([
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->display_name ?? $user->username,
                    'email' => $user->email,
                    'role' => $this->getUserRole($user),
                ],
                'session_valid' => $this->isUserActive($user) && !$this->isUserBlocked($user),
            ]);
        }

        return response()->json([
            'authenticated' => false,
            'user' => null,
            'session_valid' => false,
        ]);
    }
}