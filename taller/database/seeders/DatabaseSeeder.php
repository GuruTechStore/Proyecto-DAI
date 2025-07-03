<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando el seeding de la base de datos...');
        
        $this->call([
            // 1. Configuración inicial
            ConfiguracionSeeder::class,
            
            // 2. Roles y permisos (CORREGIDO: usar el nombre correcto)
            PermissionsAndRolesSeeder::class,
            
            // 3. Empleados y usuarios
            EmpleadoSeeder::class,
            
            // 4. Datos maestros
            CategoriaSeeder::class,
            ProveedorSeeder::class,
            
            // 5. Datos de prueba (solo en desarrollo)
            ...(app()->environment('local', 'development') ? [
                ClienteSeeder::class,
                ProductoSeeder::class,
                // ReparacionSeeder::class,  // Comentado si no existe
                // VentaSeeder::class,       // Comentado si no existe
            ] : [])
        ]);
        
        $this->command->info('✅ Seeding completado exitosamente');
        $this->command->info('');
        $this->command->info('📊 RESUMEN:');
        $this->command->info('   - Configuraciones del sistema creadas');
        $this->command->info('   - Roles y permisos configurados');
        $this->command->info('   - Usuario administrador creado');
        $this->command->info('   - Datos maestros iniciales');
        if (app()->environment('local', 'development')) {
            $this->command->info('   - Datos de prueba cargados (modo desarrollo)');
        }
    }
}