<?php

namespace App\Policies;

use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class GeneralPolicy
{
    use HandlesAuthorization;

    /**
     * Verificar acceso a un módulo específico
     */
    public function checkModuleAccess(Usuario $user, string $module): bool
    {
        // Verificar si el usuario tiene permisos en el módulo
        $modulePermissions = $user->permissions()
            ->where('name', 'like', $module . '.%')
            ->count();

        return $modulePermissions > 0;
    }

    /**
     * Verificar nivel de permiso del usuario (simplificado)
     */
    public function checkPermissionLevel(Usuario $user, int $requiredLevel): bool
    {
        $userRole = $user->roles()->first();
        
        if (!$userRole) {
            return false;
        }

        // Jerarquía simplificada por nombre de rol
        $roleHierarchy = [
            'Super Admin' => 10,
            'Gerente' => 8,
            'Supervisor' => 6,
            'Técnico Senior' => 5,
            'Técnico' => 4,
            'Vendedor Senior' => 4,
            'Vendedor' => 3,
            'Empleado' => 2,
        ];

        $userLevel = $roleHierarchy[$userRole->name] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Verificar acceso a reportes
     */
    public function canAccessReports(Usuario $user, string $reportType): bool
    {
        // Verificar permiso específico del reporte
        $permissionName = 'reportes.' . $reportType;
        
        return $user->hasPermissionTo($permissionName);
    }

    /**
     * Verificar si puede gestionar usuarios
     */
    public function canManageUsers(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.roles',
            'empleados.crear',
            'empleados.editar'
        ]);
    }

    /**
     * Verificar si es administrador del sistema
     */
    public function isSystemAdmin(Usuario $user): bool
    {
        return $user->hasRole(['Super Admin', 'Gerente']);
    }

    /**
     * Verificar si puede realizar operaciones críticas
     */
    public function canPerformCriticalOperations(Usuario $user): bool
    {
        return $this->checkPermissionLevel($user, 8); // Gerente o superior
    }

    /**
     * Verificar acceso a configuración
     */
    public function canAccessConfiguration(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'configuracion.ver',
            'configuracion.editar'
        ]);
    }

    /**
     * Verificar acceso a auditoría
     */
    public function canAccessAudit(Usuario $user): bool
    {
        return $user->hasAnyPermission([
            'auditoria.ver',
            'auditoria.exportar'
        ]);
    }
}