<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Inicializar configuraciones por defecto
        Setting::initializeDefaults();

        // Configuraciones específicas para el taller
        $tallerSettings = [
            // Configuración del taller
            'taller_nombre' => [
                'value' => 'Taller de Reparaciones TechFix',
                'category' => 'taller',
                'type' => 'string',
                'description' => 'Nombre del taller',
                'is_public' => true
            ],
            'taller_especialidad' => [
                'value' => 'Reparación de dispositivos electrónicos',
                'category' => 'taller',
                'type' => 'string',
                'description' => 'Especialidad del taller',
                'is_public' => true
            ],
            'taller_horario_lunes_viernes' => [
                'value' => '08:00 - 18:00',
                'category' => 'taller',
                'type' => 'string',
                'description' => 'Horario de lunes a viernes',
                'is_public' => true
            ],
            'taller_horario_sabado' => [
                'value' => '08:00 - 13:00',
                'category' => 'taller',
                'type' => 'string',
                'description' => 'Horario de sábado',
                'is_public' => true
            ],
            'taller_horario_domingo' => [
                'value' => 'Cerrado',
                'category' => 'taller',
                'type' => 'string',
                'description' => 'Horario de domingo',
                'is_public' => true
            ],

            // Configuración de reparaciones
            'reparacion_tiempo_estimado_default' => [
                'value' => 3,
                'category' => 'reparaciones',
                'type' => 'integer',
                'description' => 'Tiempo estimado por defecto en días',
                'is_public' => false
            ],
            'reparacion_garantia_default' => [
                'value' => 30,
                'category' => 'reparaciones',
                'type' => 'integer',
                'description' => 'Garantía por defecto en días',
                'is_public' => true
            ],
            'reparacion_estados_disponibles' => [
                'value' => ['recibido', 'diagnostico', 'esperando_repuestos', 'en_reparacion', 'completado', 'entregado', 'cancelado'],
                'category' => 'reparaciones',
                'type' => 'json',
                'description' => 'Estados disponibles para reparaciones',
                'is_public' => false
            ],
            'reparacion_notificar_cambio_estado' => [
                'value' => true,
                'category' => 'reparaciones',
                'type' => 'boolean',
                'description' => 'Notificar cambios de estado al cliente',
                'is_public' => false
            ],

            // Configuración de inventario
            'inventario_alerta_stock_minimo' => [
                'value' => true,
                'category' => 'inventario',
                'type' => 'boolean',
                'description' => 'Activar alertas de stock mínimo',
                'is_public' => false
            ],
            'inventario_stock_minimo_global' => [
                'value' => 5,
                'category' => 'inventario',
                'type' => 'integer',
                'description' => 'Stock mínimo global por defecto',
                'is_public' => false
            ],
            'inventario_metodo_valuacion' => [
                'value' => 'promedio',
                'category' => 'inventario',
                'type' => 'string',
                'description' => 'Método de valuación (promedio, fifo, lifo)',
                'is_public' => false
            ],

            // Configuración de ventas
            'ventas_impuesto_default' => [
                'value' => 18.00,
                'category' => 'ventas',
                'type' => 'float',
                'description' => 'Impuesto por defecto (IGV)',
                'is_public' => false
            ],
            'ventas_permitir_descuentos' => [
                'value' => true,
                'category' => 'ventas',
                'type' => 'boolean',
                'description' => 'Permitir descuentos en ventas',
                'is_public' => false
            ],
            'ventas_descuento_maximo' => [
                'value' => 20.00,
                'category' => 'ventas',
                'type' => 'float',
                'description' => 'Descuento máximo permitido (%)',
                'is_public' => false
            ],
            'ventas_generar_factura_automatica' => [
                'value' => false,
                'category' => 'ventas',
                'type' => 'boolean',
                'description' => 'Generar factura automáticamente',
                'is_public' => false
            ],

            // Configuración de reportes
            'reportes_items_por_pagina' => [
                'value' => 25,
                'category' => 'reportes',
                'type' => 'integer',
                'description' => 'Items por página en reportes',
                'is_public' => false
            ],
            'reportes_formatos_disponibles' => [
                'value' => ['pdf', 'excel', 'csv'],
                'category' => 'reportes',
                'type' => 'json',
                'description' => 'Formatos disponibles para exportar',
                'is_public' => false
            ],

            // Configuración de notificaciones
            'notificaciones_email_nuevas_reparaciones' => [
                'value' => true,
                'category' => 'notificaciones',
                'type' => 'boolean',
                'description' => 'Email para nuevas reparaciones',
                'is_public' => false
            ],
            'notificaciones_email_ventas_diarias' => [
                'value' => false,
                'category' => 'notificaciones',
                'type' => 'boolean',
                'description' => 'Email con resumen de ventas diarias',
                'is_public' => false
            ],
            'notificaciones_email_stock_bajo' => [
                'value' => true,
                'category' => 'notificaciones',
                'type' => 'boolean',
                'description' => 'Email para alertas de stock bajo',
                'is_public' => false
            ],
            'notificaciones_whatsapp_habilitado' => [
                'value' => false,
                'category' => 'notificaciones',
                'type' => 'boolean',
                'description' => 'Notificaciones por WhatsApp habilitadas',
                'is_public' => false
            ],

            // Configuración de UI
            'ui_tema_default' => [
                'value' => 'light',
                'category' => 'ui',
                'type' => 'string',
                'description' => 'Tema por defecto de la interfaz',
                'is_public' => true
            ],
            'ui_sidebar_colapsado' => [
                'value' => false,
                'category' => 'ui',
                'type' => 'boolean',
                'description' => 'Sidebar colapsado por defecto',
                'is_public' => true
            ],
            'ui_mostrar_ayuda_tooltips' => [
                'value' => true,
                'category' => 'ui',
                'type' => 'boolean',
                'description' => 'Mostrar tooltips de ayuda',
                'is_public' => true
            ],

            // Configuración de facturación
            'facturacion_serie_boleta' => [
                'value' => 'B001',
                'category' => 'facturacion',
                'type' => 'string',
                'description' => 'Serie para boletas',
                'is_public' => false
            ],
            'facturacion_serie_factura' => [
                'value' => 'F001',
                'category' => 'facturacion',
                'type' => 'string',
                'description' => 'Serie para facturas',
                'is_public' => false
            ],
            'facturacion_numero_actual_boleta' => [
                'value' => 1,
                'category' => 'facturacion',
                'type' => 'integer',
                'description' => 'Número actual de boletas',
                'is_public' => false
            ],
            'facturacion_numero_actual_factura' => [
                'value' => 1,
                'category' => 'facturacion',
                'type' => 'integer',
                'description' => 'Número actual de facturas',
                'is_public' => false
            ],

            // Configuración de integración
            'integracion_sunat_habilitada' => [
                'value' => false,
                'category' => 'integracion',
                'type' => 'boolean',
                'description' => 'Integración con SUNAT habilitada',
                'is_public' => false
            ],
            'integracion_whatsapp_api_token' => [
                'value' => '',
                'category' => 'integracion',
                'type' => 'string',
                'description' => 'Token de API de WhatsApp',
                'is_public' => false
            ],
        ];

        // Crear configuraciones específicas del taller
        foreach ($tallerSettings as $key => $config) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $config['value'],
                    'category' => $config['category'],
                    'type' => $config['type'],
                    'description' => $config['description'],
                    'is_public' => $config['is_public']
                ]
            );
        }

        $this->command->info(' Configuraciones del sistema creadas exitosamente');
    }
}