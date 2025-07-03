<?php
namespace App\Policies;

use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulePolicy
{
    use HandlesAuthorization;

    /**
     * Acceso al módulo de clientes
     */
    public function accessClientes(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar'
        ]);
    }

    /**
     * Acceso al módulo de productos
     */
    public function accessProductos(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'productos.ver',
            'productos.crear',
            'productos.editar',
            'productos.eliminar'
        ]);
    }

    /**
     * Acceso al módulo de inventario
     */
    public function accessInventario(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'inventario.ver',
            'inventario.entradas',
            'inventario.ajustes',
            'inventario.transferencias'
        ]);
    }

    /**
     * Acceso al módulo de reparaciones
     */
    public function accessReparaciones(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'reparaciones.ver',
            'reparaciones.crear',
            'reparaciones.diagnosticar',
            'reparaciones.reparar'
        ]);
    }

    /**
     * Acceso al módulo de ventas
     */
    public function accessVentas(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'ventas.ver',
            'ventas.crear',
            'ventas.editar',
            'ventas.cancelar'
        ]);
    }

    /**
     * Acceso al módulo de empleados
     */
    public function accessEmpleados(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'empleados.ver',
            'empleados.crear',
            'empleados.editar',
            'empleados.eliminar'
        ]);
    }

    /**
     * Acceso al módulo de proveedores
     */
    public function accessProveedores(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'proveedores.ver',
            'proveedores.crear',
            'proveedores.editar',
            'proveedores.eliminar'
        ]);
    }

    /**
     * Acceso al módulo de reportes
     */
    public function accessReportes(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'reportes.ventas',
            'reportes.inventario',
            'reportes.reparaciones',
            'reportes.financieros',
            'reportes.empleados'
        ]);
    }

    /**
     * Acceso al módulo de configuración
     */
    public function accessConfiguracion(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'configuracion.ver',
            'configuracion.editar',
            'configuracion.respaldos',
            'configuracion.mantenimiento'
        ]);
    }

    /**
     * Acceso al módulo de auditoría
     */
    public function accessAuditoria(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'auditoria.ver',
            'auditoria.exportar',
            'seguridad.ver',
            'seguridad.gestionar'
        ]);
    }

    /**
     * Verificar acceso de escritura a un módulo
     */
    public function canWrite(Usuario $user, string $module): bool
    {
        $writePermissions = [
            'clientes' => ['clientes.crear', 'clientes.editar'],
            'productos' => ['productos.crear', 'productos.editar'],
            'inventario' => ['inventario.entradas', 'inventario.ajustes'],
            'reparaciones' => ['reparaciones.crear', 'reparaciones.reparar'],
            'ventas' => ['ventas.crear', 'ventas.editar'],
            'empleados' => ['empleados.crear', 'empleados.editar'],
            'proveedores' => ['proveedores.crear', 'proveedores.editar'],
        ];

        if (!isset($writePermissions[$module])) {
            return false;
        }

        return $user->hasAnyPermission($writePermissions[$module]);
    }

    /**
     * Verificar acceso de eliminación a un módulo
     */
    public function canDelete(Usuario $user, string $module): bool
    {
        $deletePermissions = [
            'clientes' => 'clientes.eliminar',
            'productos' => 'productos.eliminar',
            'reparaciones' => 'reparaciones.cancelar',
            'ventas' => 'ventas.cancelar',
            'empleados' => 'empleados.eliminar',
            'proveedores' => 'proveedores.eliminar',
        ];

        if (!isset($deletePermissions[$module])) {
            return false;
        }

        return $user->hasPermissionTo($deletePermissions[$module]);
    }

    /**
     * Verificar acceso a funciones avanzadas de un módulo
     */
    public function canAccessAdvanced(Usuario $user, string $module): bool
    {
        $advancedPermissions = [
            'clientes' => ['clientes.exportar', 'clientes.importar'],
            'productos' => ['productos.precios'],
            'inventario' => ['inventario.transferencias'],
            'reparaciones' => ['reparaciones.asignar', 'reparaciones.garantias'],
            'ventas' => ['ventas.descuentos', 'ventas.credito'],
            'reportes' => ['reportes.exportar'],
        ];

        if (!isset($advancedPermissions[$module])) {
            return false;
        }

        return $user->hasAnyPermission($advancedPermissions[$module]);
    }
}