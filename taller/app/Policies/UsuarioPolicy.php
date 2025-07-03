<?php

namespace App\Policies;

use App\Models\Usuario;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsuarioPolicy
{
    use HandlesAuthorization;

    /**
     * Ver información de un usuario
     */
    public function view(Usuario $user, Usuario $targetUser): bool
    {
        // Super Admin puede ver todos
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Gerentes pueden ver empleados de nivel inferior
        if ($user->hasRole('Gerente')) {
            return !$targetUser->hasRole('Super Admin');
        }

        // Supervisores pueden ver técnicos y vendedores
        if ($user->hasRole('Supervisor')) {
            return $targetUser->hasAnyRole(['Técnico', 'Técnico Senior', 'Vendedor', 'Vendedor Senior', 'Empleado']);
        }

        // Los usuarios pueden ver su propia información
        return $user->id === $targetUser->id;
    }

    /**
     * Crear nuevos usuarios
     */
    public function create(Usuario $user): bool
    {
        return $user->hasPermissionTo('usuarios.crear');
    }

    /**
     * Actualizar información de usuarios
     */
    public function update(Usuario $user, Usuario $targetUser): bool
    {
        // Super Admin puede editar todos (excepto otros Super Admin)
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Gerentes pueden editar empleados de nivel inferior
        if ($user->hasRole('Gerente') && $user->hasPermissionTo('usuarios.editar')) {
            return !$targetUser->hasAnyRole(['Super Admin', 'Gerente']);
        }

        // Los usuarios pueden editar su propia información básica
        if ($user->id === $targetUser->id) {
            return true;
        }

        return false;
    }

    /**
     * Eliminar usuarios
     */
    public function delete(Usuario $user, Usuario $targetUser): bool
    {
        // Solo Super Admin puede eliminar usuarios
        if (!$user->hasRole('Super Admin')) {
            return false;
        }

        // No puede eliminarse a sí mismo
        if ($user->id === $targetUser->id) {
            return false;
        }

        // No puede eliminar otros Super Admin
        return !$targetUser->hasRole('Super Admin');
    }

    /**
     * Cambiar contraseña de usuarios
     */
    public function changePassword(Usuario $user, Usuario $targetUser): bool
    {
        // Los usuarios pueden cambiar su propia contraseña
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Super Admin y Gerentes pueden resetear contraseñas
        return $user->hasPermissionTo('usuarios.resetear') && $this->view($user, $targetUser);
    }

    /**
     * Asignar roles a usuarios
     */
    public function assignRole(Usuario $user, Usuario $targetUser): bool
    {
        // Solo usuarios con permiso de roles
        if (!$user->hasPermissionTo('usuarios.roles')) {
            return false;
        }

        // Super Admin puede asignar cualquier rol (excepto Super Admin)
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        // Gerentes pueden asignar roles de nivel inferior
        if ($user->hasRole('Gerente')) {
            return !$targetUser->hasAnyRole(['Super Admin', 'Gerente']);
        }

        return false;
    }

    /**
     * Bloquear/desbloquear usuarios
     */
    public function block(Usuario $user, Usuario $targetUser): bool
    {
        // Solo con permiso específico
        if (!$user->hasPermissionTo('usuarios.bloquear')) {
            return false;
        }

        // No puede bloquearse a sí mismo
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Verificar jerarquía
        return $this->view($user, $targetUser);
    }

    /**
     * Ver perfil completo de usuario
     */
    public function viewProfile(Usuario $user, Usuario $targetUser): bool
    {
        return $this->view($user, $targetUser);
    }

    /**
     * Gestionar permisos de empleados
     */
    public function managePermissions(Usuario $user, Usuario $targetUser): bool
    {
        return $user->hasPermissionTo('empleados.permisos') && 
               $this->assignRole($user, $targetUser);
    }
}