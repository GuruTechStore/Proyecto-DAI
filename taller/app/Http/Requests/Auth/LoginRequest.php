<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Models\Usuario;
use App\Models\SecurityLog;

class LoginRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado a hacer esta request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:usuarios,email',
            'password' => 'required|string|min:6',
            'remember' => 'boolean'
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El email es obligatorio',
            'email.email' => 'Debe ser un email válido',
            'email.exists' => 'El email no está registrado en el sistema',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres'
        ];
    }

    /**
     * Personalizar nombres de atributos
     */
    public function attributes(): array
    {
        return [
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'remember' => 'recordar sesión'
        ];
    }

    /**
     * Autenticar al usuario
     */
    public function authenticate()
    {
        // Verificar rate limiting antes de proceder
        $this->ensureIsNotRateLimited();

        $user = Usuario::where('email', $this->email)->first();

        // Verificar si el usuario está bloqueado
        if ($user && $user->bloqueado) {
            $this->logFailedAttempt('user_blocked', $user->id);
            
            throw ValidationException::withMessages([
                'email' => 'Esta cuenta está bloqueada. Contacte al administrador para más información.'
            ]);
        }

        // Verificar si el usuario está inactivo
        if ($user && !$user->activo) {
            $this->logFailedAttempt('user_inactive', $user->id);
            
            throw ValidationException::withMessages([
                'email' => 'Esta cuenta está desactivada. Contacte al administrador.'
            ]);
        }

        // Intentar autenticación
        $credentials = $this->only('email', 'password');
        $remember = $this->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            // Incrementar contador de rate limiting
            RateLimiter::hit($this->throttleKey());
            
            // Log del intento fallido
            $this->logFailedAttempt('invalid_credentials', $user ? $user->id : null);
            
            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas son incorrectas.'
            ]);
        }

        // Autenticación exitosa - CORREGIDO: Obtener usuario directamente de la base de datos
        $authenticatedUser = Usuario::where('email', $this->email)->first();
        
        // Limpiar intentos fallidos
        RateLimiter::clear($this->throttleKey());
        
        // Actualizar último login
        $authenticatedUser->update([
            'ultimo_login' => now(),
            'ultimo_ip' => $this->ip()
        ]);

        // Log del login exitoso
        $this->logSuccessfulLogin($authenticatedUser);

        // Verificar si debe cambiar contraseña
        if ($this->shouldForcePasswordChange($authenticatedUser)) {
            session()->flash('force_password_change', true);
        }

        // Verificar si hay actividad sospechosa
        if ($this->hasSuspiciousActivity($authenticatedUser)) {
            $this->logSuspiciousLogin($authenticatedUser);
        }
    }

    /**
     * Verificar que no se exceda el rate limiting
     */
    protected function ensureIsNotRateLimited(): void
    {
        $maxAttempts = config('security.failed_login_limit', 5);
        $decayMinutes = config('security.failed_login_timeout', 15);

        if (!RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());
        
        // Log del rate limiting
        SecurityLog::create([
            'tipo' => SecurityLog::TIPO_RATE_LIMIT,
            'descripcion' => 'Rate limit excedido en login',
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'severity' => SecurityLog::SEVERITY_WARNING,
            'datos_adicionales' => [
                'email' => $this->input('email'),
                'max_attempts' => $maxAttempts,
                'timeout_seconds' => $seconds,
                'throttle_key' => $this->throttleKey()
            ]
        ]);

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos de login. Intente nuevamente en {$seconds} segundos."
        ]);
    }

    /**
     * Obtener la clave de throttling
     */
    protected function throttleKey(): string
    {
        return strtolower($this->input('email')) . '|' . $this->ip();
    }

    /**
     * Verificar si debe forzar cambio de contraseña
     */
    protected function shouldForcePasswordChange(Usuario $user): bool
    {
        // Si el usuario debe cambiar contraseña
        if ($user->debe_cambiar_password) {
            return true;
        }

        // Si la contraseña tiene más de 90 días
        if ($user->password_changed_at) {
            return $user->password_changed_at->addDays(90)->isPast();
        }

        // Si nunca ha cambiado la contraseña
        return !$user->password_changed_at;
    }

    /**
     * Verificar actividad sospechosa - CORREGIDO: Type hint específico
     */
    protected function hasSuspiciousActivity(Usuario $user): bool
    {
        $currentIP = $this->ip();
        $lastIP = $user->ultimo_ip;

        // Si la IP es muy diferente a la última (simplificado)
        if ($lastIP && $lastIP !== $currentIP) {
            // Verificar si las IPs están en rangos muy diferentes
            $currentPrefix = implode('.', array_slice(explode('.', $currentIP), 0, 2));
            $lastPrefix = implode('.', array_slice(explode('.', $lastIP), 0, 2));
            
            if ($currentPrefix !== $lastPrefix) {
                return true;
            }
        }

        // Verificar login en horario inusual (entre 11 PM y 5 AM)
        $hour = now()->hour;
        if ($hour >= 23 || $hour <= 5) {
            return true;
        }

        return false;
    }

    /**
     * Log de intento fallido
     */
    protected function logFailedAttempt(string $reason, ?int $userId = null): void
    {
        try {
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_FAILED_LOGIN,
                'descripcion' => 'Intento de login fallido',
                'usuario_id' => $userId,
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'severity' => SecurityLog::SEVERITY_WARNING,
                'datos_adicionales' => [
                    'email' => $this->input('email'),
                    'reason' => $reason,
                    'url' => $this->fullUrl(),
                    'referer' => $this->header('referer'),
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log login attempt: ' . $e->getMessage());
        }
    }

    /**
     * Log de login exitoso - CORREGIDO: Type hint específico
     */
    protected function logSuccessfulLogin(Usuario $user): void
    {
        try {
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_SUCCESSFUL_LOGIN,
                'descripcion' => 'Login exitoso',
                'usuario_id' => $user->id,
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'severity' => SecurityLog::SEVERITY_INFO,
                'datos_adicionales' => [
                    'email' => $user->email,
                    'remember_me' => $this->boolean('remember'),
                    'session_id' => session()->getId(),
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log successful login: ' . $e->getMessage());
        }
    }

    /**
     * Log de login sospechoso - CORREGIDO: Type hint específico
     */
    protected function logSuspiciousLogin(Usuario $user): void
    {
        try {
            SecurityLog::create([
                'tipo' => 'suspicious_login',
                'descripcion' => 'Login desde ubicación o horario sospechoso',
                'usuario_id' => $user->id,
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'severity' => SecurityLog::SEVERITY_WARNING,
                'datos_adicionales' => [
                    'email' => $user->email,
                    'last_ip' => $user->ultimo_ip,
                    'current_ip' => $this->ip(),
                    'login_hour' => now()->hour,
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log suspicious login: ' . $e->getMessage());
        }
    }

    /**
     * Manejar validación fallida
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Log de validación fallida
        SecurityLog::create([
            'tipo' => 'validation_failed',
            'descripcion' => 'Validación de login fallida',
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'severity' => SecurityLog::SEVERITY_INFO,
            'datos_adicionales' => [
                'errors' => $validator->errors()->toArray(),
                'input_email' => $this->input('email'),
                'timestamp' => now()->toISOString()
            ]
        ]);

        parent::failedValidation($validator);
    }

    /**
     * Preparar datos para validación
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email ?? '')),
            'remember' => $this->boolean('remember')
        ]);
    }
}