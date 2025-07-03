<?php
// database/seeders/ClienteSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nombre' => 'Juan',
                'apellido' => 'Pérez García',
                'tipo_documento' => 'DNI',
                'documento' => '12345678',
                'telefono' => '999111222',
                'email' => 'juan.perez@email.com',
                'direccion' => 'Av. Las Flores 123, Lima',
                'activo' => true
            ],
            [
                'nombre' => 'María',
                'apellido' => 'López Martínez',
                'tipo_documento' => 'DNI',
                'documento' => '23456789',
                'telefono' => '999333444',
                'email' => 'maria.lopez@email.com',
                'direccion' => 'Jr. Los Olivos 456, Lima',
                'activo' => true
            ],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Rodríguez Silva',
                'tipo_documento' => 'DNI',
                'documento' => '34567890',
                'telefono' => '999555666',
                'email' => 'carlos.rodriguez@email.com',
                'direccion' => 'Calle Las Rosas 789, Lima',
                'activo' => true
            ],
            [
                'nombre' => 'Ana',
                'apellido' => 'Fernández Ruiz',
                'tipo_documento' => 'DNI',
                'documento' => '45678901',
                'telefono' => '999777888',
                'email' => 'ana.fernandez@email.com',
                'direccion' => 'Av. Los Pinos 321, Lima',
                'activo' => true
            ],
            [
                'nombre' => 'Luis',
                'apellido' => 'Sánchez Díaz',
                'tipo_documento' => 'DNI',
                'documento' => '56789012',
                'telefono' => '999999000',
                'email' => 'luis.sanchez@email.com',
                'direccion' => 'Jr. Las Palmeras 654, Lima',
                'activo' => true
            ],
            [
                'nombre' => 'Empresa ABC SAC',
                'apellido' => null,
                'tipo_documento' => 'RUC',
                'documento' => '20123456789',
                'telefono' => '014567890',
                'email' => 'contacto@empresaabc.com',
                'direccion' => 'Av. Industrial 1234, Lima',
                'activo' => true
            ],
            [
                'nombre' => 'Pedro',
                'apellido' => 'Morales Castro',
                'tipo_documento' => 'Pasaporte',
                'documento' => 'AB1234567',
                'telefono' => '999888777',
                'email' => 'pedro.morales@email.com',
                'direccion' => 'Calle Internacional 987, Lima',
                'activo' => true
            ],
            [
                'nombre' => 'Rosa',
                'apellido' => 'Torres Vega',
                'tipo_documento' => 'DNI',
                'documento' => '67890123',
                'telefono' => '999666555',
                'email' => 'rosa.torres@email.com',
                'direccion' => 'Av. Los Jazmines 159, Lima',
                'activo' => false // Cliente inactivo para pruebas
            ]
        ];

        foreach ($clientes as $clienteData) {
            Cliente::create($clienteData);
        }

        $this->command->info('✅ ' . count($clientes) . ' clientes creados con éxito');
    }
}