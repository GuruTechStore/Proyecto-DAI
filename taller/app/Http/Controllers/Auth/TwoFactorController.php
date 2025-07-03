<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User; // CAMBIADO: Usar modelo User
use App\Models\SecurityLog;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    protected $google2fa;
    
    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Mostrar página de challenge 2FA
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$this->userHas2FA($user)) {
            return redirect()->route('login')->with('error', 'Acceso no autorizado');
        }
        
        return view('auth.two-factor', [
            'user' => $user
        ]);
    }

    /**
     * Habilitar 2FA para el usuario actual
     */
    public function enable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña requerida',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        // Verificar contraseña
        if (!Hash::check($request->password, $user->password)) {
            $this->logSecurityEvent('2fa_enable_failed', $user->id, $request, [
                'reason' => 'invalid_password'
            ], 'medium');

            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 400);
        }

        // Verificar si ya tiene 2FA habilitado
        if ($this->userHas2FA($user)) {
            return response()->json([
                'success' => false,
                'message' => 'La autenticación de dos factores ya está habilitada'
            ], 400);
        }

        // Generar secret key
        $secretKey = $this->google2fa->generateSecretKey();
        
        // Generar QR code data
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );

        // Guardar secret temporalmente (no confirmado aún)
        $this->updateUser2FATemp($user, $secretKey);

        // Log intento de habilitación
        $this->logSecurityEvent('2fa_setup_started', $user->id, $request, [
            'qr_generated' => true
        ], 'low');

        return response()->json([
            'success' => true,
            'message' => 'Escanea el código QR con tu aplicación de autenticación',
            'data' => [
                'secret_key' => $secretKey,
                'qr_code_url' => $qrCodeUrl,
                'manual_entry_key' => $secretKey,
                'backup_codes' => $this->generateBackupCodes()
            ]
        ]);
    }

    /**
     * Confirmar habilitación de 2FA con código de verificación
     */
    public function confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Código de 6 dígitos requerido',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $tempSecret = $this->getUser2FATemp($user);

        if (!$tempSecret) {
            return response()->json([
                'success' => false,
                'message' => 'No hay configuración 2FA pendiente'
            ], 400);
        }

        $secretKey = decrypt($tempSecret);

        // Verificar código con window tolerance
        $isValid = $this->google2fa->verifyKey($secretKey, $request->code, 2);

        if (!$isValid) {
            $this->logSecurityEvent('2fa_confirm_failed', $user->id, $request, [
                'reason' => 'invalid_code'
            ], 'medium');

            return response()->json([
                'success' => false,
                'message' => 'Código de verificación inválido'
            ], 400);
        }

        // Confirmar 2FA
        $backupCodes = $this->generateBackupCodes();
        
        $this->confirm2FAForUser($user, $tempSecret, $backupCodes);

        // Log habilitación exitosa
        $this->logSecurityEvent('2fa_enabled', $user->id, $request, [
            'enabled_at' => now()
        ], 'low');

        $this->logUserActivity($user->id, '2fa_enabled', 'security', [
            'method' => 'google_authenticator'
        ], $request);

        return response()->json([
            'success' => true,
            'message' => 'Autenticación de dos factores habilitada correctamente',
            'data' => [
                'backup_codes' => $backupCodes,
                'enabled_at' => $this->getUser2FAEnabledAt($user)
            ]
        ]);
    }

    /**
     * Deshabilitar 2FA
     */
    public function disable(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'code' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña y código 2FA requeridos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        // Verificar contraseña
        if (!Hash::check($request->password, $user->password)) {
            $this->logSecurityEvent('2fa_disable_failed', $user->id, $request, [
                'reason' => 'invalid_password'
            ], 'high');

            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 400);
        }

        if (!$this->userHas2FA($user)) {
            return response()->json([
                'success' => false,
                'message' => 'La autenticación de dos factores no está habilitada'
            ], 400);
        }

        // Verificar código 2FA o backup code
        $isValid = $this->verify2FACode($user, $request->code);

        if (!$isValid) {
            $this->logSecurityEvent('2fa_disable_failed', $user->id, $request, [
                'reason' => 'invalid_2fa_code'
            ], 'high');

            return response()->json([
                'success' => false,
                'message' => 'Código 2FA inválido'
            ], 400);
        }

        // Deshabilitar 2FA
        $this->disable2FAForUser($user);

        // Log deshabilitación
        $this->logSecurityEvent('2fa_disabled', $user->id, $request, [
            'disabled_at' => now()
        ], 'high');

        $this->logUserActivity($user->id, '2fa_disabled', 'security', [
            'disabled_by' => 'self'
        ], $request);

        return response()->json([
            'success' => true,
            'message' => 'Autenticación de dos factores deshabilitada'
        ]);
    }

    /**
     * Verificar código 2FA durante el login
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'user_id' => 'sometimes|integer|exists:users,id' // CAMBIADO: tabla users
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Obtener usuario de la sesión o del request
        if ($request->has('user_id')) {
            $user = User::find($request->user_id);
        } else {
            $user = Auth::user();
        }

        if (!$user || !$this->userHas2FA($user)) {
            return response()->json([
                'success' => false,
                'message' => '2FA no está habilitado para este usuario'
            ], 400);
        }

        $isValid = $this->verify2FACode($user, $request->code);

        if (!$isValid) {
            $this->logSecurityEvent('2fa_verification_failed', $user->id, $request, [
                'reason' => 'invalid_code'
            ], 'high');

            return response()->json([
                'success' => false,
                'message' => 'Código 2FA inválido'
            ], 400);
        }

        // Marcar 2FA como verificado en la sesión
        session(['2fa_verified' => true, '2fa_verified_at' => now()]);

        $this->logSecurityEvent('2fa_verification_success', $user->id, $request, [
            'verified_at' => now()
        ], 'low');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Código 2FA verificado correctamente'
            ]);
        }

        // Redirigir a la página intended o dashboard
        return redirect()->intended(route('dashboard'))
                        ->with('success', 'Autenticación completada exitosamente');
    }

    /**
     * Regenerar códigos de backup
     */
    public function regenerateBackupCodes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña requerida',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            $this->logSecurityEvent('backup_codes_regen_failed', $user->id, $request, [
                'reason' => 'invalid_password'
            ], 'medium');

            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ], 400);
        }

        if (!$this->userHas2FA($user)) {
            return response()->json([
                'success' => false,
                'message' => '2FA no está habilitado'
            ], 400);
        }

        $newBackupCodes = $this->generateBackupCodes();
        
        $this->updateUser2FABackupCodes($user, $newBackupCodes);

        $this->logSecurityEvent('backup_codes_regenerated', $user->id, $request, [
            'regenerated_at' => now()
        ], 'medium');

        return response()->json([
            'success' => true,
            'message' => 'Códigos de backup regenerados',
            'data' => [
                'backup_codes' => $newBackupCodes
            ]
        ]);
    }

    /**
     * Obtener estado 2FA del usuario actual
     */
    public function status()
    {
        $user = auth()->user();

        $backupCodesCount = 0;
        $backupCodes = $this->getUser2FABackupCodes($user);
        
        if ($backupCodes) {
            try {
                $codes = json_decode(decrypt($backupCodes), true);
                $backupCodesCount = is_array($codes) ? count($codes) : 0;
            } catch (\Exception $e) {
                $backupCodesCount = 0;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => $this->userHas2FA($user),
                'enabled_at' => $this->getUser2FAEnabledAt($user),
                'backup_codes_count' => $backupCodesCount,
                'has_pending_setup' => !is_null($this->getUser2FATemp($user))
            ]
        ]);
    }

    /**
     * Generar códigos de backup
     */
    private function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(Str::random(8));
        }
        return $codes;
    }

    /**
     * Verificar código 2FA (normal o backup)
     */
    private function verify2FACode($user, $code): bool
    {
        try {
            $secret = $this->getUser2FASecret($user);
            if (!$secret) {
                return false;
            }

            $secretKey = decrypt($secret);

            // Verificar código normal con window tolerance
            if ($this->google2fa->verifyKey($secretKey, $code, 2)) {
                return true;
            }

            // Verificar código de backup
            $backupCodes = $this->getUser2FABackupCodes($user);
            if ($backupCodes) {
                $codes = json_decode(decrypt($backupCodes), true);
                
                if (is_array($codes) && in_array(strtoupper($code), $codes)) {
                    // Remover código usado
                    $codes = array_diff($codes, [strtoupper($code)]);
                    $this->updateUser2FABackupCodes($user, array_values($codes));
                    
                    $this->logSecurityEvent('backup_code_used', $user->id, request(), [
                        'remaining_codes' => count($codes)
                    ], 'medium');

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Error verifying 2FA code: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper methods para manejar diferentes campos del modelo User
     */
    private function userHas2FA($user): bool
    {
        return !empty($this->getUser2FASecret($user));
    }

    private function getUser2FASecret($user)
    {
        return $user->two_factor_secret ?? null;
    }

    private function getUser2FATemp($user)
    {
        return $user->two_factor_secret_temp ?? null;
    }

    private function getUser2FABackupCodes($user)
    {
        return $user->two_factor_backup_codes ?? null;
    }

    private function getUser2FAEnabledAt($user)
    {
        return $user->two_factor_enabled_at ?? null;
    }

    private function updateUser2FATemp($user, $secretKey): void
    {
        $updateData = ['two_factor_secret_temp' => encrypt($secretKey)];
        
        // Verificar si la columna existe antes de actualizar
        if ($user->getConnection()->getSchemaBuilder()->hasColumn($user->getTable(), 'two_factor_secret_temp')) {
            $user->update($updateData);
        } else {
            \Log::warning('Column two_factor_secret_temp does not exist in users table');
        }
    }

    private function confirm2FAForUser($user, $tempSecret, $backupCodes): void
    {
        $updateData = [
            'two_factor_secret' => $tempSecret,
            'two_factor_secret_temp' => null,
            'two_factor_backup_codes' => encrypt(json_encode($backupCodes)),
            'two_factor_enabled_at' => now()
        ];

        // Solo actualizar campos que existen
        $existingFields = [];
        foreach ($updateData as $field => $value) {
            if ($user->getConnection()->getSchemaBuilder()->hasColumn($user->getTable(), $field)) {
                $existingFields[$field] = $value;
            }
        }

        if (!empty($existingFields)) {
            $user->update($existingFields);
        }
    }

    private function disable2FAForUser($user): void
    {
        $updateData = [
            'two_factor_secret' => null,
            'two_factor_backup_codes' => null,
            'two_factor_enabled_at' => null
        ];

        // Solo actualizar campos que existen
        $existingFields = [];
        foreach ($updateData as $field => $value) {
            if ($user->getConnection()->getSchemaBuilder()->hasColumn($user->getTable(), $field)) {
                $existingFields[$field] = $value;
            }
        }

        if (!empty($existingFields)) {
            $user->update($existingFields);
        }
    }

    private function updateUser2FABackupCodes($user, $codes): void
    {
        $field = 'two_factor_backup_codes';
        
        if ($user->getConnection()->getSchemaBuilder()->hasColumn($user->getTable(), $field)) {
            $user->update([$field => encrypt(json_encode($codes))]);
        }
    }

    /**
     * Log de evento de seguridad
     */
    private function logSecurityEvent(string $evento, int $userId, Request $request, array $detalles = [], string $nivelRiesgo = 'low'): void
    {
        try {
            SecurityLog::create([
                'evento' => $evento,
                'usuario_id' => $userId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode($detalles),
                'nivel_riesgo' => $nivelRiesgo
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log security event: ' . $e->getMessage());
        }
    }

    /**
     * Log de actividad de usuario
     */
    private function logUserActivity(int $userId, string $accion, string $modulo, array $detalles, Request $request): void
    {
        try {
            if (class_exists(UserActivity::class)) {
                UserActivity::create([
                    'usuario_id' => $userId,
                    'accion' => $accion,
                    'modulo' => $modulo,
                    'detalles' => json_encode($detalles),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to log user activity: ' . $e->getMessage());
        }
    }
}