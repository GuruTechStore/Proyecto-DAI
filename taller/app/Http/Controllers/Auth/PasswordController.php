<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordController extends Controller
{
    /**
     * Cambio de contraseña para usuario autenticado
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

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            // Log intento de cambio con contraseña incorrecta
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

        // Actualizar contraseña
        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
            'force_password_change' => false
        ]);

        // Log cambio exitoso
        SecurityLog::create([
            'evento' => 'password_changed',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode(['changed_by' => 'self']),
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

        // Log solicitud de reset
        SecurityLog::create([
            'evento' => 'password_reset_requested',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode(['email' => $request->email]),
            'nivel_riesgo' => 'low'
        ]);

        // Enviar email de reset
        $status = Password::broker('usuarios')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Enlace de recuperación enviado a tu email'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al enviar el enlace de recuperación'
        ], 500);
    }

    /**
     * Reset de contraseña con token
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

        $status = Password::broker('usuarios')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'password_changed_at' => now(),
                    'force_password_change' => false
                ])->save();

                // Revocar todos los tokens existentes
                $user->tokens()->delete();

                // Log reset exitoso
                SecurityLog::create([
                    'evento' => 'password_reset_completed',
                    'usuario_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'detalles' => json_encode(['reset_method' => 'email_token']),
                    'nivel_riesgo' => 'low'
                ]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Contraseña restablecida correctamente'
            ]);
        }

        // Log reset fallido
        $user = Usuario::where('email', $request->email)->first();
        if ($user) {
            SecurityLog::create([
                'evento' => 'password_reset_failed',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode(['reason' => $status]),
                'nivel_riesgo' => 'medium'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al restablecer la contraseña. Token inválido o expirado.'
        ], 400);
    }

    /**
     * Forzar cambio de contraseña
     */
    public function forcePasswordChange(Request $request, Usuario $user)
    {
        $this->authorize('forcePasswordChange', $user);

        $validator = Validator::make($request->all(), [
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

        // Actualizar contraseña
        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
            'force_password_change' => false
        ]);

        // Revocar todos los tokens del usuario
        $user->tokens()->delete();

        // Log cambio forzado
        SecurityLog::create([
            'evento' => 'password_force_changed',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'changed_by' => auth()->user()->username,
                'admin_id' => auth()->id()
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente. El usuario deberá iniciar sesión nuevamente.'
        ]);
    }

    /**
     * Verificar si la contraseña requiere cambio
     */
    public function checkPasswordExpiry()
    {
        $user = auth()->user();
        $passwordAge = $user->password_changed_at 
            ? now()->diffInDays($user->password_changed_at)
            : 999;

        $requiresChange = $user->force_password_change || $passwordAge > 90;

        return response()->json([
            'success' => true,
            'data' => [
                'requires_change' => $requiresChange,
                'force_change' => $user->force_password_change,
                'password_age_days' => $passwordAge,
                'max_age_days' => 90
            ]
        ]);
    }
}