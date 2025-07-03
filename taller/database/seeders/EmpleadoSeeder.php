<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empleado;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        // Crear empleado administrador
        $empleadoAdmin = Empleado::create([
            'dni' => '12345678',
            'nombres' => 'Administrador',
            'apellidos' => 'Del Sistema',
            'telefono' => '999-000-001',
            'email' => 'admin@gestion.com',
            'especialidad' => 'Administración',
            'fecha_contratacion' => now(),
            'activo' => true,
        ]);

        // Crear usuario administrador
        $usuarioAdmin = Usuario::create([
            'empleado_id' => $empleadoAdmin->id,
            'username' => 'admin',
            'email' => 'admin@gestion.com',
            'password' => Hash::make('Admin123!'), // Hash manual para evitar el mutator problemático
            'tipo_usuario' => 'Super Admin',
            'activo' => true,
        ]);

        // Asignar rol
        if (Role::where('name', 'Super Admin')->exists()) {
            $usuarioAdmin->assignRole('Super Admin');
        }

        $this->command->info('✅ Usuario administrador creado: admin / Admin123!');

        // Crear más empleados de prueba
        $empleados = [
            [
                'empleado' => [
                    'dni' => '87654321',
                    'nombres' => 'Juan Carlos',
                    'apellidos' => 'Rodríguez Pérez',
                    'telefono' => '999-123-456',
                    'email' => 'gerente@gestion.com',
                    'especialidad' => 'Gestión',
                    'fecha_contratacion' => now()->subYear(),
                ],
                'usuario' => [
                    'username' => 'gerente',
                    'email' => 'gerente@gestion.com',
                    'password' => 'Gerente123!',
                    'tipo_usuario' => 'Gerente',
                ],
                'rol' => 'Gerente'
            ],
            [
                'empleado' => [
                    'dni' => '11223344',
                    'nombres' => 'María Elena',
                    'apellidos' => 'García Torres',
                    'telefono' => '999-456-789',
                    'email' => 'tecnico@gestion.com',
                    'especialidad' => 'Reparación de dispositivos',
                    'fecha_contratacion' => now()->subMonths(6),
                ],
                'usuario' => [
                    'username' => 'tecnico',
                    'email' => 'tecnico@gestion.com',
                    'password' => 'Tecnico123!',
                    'tipo_usuario' => 'Técnico',
                ],
                'rol' => 'Técnico'
            ],
        ];

        foreach ($empleados as $data) {
            // Verificar si ya existe
            $existingEmpleado = Empleado::where('dni', $data['empleado']['dni'])->first();
            if ($existingEmpleado) {
                $this->command->info("⚠️ Empleado ya existe: {$data['empleado']['dni']}");
                continue;
            }

            $existingUsuario = Usuario::where('username', $data['usuario']['username'])->first();
            if ($existingUsuario) {
                $this->command->info("⚠️ Usuario ya existe: {$data['usuario']['username']}");
                continue;
            }

            // Crear empleado
            $empleado = Empleado::create($data['empleado']);

            // Crear usuario con hash manual
            $usuario = Usuario::create([
                'empleado_id' => $empleado->id,
                'username' => $data['usuario']['username'],
                'email' => $data['usuario']['email'],
                'password' => Hash::make($data['usuario']['password']), // Hash manual
                'tipo_usuario' => $data['usuario']['tipo_usuario'],
                'activo' => true,
            ]);

            // Asignar rol
            if (Role::where('name', $data['rol'])->exists()) {
                $usuario->assignRole($data['rol']);
            }

            $this->command->info("✅ Usuario creado: {$data['usuario']['username']} / {$data['usuario']['password']}");
        }
    }
}