<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reparacion;
use App\Models\Equipo;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Usuario;

class ReparacionSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::limit(3)->get();
        $tecnicos = Usuario::whereHas('roles', function($q) {
            $q->whereIn('name', ['Técnico', 'Técnico Senior']);
        })->get();

        if ($clientes->isEmpty() || $tecnicos->isEmpty()) {
            $this->command->warn('⚠️ No hay clientes o técnicos disponibles');
            return;
        }

        $reparaciones = [
            [
                'cliente_id' => $clientes->first()->id,
                'equipo' => [
                    'tipo' => 'Smartphone',
                    'marca' => 'Apple',
                    'modelo' => 'iPhone 12',
                    'imei' => '123456789012345',
                ],
                'reparacion' => [
                    'codigo_ticket' => 'REP-202501001',
                    'estado' => 'recibido',
                    'problema_reportado' => 'Pantalla rota, no responde al tacto',
                    'costo_estimado' => 250.00,
                ],
            ],
            [
                'cliente_id' => $clientes->skip(1)->first()->id,
                'equipo' => [
                    'tipo' => 'Smartphone',
                    'marca' => 'Samsung',
                    'modelo' => 'Galaxy A50',
                    'imei' => '987654321098765',
                ],
                'reparacion' => [
                    'codigo_ticket' => 'REP-202501002',
                    'estado' => 'diagnosticando',
                    'problema_reportado' => 'No carga, batería se descarga rápidamente',
                    'diagnostico' => 'Batería defectuosa, puerto de carga con suciedad',
                    'costo_estimado' => 80.00,
                ],
            ],
            [
                'cliente_id' => $clientes->skip(2)->first()->id,
                'equipo' => [
                    'tipo' => 'Tablet',
                    'marca' => 'Apple',
                    'modelo' => 'iPad Air 4',
                ],
                'reparacion' => [
                    'codigo_ticket' => 'REP-202501003',
                    'estado' => 'completado',
                    'problema_reportado' => 'Botón de home no funciona',
                    'diagnostico' => 'Flex del botón home dañado',
                    'solucion' => 'Se reemplazó el flex del botón home',
                    'costo_estimado' => 120.00,
                    'costo_final' => 120.00,
                ],
            ],
        ];

        foreach ($reparaciones as $data) {
            // Crear equipo
            $equipo = Equipo::create(array_merge(
                $data['equipo'],
                ['cliente_id' => $data['cliente_id']]
            ));

            // Crear reparación
            $reparacion = Reparacion::create(array_merge(
                $data['reparacion'],
                [
                    'cliente_id' => $data['cliente_id'],
                    'equipo_id' => $equipo->id,
                    'empleado_id' => $tecnicos->random()->empleado_id,
                    'creado_por' => $tecnicos->first()->id,
                    'fecha_ingreso' => now(),
                ]
            ));
        }

        $this->command->info('✅ ' . count($reparaciones) . ' reparaciones creadas');
    }
}