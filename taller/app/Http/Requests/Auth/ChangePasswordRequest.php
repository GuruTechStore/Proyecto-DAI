<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\PasswordHistory;
use App\Models\SecurityLog;
use App\Models\Usuario; // AGREGADO: Import del modelo Usuario
use Carbon\Carbon;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // CORREGIDO: Obtener usuario como Usuario, no User
                    $user = Usuario::find(auth()->id());
                    if (!Hash::check($value, $user->password)) {
                        $fail('La contraseña actual es incorrecta.');
                    }
                }
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:current_password',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                function ($attribute, $value, $fail) {
                    if ($this->hasUsedPasswordRecently($value)) {
                        $fail('No puedes usar una de tus últimas 5 contraseñas.');
                    }
                },
                function ($attribute, $value, $fail) {
                    if ($this->isCommonPassword($value)) {
                        $fail('Esta contraseña es muy común. Elige una más segura.');
                    }
                }
            ],
            'password_confirmation' => 'required|string'
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'La contraseña actual es obligatoria',
            'password.required' => 'La nueva contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'La confirmación de contraseña no coincide',
            'password.different' => 'La nueva contraseña debe ser diferente a la actual',
            'password_confirmation.required' => 'Debe confirmar la nueva contraseña'
        ];
    }

    /**
     * Nombres de atributos personalizados
     */
    public function attributes(): array
    {
        return [
            'current_password' => 'contraseña actual',
            'password' => 'nueva contraseña',
            'password_confirmation' => 'confirmación de contraseña'
        ];
    }

    /**
     * Verificar si la contraseña fue usada recientemente - CORREGIDO
     */
    protected function hasUsedPasswordRecently(string $newPassword): bool
    {
        $user = Usuario::find(auth()->id()); // CORREGIDO: Usar Usuario en lugar de auth()->user()
        $historyLimit = config('security.password_policy.history_limit', 5);
        
        // Verificar contraseña actual
        if (Hash::check($newPassword, $user->password)) {
            return true;
        }

        // Usar el método del modelo para verificar historial
        return PasswordHistory::passwordWasUsed($user->id, $newPassword, $historyLimit);
    }

    /**
     * Verificar si es una contraseña común
     */
    protected function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            '12345678', 'password', 'password123', '123456789',
            'qwerty123', 'admin123', 'letmein123', 'welcome123',
            'Password1', 'Password123', '12345678!', 'password!',
            'administrador', 'bienvenido', 'contraseña'
        ];

        return in_array(strtolower($password), array_map('strtolower', $commonPasswords));
    }

    /**
     * Verificar si debe forzar cambio de contraseña - CORREGIDO
     */
    public function shouldForcePasswordChange(): bool
    {
        $user = Usuario::find(auth()->id()); // CORREGIDO: Usar Usuario
        $expiryDays = config('security.password_policy.expiry_days', 90);
        
        // Si no tiene fecha de último cambio, forzar cambio
        if (!$user->password_changed_at) {
            return true;
        }

        // Si han pasado más días de los permitidos
        return $user->password_changed_at->addDays($expiryDays)->isPast();
    }

    /**
     * Verificar fortaleza de la contraseña
     */
    public function getPasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Longitud
        if (strlen($password) >= 12) {
            $score += 2;
        } elseif (strlen($password) >= 8) {
            $score += 1;
        } else {
            $feedback[] = 'Usa al menos 8 caracteres';
        }

        // Letras minúsculas
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Incluye letras minúsculas';
        }

        // Letras mayúsculas
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Incluye letras mayúsculas';
        }

        // Números
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Incluye números';
        }

        // Símbolos
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Incluye símbolos (!@#$%^&*)';
        }

        // Variedad de caracteres
        if (strlen(array_unique(str_split($password))) > strlen($password) * 0.7) {
            $score += 1;
        }

        // Determinar nivel
        $level = match(true) {
            $score >= 6 => 'Muy fuerte',
            $score >= 5 => 'Fuerte', 
            $score >= 4 => 'Moderada',
            $score >= 2 => 'Débil',
            default => 'Muy débil'
        };

        return [
            'score' => $score,
            'level' => $level,
            'feedback' => $feedback,
            'percentage' => min(100, ($score / 6) * 100)
        ];
    }

    /**
     * Procesar después de la validación exitosa - CORREGIDO
     */
    protected function passedValidation()
    {
        $user = Usuario::find(auth()->id()); // CORREGIDO: Usar Usuario
        
        // Guardar la contraseña actual en el historial
        PasswordHistory::create([
            'usuario_id' => $user->id,
            'password_hash' => $user->password
        ]);

        // Limpiar historial usando el método del modelo
        $historyLimit = config('security.password_policy.history_limit', 5);
        PasswordHistory::limpiarHistorial($user->id, $historyLimit);

        // Log del cambio de contraseña
        $this->logPasswordChange();
    }

    /**
     * Obtener datos validados con campos adicionales
     */
    public function getValidatedData(): array
    {
        $validated = $this->validated();
        
        // Agregar campos de auditoría
        $validated['password_changed_at'] = now();
        $validated['debe_cambiar_password'] = false;
        $validated['updated_by'] = auth()->id();
        
        // Hash de la nueva contraseña
        $validated['password'] = Hash::make($validated['password']);
        
        // Remover confirmación y contraseña actual
        unset($validated['password_confirmation'], $validated['current_password']);
        
        return $validated;
    }

    /**
     * Log del cambio de contraseña - CORREGIDO
     */
    protected function logPasswordChange(): void
    {
        try {
            $user = Usuario::find(auth()->id()); // CORREGIDO: Usar Usuario
            
            SecurityLog::create([
                'tipo' => 'password_changed',
                'descripcion' => 'Contraseña cambiada exitosamente',
                'usuario_id' => $user->id,
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'severity' => SecurityLog::SEVERITY_INFO,
                'datos_adicionales' => [
                    'email' => $user->email,
                    'forced_change' => $this->shouldForcePasswordChange(),
                    'strength' => $this->getPasswordStrength($this->input('password')),
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log password change: ' . $e->getMessage());
        }
    }

    /**
     * Manejar validación fallida
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Log de intento fallido de cambio de contraseña
        SecurityLog::create([
            'tipo' => 'password_change_failed',
            'descripcion' => 'Intento fallido de cambio de contraseña',
            'usuario_id' => auth()->id(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'severity' => SecurityLog::SEVERITY_WARNING,
            'datos_adicionales' => [
                'errors' => $validator->errors()->toArray(),
                'timestamp' => now()->toISOString()
            ]
        ]);

        parent::failedValidation($validator);
    }

    /**
     * Generar sugerencias de contraseña segura
     */
    public function generatePasswordSuggestion(): string
    {
        $words = ['Secure', 'Strong', 'Safe', 'Protected', 'Guard', 'Shield'];
        $numbers = rand(100, 999);
        $symbols = ['!', '@', '#', '$', '%', '^', '&', '*'];
        
        $word = $words[array_rand($words)];
        $symbol = $symbols[array_rand($symbols)];
        
        return $word . $numbers . $symbol;
    }

    /**
     * Preparar datos para validación
     */
    protected function prepareForValidation(): void
    {
        // Limpiar espacios en blanco de las contraseñas
        $this->merge([
            'current_password' => trim($this->current_password ?? ''),
            'password' => trim($this->password ?? ''),
            'password_confirmation' => trim($this->password_confirmation ?? '')
        ]);
    }
}