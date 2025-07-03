<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\SecurityLog;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EmailVerificationController extends Controller
{
    /**
     * Verificar email con token
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de verificación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Usuario::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Verificar si ya está verificado
        if ($user->email_verified_at) {
            return response()->json([
                'success' => true,
                'message' => 'El email ya ha sido verificado anteriormente'
            ]);
        }

        // Verificar token (usando hash del email + created_at como token básico)
        $expectedToken = hash('sha256', $user->email . $user->created_at->timestamp);
        
        if (!hash_equals($expectedToken, $request->token)) {
            // Log intento de verificación con token inválido
            SecurityLog::create([
                'evento' => 'email_verification_failed',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'reason' => 'invalid_token',
                    'email' => $request->email
                ]),
                'nivel_riesgo' => 'medium'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Token de verificación inválido o expirado'
            ], 400);
        }

        // Marcar como verificado
        $user->update([
            'email_verified_at' => now()
        ]);

        // Log verificación exitosa
        SecurityLog::create([
            'evento' => 'email_verified',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'email' => $user->email,
                'verified_at' => now()
            ]),
            'nivel_riesgo' => 'low'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email verificado correctamente',
            'data' => [
                'user' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'verified_at' => $user->email_verified_at
                ]
            ]
        ]);
    }

    /**
     * Reenviar email de verificación
     */
    public function resend(Request $request)
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

        // Verificar si ya está verificado
        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'El email ya ha sido verificado'
            ], 400);
        }

        // Verificar límite de reenvíos (máximo 3 por hora)
        $recentLogs = SecurityLog::where('usuario_id', $user->id)
            ->where('evento', 'email_verification_sent')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentLogs >= 3) {
            SecurityLog::create([
                'evento' => 'email_verification_rate_limit',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'attempts_in_hour' => $recentLogs,
                    'email' => $request->email
                ]),
                'nivel_riesgo' => 'medium'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Límite de reenvíos alcanzado. Intenta nuevamente en una hora.'
            ], 429);
        }

        try {
            // Generar token de verificación
            $token = hash('sha256', $user->email . $user->created_at->timestamp);
            
            // Crear URL de verificación
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addHours(24),
                ['token' => $token, 'email' => $user->email]
            );

            // Enviar email (aquí deberías usar tu clase de Mail)
            // Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));

            // Log envío de verificación
            SecurityLog::create([
                'evento' => 'email_verification_sent',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'email' => $user->email,
                    'sent_at' => now()
                ]),
                'nivel_riesgo' => 'low'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email de verificación enviado correctamente',
                'data' => [
                    'email' => $user->email,
                    'expires_at' => now()->addHours(24)
                ]
            ]);

        } catch (\Exception $e) {
            // Log error de envío
            SecurityLog::create([
                'evento' => 'email_verification_send_failed',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'error' => $e->getMessage(),
                    'email' => $user->email
                ]),
                'nivel_riesgo' => 'medium'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el email de verificación'
            ], 500);
        }
    }

    /**
     * Verificar estado de verificación del usuario actual
     */
    public function status()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $user->email,
                'verified' => !is_null($user->email_verified_at),
                'verified_at' => $user->email_verified_at,
                'can_resend' => is_null($user->email_verified_at)
            ]
        ]);
    }

    /**
     * Forzar verificación de email (solo administradores)
     */
    public function forceVerify(Request $request, Usuario $user)
    {
        $this->authorize('update', $user);

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'El email ya está verificado'
            ], 400);
        }

        $user->update([
            'email_verified_at' => now()
        ]);

        // Log verificación forzada
        SecurityLog::create([
            'evento' => 'email_force_verified',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'verified_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'email' => $user->email
            ]),
            'nivel_riesgo' => 'low'
        ]);

        // Log actividad del administrador
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'email_force_verified',
            'modulo' => 'usuarios',
            'detalles' => json_encode([
                'target_user_id' => $user->id,
                'target_username' => $user->username,
                'target_email' => $user->email
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email verificado correctamente por el administrador',
            'data' => [
                'user' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'verified_at' => $user->email_verified_at
                ]
            ]
        ]);
    }

    /**
     * Cambiar email del usuario
     */
    public function changeEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string'
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
        if (!Hash::check($request->password, $user->password)) {
            SecurityLog::create([
                'evento' => 'email_change_failed',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'reason' => 'invalid_password',
                    'old_email' => $user->email,
                    'attempted_new_email' => $request->new_email
                ]),
                'nivel_riesgo' => 'medium'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 400);
        }

        $oldEmail = $user->email;

        // Actualizar email y marcar como no verificado
        $user->update([
            'email' => $request->new_email,
            'email_verified_at' => null
        ]);

        // Log cambio de email
        SecurityLog::create([
            'evento' => 'email_changed',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'old_email' => $oldEmail,
                'new_email' => $request->new_email,
                'changed_by' => 'self'
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        // Enviar verificación al nuevo email
        try {
            $token = hash('sha256', $user->email . $user->created_at->timestamp);
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addHours(24),
                ['token' => $token, 'email' => $user->email]
            );

            // Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));

            SecurityLog::create([
                'evento' => 'email_verification_sent',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'email' => $user->email,
                    'reason' => 'email_changed'
                ]),
                'nivel_riesgo' => 'low'
            ]);

        } catch (\Exception $e) {
            // Log pero no fallar la operación
            SecurityLog::create([
                'evento' => 'email_verification_send_failed',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'error' => $e->getMessage(),
                    'email' => $user->email
                ]),
                'nivel_riesgo' => 'medium'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email actualizado correctamente. Se ha enviado un enlace de verificación al nuevo email.',
            'data' => [
                'old_email' => $oldEmail,
                'new_email' => $user->email,
                'verified' => false,
                'verification_sent' => true
            ]
        ]);
    }

    /**
     * Obtener usuarios no verificados (solo administradores)
     */
    public function unverifiedUsers(Request $request)
    {
        $this->authorize('viewAny', Usuario::class);

        $query = Usuario::whereNull('email_verified_at')
            ->with('roles');

        // Filtros opcionales
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $users,
            'summary' => [
                'total_unverified' => Usuario::whereNull('email_verified_at')->count(),
                'total_verified' => Usuario::whereNotNull('email_verified_at')->count()
            ]
        ]);
    }
}