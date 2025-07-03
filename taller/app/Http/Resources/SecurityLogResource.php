<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecurityLogResource extends JsonResource
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
            'evento' => $this->evento,
            'evento_display' => $this->getEventoDisplay(),
            'evento_description' => $this->getEventoDescription(),
            
            // Usuario relacionado
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
            
            // Información de la solicitud
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'user_agent_parsed' => $this->parseUserAgent($this->user_agent),
            'location_info' => $this->getLocationInfo($this->ip_address),
            
            // Detalles del evento
            'detalles' => $this->detalles,
            'detalles_parsed' => $this->parseDetalles(),
            
            // Nivel de riesgo
            'nivel_riesgo' => $this->nivel_riesgo,
            'nivel_riesgo_display' => $this->getNivelRiesgoDisplay(),
            'nivel_riesgo_color' => $this->getNivelRiesgoColor(),
            'nivel_riesgo_icon' => $this->getNivelRiesgoIcon(),
            'risk_score' => $this->calculateRiskScore(),
            
            // Estado de resolución
            'resuelto' => $this->resuelto,
            'resuelto_por' => $this->resuelto_por,
            'resuelto_en' => $this->resuelto_en,
            'resuelto_en_human' => $this->resuelto_en ? $this->resuelto_en->diffForHumans() : null,
            'notas_resolucion' => $this->notas_resolucion,
            'accion_tomada' => $this->accion_tomada,
            
            'resuelto_por_usuario' => $this->whenLoaded('resueltoBy', function () {
                return [
                    'id' => $this->resueltoBy->id,
                    'username' => $this->resueltoBy->username,
                    'nombre_completo' => $this->resueltoBy->nombres . ' ' . $this->resueltoBy->apellidos
                ];
            }),
            
            'tiempo_resolucion' => $this->resuelto_en && $this->created_at 
                ? $this->created_at->diffInMinutes($this->resuelto_en) . ' minutos'
                : null,
            
            // Fechas
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'created_at_formatted' => $this->created_at->format('d/m/Y H:i:s'),
            'fecha_local' => $this->created_at->setTimezone(config('app.timezone'))->format('d/m/Y H:i:s'),
            'hora_evento' => $this->created_at->format('H:i:s'),
            'dia_semana' => $this->created_at->locale('es')->dayName,
            
            // Contexto adicional
            'context' => [
                'is_suspicious' => $this->isSuspicious(),
                'requires_attention' => $this->requiresAttention(),
                'is_recent' => $this->created_at >= now()->subHours(24),
                'session_info' => $this->getSessionInfo(),
                'related_events_count' => $this->getRelatedEventsCount(),
                'user_first_time' => $this->isUserFirstTime(),
                'ip_reputation' => $this->getIPReputation(),
                'geographic_anomaly' => $this->hasGeographicAnomaly()
            ],
            
            // Categorización
            'category' => $this->getEventCategory(),
            'subcategory' => $this->getEventSubcategory(),
            'tags' => $this->getEventTags(),
            
            // Métricas de impacto
            'impact' => [
                'severity' => $this->getImpactSeverity(),
                'affected_systems' => $this->getAffectedSystems(),
                'business_impact' => $this->getBusinessImpact(),
                'compliance_impact' => $this->getComplianceImpact()
            ],
            
            // Información técnica
            'technical_details' => [
                'protocol' => $this->getProtocol(),
                'method' => $this->getMethod(),
                'status_code' => $this->getStatusCode(),
                'response_time' => $this->getResponseTime(),
                'data_size' => $this->getDataSize()
            ],
            
            // Patrones y anomalías
            'patterns' => [
                'is_pattern_match' => $this->isPatternMatch(),
                'pattern_type' => $this->getPatternType(),
                'anomaly_score' => $this->getAnomalyScore(),
                'frequency_analysis' => $this->getFrequencyAnalysis()
            ],
            
            // Recomendaciones
            'recommendations' => $this->getRecommendations(),
            
            // Exportable data
            'exportable' => [
                'csv_row' => $this->toCsvRow(),
                'json_summary' => $this->toJsonSummary()
            ]
        ];
    }

    /**
     * Get display name for event
     */
    private function getEventoDisplay()
    {
        $events = [
            'login_success' => 'Inicio de sesión exitoso',
            'login_failed' => 'Intento de inicio de sesión fallido',
            'logout' => 'Cierre de sesión',
            'password_changed' => 'Cambio de contraseña',
            'password_reset_requested' => 'Solicitud de restablecimiento de contraseña',
            'password_reset_completed' => 'Restablecimiento de contraseña completado',
            'account_locked' => 'Cuenta bloqueada',
            'account_unlocked' => 'Cuenta desbloqueada',
            'email_verified' => 'Email verificado',
            '2fa_enabled' => 'Autenticación 2FA habilitada',
            '2fa_disabled' => 'Autenticación 2FA deshabilitada',
            'user_created' => 'Usuario creado',
            'user_updated' => 'Usuario actualizado',
            'user_deleted' => 'Usuario eliminado',
            'role_assigned' => 'Rol asignado',
            'role_removed' => 'Rol removido',
            'permission_granted' => 'Permiso otorgado',
            'permission_revoked' => 'Permiso revocado',
            'suspicious_activity' => 'Actividad sospechosa',
            'security_breach' => 'Brecha de seguridad',
            'data_export' => 'Exportación de datos',
            'data_import' => 'Importación de datos'
        ];

        return $events[$this->evento] ?? ucfirst(str_replace('_', ' ', $this->evento));
    }

    /**
     * Get event description
     */
    private function getEventoDescription()
    {
        $descriptions = [
            'login_success' => 'El usuario inició sesión correctamente en el sistema',
            'login_failed' => 'Intento fallido de inicio de sesión con credenciales incorrectas',
            'account_locked' => 'La cuenta fue bloqueada por motivos de seguridad',
            'password_changed' => 'El usuario cambió su contraseña de acceso',
            'suspicious_activity' => 'Se detectó actividad sospechosa en la cuenta',
            'user_created' => 'Se creó una nueva cuenta de usuario en el sistema',
            'role_assigned' => 'Se asignó un nuevo rol al usuario'
        ];

        return $descriptions[$this->evento] ?? 'Evento de seguridad registrado';
    }

    /**
     * Get risk level display
     */
    private function getNivelRiesgoDisplay()
    {
        $levels = [
            'low' => 'Bajo',
            'medium' => 'Medio',
            'high' => 'Alto',
            'critical' => 'Crítico'
        ];

        return $levels[$this->nivel_riesgo] ?? 'Desconocido';
    }

    /**
     * Get risk level color
     */
    private function getNivelRiesgoColor()
    {
        $colors = [
            'low' => '#10b981',      // green
            'medium' => '#f59e0b',   // yellow
            'high' => '#f97316',     // orange
            'critical' => '#ef4444'  // red
        ];

        return $colors[$this->nivel_riesgo] ?? '#6b7280';
    }

    /**
     * Get risk level icon
     */
    private function getNivelRiesgoIcon()
    {
        $icons = [
            'low' => 'shield-check',
            'medium' => 'exclamation-triangle',
            'high' => 'exclamation-circle',
            'critical' => 'x-circle'
        ];

        return $icons[$this->nivel_riesgo] ?? 'question-mark-circle';
    }

    /**
     * Calculate risk score (0-100)
     */
    private function calculateRiskScore()
    {
        $baseScore = [
            'low' => 25,
            'medium' => 50,
            'high' => 75,
            'critical' => 100
        ][$this->nivel_riesgo] ?? 0;

        // Adjust based on context
        if ($this->isSuspicious()) $baseScore += 10;
        if ($this->hasGeographicAnomaly()) $baseScore += 15;
        if ($this->isUserFirstTime()) $baseScore += 5;

        return min(100, $baseScore);
    }

    /**
     * Parse user agent information
     */
    private function parseUserAgent($userAgent)
    {
        if (!$userAgent) return null;

        $info = [
            'browser' => 'Unknown',
            'platform' => 'Unknown',
            'device' => 'Desktop',
            'is_mobile' => false,
            'is_bot' => false
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

        // Bot detection
        if (str_contains(strtolower($userAgent), 'bot') || 
            str_contains(strtolower($userAgent), 'crawler') ||
            str_contains(strtolower($userAgent), 'spider')) {
            $info['is_bot'] = true;
        }

        return $info;
    }

    /**
     * Get location information from IP
     */
    private function getLocationInfo($ip)
    {
        // Basic implementation - in production use a real geolocation service
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return [
                'country' => 'Local',
                'city' => 'Localhost',
                'is_local' => true,
                'is_vpn' => false,
                'is_tor' => false
            ];
        }

        // Placeholder - integrate with IPInfo, MaxMind, etc.
        return [
            'country' => 'Unknown',
            'city' => 'Unknown',
            'is_local' => false,
            'is_vpn' => false,
            'is_tor' => false,
            'timezone' => null,
            'isp' => null
        ];
    }

    /**
     * Parse event details JSON
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
     * Check if event is suspicious
     */
    private function isSuspicious()
    {
        $suspiciousEvents = [
            'login_failed', 'account_locked', 'suspicious_activity',
            'security_breach', 'unauthorized_access'
        ];

        return in_array($this->evento, $suspiciousEvents) ||
               $this->nivel_riesgo === 'critical' ||
               str_contains($this->evento, 'failed') ||
               str_contains($this->evento, 'suspicious');
    }

    /**
     * Check if event requires attention
     */
    private function requiresAttention()
    {
        return !$this->resuelto && 
               in_array($this->nivel_riesgo, ['high', 'critical']);
    }

    /**
     * Get session information
     */
    private function getSessionInfo()
    {
        $detalles = $this->parseDetalles();
        
        return [
            'session_id' => $detalles['session_id'] ?? null,
            'token_id' => $detalles['token_id'] ?? null,
            'duration' => $detalles['session_duration'] ?? null,
            'concurrent_sessions' => $detalles['concurrent_sessions'] ?? null
        ];
    }

    /**
     * Get related events count
     */
    private function getRelatedEventsCount()
    {
        return \App\Models\SecurityLog::where('id', '!=', $this->id)
            ->where(function($q) {
                $q->where('usuario_id', $this->usuario_id)
                  ->orWhere('ip_address', $this->ip_address);
            })
            ->where('created_at', '>=', $this->created_at->subHours(2))
            ->where('created_at', '<=', $this->created_at->addHours(2))
            ->count();
    }

    /**
     * Check if this is user's first time event
     */
    private function isUserFirstTime()
    {
        if (!$this->usuario_id) return false;

        return \App\Models\SecurityLog::where('usuario_id', $this->usuario_id)
            ->where('evento', $this->evento)
            ->where('created_at', '<', $this->created_at)
            ->doesntExist();
    }

    /**
     * Get IP reputation
     */
    private function getIPReputation()
    {
        // Simplified - would integrate with threat intelligence feeds
        $knownBadIPs = ['192.168.1.100']; // Example
        
        return [
            'is_malicious' => in_array($this->ip_address, $knownBadIPs),
            'reputation_score' => 85, // 0-100, higher is better
            'source' => 'internal'
        ];
    }

    /**
     * Check for geographic anomaly
     */
    private function hasGeographicAnomaly()
    {
        if (!$this->usuario_id) return false;

        // Check if login from different country than usual
        $recentLogs = \App\Models\SecurityLog::where('usuario_id', $this->usuario_id)
            ->where('evento', 'login_success')
            ->where('created_at', '>=', now()->subDays(30))
            ->where('id', '!=', $this->id)
            ->limit(10)
            ->get();

        // Simplified logic - would use actual geolocation
        return $recentLogs->isNotEmpty() && 
               $recentLogs->pluck('ip_address')->unique()->count() > 3;
    }

    /**
     * Get event category
     */
    private function getEventCategory()
    {
        if (str_contains($this->evento, 'login')) return 'authentication';
        if (str_contains($this->evento, 'password')) return 'authentication';
        if (str_contains($this->evento, 'user')) return 'user_management';
        if (str_contains($this->evento, 'role')) return 'authorization';
        if (str_contains($this->evento, 'permission')) return 'authorization';
        if (str_contains($this->evento, 'suspicious')) return 'security';
        if (str_contains($this->evento, 'data')) return 'data_management';
        
        return 'general';
    }

    /**
     * Get event subcategory
     */
    private function getEventSubcategory()
    {
        $subcategories = [
            'login_success' => 'successful_login',
            'login_failed' => 'failed_login',
            'password_changed' => 'password_management',
            'user_created' => 'account_creation',
            'role_assigned' => 'privilege_escalation'
        ];

        return $subcategories[$this->evento] ?? 'other';
    }

    /**
     * Get event tags
     */
    private function getEventTags()
    {
        $tags = [];
        
        if ($this->isSuspicious()) $tags[] = 'suspicious';
        if ($this->nivel_riesgo === 'critical') $tags[] = 'critical';
        if ($this->hasGeographicAnomaly()) $tags[] = 'geographic_anomaly';
        if ($this->isUserFirstTime()) $tags[] = 'first_time';
        if (!$this->resuelto && $this->requiresAttention()) $tags[] = 'needs_attention';
        
        return $tags;
    }

    /**
     * Additional helper methods for impact, technical details, patterns, etc.
     */
    private function getImpactSeverity() { return $this->nivel_riesgo; }
    private function getAffectedSystems() { return ['web_app']; }
    private function getBusinessImpact() { return 'low'; }
    private function getComplianceImpact() { return 'none'; }
    private function getProtocol() { return 'HTTPS'; }
    private function getMethod() { return 'POST'; }
    private function getStatusCode() { return 200; }
    private function getResponseTime() { return null; }
    private function getDataSize() { return null; }
    private function isPatternMatch() { return false; }
    private function getPatternType() { return null; }
    private function getAnomalyScore() { return 0; }
    private function getFrequencyAnalysis() { return []; }

    /**
     * Get recommendations based on event
     */
    private function getRecommendations()
    {
        $recommendations = [];

        if ($this->evento === 'login_failed' && !$this->resuelto) {
            $recommendations[] = 'Verificar si es un ataque de fuerza bruta';
            $recommendations[] = 'Considerar bloquear la IP si hay múltiples intentos';
        }

        if ($this->nivel_riesgo === 'critical' && !$this->resuelto) {
            $recommendations[] = 'Investigar inmediatamente';
            $recommendations[] = 'Notificar al equipo de seguridad';
        }

        return $recommendations;
    }

    /**
     * Convert to CSV row
     */
    private function toCsvRow()
    {
        return [
            $this->id,
            $this->evento,
            $this->usuario?->username ?? 'N/A',
            $this->ip_address,
            $this->nivel_riesgo,
            $this->resuelto ? 'Sí' : 'No',
            $this->created_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Convert to JSON summary
     */
    private function toJsonSummary()
    {
        return [
            'id' => $this->id,
            'event' => $this->evento,
            'user' => $this->usuario?->username,
            'ip' => $this->ip_address,
            'risk' => $this->nivel_riesgo,
            'resolved' => $this->resuelto,
            'timestamp' => $this->created_at->toISOString()
        ];
    }
}