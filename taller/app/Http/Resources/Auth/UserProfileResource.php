<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Basic user information
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'nombre_completo' => $this->nombres . ' ' . $this->apellidos,
            'telefono' => $this->telefono,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'direccion' => $this->direccion,
            
            // Account status
            'activo' => $this->activo,
            'email_verified' => !is_null($this->email_verified_at),
            'email_verified_at' => $this->email_verified_at,
            
            // Security information
            'security' => [
                'two_factor_enabled' => !is_null($this->two_factor_secret),
                'two_factor_enabled_at' => $this->two_factor_enabled_at,
                'password_changed_at' => $this->password_changed_at,
                'password_age_days' => $this->password_changed_at 
                    ? now()->diffInDays($this->password_changed_at) 
                    : null,
                'password_expires_in_days' => $this->getPasswordExpirationDays(),
                'password_expired' => $this->passwordExpired(),
                'force_password_change' => $this->force_password_change,
                'failed_login_attempts' => $this->failed_login_attempts,
                'last_login_at' => $this->last_login_at,
                'last_login_ip' => $this->last_login_ip,
                'is_blocked' => $this->blocked_until && $this->blocked_until > now(),
                'blocked_until' => $this->blocked_until,
                'blocked_reason' => $this->blocked_reason,
                'active_sessions' => $this->tokens()->count(),
                'backup_codes_count' => $this->two_factor_backup_codes 
                    ? count(json_decode(decrypt($this->two_factor_backup_codes))) 
                    : 0
            ],
            
            // Roles and permissions
            'roles' => $this->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'descripcion' => $role->descripcion,
                    'nivel_permiso' => $role->nivel_permiso,
                    'color' => $this->getRoleColor($role->name)
                ];
            }),
            
            'permissions' => $this->getAllPermissions()->groupBy('category')->map(function ($perms, $category) {
                return [
                    'category' => $category ?: 'general',
                    'permissions' => $perms->map(function ($permission) {
                        return [
                            'name' => $permission->name,
                            'display_name' => $permission->display_name ?? $permission->name,
                            'via_role' => !$this->hasDirectPermission($permission->name)
                        ];
                    })
                ];
            })->values(),
            
            'highest_role' => $this->getHighestRole(),
            'nivel_permiso' => $this->roles->max('nivel_permiso') ?? 0,
            'is_admin' => $this->hasRole(['Super Admin', 'Gerente']),
            
            // User preferences
            'preferences' => [
                'timezone' => $this->timezone ?? config('app.timezone'),
                'locale' => $this->locale ?? config('app.locale'),
                'theme' => $this->theme ?? 'light',
                'sidebar_collapsed' => $this->sidebar_collapsed ?? false,
                'notifications_enabled' => $this->notifications_enabled ?? true,
                'email_notifications' => $this->email_notifications ?? true,
                'sms_notifications' => $this->sms_notifications ?? false
            ],
            
            // Avatar and display
            'avatar' => [
                'url' => $this->getAvatarUrl(),
                'initials' => $this->getInitials(),
                'color' => $this->getAvatarColor(),
                'has_custom' => !is_null($this->avatar_path)
            ],
            
            // Activity statistics
            'activity_stats' => [
                'total_logins' => $this->whenCounted('securityLogs', function () {
                    return $this->securityLogs->where('evento', 'login_success')->count();
                }),
                'last_30_days_logins' => $this->getLoginsCount(30),
                'total_activities' => $this->whenCounted('userActivities', function () {
                    return $this->userActivities->count();
                }),
                'last_30_days_activities' => $this->getActivitiesCount(30),
                'most_used_modules' => $this->getMostUsedModules(),
                'login_streak' => $this->getLoginStreak(),
                'average_session_duration' => $this->getAverageSessionDuration()
            ],
            
            // Security events summary
            'security_summary' => [
                'total_security_events' => $this->getSecurityEventsCount(),
                'high_risk_events' => $this->getSecurityEventsCount(['high', 'critical']),
                'recent_suspicious_activity' => $this->hasRecentSuspiciousActivity(),
                'password_changes' => $this->getPasswordChangesCount(),
                'account_lockouts' => $this->getAccountLockoutsCount(),
                'failed_logins_last_week' => $this->getFailedLoginsCount(7)
            ],
            
            // Device and location info
            'device_info' => [
                'last_user_agent' => $this->last_user_agent,
                'last_ip_address' => $this->last_login_ip,
                'known_devices' => $this->getKnownDevices(),
                'login_locations' => $this->getLoginLocations()
            ],
            
            // Connected services (if any)
            'connected_services' => [
                'google' => $this->google_id ? ['connected' => true, 'email' => $this->google_email] : ['connected' => false],
                'microsoft' => $this->microsoft_id ? ['connected' => true, 'email' => $this->microsoft_email] : ['connected' => false]
            ],
            
            // Data export and privacy
            'data_export' => [
                'last_export_request' => $this->last_data_export,
                'can_request_export' => $this->canRequestDataExport(),
                'export_in_progress' => $this->data_export_in_progress ?? false
            ],
            
            // Account limits and quotas
            'limits' => [
                'api_requests_today' => $this->getApiRequestsToday(),
                'api_rate_limit' => $this->getRateLimit(),
                'storage_used' => $this->getStorageUsed(),
                'storage_limit' => $this->getStorageLimit()
            ],
            
            // Compliance and audit
            'compliance' => [
                'gdpr_consent' => $this->gdpr_consent_at,
                'terms_accepted' => $this->terms_accepted_at,
                'privacy_policy_accepted' => $this->privacy_policy_accepted_at,
                'data_retention_days' => $this->getDataRetentionDays(),
                'account_age_days' => $this->created_at->diffInDays(now())
            ],
            
            // Notifications and alerts
            'notifications' => [
                'unread_count' => $this->getUnreadNotificationsCount(),
                'security_alerts' => $this->getSecurityAlerts(),
                'system_messages' => $this->getSystemMessages(),
                'pending_actions' => $this->getPendingActions()
            ],
            
            // Professional information (if employee record exists)
            'employee_info' => $this->whenLoaded('empleado', function () {
                return [
                    'codigo_empleado' => $this->empleado->codigo_empleado,
                    'cargo' => $this->empleado->cargo,
                    'departamento' => $this->empleado->departamento_trabajo,
                    'fecha_ingreso' => $this->empleado->fecha_ingreso,
                    'jefe_inmediato' => $this->empleado->jefeInmediato ? [
                        'nombre' => $this->empleado->jefeInmediato->nombres . ' ' . $this->empleado->jefeInmediato->apellidos,
                        'email' => $this->empleado->jefeInmediato->email,
                        'cargo' => $this->empleado->jefeInmediato->cargo
                    ] : null
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at_human' => $this->updated_at->diffForHumans(),
            
            // Profile completion
            'profile_completion' => $this->calculateProfileCompletion()
        ];
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
    private function getHighestRole()
    {
        $highestRole = $this->roles->sortByDesc('nivel_permiso')->first();
        
        return $highestRole ? [
            'name' => $highestRole->name,
            'display_name' => $highestRole->display_name,
            'nivel_permiso' => $highestRole->nivel_permiso,
            'color' => $this->getRoleColor($highestRole->name)
        ] : null;
    }

    /**
     * Get user avatar URL
     */
    private function getAvatarUrl()
    {
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }
        
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=200";
    }

    /**
     * Get user initials
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
     * Get avatar color based on user ID
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
     * Get password expiration days
     */
    private function getPasswordExpirationDays()
    {
        if (!$this->password_changed_at) return 0;
        
        $daysSinceChange = now()->diffInDays($this->password_changed_at);
        return max(0, 90 - $daysSinceChange);
    }

    /**
     * Check if password has expired
     */
    private function passwordExpired()
    {
        if (!$this->password_changed_at) return true;
        return now()->diffInDays($this->password_changed_at) > 90;
    }

    /**
     * Get logins count for specified days
     */
    private function getLoginsCount($days = 30)
    {
        return \App\Models\SecurityLog::where('usuario_id', $this->id)
            ->where('evento', 'login_success')
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
    }

    /**
     * Get activities count for specified days
     */
    private function getActivitiesCount($days = 30)
    {
        return \App\Models\UserActivity::where('usuario_id', $this->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
    }

    /**
     * Get most used modules
     */
    private function getMostUsedModules()
    {
        return \App\Models\UserActivity::where('usuario_id', $this->id)
            ->selectRaw('modulo, COUNT(*) as count')
            ->groupBy('modulo')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->pluck('count', 'modulo');
    }

    /**
     * Get login streak (consecutive days with login)
     */
    private function getLoginStreak()
    {
        // Simplified implementation - would need more complex logic for accurate streaks
        $recentLogins = \App\Models\SecurityLog::where('usuario_id', $this->id)
            ->where('evento', 'login_success')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date');

        return $recentLogins->count();
    }

    /**
     * Get average session duration
     */
    private function getAverageSessionDuration()
    {
        // This would require storing session end times
        return 45; // minutes (placeholder)
    }

    /**
     * Get security events count
     */
    private function getSecurityEventsCount($riskLevels = null)
    {
        $query = \App\Models\SecurityLog::where('usuario_id', $this->id);
        
        if ($riskLevels) {
            $query->whereIn('nivel_riesgo', $riskLevels);
        }
        
        return $query->count();
    }

    /**
     * Check for recent suspicious activity
     */
    private function hasRecentSuspiciousActivity()
    {
        return \App\Models\SecurityLog::where('usuario_id', $this->id)
            ->whereIn('nivel_riesgo', ['high', 'critical'])
            ->where('created_at', '>=', now()->subDays(7))
            ->exists();
    }

    /**
     * Get password changes count
     */
    private function getPasswordChangesCount()
    {
        return \App\Models\SecurityLog::where('usuario_id', $this->id)
            ->where('evento', 'password_changed')
            ->count();
    }

    /**
     * Get account lockouts count
     */
    private function getAccountLockoutsCount()
    {
        return \App\Models\SecurityLog::where('usuario_id', $this->id)
            ->where('evento', 'account_auto_locked')
            ->count();
    }

    /**
     * Get failed logins count for specified days
     */
    private function getFailedLoginsCount($days = 7)
    {
        return \App\Models\SecurityLog::where('usuario_id', $this->id)
            ->where('evento', 'login_failed')
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
    }

    /**
     * Get known devices (simplified)
     */
    private function getKnownDevices()
    {
        return \App\Models\SecurityLog::where('usuario_id', $this->id)
            ->where('evento', 'login_success')
            ->selectRaw('user_agent, MAX(created_at) as last_used')
            ->groupBy('user_agent')
            ->orderBy('last_used', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($device) {
                return [
                    'device_info' => $this->parseUserAgent($device->user_agent),
                    'last_used' => $device->last_used
                ];
            });
    }

    /**
     * Get login locations (simplified)
     */
    private function getLoginLocations()
    {
        return \App\Models\SecurityLog::where('usuario_id', $this->id)
            ->where('evento', 'login_success')
            ->selectRaw('ip_address, COUNT(*) as count, MAX(created_at) as last_used')
            ->groupBy('ip_address')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($location) {
                return [
                    'ip_address' => $location->ip_address,
                    'count' => $location->count,
                    'last_used' => $location->last_used,
                    // 'location' => $this->getIPLocation($location->ip_address) // Would need IP geolocation service
                ];
            });
    }

    /**
     * Check if user can request data export
     */
    private function canRequestDataExport()
    {
        // Allow export once per month
        return !$this->last_data_export || 
               $this->last_data_export < now()->subMonth();
    }

    /**
     * Get API requests today
     */
    private function getApiRequestsToday()
    {
        // This would require API request logging
        return 0; // Placeholder
    }

    /**
     * Get rate limit based on role
     */
    private function getRateLimit()
    {
        if ($this->hasRole('Super Admin')) return null;
        if ($this->hasRole(['Gerente', 'Supervisor'])) return 200;
        if ($this->hasRole(['Técnico Senior', 'Técnico', 'Vendedor Senior', 'Vendedor'])) return 100;
        return 50;
    }

    /**
     * Get storage used (placeholder)
     */
    private function getStorageUsed()
    {
        return 0; // MB
    }

    /**
     * Get storage limit (placeholder)
     */
    private function getStorageLimit()
    {
        return 100; // MB
    }

    /**
     * Get data retention days
     */
    private function getDataRetentionDays()
    {
        return 2555; // 7 years default
    }

    /**
     * Get unread notifications count
     */
    private function getUnreadNotificationsCount()
    {
        return 0; // Placeholder
    }

    /**
     * Get security alerts
     */
    private function getSecurityAlerts()
    {
        $alerts = [];

        if ($this->force_password_change) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Debes cambiar tu contraseña',
                'action' => 'change_password'
            ];
        }

        if (is_null($this->email_verified_at)) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'Verifica tu dirección de correo electrónico',
                'action' => 'verify_email'
            ];
        }

        return $alerts;
    }

    /**
     * Get system messages
     */
    private function getSystemMessages()
    {
        return []; // Placeholder
    }

    /**
     * Get pending actions
     */
    private function getPendingActions()
    {
        $actions = [];

        if ($this->passwordExpired()) {
            $actions[] = [
                'type' => 'password_change',
                'message' => 'Tu contraseña ha expirado',
                'priority' => 'high'
            ];
        }

        return $actions;
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateProfileCompletion()
    {
        $fields = [
            'nombres' => !empty($this->nombres),
            'apellidos' => !empty($this->apellidos),
            'email' => !empty($this->email),
            'telefono' => !empty($this->telefono),
            'fecha_nacimiento' => !empty($this->fecha_nacimiento),
            'direccion' => !empty($this->direccion),
            'email_verified' => !is_null($this->email_verified_at),
            'avatar' => !is_null($this->avatar_path)
        ];

        $completed = count(array_filter($fields));
        $total = count($fields);

        return [
            'percentage' => round(($completed / $total) * 100),
            'completed_fields' => $completed,
            'total_fields' => $total,
            'missing_fields' => array_keys(array_filter($fields, fn($v) => !$v))
        ];
    }

    /**
     * Parse user agent (simplified)
     */
    private function parseUserAgent($userAgent)
    {
        return [
            'browser' => 'Unknown',
            'platform' => 'Unknown',
            'device' => 'Desktop'
        ];
    }
}