<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserActivityResource extends JsonResource
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
            'accion' => $this->accion,
            'accion_display' => $this->getAccionDisplay(),
            'accion_description' => $this->getAccionDescription(),
            'accion_icon' => $this->getAccionIcon(),
            'accion_color' => $this->getAccionColor(),
            
            // Módulo
            'modulo' => $this->modulo,
            'modulo_display' => $this->getModuloDisplay(),
            'modulo_icon' => $this->getModuloIcon(),
            'modulo_color' => $this->getModuloColor(),
            
            // Usuario
            'usuario_id' => $this->usuario_id,
            'usuario' => $this->whenLoaded('usuario', function () {
                return [
                    'id' => $this->usuario->id,
                    'username' => $this->usuario->username,
                    'email' => $this->usuario->email,
                    'nombre_completo' => $this->usuario->nombres . ' ' . $this->usuario->apellidos,
                    'roles' => $this->usuario->roles->pluck('name'),
                    'avatar' => [
                        'initials' => $this->getInitials($this->usuario),
                        'color' => $this->getAvatarColor($this->usuario)
                    ]
                ];
            }),
            
            // Detalles de la actividad
            'detalles' => $this->detalles,
            'detalles_parsed' => $this->parseDetalles(),
            'detalles_summary' => $this->getDetallesSummary(),
            
            // Información de contexto
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'user_agent_parsed' => $this->parseUserAgent($this->user_agent),
            'device_info' => $this->getDeviceInfo(),
            'location_info' => $this->getLocationInfo($this->ip_address),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'created_at_formatted' => $this->created_at->format('d/m/Y H:i:s'),
            'fecha_local' => $this->created_at->setTimezone(config('app.timezone'))->format('d/m/Y H:i:s'),
            'hora_actividad' => $this->created_at->format('H:i:s'),
            'dia_semana' => $this->created_at->locale('es')->dayName,
            'es_reciente' => $this->created_at >= now()->subHours(24),
            
            // Categorización
            'categoria' => $this->getCategoria(),
            'subcategoria' => $this->getSubcategoria(),
            'tipo_actividad' => $this->getTipoActividad(),
            'nivel_importancia' => $this->getNivelImportancia(),
            'tags' => $this->getTags(),
            
            // Métricas y análisis
            'duracion_estimada' => $this->getDuracionEstimada(),
            'impacto' => $this->getImpacto(),
            'frecuencia_usuario' => $this->getFrecuenciaUsuario(),
            'patron_horario' => $this->getPatronHorario(),
            'anomalia_score' => $this->getAnomaliaScore(),
            
            // Relaciones
            'actividades_relacionadas' => $this->getActividadesRelacionadas(),
            'sesion_info' => $this->getSesionInfo(),
            'transaccion_id' => $this->getTransaccionId(),
            
            // Contexto del negocio
            'business_context' => [
                'department' => $this->getDepartamento(),
                'project' => $this->getProyecto(),
                'customer' => $this->getCliente(),
                'value_impact' => $this->getValorImpacto()
            ],
            
            // Análisis de comportamiento
            'behavior_analysis' => [
                'is_unusual_time' => $this->isUnusualTime(),
                'is_unusual_module' => $this->isUnusualModule(),
                'is_bulk_operation' => $this->isBulkOperation(),
                'is_automated' => $this->isAutomated(),
                'user_expertise_level' => $this->getUserExpertiseLevel()
            ],
            
            // Información de auditoría
            'audit_info' => [
                'is_auditable' => $this->isAuditable(),
                'retention_period' => $this->getRetentionPeriod(),
                'compliance_tags' => $this->getComplianceTags(),
                'privacy_level' => $this->getPrivacyLevel()
            ],
            
            // Exportable data
            'exportable' => [
                'csv_row' => $this->toCsvRow(),
                'summary' => $this->toSummary()
            ]
        ];
    }

    /**
     * Get display name for action
     */
    private function getAccionDisplay()
    {
        $actions = [
            'login' => 'Inicio de sesión',
            'logout' => 'Cierre de sesión',
            'user_created' => 'Usuario creado',
            'user_updated' => 'Usuario actualizado',
            'user_deleted' => 'Usuario eliminado',
            'user_viewed' => 'Usuario consultado',
            'role_assigned' => 'Rol asignado',
            'role_removed' => 'Rol removido',
            'password_changed' => 'Contraseña cambiada',
            'profile_updated' => 'Perfil actualizado',
            'data_exported' => 'Datos exportados',
            'data_imported' => 'Datos importados',
            'report_generated' => 'Reporte generado',
            'settings_changed' => 'Configuración modificada',
            'backup_created' => 'Respaldo creado',
            'email_sent' => 'Email enviado',
            'document_uploaded' => 'Documento subido',
            'document_downloaded' => 'Documento descargado',
            'search_performed' => 'Búsqueda realizada',
            'filter_applied' => 'Filtro aplicado',
            'dashboard_viewed' => 'Dashboard consultado',
            'api_request' => 'Solicitud API'
        ];

        return $actions[$this->accion] ?? ucfirst(str_replace('_', ' ', $this->accion));
    }

    /**
     * Get action description
     */
    private function getAccionDescription()
    {
        $descriptions = [
            'login' => 'El usuario inició sesión en el sistema',
            'user_created' => 'Se creó un nuevo usuario en el sistema',
            'data_exported' => 'Se exportaron datos del sistema',
            'report_generated' => 'Se generó un reporte',
            'settings_changed' => 'Se modificaron configuraciones del sistema',
            'document_uploaded' => 'Se subió un documento al sistema',
            'search_performed' => 'El usuario realizó una búsqueda'
        ];

        return $descriptions[$this->accion] ?? 'Actividad realizada por el usuario';
    }

    /**
     * Get action icon
     */
    private function getAccionIcon()
    {
        $icons = [
            'login' => 'login',
            'logout' => 'logout',
            'user_created' => 'user-plus',
            'user_updated' => 'user-edit',
            'user_deleted' => 'user-minus',
            'user_viewed' => 'eye',
            'role_assigned' => 'shield-plus',
            'role_removed' => 'shield-minus',
            'password_changed' => 'key',
            'profile_updated' => 'user-circle',
            'data_exported' => 'download',
            'data_imported' => 'upload',
            'report_generated' => 'document-text',
            'settings_changed' => 'cog',
            'backup_created' => 'server',
            'email_sent' => 'mail',
            'document_uploaded' => 'document-plus',
            'document_downloaded' => 'document-arrow-down',
            'search_performed' => 'search',
            'filter_applied' => 'filter',
            'dashboard_viewed' => 'chart-bar',
            'api_request' => 'code'
        ];

        return $icons[$this->accion] ?? 'cursor-click';
    }

    /**
     * Get action color
     */
    private function getAccionColor()
    {
        $colors = [
            'login' => '#10b981',        // green
            'logout' => '#6b7280',       // gray
            'user_created' => '#3b82f6', // blue
            'user_updated' => '#f59e0b',  // yellow
            'user_deleted' => '#ef4444', // red
            'user_viewed' => '#8b5cf6',  // purple
            'role_assigned' => '#059669', // emerald
            'role_removed' => '#dc2626',  // red
            'password_changed' => '#d97706', // amber
            'data_exported' => '#0ea5e9', // sky
            'report_generated' => '#7c3aed', // violet
            'settings_changed' => '#ea580c' // orange
        ];

        return $colors[$this->accion] ?? '#6b7280';
    }

    /**
     * Get module display name
     */
    private function getModuloDisplay()
    {
        $modules = [
            'auth' => 'Autenticación',
            'usuarios' => 'Gestión de Usuarios',
            'roles' => 'Roles y Permisos',
            'seguridad' => 'Seguridad',
            'actividades' => 'Actividades',
            'dashboard' => 'Dashboard',
            'reportes' => 'Reportes',
            'configuracion' => 'Configuración',
            'empleados' => 'Gestión de Empleados',
            'proyectos' => 'Proyectos',
            'documentos' => 'Documentos',
            'api' => 'API',
            'sistema' => 'Sistema',
            'backup' => 'Respaldos',
            'logs' => 'Logs',
            'notificaciones' => 'Notificaciones'
        ];

        return $modules[$this->modulo] ?? ucfirst($this->modulo);
    }

    /**
     * Get module icon
     */
    private function getModuloIcon()
    {
        $icons = [
            'auth' => 'shield-check',
            'usuarios' => 'users',
            'roles' => 'key',
            'seguridad' => 'shield',
            'actividades' => 'clock',
            'dashboard' => 'chart-bar',
            'reportes' => 'document-chart-bar',
            'configuracion' => 'cog',
            'empleados' => 'user-group',
            'proyectos' => 'briefcase',
            'documentos' => 'folder',
            'api' => 'code',
            'sistema' => 'server',
            'backup' => 'server-stack',
            'logs' => 'document-text',
            'notificaciones' => 'bell'
        ];

        return $icons[$this->modulo] ?? 'cube';
    }

    /**
     * Get module color
     */
    private function getModuloColor()
    {
        $colors = [
            'auth' => '#10b981',
            'usuarios' => '#3b82f6',
            'roles' => '#8b5cf6',
            'seguridad' => '#ef4444',
            'actividades' => '#f59e0b',
            'dashboard' => '#06b6d4',
            'reportes' => '#7c3aed',
            'configuracion' => '#6b7280',
            'empleados' => '#059669',
            'proyectos' => '#d97706',
            'documentos' => '#0ea5e9',
            'api' => '#84cc16',
            'sistema' => '#f97316'
        ];

        return $colors[$this->modulo] ?? '#6b7280';
    }

    /**
     * Parse activity details
     */
    private function parseDetalles()
    {
        if (!$this->detalles) return null;

        try {
            return json_decode($this->detalles, true);
        } catch (\Exception $e) {
            return ['raw' => $this->detalles];
        }
    }

    /**
     * Get summary of details
     */
    private function getDetallesSummary()
    {
        $detalles = $this->parseDetalles();
        if (!$detalles) return null;

        $summary = [];

        // Extract key information
        if (isset($detalles['target_user_id'])) {
            $summary['usuario_objetivo'] = $detalles['target_username'] ?? "ID: {$detalles['target_user_id']}";
        }

        if (isset($detalles['role_assigned'])) {
            $summary['rol_asignado'] = $detalles['role_assigned'];
        }

        if (isset($detalles['changes'])) {
            $summary['cambios'] = is_array($detalles['changes']) ? count($detalles['changes']) . ' campos' : $detalles['changes'];
        }

        if (isset($detalles['records_count'])) {
            $summary['registros'] = $detalles['records_count'];
        }

        return $summary;
    }

    /**
     * Parse user agent
     */
    private function parseUserAgent($userAgent)
    {
        if (!$userAgent) return null;

        $info = [
            'browser' => 'Unknown',
            'platform' => 'Unknown',
            'device' => 'Desktop',
            'is_mobile' => false
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
            $info['is_mobile'] = true;
        } elseif (str_contains($userAgent, 'iOS')) {
            $info['platform'] = 'iOS';
            $info['device'] = 'Mobile';
            $info['is_mobile'] = true;
        }

        return $info;
    }

    /**
     * Get device information
     */
    private function getDeviceInfo()
    {
        $parsed = $this->parseUserAgent($this->user_agent);
        
        return [
            'type' => $parsed['device'] ?? 'Desktop',
            'browser' => $parsed['browser'] ?? 'Unknown',
            'platform' => $parsed['platform'] ?? 'Unknown',
            'is_mobile' => $parsed['is_mobile'] ?? false,
            'fingerprint' => md5($this->user_agent . $this->ip_address)
        ];
    }

    /**
     * Get location info
     */
    private function getLocationInfo($ip)
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return ['country' => 'Local', 'city' => 'Localhost', 'is_local' => true];
        }

        return ['country' => 'Unknown', 'city' => 'Unknown', 'is_local' => false];
    }

    /**
     * Get user initials
     */
    private function getInitials($user)
    {
        if (!$user) return '?';
        
        $nombres = explode(' ', $user->nombres);
        $apellidos = explode(' ', $user->apellidos);
        
        $inicial1 = !empty($nombres[0]) ? strtoupper(substr($nombres[0], 0, 1)) : '';
        $inicial2 = !empty($apellidos[0]) ? strtoupper(substr($apellidos[0], 0, 1)) : '';
        
        return $inicial1 . $inicial2;
    }

    /**
     * Get avatar color
     */
    private function getAvatarColor($user)
    {
        if (!$user) return '#6b7280';
        
        $colors = [
            '#f59e0b', '#ef4444', '#10b981', '#3b82f6', 
            '#8b5cf6', '#06b6d4', '#f97316', '#84cc16'
        ];
        
        return $colors[$user->id % count($colors)];
    }

    /**
     * Get activity category
     */
    private function getCategoria()
    {
        if (str_contains($this->accion, 'login') || str_contains($this->accion, 'logout')) {
            return 'authentication';
        }
        if (str_contains($this->accion, 'user')) return 'user_management';
        if (str_contains($this->accion, 'role')) return 'role_management';
        if (str_contains($this->accion, 'data')) return 'data_management';
        if (str_contains($this->accion, 'report')) return 'reporting';
        if (str_contains($this->accion, 'settings')) return 'configuration';
        
        return 'general';
    }

    /**
     * Get subcategory
     */
    private function getSubcategoria()
    {
        $subcategories = [
            'user_created' => 'account_creation',
            'user_updated' => 'account_modification',
            'user_deleted' => 'account_deletion',
            'role_assigned' => 'privilege_assignment',
            'data_exported' => 'data_extraction',
            'report_generated' => 'report_creation'
        ];

        return $subcategories[$this->accion] ?? 'other';
    }

    /**
     * Get activity type
     */
    private function getTipoActividad()
    {
        if (str_contains($this->accion, 'viewed') || str_contains($this->accion, 'search')) {
            return 'read';
        }
        if (str_contains($this->accion, 'created') || str_contains($this->accion, 'uploaded')) {
            return 'create';
        }
        if (str_contains($this->accion, 'updated') || str_contains($this->accion, 'changed')) {
            return 'update';
        }
        if (str_contains($this->accion, 'deleted') || str_contains($this->accion, 'removed')) {
            return 'delete';
        }

        return 'action';
    }

    /**
     * Get importance level
     */
    private function getNivelImportancia()
    {
        $highImportance = ['user_deleted', 'role_assigned', 'settings_changed', 'data_exported'];
        $mediumImportance = ['user_created', 'user_updated', 'password_changed'];
        
        if (in_array($this->accion, $highImportance)) return 'high';
        if (in_array($this->accion, $mediumImportance)) return 'medium';
        
        return 'low';
    }

    /**
     * Get activity tags
     */
    private function getTags()
    {
        $tags = [];
        
        if ($this->isUnusualTime()) $tags[] = 'unusual_time';
        if ($this->isBulkOperation()) $tags[] = 'bulk_operation';
        if ($this->isAutomated()) $tags[] = 'automated';
        if ($this->getNivelImportancia() === 'high') $tags[] = 'high_importance';
        if ($this->created_at >= now()->subHours(1)) $tags[] = 'recent';
        
        return $tags;
    }

    /**
     * Helper methods for various calculations
     */
    private function getDuracionEstimada() { return '2 min'; } // Placeholder
    private function getImpacto() { return $this->getNivelImportancia(); }
    private function getFrecuenciaUsuario() { return 'normal'; } // Placeholder
    private function getPatronHorario() { return 'business_hours'; } // Placeholder
    private function getAnomaliaScore() { return 0; } // 0-100
    private function getActividadesRelacionadas() { return []; }
    private function getSesionInfo() { return ['session_id' => null]; }
    private function getTransaccionId() { return null; }
    private function getDepartamento() { return null; }
    private function getProyecto() { return null; }
    private function getCliente() { return null; }
    private function getValorImpacto() { return 'low'; }

    private function isUnusualTime()
    {
        $hour = $this->created_at->hour;
        return $hour < 6 || $hour > 22; // Outside business hours
    }

    private function isUnusualModule()
    {
        // Check if user rarely uses this module
        return false; // Placeholder
    }

    private function isBulkOperation()
    {
        $detalles = $this->parseDetalles();
        return isset($detalles['bulk_operation']) || 
               isset($detalles['records_count']) && $detalles['records_count'] > 10;
    }

    private function isAutomated()
    {
        return str_contains($this->user_agent, 'bot') || 
               str_contains($this->accion, 'api_');
    }

    private function getUserExpertiseLevel() { return 'intermediate'; }
    private function isAuditable() { return true; }
    private function getRetentionPeriod() { return '7 years'; }
    private function getComplianceTags() { return []; }
    private function getPrivacyLevel() { return 'internal'; }

    /**
     * Convert to CSV row
     */
    private function toCsvRow()
    {
        return [
            $this->id,
            $this->usuario?->username ?? 'N/A',
            $this->accion,
            $this->modulo,
            $this->ip_address,
            $this->created_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Convert to summary
     */
    private function toSummary()
    {
        return [
            'id' => $this->id,
            'user' => $this->usuario?->username,
            'action' => $this->accion,
            'module' => $this->modulo,
            'timestamp' => $this->created_at->toISOString(),
            'importance' => $this->getNivelImportancia()
        ];
    }
}