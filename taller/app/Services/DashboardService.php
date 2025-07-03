<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Empleado;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    /**
     * Obtener estadísticas para un usuario específico
     */
    public function getStatsForUser(Usuario $user): array
    {
        // Cache por 5 minutos para mejorar rendimiento
        return Cache::remember("dashboard_stats_user_{$user->id}", 300, function () use ($user) {
            
            $stats = [
                'usuarios_total' => 0,
                'usuarios_activos' => 0,
                'empleados_total' => 0,
                'empleados_activos' => 0,
            ];

            try {
                // Estadísticas básicas para todos
                $stats['usuarios_total'] = Usuario::count();
                $stats['usuarios_activos'] = Usuario::where('activo', true)->count();
                
                // Si el usuario tiene permisos de administrador
                if ($user->isSuperAdmin() || $user->hasRole(['Super Admin', 'Gerente', 'Supervisor'])) {
                    $stats = array_merge($stats, $this->getAdminStats($user));
                }
                
                // Estadísticas específicas por rol
                if ($user->hasRole('Técnico')) {
                    $stats = array_merge($stats, $this->getTechnicianStats($user));
                }
                
                if ($user->hasRole('Vendedor')) {
                    $stats = array_merge($stats, $this->getSalesStats($user));
                }
                
            } catch (\Exception $e) {
                Log::error('Error en DashboardService: ' . $e->getMessage());
            }

            return $stats;
        });
    }

    /**
     * Obtener datos completos del dashboard según el rol del usuario
     */
    public function getDashboardData($user): array
    {
        $cacheKey = "dashboard_data_{$user->id}_" . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 3600, function () use ($user) {
            $data = [
                'usuario' => $user,
                'fecha_actual' => now()->format('d/m/Y'),
                'hora_actual' => now()->format('H:i'),
            ];

            if ($user->isSuperAdmin() || $user->hasRole(['Gerente'])) {
                $data['stats'] = $this->getAdminStats($user);
                $data['charts'] = $this->getAdminCharts();
                $data['recent_activities'] = $this->getAdminActivities();
                $data['alerts'] = $this->getSystemAlerts();
            } elseif ($user->hasRole(['Supervisor'])) {
                $data['stats'] = $this->getSupervisorStats($user);
                $data['charts'] = $this->getSupervisorCharts();
                $data['recent_activities'] = $this->getSupervisorActivities($user);
                $data['alerts'] = $this->getSupervisorAlerts();
            } else {
                $data['stats'] = $this->getEmployeeStats($user);
                $data['recent_activities'] = $this->getEmployeeActivities($user);
                $data['alerts'] = $this->getEmployeeAlerts($user);
            }

            return $data;
        });
    }

    /**
     * Estadísticas para administradores
     */
    private function getAdminStats(Usuario $user): array
    {
        $stats = [];
        
        try {
            $stats['empleados_total'] = class_exists('App\Models\Empleado') ? Empleado::count() : 0;
            $stats['empleados_activos'] = class_exists('App\Models\Empleado') ? Empleado::where('activo', true)->count() : 0;
            $stats['usuarios_conectados_hoy'] = Usuario::whereDate('ultimo_login', today())->count();
            $stats['nuevos_usuarios_mes'] = Usuario::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            // Estadísticas adicionales si existen los modelos
            if (class_exists('App\Models\Cliente')) {
                $stats['clientes_total'] = \App\Models\Cliente::count();
                $stats['clientes_activos'] = \App\Models\Cliente::where('activo', true)->count();
            }

            if (class_exists('App\Models\Producto')) {
                $stats['productos_total'] = \App\Models\Producto::count();
                $stats['productos_bajo_stock'] = \App\Models\Producto::whereRaw('stock_actual <= stock_minimo')->count();
            }

            if (class_exists('App\Models\Venta')) {
                $stats['ventas_mes'] = \App\Models\Venta::whereMonth('created_at', now()->month)->count();
                $stats['ingresos_mes'] = \App\Models\Venta::whereMonth('created_at', now()->month)->sum('total');
            }

            if (class_exists('App\Models\Reparacion')) {
                $stats['reparaciones_pendientes'] = \App\Models\Reparacion::whereIn('estado', ['pendiente', 'en_proceso'])->count();
                $stats['reparaciones_completadas_mes'] = \App\Models\Reparacion::where('estado', 'completado')
                    ->whereMonth('updated_at', now()->month)->count();
            }
                
        } catch (\Exception $e) {
            Log::error('Error en getAdminStats: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Estadísticas para supervisores
     */
    private function getSupervisorStats(Usuario $user): array
    {
        $stats = [];
        
        try {
            // Estadísticas básicas
            $stats = array_merge($stats, $this->getAdminStats($user));
            
            // Estadísticas específicas de supervisión
            if (class_exists('App\Models\Empleado')) {
                $equipoIds = $this->getTeamMemberIds($user);
                $stats['equipo_total'] = count($equipoIds);
                $stats['equipo_activo_hoy'] = Usuario::whereIn('empleado_id', $equipoIds)
                    ->whereDate('ultimo_login', today())->count();
            }
            
        } catch (\Exception $e) {
            Log::error('Error en getSupervisorStats: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Estadísticas para empleados
     */
    private function getEmployeeStats(Usuario $user): array
    {
        $stats = [];
        
        try {
            $stats['ultimo_acceso'] = $user->ultimo_login ? $user->ultimo_login->diffForHumans() : 'Primer acceso';
            $stats['sesiones_mes'] = $this->getSesionesMes($user->id);
            
            // Agregar estadísticas según rol
            if ($user->hasRole('Técnico')) {
                $stats = array_merge($stats, $this->getTechnicianStats($user));
            }
            
            if ($user->hasRole('Vendedor')) {
                $stats = array_merge($stats, $this->getSalesStats($user));
            }
            
        } catch (\Exception $e) {
            Log::error('Error en getEmployeeStats: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Estadísticas para técnicos
     */
    private function getTechnicianStats(Usuario $user): array
    {
        $stats = [];
        
        try {
            // Si existe el modelo Reparacion
            if (class_exists('App\Models\Reparacion')) {
                $stats['mis_reparaciones_pendientes'] = \App\Models\Reparacion::where('tecnico_id', $user->empleado_id ?? $user->id)
                    ->whereIn('estado', ['pendiente', 'en_proceso'])
                    ->count();
                    
                $stats['mis_reparaciones_completadas_mes'] = \App\Models\Reparacion::where('tecnico_id', $user->empleado_id ?? $user->id)
                    ->where('estado', 'completado')
                    ->whereMonth('updated_at', now()->month)
                    ->count();
            }
            
        } catch (\Exception $e) {
            Log::error('Error en getTechnicianStats: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Estadísticas para vendedores
     */
    private function getSalesStats(Usuario $user): array
    {
        $stats = [];
        
        try {
            // Si existe el modelo Venta
            if (class_exists('App\Models\Venta')) {
                $stats['mis_ventas_mes'] = \App\Models\Venta::where('vendedor_id', $user->empleado_id ?? $user->id)
                    ->whereMonth('created_at', now()->month)
                    ->count();
                    
                $stats['mi_monto_ventas_mes'] = \App\Models\Venta::where('vendedor_id', $user->empleado_id ?? $user->id)
                    ->whereMonth('created_at', now()->month)
                    ->sum('total');
            }
            
        } catch (\Exception $e) {
            Log::error('Error en getSalesStats: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Gráficos para administradores
     */
    private function getAdminCharts(): array
    {
        $charts = [];
        
        try {
            if (class_exists('App\Models\Venta')) {
                $charts['ventas_mensuales'] = $this->getVentasMensualesChart();
            }
            
            if (class_exists('App\Models\Reparacion')) {
                $charts['reparaciones_estados'] = $this->getReparacionesEstadosChart();
            }
            
        } catch (\Exception $e) {
            Log::error('Error en getAdminCharts: ' . $e->getMessage());
        }
        
        return $charts;
    }

    /**
     * Gráficos para supervisores
     */
    private function getSupervisorCharts(): array
    {
        return [
            'rendimiento_equipo' => $this->getRendimientoEquipoChart(),
        ];
    }

    /**
     * Actividades recientes para administradores
     */
    private function getAdminActivities(): array
    {
        $activities = [];
        
        try {
            if (class_exists('App\Models\Venta')) {
                $activities['ventas_recientes'] = \App\Models\Venta::with(['cliente', 'empleado'])
                    ->latest()->limit(5)->get();
            }
            
            if (class_exists('App\Models\Cliente')) {
                $activities['clientes_nuevos'] = \App\Models\Cliente::latest()->limit(3)->get();
            }
            
        } catch (\Exception $e) {
            Log::error('Error en getAdminActivities: ' . $e->getMessage());
        }
        
        return $activities;
    }

    /**
     * Alertas del sistema
     */
    private function getSystemAlerts(): array
    {
        $alerts = [];

        try {
            // Stock bajo
            if (class_exists('App\Models\Producto')) {
                $stockBajo = \App\Models\Producto::whereRaw('stock_actual <= stock_minimo')->count();
                if ($stockBajo > 0) {
                    $alerts[] = [
                        'type' => 'warning',
                        'title' => 'Stock Bajo',
                        'message' => "{$stockBajo} productos con stock bajo",
                        'priority' => 'high',
                    ];
                }
            }

            // Usuarios inactivos
            $usuariosInactivos = Usuario::where('activo', false)->count();
            if ($usuariosInactivos > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'Usuarios Inactivos',
                    'message' => "{$usuariosInactivos} usuarios desactivados",
                    'priority' => 'low',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error en getSystemAlerts: ' . $e->getMessage());
        }

        return $alerts;
    }

    // Métodos auxiliares

    private function getTeamMemberIds($supervisor): array
    {
        try {
            if (class_exists('App\Models\Empleado')) {
                return Empleado::where('supervisor_id', $supervisor->empleado_id)
                    ->pluck('id')
                    ->toArray();
            }
        } catch (\Exception $e) {
            Log::error('Error en getTeamMemberIds: ' . $e->getMessage());
        }
        
        return [];
    }

    private function getSesionesMes(int $userId): int
    {
        // Implementar lógica para contar sesiones del mes
        // Esto requeriría una tabla de logs de sesiones
        return 0;
    }

    private function getVentasMensualesChart(): array
    {
        // Implementación básica
        return [
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            'datasets' => [
                [
                    'label' => 'Ventas Mensuales',
                    'data' => [0, 0, 0, 0, 0, 0],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ]
            ]
        ];
    }

    private function getReparacionesEstadosChart(): array
    {
        // Implementación básica
        return [
            'labels' => ['Pendiente', 'En Proceso', 'Completado'],
            'datasets' => [
                [
                    'data' => [0, 0, 0],
                    'backgroundColor' => ['#6B7280', '#3B82F6', '#10B981'],
                ]
            ]
        ];
    }

    private function getRendimientoEquipoChart(): array
    {
        return [
            'labels' => [],
            'datasets' => []
        ];
    }

    private function getSupervisorActivities($user): array
    {
        return [
            'ventas_equipo' => [],
            'reparaciones_equipo' => [],
            'alertas_equipo' => [],
        ];
    }

    private function getSupervisorAlerts(): array
    {
        return [];
    }

    private function getEmployeeActivities($user): array
    {
        return [
            'mis_actividades' => [],
            'notificaciones' => [],
        ];
    }

    private function getEmployeeAlerts($user): array
    {
        return [];
    }

    /**
     * Limpiar cache de estadísticas
     */
    public function clearStatsCache(?int $userId = null): void
    {
        if ($userId) {
            Cache::forget("dashboard_stats_user_{$userId}");
        } else {
            // Limpiar todas las estadísticas (usar con cuidado)
            $users = Usuario::pluck('id');
            foreach ($users as $id) {
                Cache::forget("dashboard_stats_user_{$id}");
            }
        }
    }
}