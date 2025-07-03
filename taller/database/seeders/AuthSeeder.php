<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AuthSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Iniciando creación del sistema de autenticación completo...');
        
        // Ejecutar seeders en orden específico
        $this->call([
            RolesPermisosSeeder::class,
            UsuarioAdminSeeder::class,
            EmpleadosTestSeeder::class,
        ]);

        $this->command->info('✅ Sistema de autenticación completado exitosamente');
        $this->command->info('');
        $this->command->info('👥 USUARIOS CREADOS:');
        $this->command->info('   admin / Admin123! (Super Admin)');
        $this->command->info('   gerente / Gerente123! (Gerente)');
        $this->command->info('   supervisor / Super123! (Supervisor)');
        $this->command->info('   tecnicosr / TecSr123! (Técnico Senior)');
        $this->command->info('   tecnico / Tecnico123! (Técnico)');
        $this->command->info('   vendedorsr / VentSr123! (Vendedor Senior)');
        $this->command->info('   vendedor / Vendedor123! (Vendedor)');
        $this->command->info('   empleado / Empleado123! (Empleado)');
    }
}