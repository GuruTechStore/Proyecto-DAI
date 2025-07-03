<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Empleado;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuarioAdminSeeder extends Seeder
{
    public function run()
    {
        // Verificar si el usuario admin ya existe
        $existingUsuario = Usuario::where('username', 'admin')->first();
        if ($existingUsuario) {
            $this->command->info('✅ Usuario administrador ya existe: admin');
            return;
        }

        // Verificar si el empleado ya existe
        $existingEmpleado = Empleado::where('dni', '12345678')->first();
        if ($existingEmpleado) {
            $empleadoAdmin = $existingEmpleado;
        } else {
            // Crear empleado administrador (solo con columnas básicas)
            $empleadoAdmin = Empleado::create([
                'dni' => '12345678',
                'nombres' => 'Administrador',
                'apellidos' => 'Del Sistema',
                'telefono' => '999-000-001',
                'email' => 'admin@gestion.com',
                'especialidad' => 'Administración',
                'fecha_contratacion' => now(),
            ]);
        }

        // Crear usuario administrador (solo con columnas básicas)
        $usuarioAdmin = Usuario::create([
            'empleado_id' => $empleadoAdmin->id,
            'username' => 'admin',
            'email' => 'admin@gestion.com',
            'password' => Hash::make('Admin123!'),
            'tipo_usuario' => 'Super Admin',
        ]);

        // Asignar rol Super Admin
        $roleSuperAdmin = Role::findByName('Super Admin');
        $usuarioAdmin->assignRole($roleSuperAdmin);

        $this->command->info('✅ Usuario administrador creado: admin / Admin123!');
    }
}
