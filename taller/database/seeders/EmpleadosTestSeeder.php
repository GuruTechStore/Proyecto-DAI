<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Empleado;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmpleadosTestSeeder extends Seeder
{
    public function run()
    {
        $usuarios = [
            [
                'empleado_data' => [
                    'dni' => '87654321',
                    'nombres' => 'Carlos',
                    'apellidos' => 'Gerente Lopez',
                    'telefono' => '999-000-002',
                    'email' => 'gerente@gestion.com',
                    'especialidad' => 'Gestión',
                ],
                'usuario_data' => [
                    'username' => 'gerente',
                    'email' => 'gerente@gestion.com',
                    'password' => 'Gerente123!',
                    'tipo_usuario' => 'Gerente',
                ],
                'rol' => 'Gerente',
            ],
            [
                'empleado_data' => [
                    'dni' => '11223344',
                    'nombres' => 'Ana',
                    'apellidos' => 'Supervisor Martinez',
                    'telefono' => '999-000-003',
                    'email' => 'supervisor@gestion.com',
                    'especialidad' => 'Supervisión',
                ],
                'usuario_data' => [
                    'username' => 'supervisor',
                    'email' => 'supervisor@gestion.com',
                    'password' => 'Super123!',
                    'tipo_usuario' => 'Supervisor',
                ],
                'rol' => 'Supervisor',
            ],
            [
                'empleado_data' => [
                    'dni' => '55667788',
                    'nombres' => 'Miguel',
                    'apellidos' => 'Técnico Senior Rodriguez',
                    'telefono' => '999-000-004',
                    'email' => 'tecnicosr@gestion.com',
                    'especialidad' => 'Reparación de Equipos',
                ],
                'usuario_data' => [
                    'username' => 'tecnicosr',
                    'email' => 'tecnicosr@gestion.com',
                    'password' => 'TecSr123!',
                    'tipo_usuario' => 'Técnico Senior',
                ],
                'rol' => 'Técnico Senior',
            ],
            [
                'empleado_data' => [
                    'dni' => '99887766',
                    'nombres' => 'Luis',
                    'apellidos' => 'Técnico Gomez',
                    'telefono' => '999-000-005',
                    'email' => 'tecnico@gestion.com',
                    'especialidad' => 'Reparación Básica',
                ],
                'usuario_data' => [
                    'username' => 'tecnico',
                    'email' => 'tecnico@gestion.com',
                    'password' => 'Tecnico123!',
                    'tipo_usuario' => 'Técnico',
                ],
                'rol' => 'Técnico',
            ],
            [
                'empleado_data' => [
                    'dni' => '33445566',
                    'nombres' => 'María',
                    'apellidos' => 'Vendedor Senior Perez',
                    'telefono' => '999-000-006',
                    'email' => 'vendedorsr@gestion.com',
                    'especialidad' => 'Ventas Avanzadas',
                ],
                'usuario_data' => [
                    'username' => 'vendedorsr',
                    'email' => 'vendedorsr@gestion.com',
                    'password' => 'VentSr123!',
                    'tipo_usuario' => 'Vendedor Senior',
                ],
                'rol' => 'Vendedor Senior',
            ],
            [
                'empleado_data' => [
                    'dni' => '77889900',
                    'nombres' => 'Pedro',
                    'apellidos' => 'Vendedor Silva',
                    'telefono' => '999-000-007',
                    'email' => 'vendedor@gestion.com',
                    'especialidad' => 'Ventas',
                ],
                'usuario_data' => [
                    'username' => 'vendedor',
                    'email' => 'vendedor@gestion.com',
                    'password' => 'Vendedor123!',
                    'tipo_usuario' => 'Vendedor',
                ],
                'rol' => 'Vendedor',
            ],
            [
                'empleado_data' => [
                    'dni' => '44556677',
                    'nombres' => 'Sofia',
                    'apellidos' => 'Empleado Torres',
                    'telefono' => '999-000-008',
                    'email' => 'empleado@gestion.com',
                    'especialidad' => 'General',
                ],
                'usuario_data' => [
                    'username' => 'empleado',
                    'email' => 'empleado@gestion.com',
                    'password' => 'Empleado123!',
                    'tipo_usuario' => 'Empleado',
                ],
                'rol' => 'Empleado',
            ],
        ];

        foreach ($usuarios as $userData) {
            // Verificar si el usuario ya existe
            $existingUsuario = Usuario::where('username', $userData['usuario_data']['username'])->first();
            if ($existingUsuario) {
                $this->command->info("⚠️ Usuario ya existe: {$userData['usuario_data']['username']}");
                continue;
            }

            // Verificar si el empleado ya existe
            $existingEmpleado = Empleado::where('dni', $userData['empleado_data']['dni'])->first();
            if ($existingEmpleado) {
                $empleado = $existingEmpleado;
                $this->command->info("⚠️ Empleado ya existe: {$userData['empleado_data']['dni']}");
            } else {
                // Crear empleado
                $empleado = Empleado::create(array_merge($userData['empleado_data'], [
                    'fecha_contratacion' => now(),
                    'activo' => true,
                ]));
            }

            // Crear usuario con hash manual para evitar problemas
            $usuario = Usuario::create([
                'empleado_id' => $empleado->id,
                'username' => $userData['usuario_data']['username'],
                'email' => $userData['usuario_data']['email'],
                'password' => Hash::make($userData['usuario_data']['password']), // Hash manual
                'tipo_usuario' => $userData['usuario_data']['tipo_usuario'],
                'activo' => true,
            ]);

            // Asignar rol
            $role = Role::where('name', $userData['rol'])->first();
            if ($role) {
                $usuario->assignRole($role);
            }

            $this->command->info("✅ Usuario creado: {$userData['usuario_data']['username']} / {$userData['usuario_data']['password']}");
        }
    }
}