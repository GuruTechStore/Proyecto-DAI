<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Proveedor;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'nombre' => 'TechParts Internacional S.A.',
                'ruc' => '20501234567',
                'contacto' => 'Carlos Mendoza',
                'telefono' => '01-456-7890',
                'email' => 'ventas@techparts.com',
                'direccion' => 'Av. Industrial 456, Lima',
                'banco' => 'BCP',
                'numero_cuenta' => '123-456789-0-12',
                'tipo_cuenta' => 'corriente',
                'observaciones' => 'Proveedor principal de pantallas',
                'activo' => true,
            ],
            [
                'nombre' => 'Mobile Solutions Peru',
                'ruc' => '20512345678',
                'contacto' => 'Ana García',
                'telefono' => '01-234-5678',
                'email' => 'info@mobilesolutions.pe',
                'direccion' => 'Jr. Tecnología 789, Lima',
                'banco' => 'Interbank',
                'numero_cuenta' => '200-3456789012',
                'tipo_cuenta' => 'ahorros',
                'observaciones' => 'Especialistas en baterías',
                'activo' => true,
            ],
            [
                'nombre' => 'Importaciones TechRepair',
                'ruc' => '20523456789',
                'contacto' => 'Luis Rodríguez',
                'telefono' => '01-345-6789',
                'email' => 'compras@importechrepair.com',
                'direccion' => 'Av. Electrónica 321, Lima',
                'activo' => true,
            ],
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }

        $this->command->info('✅ ' . count($proveedores) . ' proveedores creados');
    }
}