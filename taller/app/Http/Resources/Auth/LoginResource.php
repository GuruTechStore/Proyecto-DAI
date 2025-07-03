<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource->user;
        
        return [
            // Token information
            'token' => $this->resource->token,
            'token_type' => 'Bearer',
            'expires_at' => $this->resource->expires_at,
            'expires_in' => $this->resource->expires_at ? now()->diffInSeconds($this->resource->expires_at) : null,
            
            // User information
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'nombre_completo' => $user->nombres . ' ' . $user->apellidos,
                'activo' => $user->activo,
                'email_verified' => !is_null($user->email_verified_at),
                'two_factor_enabled' => !is_null($user->two_factor_secret),
                'avatar' => [
                    'url' => $this->getAvatarUrl($user),
                    'initials' => $this->getInitials($user),
                    'color' => $this->getAvatarColor($user)
                ]
            ],
            
            // Roles and permissions
            'roles' => $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'nivel_permiso' => $role->nivel_permiso,
                    'color' => $this->getRoleColor($role->name)
                ];
            }),
            
            'permissions' => $user->getAllPermissions()->map(function ($permission) {
                return [
                    'name' => $permission->name,
                    'display_name' => $permission->display_name ?? $permission->name,
                    'category' => $permission->category ?? 'general'
                ];
            }),
            
            'role_names' => $user->roles->pluck('name')->toArray(),
            'permission_names' => $user->getAllPermissions()->pluck('name')->toArray(),
            
            // User level and hierarchy
            'nivel_permiso' => $user->roles->max('nivel_permiso') ?? 0,
            'highest_role' => $this->getHighestRole($user),
            'is_admin' => $user->hasRole(['Super Admin', 'Gerente']),
            'can_manage_users' => $user->can('viewAny', \App\Models\Usuario::class),
            
            // Navigation menu items based on permissions
            'menu_items' => $this->generateMenuItems($user),
            
            // Security configuration
            'security_config' => [
                'rate_limit' => $this->getRateLimit($user),
                'session_timeout' => config('sanctum.expiration', 480), // minutes
                'password_expires_in' => $this->getPasswordExpirationDays($user),
                'requires_password_change' => $user->force_password_change || $this->passwordExpired($user),
                'requires_2fa_setup' => $this->requires2FASetup($user),
                'has_security_alerts' => $this->hasSecurityAlerts($user)
            ],
            
            // Application preferences
            'preferences' => [
                'timezone' => $user->timezone ?? config('app.timezone'),
                'locale' => $user->locale ?? config('app.locale'),
                'theme' => $user->theme ?? 'light',
                'sidebar_collapsed' => $user->sidebar_collapsed ?? false,
                'notifications_enabled' => $user->notifications_enabled ?? true
            ],
            
            // Dashboard configuration based on role
            'dashboard_config' => $this->getDashboardConfig($user),
            
            // Shortcuts and quick actions based on permissions
            'quick_actions' => $this->getQuickActions($user),
            
            // System notifications and alerts
            'notifications' => [
                'unread_count' => $this->getUnreadNotificationsCount($user),
                'security_alerts' => $this->getSecurityAlerts($user),
                'system_announcements' => $this->getSystemAnnouncements($user)
            ],
            
            // Login session info
            'session_info' => [
                'login_time' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'is_remember_session' => str_contains($this->resource->token ?? '', 'remember'),
                'device_info' => $this->parseUserAgent(request()->userAgent())
            ],
            
            // Feature flags and permissions for frontend
            'features' => [
                'can_export_data' => $user->can('export', \App\Models\Usuario::class),
                'can_view_analytics' => $user->hasPermissionTo('view-analytics'),
                'can_manage_settings' => $user->hasPermissionTo('manage-settings'),
                'can_view_logs' => $user->hasPermissionTo('view-security-logs'),
                'can_manage_roles' => $user->hasPermissionTo('manage-roles'),
                'advanced_search' => $user->hasRole(['Super Admin', 'Gerente', 'Supervisor']),
                'bulk_operations' => $user->hasRole(['Super Admin', 'Gerente'])
            ],
            
            // Company/organization info (if applicable)
            'organization' => [
                'name' => config('app.company_name', config('app.name')),
                'logo' => config('app.company_logo'),
                'timezone' => config('app.timezone'),
                'currency' => config('app.currency', 'PEN')
            ]
        ];
    }

    /**
     * Get user avatar URL
     */
    private function getAvatarUrl($user)
    {
        if ($user->avatar_path) {
            return asset('storage/' . $user->avatar_path);
        }
        
        $hash = md5(strtolower(trim($user->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=200";
    }

    /**
     * Get user initials
     */
    private function getInitials($user)
    {
        $nombres = explode(' ', $user->nombres);
        $apellidos = explode(' ', $user->apellidos);
        
        $inicial1 = !empty($nombres[0]) ? strtoupper(substr($nombres[0], 0, 1)) : '';
        $inicial2 = !empty($apellidos[0]) ? strtoupper(substr($apellidos[0], 0, 1)) : '';
        
        return $inicial1 . $inicial2;
    }

    /**
     * Get avatar color based on user ID
     */
    private function getAvatarColor($user)
    {
        $colors = [
            '#f59e0b', '#ef4444', '#10b981', '#3b82f6', 
            '#8b5cf6', '#06b6d4', '#f97316', '#84cc16'
        ];
        
        return $colors[$user->id % count($colors)];
    }

    /**
     * Get role color for UI
     */
    private function getRoleColor($roleName)
    {
        $colors = [
            'Super Admin' => '#dc2626',
            'Gerente' => '#ea580c',
            'Supervisor' => '#d97706',
            'Técnico Senior' => '#059669',
            'Técnico' => '#0d9488',
            'Vendedor Senior' => '#0ea5e9',
            'Vendedor' => '#3b82f6',
            'Empleado' => '#6b7280',
        ];

        return $colors[$roleName] ?? '#6b7280';
    }

    /**
     * Get highest role information
     */
    private function getHighestRole($user)
    {
        $highestRole = $user->roles->sortByDesc('nivel_permiso')->first();
        
        return $highestRole ? [
            'name' => $highestRole->name,
            'display_name' => $highestRole->display_name,
            'nivel_permiso' => $highestRole->nivel_permiso,
            'color' => $this->getRoleColor($highestRole->name)
        ] : null;
    }

    /**
     * Generate menu items based on user permissions
     */
    private function generateMenuItems($user)
    {
        $menuItems = [];

        // Dashboard (always available)
        $menuItems[] = [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'dashboard',
            'route' => '/dashboard',
            'order' => 1
        ];

        // Users management
        if ($user->can('viewAny', \App\Models\Usuario::class)) {
            $menuItems[] = [
                'key' => 'users',
                'label' => 'Gestión de Usuarios',
                'icon' => 'users',
                'route' => '/users',
                'order' => 2,
                'children' => [
                    [
                        'key' => 'users.list',
                        'label' => 'Lista de Usuarios',
                        'route' => '/users'
                    ],
                    [
                        'key' => 'users.create',
                        'label' => 'Crear Usuario',
                        'route' => '/users/create',
                        'permission' => 'create-users'
                    ]
                ]
            ];
        }

        // Security
        if ($user->hasPermissionTo('view-security-logs')) {
            $menuItems[] = [
                'key' => 'security',
                'label' => 'Seguridad',
                'icon' => 'shield',
                'route' => '/security',
                'order' => 3,
                'children' => [
                    [
                        'key' => 'security.logs',
                        'label' => 'Logs de Seguridad',
                        'route' => '/security/logs'
                    ],
                    [
                        'key' => 'security.dashboard',
                        'label' => 'Dashboard de Seguridad',
                        'route' => '/security/dashboard'
                    ]
                ]
            ];
        }

        // Activities
        if ($user->hasPermissionTo('view-user-activities')) {
            $menuItems[] = [
                'key' => 'activities',
                'label' => 'Actividades',
                'icon' => 'activity',
                'route' => '/activities',
                'order' => 4
            ];
        }

        // Roles and Permissions (Super Admin only)
        if ($user->hasRole('Super Admin')) {
            $menuItems[] = [
                'key' => 'roles',
                'label' => 'Roles y Permisos',
                'icon' => 'key',
                'route' => '/roles',
                'order' => 5
            ];
        }

        // Settings
        $menuItems[] = [
            'key' => 'settings',
            'label' => 'Configuración',
            'icon' => 'settings',
            'route' => '/settings',
            'order' => 99
        ];

        return collect($menuItems)->sortBy('order')->values()->toArray();
    }

    /**
     * Get rate limit based on user role
     */
    private function getRateLimit($user)
    {
        if ($user->hasRole('Super Admin')) return null;
        if ($user->hasRole(['Gerente', 'Supervisor'])) return 200;
        if ($user->hasRole(['Técnico Senior', 'Técnico', 'Vendedor Senior', 'Vendedor'])) return 100;
        return 50;
    }

    /**
     * Get password expiration days
     */
    private function getPasswordExpirationDays($user)
    {
        if (!$user->password_changed_at) return 0;
        
        $daysSinceChange = now()->diffInDays($user->password_changed_at);
        return max(0, 90 - $daysSinceChange);
    }

    /**
     * Check if password has expired
     */
    private function passwordExpired($user)
    {
        if (!$user->password_changed_at) return true;
        return now()->diffInDays($user->password_changed_at) > 90;
    }

    /**
     * Check if user requires 2FA setup
     */
    private function requires2FASetup($user)
    {
        // Require 2FA for admin roles
        return $user->hasRole(['Super Admin', 'Gerente']) && is_null($user->two_factor_secret);
    }

    /**
     * Check if user has security alerts
     */
    private function hasSecurityAlerts($user)
    {
        return $user->failed_login_attempts > 0 ||
               $user->force_password_change ||
               is_null($user->email_verified_at) ||
               $this->requires2FASetup($user);
    }

    /**
     * Get dashboard configuration based on role
     */
    private function getDashboardConfig($user)
    {
        $config = [
            'default_widgets' => ['welcome', 'recent_activity'],
            'available_widgets' => [
                'welcome' => ['title' => 'Bienvenido', 'size' => 'large'],
                'recent_activity' => ['title' => 'Actividad Reciente', 'size' => 'medium'],
                'quick_stats' => ['title' => 'Estadísticas Rápidas', 'size' => 'small']
            ]
        ];

        if ($user->hasRole(['Super Admin', 'Gerente'])) {
            $config['default_widgets'][] = 'user_stats';
            $config['default_widgets'][] = 'security_overview';
            $config['available_widgets']['user_stats'] = ['title' => 'Estadísticas de Usuarios', 'size' => 'medium'];
            $config['available_widgets']['security_overview'] = ['title' => 'Resumen de Seguridad', 'size' => 'medium'];
            $config['available_widgets']['system_health'] = ['title' => 'Estado del Sistema', 'size' => 'small'];
        }

        if ($user->hasRole(['Super Admin', 'Gerente', 'Supervisor'])) {
            $config['available_widgets']['team_activity'] = ['title' => 'Actividad del Equipo', 'size' => 'large'];
            $config['available_widgets']['reports'] = ['title' => 'Reportes', 'size' => 'medium'];
        }

        return $config;
    }

    /**
     * Get quick actions based on permissions
     */
    private function getQuickActions($user)
    {
        $actions = [];

        if ($user->can('create', \App\Models\Usuario::class)) {
            $actions[] = [
                'key' => 'create_user',
                'label' => 'Crear Usuario',
                'icon' => 'user-plus',
                'route' => '/users/create',
                'color' => 'blue'
            ];
        }

        if ($user->hasPermissionTo('view-security-logs')) {
            $actions[] = [
                'key' => 'security_logs',
                'label' => 'Ver Logs de Seguridad',
                'icon' => 'shield',
                'route' => '/security/logs',
                'color' => 'red'
            ];
        }

        if ($user->hasPermissionTo('view-user-activities')) {
            $actions[] = [
                'key' => 'activities',
                'label' => 'Ver Actividades',
                'icon' => 'activity',
                'route' => '/activities',
                'color' => 'green'
            ];
        }

        $actions[] = [
            'key' => 'profile',
            'label' => 'Mi Perfil',
            'icon' => 'user',
            'route' => '/profile',
            'color' => 'gray'
        ];

        return $actions;
    }

    /**
     * Get unread notifications count
     */
    private function getUnreadNotificationsCount($user)
    {
        // Implementar cuando tengas sistema de notificaciones
        return 0;
    }

    /**
     * Get security alerts for user
     */
    private function getSecurityAlerts($user)
    {
        $alerts = [];

        if ($user->force_password_change) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Debes cambiar tu contraseña',
                'action' => '/profile/security'
            ];
        }

        if (is_null($user->email_verified_at)) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'Verifica tu dirección de correo electrónico',
                'action' => '/profile/verify-email'
            ];
        }

        if ($this->requires2FASetup($user)) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Configura la autenticación de dos factores',
                'action' => '/profile/2fa'
            ];
        }

        if ($user->failed_login_attempts > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "Se detectaron {$user->failed_login_attempts} intentos fallidos de login",
                'action' => '/profile/security'
            ];
        }

        return $alerts;
    }

    /**
     * Get system announcements
     */
    private function getSystemAnnouncements($user)
    {
        // Implementar cuando tengas sistema de anuncios
        return [];
    }

    /**
     * Parse user agent information
     */
    private function parseUserAgent($userAgent)
    {
        $info = [
            'browser' => 'Unknown',
            'platform' => 'Unknown',
            'device' => 'Desktop'
        ];

        // Browser detection
        if (str_contains($userAgent, 'Chrome')) {
            $info['browser'] = 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            $info['browser'] = 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            $info['browser'] = 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            $info['browser'] = 'Edge';
        }

        // Platform detection
        if (str_contains($userAgent, 'Windows')) {
            $info['platform'] = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $info['platform'] = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $info['platform'] = 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            $info['platform'] = 'Android';
            $info['device'] = 'Mobile';
        } elseif (str_contains($userAgent, 'iOS')) {
            $info['platform'] = 'iOS';
            $info['device'] = 'Mobile';
        }

        // Device type detection
        if (str_contains($userAgent, 'Mobile')) {
            $info['device'] = 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet')) {
            $info['device'] = 'Tablet';
        }

        return $info;
    }
}