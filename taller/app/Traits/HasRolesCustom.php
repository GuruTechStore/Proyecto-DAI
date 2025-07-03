<?php

namespace App\Traits;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

trait HasRolesCustom
{
    /**
     * Verificar múltiples roles (cualquiera)
     */
    public function hasAnyRole($roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar múltiples roles (todos)
     */
    public function hasAllRoles($roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verificar múltiples permisos (cualquiera)
     */
    public function hasAnyPermission($permissions): bool
    {
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verificar múltiples permisos (todos)
     */
    public function hasAllPermissions($permissions): bool
    {
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtener roles como array de strings
     */
    public function getRoleNames(): array
    {
        return $this->roles->pluck('name')->toArray();
    }

    /**
     * Obtener permisos como array de strings
     */
    public function getPermissionNames(): array
    {
        return $this->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * Verificar si es administrador de cualquier tipo
     */
    public function isAnyAdmin(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Admin', 'Administrador']);
    }

    /**
     * Verificar si tiene rol de supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->hasAnyRole(['Supervisor', 'Jefe', 'Gerente']);
    }

    /**
     * Verificar si puede gestionar usuarios
     */
    public function canManageUsers(): bool
    {
        return $this->hasAnyPermission([
            'manage_users',
            'create_users',
            'edit_users',
            'delete_users'
        ]) || $this->isSuperAdmin();
    }

    /**
     * Verificar si puede gestionar roles
     */
    public function canManageRoles(): bool
    {
        return $this->hasPermissionTo('manage_roles') || $this->isSuperAdmin();
    }

    /**
     * Verificar si puede gestionar permisos
     */
    public function canManagePermissions(): bool
    {
        return $this->hasPermissionTo('manage_permissions') || $this->isSuperAdmin();
    }

    /**
     * Verificar acceso a módulo específico con jerarquía
     */
    public function canAccessModule($module): bool
    {
        // Super Admin siempre tiene acceso
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Verificar permiso específico del módulo
        if ($this->hasPermissionTo("access_{$module}")) {
            return true;
        }

        // Verificar rol específico del módulo
        if ($this->hasRole("{$module}_admin") || $this->hasRole("{$module}_manager")) {
            return true;
        }

        // Verificar permisos generales del módulo
        return $this->hasAnyPermission([
            "{$module}_view",
            "{$module}_create",
            "{$module}_edit",
            "{$module}_delete"
        ]);
    }

    /**
     * Verificar si puede realizar acción específica en módulo
     */
    public function canPerformAction($module, $action): bool
    {
        // Super Admin siempre puede
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Verificar permiso específico
        return $this->hasPermissionTo("{$module}_{$action}");
    }

    /**
     * Obtener módulos accesibles
     */
    public function getAccessibleModules(): array
    {
        if ($this->isSuperAdmin()) {
            return ['all']; // Super Admin tiene acceso a todo
        }

        $modules = [];
        $permissions = $this->getAllPermissions();

        foreach ($permissions as $permission) {
            if (str_contains($permission->name, 'access_')) {
                $modules[] = str_replace('access_', '', $permission->name);
            } elseif (str_contains($permission->name, '_')) {
                $parts = explode('_', $permission->name);
                if (count($parts) >= 2) {
                    $modules[] = $parts[0];
                }
            }
        }

        return array_unique($modules);
    }

    /**
     * Verificar si puede delegar permisos
     */
    public function canDelegate(): bool
    {
        return $this->hasPermissionTo('delegate_permissions') || 
               $this->isSuperAdmin();
    }

    /**
     * Obtener nivel de autoridad (0-100)
     */
    public function getAuthorityLevel(): int
    {
        if ($this->isSuperAdmin()) {
            return 100;
        }

        if ($this->hasRole('Admin')) {
            return 80;
        }

        if ($this->isSupervisor()) {
            return 60;
        }

        if ($this->hasRole('Empleado Senior')) {
            return 40;
        }

        return 20; // Usuario básico
    }

    /**
     * Verificar si puede gestionar usuario específico
     */
    public function canManageUser($targetUser): bool
    {
        // No puede gestionarse a sí mismo para roles críticos
        if ($this->id === $targetUser->id) {
            return false;
        }

        // Super Admin puede gestionar a todos excepto otros Super Admin
        if ($this->isSuperAdmin()) {
            return !$targetUser->isSuperAdmin() || $this->id !== $targetUser->id;
        }

        // Verificar jerarquía de autoridad
        return $this->getAuthorityLevel() > $targetUser->getAuthorityLevel();
    }
}