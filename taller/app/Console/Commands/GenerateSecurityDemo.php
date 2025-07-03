<?php

// ====== app/Console/Commands/GenerateSecurityDemo.php ======

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SecurityLog;
use App\Models\UserActivity;
use App\Models\Usuario;
use Carbon\Carbon;

class GenerateSecurityDemo extends Command
{
    protected $signature = 'security:demo 
                          {--reset : Limpiar datos existentes antes de generar}
                          {--days=7 : Días de datos a generar}
                          {--users=all : Número de usuarios a usar (all|número)}';

    protected $description = 'Genera datos de demostración realistas para el sistema de seguridad';

    public function handle()
    {
        $reset = $this->option('reset');
        $days = (int) $this->option('days');
        $usersOption = $this->option('users');

        $this->info("🔒 Generando datos demo de seguridad para {$days} días...");

        if ($reset) {
            $this->warn('Limpiando datos existentes...');
            if ($this->confirm('¿Está seguro de eliminar todos los security logs y user activities?')) {
                SecurityLog::truncate();
                UserActivity::truncate();
                $this->info('✅ Datos limpiados');
            } else {
                $this->info('Operación cancelada');
                return Command::FAILURE;
            }
        }

        // Obtener usuarios
        $usuarios = $this->getUsers($usersOption);
        if ($usuarios->isEmpty()) {
            $this->error('❌ No hay usuarios activos. Cree algunos usuarios primero.');
            return Command::FAILURE;
        }

        $this->info("👥 Usando {$usuarios->count()} usuarios para generar datos");

        // Generar datos día por día
        $progressBar = $this->output->createProgressBar($days);
        $progressBar->start();

        for ($day = 0; $day < $days; $day++) {
            $fecha = Carbon::now()->subDays($day);
            $this->generateDayData($fecha, $usuarios);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        // Generar algunos eventos especiales
        $this->generateSpecialSecurityScenarios($usuarios);

        // Estadísticas finales
        $stats = $this->getGeneratedStats();
        
        $this->info("\n📊 DATOS GENERADOS:");
        $this->table(
            ['Tipo', 'Cantidad'],
            [
                ['Security Logs', number_format($stats['security_logs'])],
                ['User Activities', number_format($stats['user_activities'])],
                ['Eventos Críticos', number_format($stats['critical_events'])],
                ['IPs Únicas', number_format($stats['unique_ips'])],
                ['Usuarios Activos', number_format($stats['active_users'])]
            ]
        );

        $this->info("\n🔍 DESGLOSE POR TIPO DE EVENTO:");
        foreach ($stats['events_by_type'] as $tipo => $cantidad) {
            $this->line("  • {$tipo}: {$cantidad}");
        }

        $this->info("\n🚀 DEMO LISTO! Comandos sugeridos:");
        $this->line("  php artisan security:report --days={$days}");
        $this->line("  php artisan security:clean-sessions --dry-run");
        $this->line("  php artisan tinker");
        $this->line("    >>> App\\Models\\SecurityLog::latest()->take(5)->get()");
        $this->line("    >>> App\\Models\\UserActivity::getStatsForPeriod({$days})");

        return Command::SUCCESS;
    }

    protected function getUsers(string $usersOption)
    {
        if ($usersOption === 'all') {
            return Usuario::where('activo', true)->get();
        }

        if (is_numeric($usersOption)) {
            return Usuario::where('activo', true)->limit((int) $usersOption)->get();
        }

        return Usuario::where('activo', true)->get();
    }

    protected function generateDayData(Carbon $fecha, $usuarios)
    {
        $isWeekend = $fecha->isWeekend();
        $isHoliday = $this->isHoliday($fecha);
        
        // Menos actividad en fines de semana y días festivos
        $activityMultiplier = $isWeekend ? 0.3 : ($isHoliday ? 0.1 : 1.0);
        
        foreach ($usuarios as $usuario) {
            // Probabilidad de que el usuario esté activo este día
            $activeChance = $isWeekend ? 0.2 : ($isHoliday ? 0.05 : 0.85);
            
            if (rand(1, 100) <= ($activeChance * 100)) {
                $this->generateUserDayActivity($usuario, $fecha, $activityMultiplier);
            }
        }

        // Generar eventos de seguridad base
        $this->generateDaySecurityEvents($fecha, $activityMultiplier);
        
        // Eventos especiales según el día
        $this->generateSpecialDayEvents($fecha, $usuarios);
    }

    protected function generateUserDayActivity($usuario, Carbon $fecha, float $multiplier)
    {
        $modulos = [
            UserActivity::MODULO_DASHBOARD,
            UserActivity::MODULO_CLIENTES,
            UserActivity::MODULO_PRODUCTOS,
            UserActivity::MODULO_VENTAS,
            UserActivity::MODULO_REPARACIONES,
            UserActivity::MODULO_REPORTES
        ];

        // Patrones de uso según rol
        $modulosDelDia = $this->getModulesForUserRole($usuario->rol, $modulos);
        
        // Número de módulos que usará este día
        $modulosCount = max(1, round(rand(1, count($modulosDelDia)) * $multiplier));
        $modulosSeleccionados = collect($modulosDelDia)->random(min($modulosCount, count($modulosDelDia)));

        foreach ($modulosSeleccionados as $modulo) {
            $horaInicio = $this->getWorkingHour($fecha);
            $accesos = max(1, round(rand(1, 25) * $multiplier));
            
            UserActivity::updateOrCreate(
                [
                    'usuario_id' => $usuario->id,
                    'modulo' => $modulo,
                    'fecha' => $fecha->toDateString()
                ],
                [
                    'accion' => $this->getRandomAction($modulo),
                    'ruta' => $this->getModuleRoute($modulo),
                    'ultima_actividad' => $fecha->copy()->setHour($horaInicio)->addMinutes(rand(0, 480)), // 8 horas de trabajo
                    'ip' => $this->getUserIP($usuario),
                    'user_agent' => $this->getRandomUserAgent(),
                    'contador_accesos' => $accesos,
                    'datos_sesion' => [
                        'demo' => true,
                        'work_pattern' => $this->getWorkPattern($horaInicio),
                        'session_duration' => rand(30, 480), // minutos
                        'generated_at' => now()
                    ]
                ]
            );
        }

        // Login exitoso con probabilidad alta
        if (rand(1, 100) <= 90) {
            $horaLogin = $this->getLoginHour($fecha);
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_SUCCESSFUL_LOGIN,
                'descripcion' => 'Login exitoso',
                'usuario_id' => $usuario->id,
                'ip' => $this->getUserIP($usuario),
                'user_agent' => $this->getRandomUserAgent(),
                'severity' => SecurityLog::SEVERITY_INFO,
                'datos_adicionales' => [
                    'demo' => true,
                    'login_time' => $horaLogin,
                    'remember_me' => rand(1, 100) <= 30
                ],
                'created_at' => $fecha->copy()->setHour($horaLogin)->addMinutes(rand(0, 30))
            ]);
        }
    }

    protected function generateDaySecurityEvents(Carbon $fecha, float $multiplier)
    {
        $baseEvents = round(rand(2, 15) * $multiplier);
        
        for ($i = 0; $i < $baseEvents; $i++) {
            $eventType = $this->getRandomEventType();
            $hora = rand(0, 23);
            
            switch ($eventType) {
                case 'failed_login':
                    SecurityLog::create([
                        'tipo' => SecurityLog::TIPO_FAILED_LOGIN,
                        'descripcion' => 'Intento de login fallido',
                        'ip' => $this->getSuspiciousIP(),
                        'user_agent' => $this->getSuspiciousUserAgent(),
                        'severity' => SecurityLog::SEVERITY_WARNING,
                        'datos_adicionales' => [
                            'email' => $this->getRandomBadEmail(),
                            'attempts' => rand(1, 3),
                            'demo' => true
                        ],
                        'created_at' => $fecha->copy()->setHour($hora)->addMinutes(rand(0, 59))
                    ]);
                    break;

                case 'rate_limit':
                    SecurityLog::create([
                        'tipo' => SecurityLog::TIPO_RATE_LIMIT,
                        'descripcion' => 'Límite de peticiones excedido',
                        'ip' => $this->getSuspiciousIP(),
                        'user_agent' => $this->getBotUserAgent(),
                        'severity' => SecurityLog::SEVERITY_WARNING,
                        'datos_adicionales' => [
                            'endpoint' => $this->getRandomAPIEndpoint(),
                            'requests_count' => rand(50, 200),
                            'demo' => true
                        ],
                        'created_at' => $fecha->copy()->setHour($hora)->addMinutes(rand(0, 59))
                    ]);
                    break;

                case 'suspicious_pattern':
                    $pattern = $this->getRandomSuspiciousPattern();
                    SecurityLog::create([
                        'tipo' => SecurityLog::TIPO_SUSPICIOUS_PATTERN,
                        'descripcion' => 'Patrón sospechoso detectado',
                        'ip' => $this->getAttackerIP(),
                        'user_agent' => $this->getAttackerUserAgent(),
                        'severity' => SecurityLog::SEVERITY_ERROR,
                        'datos_adicionales' => [
                            'pattern' => $pattern['pattern'],
                            'type' => $pattern['type'],
                            'url' => $pattern['url'],
                            'demo' => true
                        ],
                        'created_at' => $fecha->copy()->setHour($hora)->addMinutes(rand(0, 59))
                    ]);
                    break;

                case 'unauthorized':
                    SecurityLog::create([
                        'tipo' => SecurityLog::TIPO_UNAUTHORIZED,
                        'descripcion' => 'Acceso no autorizado',
                        'ip' => $this->getSuspiciousIP(),
                        'user_agent' => $this->getRandomUserAgent(),
                        'severity' => SecurityLog::SEVERITY_WARNING,
                        'datos_adicionales' => [
                            'endpoint' => $this->getProtectedEndpoint(),
                            'method' => ['GET', 'POST', 'PUT', 'DELETE'][array_rand(['GET', 'POST', 'PUT', 'DELETE'])],
                            'token_provided' => rand(1, 100) <= 70,
                            'demo' => true
                        ],
                        'created_at' => $fecha->copy()->setHour($hora)->addMinutes(rand(0, 59))
                    ]);
                    break;
            }
        }
    }

    protected function generateSpecialDayEvents(Carbon $fecha, $usuarios)
    {
        // Eventos especiales según el día de la semana
        if ($fecha->isMonday()) {
            // Lunes: más logins (gente regresando del fin de semana)
            $this->generateExtraLogins($fecha, $usuarios, 1.5);
        }

        if ($fecha->isFriday()) {
            // Viernes: más actividad de reportes
            $this->generateReportActivity($fecha, $usuarios);
        }

        // Eventos nocturnos sospechosos (1% probabilidad)
        if (rand(1, 100) <= 1) {
            $this->generateNightTimeActivity($fecha, $usuarios);
        }

        // Ataque coordinado (0.5% probabilidad)
        if (rand(1, 1000) <= 5) {
            $this->generateCoordinatedAttack($fecha);
        }
    }

    protected function generateSpecialSecurityScenarios($usuarios)
    {
        $this->info("\n🎭 Generando escenarios especiales de seguridad...");

        // Escenario 1: Usuario con comportamiento sospechoso
        $this->generateSuspiciousUserBehavior($usuarios->first());

        // Escenario 2: Ataque de fuerza bruta
        $this->generateBruteForceAttack();

        // Escenario 3: Escalación de privilegios
        $this->generatePrivilegeEscalation($usuarios->where('rol', 'empleado')->first());

        // Escenario 4: Actividad desde ubicaciones múltiples
        $this->generateMultiLocationActivity($usuarios->skip(1)->first());

        $this->line("✅ Escenarios especiales generados");
    }

    protected function generateSuspiciousUserBehavior($usuario)
    {
        if (!$usuario) return;

        // Actividad inusual en horarios nocturnos
        for ($i = 0; $i < 3; $i++) {
            $fecha = Carbon::now()->subDays(rand(1, 5));
            $horaNocturna = rand(23, 4); // 11 PM a 4 AM
            
            UserActivity::create([
                'usuario_id' => $usuario->id,
                'modulo' => UserActivity::MODULO_CONFIGURACION,
                'accion' => 'GET /configuracion/sistema',
                'ruta' => '/configuracion/sistema',
                'fecha' => $fecha->toDateString(),
                'ultima_actividad' => $fecha->copy()->setHour($horaNocturna),
                'ip' => $this->getUserIP($usuario),
                'user_agent' => $this->getRandomUserAgent(),
                'contador_accesos' => rand(5, 15),
                'datos_sesion' => [
                    'unusual_hour' => true,
                    'suspicious_activity' => true,
                    'demo_scenario' => 'night_access'
                ]
            ]);
        }
    }

    protected function generateBruteForceAttack()
    {
        $attackerIP = '203.0.113.' . rand(1, 50);
        $emails = ['admin@sistema.com', 'root@sistema.com', 'administrator@sistema.com'];
        
        // 20 intentos fallidos en 10 minutos
        $startTime = Carbon::now()->subHours(rand(1, 24));
        
        for ($i = 0; $i < 20; $i++) {
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_FAILED_LOGIN,
                'descripcion' => 'Intento de login fallido - Ataque de fuerza bruta',
                'ip' => $attackerIP,
                'user_agent' => 'Mozilla/5.0 (BruteForcer)',
                'severity' => SecurityLog::SEVERITY_ERROR,
                'datos_adicionales' => [
                    'email' => $emails[array_rand($emails)],
                    'brute_force_attack' => true,
                    'attack_sequence' => $i + 1,
                    'demo_scenario' => 'brute_force'
                ],
                'created_at' => $startTime->copy()->addMinutes($i * 0.5)
            ]);
        }
    }

    protected function generatePrivilegeEscalation($usuario)
    {
        if (!$usuario) return;

        // Intentos de acceso a recursos administrativos
        $adminEndpoints = ['/admin/users', '/admin/system', '/admin/logs', '/admin/config'];
        
        foreach ($adminEndpoints as $endpoint) {
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_UNAUTHORIZED,
                'descripcion' => 'Intento de escalación de privilegios',
                'usuario_id' => $usuario->id,
                'ip' => $this->getUserIP($usuario),
                'user_agent' => $this->getRandomUserAgent(),
                'severity' => SecurityLog::SEVERITY_ERROR,
                'datos_adicionales' => [
                    'endpoint' => $endpoint,
                    'user_role' => $usuario->rol,
                    'privilege_escalation' => true,
                    'demo_scenario' => 'privilege_escalation'
                ],
                'created_at' => Carbon::now()->subHours(rand(1, 12))
            ]);
        }
    }

    protected function generateMultiLocationActivity($usuario)
    {
        if (!$usuario) return;

        $locations = [
            ['ip' => '1.2.3.4', 'country' => 'China'],
            ['ip' => '5.6.7.8', 'country' => 'Russia'], 
            ['ip' => '9.10.11.12', 'country' => 'Nigeria']
        ];

        // Login desde múltiples países en poco tiempo
        $baseTime = Carbon::now()->subHours(2);
        
        foreach ($locations as $index => $location) {
            SecurityLog::create([
                'tipo' => SecurityLog::TIPO_SUCCESSFUL_LOGIN,
                'descripcion' => 'Login desde ubicación sospechosa',
                'usuario_id' => $usuario->id,
                'ip' => $location['ip'],
                'user_agent' => $this->getRandomUserAgent(),
                'severity' => SecurityLog::SEVERITY_WARNING,
                'datos_adicionales' => [
                    'country' => $location['country'],
                    'suspicious_location' => true,
                    'geo_impossible' => true,
                    'demo_scenario' => 'multi_location'
                ],
                'created_at' => $baseTime->copy()->addMinutes($index * 30)
            ]);
        }
    }

    protected function generateExtraLogins(Carbon $fecha, $usuarios, float $multiplier)
    {
        foreach ($usuarios->take(round($usuarios->count() * 0.7)) as $usuario) {
            if (rand(1, 100) <= 80) {
                SecurityLog::create([
                    'tipo' => SecurityLog::TIPO_SUCCESSFUL_LOGIN,
                    'descripcion' => 'Login de inicio de semana',
                    'usuario_id' => $usuario->id,
                    'ip' => $this->getUserIP($usuario),
                    'user_agent' => $this->getRandomUserAgent(),
                    'severity' => SecurityLog::SEVERITY_INFO,
                    'datos_adicionales' => [
                        'monday_login' => true,
                        'demo' => true
                    ],
                    'created_at' => $fecha->copy()->setHour(rand(7, 9))->addMinutes(rand(0, 59))
                ]);
            }
        }
    }

    protected function generateReportActivity(Carbon $fecha, $usuarios)
    {
        foreach ($usuarios->where('rol', '!=', 'cliente')->take(3) as $usuario) {
            UserActivity::updateOrCreate(
                [
                    'usuario_id' => $usuario->id,
                    'modulo' => UserActivity::MODULO_REPORTES,
                    'fecha' => $fecha->toDateString()
                ],
                [
                    'accion' => 'GET /reportes/semanal',
                    'ruta' => '/reportes/semanal',
                    'ultima_actividad' => $fecha->copy()->setHour(rand(14, 17)),
                    'ip' => $this->getUserIP($usuario),
                    'user_agent' => $this->getRandomUserAgent(),
                    'contador_accesos' => rand(5, 15),
                    'datos_sesion' => [
                        'friday_reports' => true,
                        'demo' => true
                    ]
                ]
            );
        }
    }

    protected function generateNightTimeActivity(Carbon $fecha, $usuarios)
    {
        $usuario = $usuarios->random();
        
        UserActivity::create([
            'usuario_id' => $usuario->id,
            'modulo' => UserActivity::MODULO_CONFIGURACION,
            'accion' => 'GET /configuracion',
            'ruta' => '/configuracion',
            'fecha' => $fecha->toDateString(),
            'ultima_actividad' => $fecha->copy()->setHour(rand(23, 4)),
            'ip' => $this->getUserIP($usuario),
            'user_agent' => $this->getRandomUserAgent(),
            'contador_accesos' => rand(1, 5),
            'datos_sesion' => [
                'night_activity' => true,
                'suspicious' => true,
                'demo_scenario' => 'night_work'
            ]
        ]);
    }

    protected function generateCoordinatedAttack(Carbon $fecha)
    {
        $attackerIPs = ['203.0.113.10', '203.0.113.11', '203.0.113.12'];
        $startTime = $fecha->copy()->setHour(rand(0, 23));
        
        foreach ($attackerIPs as $index => $ip) {
            for ($i = 0; $i < rand(5, 10); $i++) {
                SecurityLog::create([
                    'tipo' => SecurityLog::TIPO_SUSPICIOUS_PATTERN,
                    'descripcion' => 'Ataque coordinado detectado',
                    'ip' => $ip,
                    'user_agent' => 'AttackBot/' . ($index + 1),
                    'severity' => SecurityLog::SEVERITY_CRITICAL,
                    'datos_adicionales' => [
                        'attack_pattern' => 'coordinated',
                        'attack_group' => 'botnet_' . date('Ymd'),
                        'sequence' => $i + 1,
                        'demo_scenario' => 'coordinated_attack'
                    ],
                    'created_at' => $startTime->copy()->addMinutes($i * 2)
                ]);
            }
        }
    }

    // Métodos helper para generar datos realistas
    protected function getModulesForUserRole(string $rol, array $allModules): array
    {
        return match($rol) {
            'admin' => $allModules,
            'supervisor' => array_diff($allModules, [UserActivity::MODULO_CONFIGURACION]),
            'empleado' => [
                UserActivity::MODULO_DASHBOARD,
                UserActivity::MODULO_CLIENTES,
                UserActivity::MODULO_PRODUCTOS,
                UserActivity::MODULO_VENTAS,
                UserActivity::MODULO_REPARACIONES
            ],
            default => [UserActivity::MODULO_DASHBOARD]
        };
    }

    protected function getRandomAction(string $modulo): string
    {
        $actions = [
            UserActivity::MODULO_DASHBOARD => ['GET /dashboard', 'GET /dashboard/stats'],
            UserActivity::MODULO_CLIENTES => ['GET /clientes', 'POST /clientes', 'PUT /clientes/1'],
            UserActivity::MODULO_PRODUCTOS => ['GET /productos', 'POST /productos', 'GET /productos/search'],
            UserActivity::MODULO_VENTAS => ['GET /ventas', 'POST /ventas', 'GET /ventas/1'],
            UserActivity::MODULO_REPARACIONES => ['GET /reparaciones', 'POST /reparaciones'],
            UserActivity::MODULO_REPORTES => ['GET /reportes', 'GET /reportes/ventas'],
        ];

        $moduleActions = $actions[$modulo] ?? ['GET /' . strtolower($modulo)];
        return $moduleActions[array_rand($moduleActions)];
    }

    protected function getModuleRoute(string $modulo): string
    {
        return '/' . strtolower(str_replace('Modulo', '', $modulo));
    }

    protected function getUserIP($usuario): string
    {
        // IPs consistentes por usuario para simular ubicaciones fijas
        $userIPs = [
            'admin' => '192.168.1.100',
            'supervisor' => '192.168.1.101',
            'empleado' => '192.168.1.' . (102 + ($usuario->id % 10))
        ];

        return $userIPs[$usuario->rol] ?? '192.168.1.' . (110 + ($usuario->id % 20));
    }

    protected function getRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/120.0'
        ];

        return $userAgents[array_rand($userAgents)];
    }

    protected function getSuspiciousUserAgent(): string
    {
        $suspicious = [
            'curl/7.68.0',
            'wget/1.20.3',
            'Python-requests/2.25.1',
            'Go-http-client/1.1',
            'PostmanRuntime/7.28.0'
        ];

        return $suspicious[array_rand($suspicious)];
    }

    protected function getBotUserAgent(): string
    {
        $bots = [
            'Mozilla/5.0 (compatible; Bot/1.0)',
            'ScrapyBot/1.0',
            'AutomatedBot',
            'DataMinerBot/2.1',
            'APITestBot'
        ];

        return $bots[array_rand($bots)];
    }

    protected function getAttackerUserAgent(): string
    {
        $attackers = [
            'sqlmap/1.5.12',
            'Nikto/2.1.6',
            'Mozilla/5.0 (compatible; Nmap Scripting Engine)',
            'Scanner/1.0',
            'Exploit/1.0'
        ];

        return $attackers[array_rand($attackers)];
    }

    protected function getSuspiciousIP(): string
    {
        $ranges = [
            '203.0.113.',  // TEST-NET-3
            '198.51.100.', // TEST-NET-2
            '185.220.',    // Tor exit nodes range
            '46.166.'      // Known bad range
        ];

        $range = $ranges[array_rand($ranges)];
        return $range . rand(1, 254);
    }

    protected function getAttackerIP(): string
    {
        $knownBadIPs = [
            '185.220.101.1',
            '46.166.139.111',
            '198.51.100.25',
            '203.0.113.50'
        ];

        return $knownBadIPs[array_rand($knownBadIPs)];
    }

    protected function getRandomEventType(): string
    {
        $weights = [
            'failed_login' => 50,
            'rate_limit' => 20,
            'suspicious_pattern' => 15,
            'unauthorized' => 15
        ];

        $random = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $type => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $type;
            }
        }

        return 'failed_login';
    }

    protected function getRandomBadEmail(): string
    {
        $emails = [
            'admin@test.com',
            'root@example.com',
            'administrator@site.com',
            'test@test.com',
            'user@demo.com',
            'hacker@evil.com'
        ];

        return $emails[array_rand($emails)];
    }

    protected function getRandomAPIEndpoint(): string
    {
        $endpoints = [
            '/api/users',
            '/api/products',
            '/api/orders',
            '/api/admin/config',
            '/api/reports',
            '/api/search'
        ];

        return $endpoints[array_rand($endpoints)];
    }

    protected function getProtectedEndpoint(): string
    {
        $endpoints = [
            '/admin/users',
            '/admin/system',
            '/admin/logs',
            '/admin/config',
            '/api/admin/users',
            '/api/admin/system'
        ];

        return $endpoints[array_rand($endpoints)];
    }

    protected function getRandomSuspiciousPattern(): array
    {
        $patterns = [
            ['pattern' => 'union select', 'type' => 'sql_injection', 'url' => '/search?q=union+select+*+from+users'],
            ['pattern' => '<script>', 'type' => 'xss', 'url' => '/comment?text=<script>alert(1)</script>'],
            ['pattern' => '../etc/passwd', 'type' => 'path_traversal', 'url' => '/file?path=../etc/passwd'],
            ['pattern' => '&&', 'type' => 'command_injection', 'url' => '/ping?host=localhost&&cat+/etc/passwd'],
            ['pattern' => 'drop table', 'type' => 'sql_injection', 'url' => '/users?filter=1;drop+table+users'],
        ];

        return $patterns[array_rand($patterns)];
    }

    protected function getWorkingHour(Carbon $fecha): int
    {
        if ($fecha->isWeekend()) {
            return rand(10, 15); // Fin de semana, horarios más flexibles
        }
        
        return rand(7, 9); // Días laborales, entrada temprano
    }

    protected function getLoginHour(Carbon $fecha): int
    {
        if ($fecha->isWeekend()) {
            return rand(9, 16); // Fin de semana
        }
        
        // Días laborales: mayoría entre 7-9 AM, algunos tarde
        return rand(1, 100) <= 80 ? rand(7, 9) : rand(10, 15);
    }

    protected function getWorkPattern(int $hora): string
    {
        if ($hora >= 22 || $hora <= 5) {
            return 'night_shift';
        } elseif ($hora >= 6 && $hora <= 9) {
            return 'early_bird';
        } elseif ($hora >= 10 && $hora <= 17) {
            return 'standard_hours';
        } else {
            return 'overtime';
        }
    }

    protected function isHoliday(Carbon $fecha): bool
    {
        // Simular algunos días festivos
        $holidays = [
            '01-01', // Año nuevo
            '05-01', // Día del trabajo
            '12-25', // Navidad
        ];

        return in_array($fecha->format('m-d'), $holidays);
    }

    protected function getGeneratedStats(): array
    {
        $securityLogs = SecurityLog::count();
        $userActivities = UserActivity::count();
        
        $eventsByType = SecurityLog::select('tipo', \DB::raw('count(*) as total'))
                                  ->groupBy('tipo')
                                  ->pluck('total', 'tipo')
                                  ->toArray();

        return [
            'security_logs' => $securityLogs,
            'user_activities' => $userActivities,
            'critical_events' => SecurityLog::where('severity', SecurityLog::SEVERITY_CRITICAL)->count(),
            'unique_ips' => SecurityLog::distinct('ip')->count(),
            'active_users' => UserActivity::distinct('usuario_id')->count(),
            'events_by_type' => $eventsByType
        ];
    }
}

?>