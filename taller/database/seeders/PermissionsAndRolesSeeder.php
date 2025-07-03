<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Usuario;

class PermissionsAndRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        echo "🚀 Creando TODOS los permisos del sistema...\n\n";

        // TODOS LOS PERMISOS DEL SISTEMA
        $permisos = [
            // Dashboard
            'dashboard.ver',
            
            // Clientes
            'clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar', 'clientes.exportar',
            
            // Productos
            'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar', 'productos.precios', 'productos.exportar',
            
            // Categorías
            'categorias.ver', 'categorias.crear', 'categorias.editar', 'categorias.eliminar',
            
            // Proveedores
            'proveedores.ver', 'proveedores.crear', 'proveedores.editar', 'proveedores.eliminar', 'proveedores.exportar',
            
            // Reparaciones
            'reparaciones.ver', 'reparaciones.crear', 'reparaciones.editar', 'reparaciones.eliminar',
            'reparaciones.asignar', 'reparaciones.diagnosticar', 'reparaciones.reparar', 'reparaciones.entregar',
            'reparaciones.exportar',
            
            // Ventas
            'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.eliminar',
            'ventas.descuentos', 'ventas.credito', 'ventas.anular', 'ventas.exportar',
            
            // Empleados
            'empleados.ver', 'empleados.crear', 'empleados.editar', 'empleados.eliminar',
            'empleados.activar', 'empleados.exportar',
            
            // Usuarios
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',
            'usuarios.roles', 'usuarios.permisos', 'usuarios.activar',
            
            // Reportes
            'reportes.ver', 'reportes.ventas', 'reportes.productos', 'reportes.reparaciones',
            'reportes.clientes', 'reportes.empleados', 'reportes.financieros', 'reportes.exportar',
            
            // Inventario
            'inventario.ver', 'inventario.entradas', 'inventario.ajustes', 'inventario.transferencias',
            'inventario.reportes',
            
            // Configuración
            'configuracion.ver', 'configuracion.editar', 'configuracion.sistema',
            'configuracion.respaldos', 'configuracion.logs',
            
            // Auditoría
            'auditoria.ver', 'seguridad.gestionar', 'seguridad.logs',
        ];

        // Crear todos los permisos
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web'
            ]);
            echo "✅ {$permiso}\n";
        }

        $totalPermisos = count($permisos);
        echo "\n📊 TOTAL PERMISOS CREADOS: {$totalPermisos}\n\n";

        // Crear roles si no existen
        $rolesData = [
            'Super Admin' => $permisos, // Todos los permisos
            'Admin' => array_filter($permisos, function($p) {
                return !in_array($p, ['usuarios.eliminar', 'configuracion.sistema', 'seguridad.gestionar']);
            }),
            'Gerente' => [
                'dashboard.ver',
                'clientes.ver', 'clientes.crear', 'clientes.editar',
                'productos.ver', 'productos.crear', 'productos.editar', 'productos.precios',
                'categorias.ver', 'categorias.crear', 'categorias.editar',
                'proveedores.ver', 'proveedores.crear', 'proveedores.editar',
                'reparaciones.ver', 'reparaciones.crear', 'reparaciones.editar', 'reparaciones.asignar',
                'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.descuentos',
                'empleados.ver', 'empleados.crear', 'empleados.editar',
                'reportes.ver', 'reportes.ventas', 'reportes.productos', 'reportes.reparaciones', 'reportes.empleados',
                'inventario.ver', 'inventario.entradas', 'inventario.ajustes',
            ],
            'Vendedor' => [
                'dashboard.ver',
                'clientes.ver', 'clientes.crear', 'clientes.editar',
                'productos.ver',
                'ventas.ver', 'ventas.crear', 'ventas.editar',
                'reportes.ver', 'reportes.ventas',
            ],
            'Tecnico' => [
                'dashboard.ver',
                'clientes.ver',
                'productos.ver',
                'reparaciones.ver', 'reparaciones.editar', 'reparaciones.diagnosticar', 'reparaciones.reparar',
                'reportes.ver', 'reportes.reparaciones',
            ],
        ];

        // Crear roles
        foreach ($rolesData as $roleName => $rolePermisos) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
            
            // Asignar permisos al rol
            $role->syncPermissions($rolePermisos);
            $cantidadPermisos = count($rolePermisos);
            echo "✅ Rol creado: {$roleName} ({$cantidadPermisos} permisos)\n";
        }

        echo "\n👤 ASIGNANDO PERMISOS AL USUARIO ACTUAL...\n";

        // Buscar el primer usuario
        $user = Usuario::first();
        
        if ($user) {
            if (!$user->hasRole('Super Admin')) {
                $user->assignRole('Super Admin');
                echo "✅ Rol 'Super Admin' asignado a: {$user->name}\n";
            } else {
                echo "✅ Usuario ya tiene rol 'Super Admin'\n";
            }

            // Limpiar caché nuevamente
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            echo "\n🔍 VERIFICANDO PERMISOS DEL USUARIO:\n";
            echo "- dashboard.ver: " . ($user->can('dashboard.ver') ? '✅' : '❌') . "\n";
            echo "- clientes.ver: " . ($user->can('clientes.ver') ? '✅' : '❌') . "\n";
            echo "- productos.ver: " . ($user->can('productos.ver') ? '✅' : '❌') . "\n";
            echo "- ventas.ver: " . ($user->can('ventas.ver') ? '✅' : '❌') . "\n";
            echo "- reparaciones.ver: " . ($user->can('reparaciones.ver') ? '✅' : '❌') . "\n";
            echo "- empleados.ver: " . ($user->can('empleados.ver') ? '✅' : '❌') . "\n";
            echo "- reportes.ver: " . ($user->can('reportes.ver') ? '✅' : '❌') . "\n";

            echo "\n🎉 ¡SISTEMA DE PERMISOS CONFIGURADO COMPLETAMENTE!\n";
            echo "🔗 Ahora puedes acceder a TODOS los módulos del sistema.\n\n";
        } else {
            echo "❌ No se encontró ningún usuario en la base de datos\n";
            echo "💡 Crea un usuario primero y luego ejecuta este seeder nuevamente\n\n";
        }
    }
}