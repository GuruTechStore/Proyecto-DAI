<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Reparacion;
use App\Models\Venta;
use App\Models\Empleado;
use App\Models\Proveedor;
use App\Policies\GeneralPolicy;
use App\Policies\UsuarioPolicy;
use App\Policies\ModulePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        Usuario::class => UsuarioPolicy::class,
        Cliente::class => GeneralPolicy::class,
        Producto::class => GeneralPolicy::class,
        Reparacion::class => GeneralPolicy::class,
        Venta::class => GeneralPolicy::class,
        Empleado::class => GeneralPolicy::class,
        Proveedor::class => GeneralPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        // === GATES PARA MÓDULOS ===
        Gate::define('access-clientes', [ModulePolicy::class, 'accessClientes']);
        Gate::define('access-productos', [ModulePolicy::class, 'accessProductos']);
        Gate::define('access-inventario', [ModulePolicy::class, 'accessInventario']);
        Gate::define('access-reparaciones', [ModulePolicy::class, 'accessReparaciones']);
        Gate::define('access-ventas', [ModulePolicy::class, 'accessVentas']);
        Gate::define('access-empleados', [ModulePolicy::class, 'accessEmpleados']);
        Gate::define('access-proveedores', [ModulePolicy::class, 'accessProveedores']);
        Gate::define('access-reportes', [ModulePolicy::class, 'accessReportes']);
        Gate::define('access-configuracion', [ModulePolicy::class, 'accessConfiguracion']);
        Gate::define('access-auditoria', [ModulePolicy::class, 'accessAuditoria']);

        // === GATES PARA OPERACIONES GENERALES ===
        Gate::define('is-system-admin', [GeneralPolicy::class, 'isSystemAdmin']);
        Gate::define('can-manage-users', [GeneralPolicy::class, 'canManageUsers']);
        Gate::define('can-access-reports', [GeneralPolicy::class, 'canAccessReports']);
        Gate::define('check-permission-level', [GeneralPolicy::class, 'checkPermissionLevel']);
        Gate::define('check-module-access', [GeneralPolicy::class, 'checkModuleAccess']);

        // === GATES ESPECÍFICOS POR ACCIÓN ===
        
        // Clientes
        Gate::define('create-cliente', function (Usuario $user) {
            return $user->hasPermissionTo('clientes.crear');
        });
        
        Gate::define('edit-cliente', function (Usuario $user) {
            return $user->hasPermissionTo('clientes.editar');
        });
        
        Gate::define('delete-cliente', function (Usuario $user) {
            return $user->hasPermissionTo('clientes.eliminar');
        });

        // Productos
        Gate::define('create-producto', function (Usuario $user) {
            return $user->hasPermissionTo('productos.crear');
        });
        
        Gate::define('edit-producto', function (Usuario $user) {
            return $user->hasPermissionTo('productos.editar');
        });
        
        Gate::define('manage-precios', function (Usuario $user) {
            return $user->hasPermissionTo('productos.precios');
        });

        // Inventario
        Gate::define('manage-inventario', function (Usuario $user) {
            return $user->hasAnyPermission([
                'inventario.entradas',
                'inventario.ajustes',
                'inventario.transferencias'
            ]);
        });

        // Reparaciones
        Gate::define('assign-reparacion', function (Usuario $user) {
            return $user->hasPermissionTo('reparaciones.asignar');
        });
        
        Gate::define('execute-reparacion', function (Usuario $user) {
            return $user->hasAnyPermission([
                'reparaciones.diagnosticar',
                'reparaciones.reparar'
            ]);
        });

        // Ventas
        Gate::define('process-venta', function (Usuario $user) {
            return $user->hasPermissionTo('ventas.crear');
        });
        
        Gate::define('apply-descuento', function (Usuario $user) {
            return $user->hasPermissionTo('ventas.descuentos');
        });
        
        Gate::define('venta-credito', function (Usuario $user) {
            return $user->hasPermissionTo('ventas.credito');
        });

        // Empleados y Usuarios
        Gate::define('manage-empleados', function (Usuario $user) {
            return $user->hasAnyPermission([
                'empleados.crear',
                'empleados.editar',
                'empleados.eliminar'
            ]);
        });
        
        Gate::define('assign-roles', function (Usuario $user) {
            return $user->hasPermissionTo('usuarios.roles');
        });

        // Reportes
        Gate::define('export-reports', function (Usuario $user) {
            return $user->hasPermissionTo('reportes.exportar');
        });
        
        Gate::define('financial-reports', function (Usuario $user) {
            return $user->hasPermissionTo('reportes.financieros');
        });

        // Configuración
        Gate::define('system-config', function (Usuario $user) {
            return $user->hasPermissionTo('configuracion.editar');
        });
        
        Gate::define('manage-backups', function (Usuario $user) {
            return $user->hasPermissionTo('configuracion.respaldos');
        });

        // Auditoría
        Gate::define('view-audit', function (Usuario $user) {
            return $user->hasPermissionTo('auditoria.ver');
        });
        
        Gate::define('manage-security', function (Usuario $user) {
            return $user->hasPermissionTo('seguridad.gestionar');
        });

        // === GATES PARA JERARQUÍA DE ROLES ===
        Gate::define('level-supervisor', function (Usuario $user) {
            return app(GeneralPolicy::class)->checkPermissionLevel($user, 6);
        });
        
        Gate::define('level-gerente', function (Usuario $user) {
            return app(GeneralPolicy::class)->checkPermissionLevel($user, 8);
        });
        
        Gate::define('level-admin', function (Usuario $user) {
            return app(GeneralPolicy::class)->checkPermissionLevel($user, 10);
        });

        // === GATE PARA SUPER USUARIO ===
        Gate::before(function (Usuario $user, $ability) {
            // Super Admin tiene acceso a todo
            if ($user->hasRole('Super Admin')) {
                return true;
            }
        });
    }
}