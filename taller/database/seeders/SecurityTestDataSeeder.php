<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SecurityLog;
use App\Models\UserActivity;
use App\Models\Usuario;
use Carbon\Carbon;

class SecurityTestDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Creando datos de prueba para seguridad...');

        // Obtener algunos usuarios para usar en los tests
        $usuarios = Usuario::limit(5)->get();
        
        if ($usuarios->isEmpty()) {
            $this->command->warn('No hay usuarios en la base de datos. Creando usuarios de prueba...');
            $this->createTestUsers();
            $usuarios = Usuario::limit(5)->get();
        }

        // Crear Security Logs de prueba
        $this->createSecurityLogs($usuarios);
        
        // Crear User Activities de prueba  
        $this->createUserActivities($usuarios);

        $this->command->info('Datos de prueba de seguridad creados correctamente.');
    }

    protected function createTestUsers()
    {
        $testUsers = [
            [
                'nombre' => 'Admin Test',
                'email' => 'admin.test@sistema.com',
                'password' => bcrypt('password123'),
                'rol' => 'admin',
                'activo' => true,
                'bloqueado' => false
            ],
            [
                'nombre' => 'Usuario Test',
                'email' => 'user.test@sistema.com', 
                'password' => bcrypt('password123'),
                'rol' => 'empleado',
                'activo' => true,
                'bloqueado' => false
            ],
            [
                'nombre' => 'Usuario Bloqueado',
                'email' => 'blocked.test@sistema.com',
                'password' => bcrypt('password123'),
                'rol' => 'empleado',
                'activo' => false,
                'bloqueado' => true,
                'fecha_bloqueo' => now()->subDays(2),
                'razon_bloqueo' => 'Demasiados intentos fallidos'
            ]
        ];

        foreach ($testUsers as $userData) {
            Usuario::create($userData);
        }
    }

    protected function createSecurityLogs($usuarios)
    {
        $this->command->info('  Creando Security Logs...');

        // IPs de prueba
        $testIPs = [
            '192.168.1.100',
            '192.168.1.101', 
            '10.0.0.5',
            '203.0.113.10', // IP sospechosa
            '198.51.100.25'
        ];

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
            'curl/7.68.0', // Posible bot
            'Python-requests/2.25.1' // Posible script
        ];

        // 1. Intentos de login fallidos
        for ($i = 0; $i < 15; $i++) {
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_FAILED_LOGIN,
                'descripcion' => 'Intento de login fallido',
                'ip' => $testIPs[array_rand($testIPs)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'severity' => SecurityLog::SEVERITY_WARNING,
                'datos_adicionales' => [
                    'rate_limit_key' => 'api_rate_limit:ip:203.0.113.10',
                    'endpoint' => '/api/users',
                    'method' => 'GET'
                ],
                'created_at' => Carbon::now()->subHours(rand(1, 12))
            ]);
        }

        // 6. Accesos no autorizados
        for ($i = 0; $i < 8; $i++) {
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_UNAUTHORIZED,
                'descripcion' => 'Acceso no autorizado a API',
                'ip' => $testIPs[array_rand($testIPs)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'severity' => SecurityLog::SEVERITY_WARNING,
                'datos_adicionales' => [
                    'endpoint' => '/api/admin/users',
                    'method' => 'GET',
                    'token' => 'invalid-token-123',
                    'reason' => 'invalid_token'
                ],
                'created_at' => Carbon::now()->subHours(rand(1, 36))
            ]);
        }

        $this->command->info('    ✅ ' . SecurityLog::count() . ' Security Logs creados');
    }

    protected function createUserActivities($usuarios)
    {
        $this->command->info('  Creando User Activities...');

        $modulos = [
            UserActivity::MODULO_DASHBOARD,
            UserActivity::MODULO_USUARIOS,
            UserActivity::MODULO_CLIENTES,
            UserActivity::MODULO_PRODUCTOS,
            UserActivity::MODULO_REPARACIONES,
            UserActivity::MODULO_VENTAS,
            UserActivity::MODULO_EMPLEADOS,
            UserActivity::MODULO_REPORTES,
            UserActivity::MODULO_CONFIGURACION,
            UserActivity::MODULO_API
        ];

        $acciones = [
            'GET /dashboard',
            'POST /usuarios',
            'PUT /usuarios/1',
            'GET /clientes',
            'POST /clientes',
            'GET /productos',
            'POST /reparaciones',
            'GET /ventas',
            'GET /reportes/ventas',
            'POST /api/usuarios'
        ];

        $testIPs = [
            '192.168.1.100',
            '192.168.1.101',
            '192.168.1.102',
            '10.0.0.15',
            '10.0.0.20'
        ];

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ];

        // Crear actividades para los últimos 30 días
        for ($day = 0; $day < 30; $day++) {
            $fecha = Carbon::now()->subDays($day);
            
            foreach ($usuarios->where('activo', true) as $usuario) {
                // Cada usuario activo tiene entre 0-3 módulos de actividad por día
                $modulosDelDia = collect($modulos)->random(rand(0, 3));
                
                foreach ($modulosDelDia as $modulo) {
                    $contadorAccesos = rand(1, 15);
                    $ultimaActividad = $fecha->copy()->addHours(rand(8, 18))->addMinutes(rand(0, 59));
                    
                    UserActivity::create([
                        'usuario_id' => $usuario->id,
                        'modulo' => $modulo,
                        'accion' => $acciones[array_rand($acciones)],
                        'ruta' => $this->generateRouteForModule($modulo),
                        'fecha' => $fecha->toDateString(),
                        'ultima_actividad' => $ultimaActividad,
                        'ip' => $testIPs[array_rand($testIPs)],
                        'user_agent' => $userAgents[array_rand($userAgents)],
                        'contador_accesos' => $contadorAccesos,
                        'datos_sesion' => [
                            'session_id' => 'sess_' . rand(100000, 999999),
                            'referer' => $day > 0 ? '/dashboard' : null,
                            'method' => rand(0, 10) > 7 ? 'POST' : 'GET',
                            'response_time' => rand(50, 500) . 'ms'
                        ]
                    ]);
                }
            }
        }

        // Crear algunas actividades especiales
        $this->createSpecialActivities($usuarios, $testIPs, $userAgents);

        $this->command->info('    ✅ ' . UserActivity::count() . ' User Activities creadas');
    }

    protected function generateRouteForModule(string $modulo): string
    {
        $routes = [
            UserActivity::MODULO_DASHBOARD => ['/dashboard', '/dashboard/stats'],
            UserActivity::MODULO_USUARIOS => ['/usuarios', '/usuarios/create', '/usuarios/1/edit'],
            UserActivity::MODULO_CLIENTES => ['/clientes', '/clientes/create', '/clientes/1'],
            UserActivity::MODULO_PRODUCTOS => ['/productos', '/productos/create', '/productos/search'],
            UserActivity::MODULO_REPARACIONES => ['/reparaciones', '/reparaciones/create', '/reparaciones/1'],
            UserActivity::MODULO_VENTAS => ['/ventas', '/ventas/create', '/ventas/1'],
            UserActivity::MODULO_EMPLEADOS => ['/empleados', '/empleados/create'],
            UserActivity::MODULO_REPORTES => ['/reportes', '/reportes/ventas', '/reportes/clientes'],
            UserActivity::MODULO_CONFIGURACION => ['/configuracion', '/configuracion/sistema'],
            UserActivity::MODULO_API => ['/api/users', '/api/clientes', '/api/productos']
        ];

        return $routes[$modulo][array_rand($routes[$modulo])];
    }

    protected function createSpecialActivities($usuarios, $testIPs, $userAgents)
    {
        // Actividad intensiva de un usuario (posible actividad sospechosa)
        $usuarioActivo = $usuarios->where('activo', true)->first();
        if ($usuarioActivo) {
            for ($i = 0; $i < 5; $i++) {
                $fecha = Carbon::now()->subDays($i);
                
                UserActivity::create([
                    'usuario_id' => $usuarioActivo->id,
                    'modulo' => UserActivity::MODULO_API,
                    'accion' => 'GET /api/users',
                    'ruta' => '/api/users',
                    'fecha' => $fecha->toDateString(),
                    'ultima_actividad' => $fecha->addHours(rand(20, 23)),
                    'ip' => '203.0.113.10', // IP sospechosa
                    'user_agent' => 'Python-requests/2.25.1',
                    'contador_accesos' => rand(50, 100), // Muchos accesos
                    'datos_sesion' => [
                        'session_id' => 'api_session_' . rand(100000, 999999),
                        'api_token' => 'token_' . rand(1000, 9999),
                        'method' => 'GET',
                        'automated' => true
                    ]
                ]);
            }
        }

        // Actividad desde múltiples IPs (posible cuenta comprometida)
        $usuarioSospechoso = $usuarios->where('activo', true)->skip(1)->first();
        if ($usuarioSospechoso) {
            $ipsSospechosas = ['1.2.3.4', '5.6.7.8', '9.10.11.12'];
            
            foreach ($ipsSospechosas as $ip) {
                UserActivity::create([
                    'usuario_id' => $usuarioSospechoso->id,
                    'modulo' => UserActivity::MODULO_DASHBOARD,
                    'accion' => 'GET /dashboard',
                    'ruta' => '/dashboard',
                    'fecha' => Carbon::today()->toDateString(),
                    'ultima_actividad' => Carbon::now()->subMinutes(rand(10, 60)),
                    'ip' => $ip,
                    'user_agent' => $userAgents[array_rand($userAgents)],
                    'contador_accesos' => rand(1, 5),
                    'datos_sesion' => [
                        'session_id' => 'suspicious_' . rand(100000, 999999),
                        'method' => 'GET',
                        'suspicious_location' => true
                    ]
                ]);
            }
        }

        // Actividad nocturna (posible acceso no autorizado)
        foreach ($usuarios->where('activo', true)->take(2) as $usuario) {
            UserActivity::create([
                'usuario_id' => $usuario->id,
                'modulo' => UserActivity::MODULO_CONFIGURACION,
                'accion' => 'GET /configuracion/sistema',
                'ruta' => '/configuracion/sistema',
                'fecha' => Carbon::today()->toDateString(),
                'ultima_actividad' => Carbon::today()->addHours(3), // 3 AM
                'ip' => $testIPs[array_rand($testIPs)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'contador_accesos' => rand(1, 3),
                'datos_sesion' => [
                    'session_id' => 'night_' . rand(100000, 999999),
                    'method' => 'GET',
                    'unusual_hour' => true
                ]
            ]);
        }
    }

    protected function createSampleAuditData()
    {
        $this->command->info('  Creando algunos registros de auditoría adicionales...');

        // Crear algunos registros en la tabla auditoria para complementar
        $usuarios = Usuario::limit(3)->get();
        
        foreach ($usuarios as $usuario) {
            for ($i = 0; $i < rand(2, 5); $i++) {
                \App\Models\Auditoria::create([
                    'usuario_id' => $usuario->id,
                    'operacion' => ['CREATE', 'UPDATE', 'DELETE'][array_rand(['CREATE', 'UPDATE', 'DELETE'])],
                    'tabla' => ['usuarios', 'clientes', 'productos'][array_rand(['usuarios', 'clientes', 'productos'])],
                    'registro_id' => rand(1, 100),
                    'datos_anteriores' => ['nombre' => 'Valor anterior'],
                    'datos_nuevos' => ['nombre' => 'Valor nuevo'],
                    'ip' => '192.168.1.' . rand(100, 200),
                    'user_agent' => 'Mozilla/5.0 (Test Browser)',
                    'ruta' => '/admin/usuarios/1',
                    'controlador' => 'UsuarioController@update',
                    'created_at' => Carbon::now()->subHours(rand(1, 48))
                ]);
            }
        }

        $this->command->info('    ✅ Registros de auditoría adicionales creados');
    }
}
