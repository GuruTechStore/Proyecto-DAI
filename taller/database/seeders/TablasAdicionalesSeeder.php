<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Notificacion;
use App\Models\Usuario;

class TablasAdicionalesSeeder extends Seeder
{
    public function run()
    {
        echo "🔧 Inicializando configuraciones del sistema...\n";
        $this->seedSettings();
        
        echo "📢 Creando notificaciones de bienvenida...\n";
        $this->seedNotificaciones();
        
        echo "✅ Seeder de tablas adicionales completado.\n";
    }

    private function seedSettings()
    {
        $createdCount = Setting::initializeDefaults();
        echo "✅ {$createdCount} configuraciones inicializadas\n";

        // Configuraciones específicas del taller
        $tallerSettings = [
            'taller_nombre' => [
                'value' => 'Taller de Reparaciones',
                'type' => 'string',
                'category' => 'empresa',
                'is_public' => true,
                'description' => 'Nombre del taller'
            ],
            'taller_especialidades' => [
                'value' => json_encode(['Celulares', 'Tablets', 'Laptops', 'Electrónicos']),
                'type' => 'json',
                'category' => 'empresa',
                'is_public' => true,
                'description' => 'Especialidades del taller'
            ],
            'reparaciones_garantia_dias_defecto' => [
                'value' => 30,
                'type' => 'integer',
                'category' => 'reparaciones',
                'is_public' => false,
                'description' => 'Días de garantía por defecto para reparaciones'
            ],
            'ventas_iva_porcentaje' => [
                'value' => 18,
                'type' => 'integer',
                'category' => 'ventas',
                'is_public' => false,
                'description' => 'Porcentaje de IVA para ventas'
            ],
            'productos_codigo_prefijo' => [
                'value' => 'PROD',
                'type' => 'string',
                'category' => 'productos',
                'is_public' => false,
                'description' => 'Prefijo para códigos de productos'
            ],
        ];

        $extraCount = 0;
        foreach ($tallerSettings as $key => $config) {
            if (!Setting::has($key)) {
                Setting::create([
                    'key' => $key,
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'category' => $config['category'],
                    'is_public' => $config['is_public'],
                    'description' => $config['description']
                ]);
                $extraCount++;
            }
        }
        
        echo "✅ {$extraCount} configuraciones adicionales del taller creadas\n";
    }

    private function seedNotificaciones()
    {
        // Obtener usuarios administradores
        $admins = Usuario::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Gerente']);
        })->get();

        if ($admins->isEmpty()) {
            echo "⚠️ No se encontraron usuarios administradores para crear notificaciones\n";
            return;
        }

        $notificacionesCreadas = 0;

        foreach ($admins as $admin) {
            // Notificación de bienvenida
            Notificacion::crear(
                Notificacion::TIPO_SISTEMA,
                $admin->id,
                '¡Bienvenido al Sistema!',
                'El sistema de gestión del taller ha sido configurado correctamente. Puedes comenzar a gestionar clientes, productos, reparaciones y ventas.',
                [
                    'prioridad' => Notificacion::PRIORIDAD_NORMAL,
                    'entidad' => 'sistema',
                    'enlace' => route('dashboard')
                ]
            );

            // Notificación sobre configuración inicial
            Notificacion::crear(
                Notificacion::TIPO_SISTEMA,
                $admin->id,
                'Configuración Inicial Completada',
                'Se han configurado los ajustes básicos del sistema. Revisa la configuración en el panel de administración para personalizar según tus necesidades.',
                [
                    'prioridad' => Notificacion::PRIORIDAD_BAJA,
                    'entidad' => 'configuracion',
                    'enlace' => route('admin.settings')
                ]
            );

            $notificacionesCreadas += 2;
        }

        // Notificación general sobre el sistema
        if ($admins->count() > 0) {
            $primerAdmin = $admins->first();
            
            Notificacion::crear(
                Notificacion::TIPO_SISTEMA,
                $primerAdmin->id,
                'Sistema Listo para Usar',
                'Todas las tablas y configuraciones han sido inicializadas. El sistema está listo para su uso en producción.',
                [
                    'prioridad' => Notificacion::PRIORIDAD_ALTA,
                    'entidad' => 'sistema'
                ]
            );
            
            $notificacionesCreadas++;
        }

        echo "✅ {$notificacionesCreadas} notificaciones de bienvenida creadas\n";
    }
}