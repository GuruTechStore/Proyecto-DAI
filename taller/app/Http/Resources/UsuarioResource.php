<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'nombre_completo' => $this->nombres . ' ' . $this->apellidos,
            'telefono' => $this->telefono,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'direccion' => $this->direccion,
            'activo' => $this->activo,
            'estado' => $this->activo ? 'Activo' : 'Inactivo',
            
            // Información de verificación
            'email_verified' => !is_null($this->email_verified_at),
            'email_verified_at' => $this->email_verified_at,
            
            // Información de seguridad
            'two_factor_enabled' => !is_null($this->two_factor_secret),
            'force_password_change' => $this->force_password_change,
            'password_changed_at' => $this->password_changed_at,
            'password_age_days' => $this->password_changed_at 
                ? now()->diffInDays($this->password_changed_at) 
                : null,
            'password_expires_soon' => $this->password_changed_at 
                ? now()->diffInDays($this->password_changed_at) > 80 
                : true,
            
            // Información de bloqueo
            'is_blocked' => $this->blocked_until && $this->blocked_until > now(),
            'blocked_until' => $this->blocked_until,
            'blocked_reason' => $this->blocked_reason,
            'failed_login_attempts' => $this->failed_login_attempts,
            
            // Información de actividad
            'last_login_at' => $this->last_login_at,
            'last_login_ip' => $this->last_login_ip,
            'last_login_human' => $this->last_login_at 
                ? $this->last_login_at->diffForHumans() 
                : 'Nunca',
            
            // Roles y permisos
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                        'descripcion' => $role->descripcion,
                        'nivel_permiso' => $role->nivel_permiso,
                        'color' => $this->getRoleColor($role->name)
                    ];
                });
            }),
            
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->getAllPermissions()->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $permission->display_name ?? $permission->name,
                        'category' => $permission->category ?? 'general',
                        'via_role' => $this->hasDirectPermission($permission->name) ? false : true
                    ];
                });
            }),
            
            'role_names' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name')->toArray();
            }),
            
            'highest_role' => $this->whenLoaded('roles', function () {
                $highestRole = $this->roles->sortByDesc('nivel_permiso')->first();
                return $highestRole ? [
                    'name' => $highestRole->name,
                    'display_name' => $highestRole->display_name,
                    'nivel_permiso' => $highestRole->nivel_permiso
                ] : null;
            }),
            
            'nivel_permiso' => $this->whenLoaded('roles', function () {
                return $this->roles->max('nivel_permiso') ?? 0;
            }),
            
            // Información de auditoría
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at_human' => $this->updated_at->diffForHumans(),
            
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'username' => $this->createdBy->username,
                    'nombre_completo' => $this->createdBy->nombres . ' ' . $this->createdBy->apellidos
                ];
            }),
            
            // Estadísticas básicas (solo si se solicitan)
            $this->mergeWhen($request->include_stats, [
                'stats' => [
                    'total_logins' => $this->whenCounted('securityLogs', function () {
                        return $this->securityLogs->where('evento', 'login_success')->count();
                    }),
                    'recent_activities' => $this->whenCounted('userActivities', function () {
                        return $this->userActivities->where('created_at', '>=', now()->subDays(30))->count();
                    }),
                    'security_events' => $this->whenCounted('securityLogs', function () {
                        return $this->securityLogs->whereIn('nivel_riesgo', ['high', 'critical'])->count();
                    })
                ]
            ]),
            
            // URLs de avatar (si implementas avatares)
            'avatar' => [
                'url' => $this->getAvatarUrl(),
                'initials' => $this->getInitials(),
                'color' => $this->getAvatarColor()
            ],
            
            // Configuraciones de usuario
            'preferences' => [
                'timezone' => $this->timezone ?? config('app.timezone'),
                'locale' => $this->locale ?? config('app.locale'),
                'theme' => $this->theme ?? 'light'
            ],
            
            // Indicadores de estado
            'status_indicators' => [
                'online' => $this->isOnline(),
                'needs_password_change' => $this->force_password_change || $this->passwordExpired(),
                'needs_email_verification' => is_null($this->email_verified_at),
                'has_security_issues' => $this->hasSecurityIssues(),
                'is_admin' => $this->hasRole(['Super Admin', 'Gerente']),
                'can_manage_users' => $this->can('viewAny', \App\Models\Usuario::class),
                'has_elevated_permissions' => $this->hasRole(['Super Admin', 'Gerente', 'Supervisor'])
            ]
        ];
    }

    /**
     * Obtener color del rol para la UI
     */
    private function getRoleColor($roleName)
    {
        $colors = [
            'Super Admin' => '#dc2626', // red-600
            'Gerente' => '#ea580c', // orange-600
            'Supervisor' => '#d97706', // amber-600
            'Técnico Senior' => '#059669', // emerald-600
            'Técnico' => '#0d9488', // teal-600
            'Vendedor Senior' => '#0ea5e9', // sky-600
            'Vendedor' => '#3b82f6', // blue-600
            'Empleado' => '#6b7280', // gray-500
        ];

        return $colors[$roleName] ?? '#6b7280';
    }

    /**
     * Obtener URL del avatar (placeholder o implementación real)
     */
    private function getAvatarUrl()
    {
        // Si tienes avatares almacenados
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }

        // Avatar por defecto usando Gravatar
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=200";
    }

    /**
     * Obtener iniciales del usuario
     */
    private function getInitials()
    {
        $nombres = explode(' ', $this->nombres);
        $apellidos = explode(' ', $this->apellidos);
        
        $inicial1 = !empty($nombres[0]) ? strtoupper(substr($nombres[0], 0, 1)) : '';
        $inicial2 = !empty($apellidos[0]) ? strtoupper(substr($apellidos[0], 0, 1)) : '';
        
        return $inicial1 . $inicial2;
    }

    /**
     * Obtener color del avatar basado en el ID
     */
    private function getAvatarColor()
    {
        $colors = [
            '#f59e0b', '#ef4444', '#10b981', '#3b82f6', 
            '#8b5cf6', '#06b6d4', '#f97316', '#84cc16'
        ];
        
        return $colors[$this->id % count($colors)];
    }

    /**
     * Verificar si el usuario está en línea (últimos 5 minutos)
     */
    private function isOnline()
    {
        // Esto requeriría un campo last_activity_at o similar
        if (!$this->last_activity_at) {
            return false;
        }
        
        return $this->last_activity_at >= now()->subMinutes(5);
    }

    /**
     * Verificar si la contraseña ha expirado
     */
    private function passwordExpired()
    {
        if (!$this->password_changed_at) {
            return true;
        }
        
        return $this->password_changed_at->diffInDays(now()) > 90;
    }

    /**
     * Verificar si el usuario tiene problemas de seguridad
     */
    private function hasSecurityIssues()
    {
        return $this->failed_login_attempts > 3 ||
               ($this->blocked_until && $this->blocked_until > now()) ||
               $this->force_password_change ||
               is_null($this->email_verified_at);
    }
}