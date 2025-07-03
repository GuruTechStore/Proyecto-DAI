<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuracion;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $configuraciones = [
            [
                'clave' => 'empresa_nombre',
                'valor' => 'Tech Repair Center',
                'tipo' => 'string',
                'descripcion' => 'Nombre de la empresa'
            ],
            [
                'clave' => 'empresa_ruc',
               'valor' => '20123456789',
               'tipo' => 'string',
               'descripcion' => 'RUC de la empresa'
           ],
           [
               'clave' => 'empresa_direccion',
               'valor' => 'Av. Principal 123, Lima, Perú',
               'tipo' => 'string',
               'descripcion' => 'Dirección de la empresa'
           ],
           [
               'clave' => 'empresa_telefono',
               'valor' => '01-234-5678',
               'tipo' => 'string',
               'descripcion' => 'Teléfono principal'
           ],
           [
               'clave' => 'empresa_email',
               'valor' => 'info@techrepair.com',
               'tipo' => 'string',
               'descripcion' => 'Email de contacto'
           ],
           [
               'clave' => 'moneda',
               'valor' => 'PEN',
               'tipo' => 'string',
               'descripcion' => 'Moneda del sistema'
           ],
           [
               'clave' => 'simbolo_moneda',
               'valor' => 'S/',
               'tipo' => 'string',
               'descripcion' => 'Símbolo de moneda'
           ],
           [
               'clave' => 'igv',
               'valor' => '18',
               'tipo' => 'integer',
               'descripcion' => 'Porcentaje de IGV'
           ],
           [
               'clave' => 'formato_codigo_reparacion',
               'valor' => 'REP-{YEAR}{MONTH}{NUMBER}',
               'tipo' => 'string',
               'descripcion' => 'Formato para códigos de reparación'
           ],
           [
               'clave' => 'formato_codigo_venta',
               'valor' => 'VTA-{YEAR}{MONTH}{NUMBER}',
               'tipo' => 'string',
               'descripcion' => 'Formato para códigos de venta'
           ],
           [
               'clave' => 'dias_garantia_reparacion',
               'valor' => '30',
               'tipo' => 'integer',
               'descripcion' => 'Días de garantía por defecto en reparaciones'
           ],
           [
               'clave' => 'permitir_stock_negativo',
               'valor' => 'false',
               'tipo' => 'boolean',
               'descripcion' => 'Permitir ventas con stock negativo'
           ],
       ];

       foreach ($configuraciones as $config) {
           Configuracion::updateOrCreate(
               ['clave' => $config['clave']],
               $config
           );
       }

       $this->command->info('✅ ' . count($configuraciones) . ' configuraciones creadas');
   }
}