<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\SecurityLog;

class CustomLogin extends BaseLogin
{
    protected static string $layout = 'filament-panels::components.layout.index';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Correo Electrónico')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1])
            ->placeholder('admin@gestion.com');
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Contraseña')
            ->password()
            ->required()
            ->extraInputAttributes(['tabindex' => 2])
            ->placeholder('Ingresa tu contraseña');
    }

    protected function getRememberFormComponent(): Component
    {
        return \Filament\Forms\Components\Checkbox::make('remember')
            ->label('Recordarme')
            ->extraInputAttributes(['tabindex' => 3]);
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $credentials = [
            'email' => $data['email'],
            'password' => $data['password'],
        ];

        // Verificar rate limiting
        $key = 'login.' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            // Registrar intento de login bloqueado
            SecurityLog::create([
                'user_id' => null,
                'action' => 'login_blocked',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => json_encode([
                    'email' => $data['email'],
                    'reason' => 'rate_limit_exceeded',
                    'available_in' => $seconds
                ])
            ]);

            throw ValidationException::withMessages([
                'email' => "Demasiados intentos de inicio de sesión. Inténtalo de nuevo en {$seconds} segundos.",
            ]);
        }

        // Intentar autenticación
        if (!Auth::attempt($credentials, $data['remember'] ?? false)) {
            RateLimiter::hit($key, 300); // 5 minutos

            // Registrar intento fallido
            SecurityLog::create([
                'user_id' => null,
                'action' => 'login_failed',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => json_encode([
                    'email' => $data['email'],
                    'reason' => 'invalid_credentials'
                ])
            ]);

            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ]);
        }

        // Verificar si el usuario tiene los roles necesarios
        $user = Auth::user();
        if (!$user->hasRole(['Super Admin', 'Gerente', 'Supervisor'])) {
            Auth::logout();
            
            // Registrar acceso denegado
            SecurityLog::create([
                'user_id' => $user->id,
                'action' => 'access_denied',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => json_encode([
                    'email' => $data['email'],
                    'reason' => 'insufficient_permissions',
                    'user_roles' => $user->roles->pluck('name')->toArray()
                ])
            ]);

            throw ValidationException::withMessages([
                'email' => 'No tienes permisos para acceder al panel administrativo.',
            ]);
        }

        // Login exitoso - limpiar rate limiting
        RateLimiter::clear($key);

        // Registrar login exitoso
        SecurityLog::create([
            'user_id' => $user->id,
            'action' => 'login_success',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => json_encode([
                'email' => $data['email'],
                'user_roles' => $user->roles->pluck('name')->toArray()
            ])
        ]);

        // Actualizar última actividad
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip()
        ]);

        return app(LoginResponse::class);
    }

    public function getHeading(): string
    {
        return 'Iniciar Sesión';
    }

    public function getSubheading(): string
    {
        return 'Accede al panel administrativo';
    }

    public function getTitle(): string
    {
        return 'Gestión Empresarial - Login';
    }


    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }
}