<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 */
class UsuarioFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Usuario::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombres = $this->faker->firstName();
        $apellidos = $this->faker->lastName() . ' ' . $this->faker->lastName();
        $username = strtolower($nombres . '.' . explode(' ', $apellidos)[0]);
        
        return [
            'username' => $username . rand(1, 999),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => $this->faker->boolean(70) ? now() : null,
            'password' => Hash::make('password123'), // Contraseña por defecto
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => $this->faker->boolean(80) ? $this->faker->phoneNumber() : null,
            'fecha_nacimiento' => $this->faker->boolean(60) ? $this->faker->date('Y-m-d', '-18 years') : null,
            'direccion' => $this->faker->boolean(70) ? $this->faker->address() : null,
            'activo' => $this->faker->boolean(90), // 90% activos
            'force_password_change' => $this->faker->boolean(20), // 20% requieren cambio
            'password_changed_at' => $this->faker->dateTimeBetween('-90 days', 'now'),
            'last_login_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-30 days', 'now') : null,
            'last_login_ip' => $this->faker->boolean(80) ? $this->faker->ipv4() : null,
            'failed_login_attempts' => $this->faker->numberBetween(0, 3),
            'blocked_until' => $this->faker->boolean(5) ? $this->faker->dateTimeBetween('now', '+7 days') : null,
            'blocked_reason' => $this->faker->boolean(5) ? $this->faker->sentence() : null,
            'two_factor_secret' => $this->faker->boolean(30) ? encrypt($this->faker->regexify('[A-Z0-9]{16}')) : null,
            'two_factor_enabled_at' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('-60 days', 'now') : null,
            'timezone' => $this->faker->randomElement(['America/Lima', 'America/New_York', 'Europe/Madrid']),
            'locale' => $this->faker->randomElement(['es', 'en']),
            'theme' => $this->faker->randomElement(['light', 'dark', 'auto']),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the user's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
            'last_login_at' => null,
        ]);
    }

    /**
     * Indicate that the user should be blocked.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'blocked_until' => $this->faker->dateTimeBetween('now', '+30 days'),
            'blocked_reason' => $this->faker->randomElement([
                'Múltiples intentos fallidos de inicio de sesión',
                'Actividad sospechosa detectada',
                'Violación de políticas de seguridad',
                'Solicitud administrativa'
            ]),
            'failed_login_attempts' => $this->faker->numberBetween(3, 10),
        ]);
    }

    /**
     * Indicate that the user should have 2FA enabled.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt($this->faker->regexify('[A-Z0-9]{16}')),
            'two_factor_enabled_at' => $this->faker->dateTimeBetween('-60 days', 'now'),
            'two_factor_backup_codes' => encrypt(json_encode([
                strtoupper($this->faker->regexify('[A-Z0-9]{8}')),
                strtoupper($this->faker->regexify('[A-Z0-9]{8}')),
                strtoupper($this->faker->regexify('[A-Z0-9]{8}')),
                strtoupper($this->faker->regexify('[A-Z0-9]{8}')),
                strtoupper($this->faker->regexify('[A-Z0-9]{8}'))
            ])),
        ]);
    }

    /**
     * Indicate that the user should require a password change.
     */
    public function requiresPasswordChange(): static
    {
        return $this->state(fn (array $attributes) => [
            'force_password_change' => true,
            'password_changed_at' => $this->faker->dateTimeBetween('-120 days', '-91 days'), // Contraseña expirada
        ]);
    }

    /**
     * Create a user with admin role.
     */
    public function admin(): static
    {
        return $this->afterCreating(function (Usuario $user) {
            $adminRole = Role::where('name', 'Super Admin')->first();
            if ($adminRole) {
                $user->assignRole($adminRole);
            }
        });
    }

    /**
     * Create a user with manager role.
     */
    public function manager(): static
    {
        return $this->afterCreating(function (Usuario $user) {
            $managerRole = Role::where('name', 'Gerente')->first();
            if ($managerRole) {
                $user->assignRole($managerRole);
            }
        });
    }

    /**
     * Create a user with supervisor role.
     */
    public function supervisor(): static
    {
        return $this->afterCreating(function (Usuario $user) {
            $supervisorRole = Role::where('name', 'Supervisor')->first();
            if ($supervisorRole) {
                $user->assignRole($supervisorRole);
            }
        });
    }

    /**
     * Create a user with technician role.
     */
    public function technician(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombres' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName() . ' ' . $this->faker->lastName(),
        ])->afterCreating(function (Usuario $user) {
            $techRole = $this->faker->randomElement(['Técnico Senior', 'Técnico']);
            $role = Role::where('name', $techRole)->first();
            if ($role) {
                $user->assignRole($role);
            }
        });
    }

    /**
     * Create a user with sales role.
     */
    public function salesperson(): static
    {
        return $this->afterCreating(function (Usuario $user) {
            $salesRole = $this->faker->randomElement(['Vendedor Senior', 'Vendedor']);
            $role = Role::where('name', $salesRole)->first();
            if ($role) {
                $user->assignRole($role);
            }
        });
    }

    /**
     * Create a user with employee role (basic).
     */
    public function employee(): static
    {
        return $this->afterCreating(function (Usuario $user) {
            $employeeRole = Role::where('name', 'Empleado')->first();
            if ($employeeRole) {
                $user->assignRole($employeeRole);
            }
        });
    }

    /**
     * Create a user with random role from existing roles.
     */
    public function withRandomRole(): static
    {
        return $this->afterCreating(function (Usuario $user) {
            $roles = Role::whereNotIn('name', ['Super Admin'])->get();
            if ($roles->isNotEmpty()) {
                $randomRole = $roles->random();
                $user->assignRole($randomRole);
            }
        });
    }

    /**
     * Create a user with recent activity.
     */
    public function recentlyActive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => $this->faker->dateTimeBetween('-3 days', 'now'),
            'last_login_ip' => $this->faker->ipv4(),
        ])->afterCreating(function (Usuario $user) {
            // Crear algunas actividades recientes
            for ($i = 0; $i < rand(3, 10); $i++) {
                \App\Models\UserActivity::create([
                    'usuario_id' => $user->id,
                    'accion' => $this->faker->randomElement([
                        'login', 'profile_updated', 'dashboard_viewed', 
                        'search_performed', 'document_viewed'
                    ]),
                    'modulo' => $this->faker->randomElement([
                        'auth', 'dashboard', 'usuarios', 'documentos', 'reportes'
                    ]),
                    'detalles' => json_encode(['generated' => true]),
                    'ip_address' => $this->faker->ipv4(),
                    'user_agent' => $this->faker->userAgent(),
                    'created_at' => $this->faker->dateTimeBetween('-3 days', 'now')
                ]);
            }
        });
    }

    /**
     * Create a user with security events.
     */
    public function withSecurityEvents(): static
    {
        return $this->afterCreating(function (Usuario $user) {
            // Crear algunos eventos de seguridad
            for ($i = 0; $i < rand(2, 5); $i++) {
                \App\Models\SecurityLog::create([
                    'evento' => $this->faker->randomElement([
                        'login_success', 'login_failed', 'password_changed', 
                        'suspicious_activity'
                    ]),
                    'usuario_id' => $user->id,
                    'ip_address' => $this->faker->ipv4(),
                    'user_agent' => $this->faker->userAgent(),
                    'detalles' => json_encode(['generated' => true]),
                    'nivel_riesgo' => $this->faker->randomElement(['low', 'medium', 'high']),
                    'resuelto' => $this->faker->boolean(60),
                    'created_at' => $this->faker->dateTimeBetween('-30 days', 'now')
                ]);
            }
        });
    }

    /**
     * Create a complete test user with all features.
     */
    public function complete(): static
    {
        return $this->withTwoFactor()
            ->recentlyActive()
            ->withSecurityEvents()
            ->state(fn (array $attributes) => [
                'email_verified_at' => now(),
                'activo' => true,
                'timezone' => 'America/Lima',
                'locale' => 'es',
            ]);
    }

    /**
     * Create a problematic user for testing security features.
     */
    public function problematic(): static
    {
        return $this->state(fn (array $attributes) => [
            'failed_login_attempts' => $this->faker->numberBetween(3, 8),
            'email_verified_at' => null,
            'force_password_change' => true,
            'password_changed_at' => $this->faker->dateTimeBetween('-180 days', '-100 days'),
        ])->afterCreating(function (Usuario $user) {
            // Crear eventos de seguridad problemáticos
            for ($i = 0; $i < rand(3, 8); $i++) {
                \App\Models\SecurityLog::create([
                    'evento' => $this->faker->randomElement([
                        'login_failed', 'suspicious_activity', 'account_locked'
                    ]),
                    'usuario_id' => $user->id,
                    'ip_address' => $this->faker->ipv4(),
                    'user_agent' => $this->faker->userAgent(),
                    'detalles' => json_encode([
                        'reason' => 'Automated test data',
                        'attempts' => rand(1, 5)
                    ]),
                    'nivel_riesgo' => $this->faker->randomElement(['medium', 'high', 'critical']),
                    'resuelto' => false,
                    'created_at' => $this->faker->dateTimeBetween('-7 days', 'now')
                ]);
            }
        });
    }

    /**
     * Configure the model factory for testing.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Usuario $user) {
            // Ensure username is unique
            $baseUsername = $user->username;
            $counter = 1;
            
            while (Usuario::where('username', $user->username)->exists()) {
                $user->username = $baseUsername . $counter;
                $counter++;
            }
        });
    }
}