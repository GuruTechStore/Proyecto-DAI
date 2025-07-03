<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\SecurityLog;
use App\Models\Usuario;

class LogSecurityEvents
{
    protected $suspiciousPatterns = [
        'sql injection' => ['union', 'select', 'drop', 'delete', 'insert', 'update', '--', ';'],
        'xss' => ['<script', 'javascript:', 'onerror=', 'onload='],
        'path traversal' => ['../', '..\\', '/etc/passwd', '\\windows\\'],
        'command injection' => ['&&', '||', ';', '|', '`']
    ];

    public function handle(Request $request, Closure $next)
    {
        // Detectar patrones sospechosos
        $this->detectSuspiciousActivity($request);

        // Verificar IP sospechosa
        $this->checkSuspiciousIP($request);

        $response = $next($request);

        // Log intentos de login después de la respuesta
        if ($request->is('login') && $request->isMethod('POST')) {
            $this->handleLoginAttempt($request, $response);
        }

        return $response;
    }

    protected function detectSuspiciousActivity(Request $request)
    {
        $allInput = collect($request->all())
            ->merge($request->query())
            ->merge([$request->path(), $request->userAgent()])
            ->filter()
            ->map(fn($value) => is_string($value) ? strtolower($value) : '')
            ->join(' ');

        foreach ($this->suspiciousPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($allInput, $pattern)) {
                    SecurityLog::logSuspiciousPattern(
                        $pattern,
                        $type,
                        $request->ip(),
                        $request->userAgent(),
                        [
                            'url' => $request->fullUrl(),
                            'method' => $request->method(),
                            'input_sample' => substr($allInput, 0, 200)
                        ]
                    );
                    break 2; // Solo log el primer patrón encontrado
                }
            }
        }
    }

    protected function checkSuspiciousIP(Request $request)
    {
        $ip = $request->ip();
        $key = "suspicious_ip:{$ip}";

        // Verificar si la IP está en lista negra
        if (Cache::has("blocked_ip:{$ip}")) {
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_IP_BLOCKED,
                'descripcion' => 'Intento de acceso desde IP bloqueada',
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'severity' => SecurityLog::SEVERITY_CRITICAL,
                'datos_adicionales' => [
                    'url' => $request->fullUrl(),
                    'method' => $request->method()
                ]
            ]);
            abort(403, 'Access denied');
        }

        // Verificar actividad sospechosa reciente
        $recentActivity = Cache::get($key, []);
        
        if (count($recentActivity) > 50) { // Más de 50 requests en la ventana de tiempo
            Cache::put("blocked_ip:{$ip}", true, now()->addHours(1));
            
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_IP_BLOCKED,
                'descripcion' => 'IP bloqueada automáticamente por actividad sospechosa',
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'severity' => SecurityLog::SEVERITY_CRITICAL,
                'datos_adicionales' => [
                    'request_count' => count($recentActivity),
                    'auto_blocked' => true
                ]
            ]);
        }

        // Agregar request actual al tracking
        $recentActivity[] = now()->timestamp;
        $recentActivity = array_filter($recentActivity, fn($timestamp) => $timestamp > now()->subMinutes(15)->timestamp);
        
        Cache::put($key, $recentActivity, now()->addMinutes(15));
    }

    protected function handleLoginAttempt(Request $request, $response)
    {
        $email = $request->input('email');
        $isSuccessful = $response->getStatusCode() === 200 || $response->isRedirection();

        if (!$isSuccessful && $email) {
            $this->handleFailedLogin($request, $email);
        } elseif ($isSuccessful && $email) {
            $this->handleSuccessfulLogin($request, $email);
        }
    }

    protected function handleFailedLogin(Request $request, string $email)
    {
        $key = "failed_login:{$email}";
        $ipKey = "failed_login_ip:{$request->ip()}";
        $maxAttempts = config('security.failed_login_limit', 5);
        $lockoutTime = config('security.failed_login_timeout', 15);

        // Incrementar contador de intentos fallidos por email
        $attempts = Cache::increment($key);
        Cache::increment($ipKey);
        
        if ($attempts === 1) {
            Cache::put($key, 1, now()->addMinutes($lockoutTime));
            Cache::put($ipKey, 1, now()->addMinutes($lockoutTime));
        }

        // Log el intento fallido usando SecurityLog
        SecurityLog::logFailedLogin(
            $email,
            $request->ip(),
            $request->userAgent(),
            [
                'attempts' => $attempts,
                'url' => $request->fullUrl()
            ]
        );

        // Bloquear usuario después del límite
        if ($attempts >= $maxAttempts) {
            $this->blockUser($email);
            
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_USER_BLOCKED,
                'descripcion' => 'Usuario bloqueado por demasiados intentos fallidos',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'severity' => SecurityLog::SEVERITY_CRITICAL,
                'datos_adicionales' => [
                    'email' => $email,
                    'reason' => 'too_many_failed_attempts',
                    'attempts' => $attempts
                ]
            ]);

            // Enviar alerta
            $this->sendSecurityAlert($email, $attempts, $request->ip());
        }
    }

    protected function handleSuccessfulLogin(Request $request, string $email)
    {
        $key = "failed_login:{$email}";
        $ipKey = "failed_login_ip:{$request->ip()}";
        
        // Limpiar intentos fallidos al login exitoso
        Cache::forget($key);
        Cache::forget($ipKey);

        // Obtener usuario para el log
        $usuario = Usuario::where('email', $email)->first();
        
        if ($usuario) {
            SecurityLog::logSuccessfulLogin(
                $usuario->id,
                $request->ip(),
                $request->userAgent()
            );
        }
    }

    protected function blockUser(string $email)
    {
        try {
            Usuario::where('email', $email)->update([
                'bloqueado' => true,
                'fecha_bloqueo' => now(),
                'razon_bloqueo' => 'Demasiados intentos de login fallidos'
            ]);
        } catch (\Exception $e) {
            \Log::error("Error blocking user {$email}: " . $e->getMessage());
        }
    }

    protected function sendSecurityAlert(string $email, int $attempts, string $ip)
    {
        try {
            \Log::critical("ALERTA DE SEGURIDAD: Usuario {$email} bloqueado", [
                'email' => $email,
                'attempts' => $attempts,
                'ip' => $ip,
                'timestamp' => now()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Security alert sending error: ' . $e->getMessage());
        }
    }
}
