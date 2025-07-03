<?php

// app/Services/NotificationService.php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Reparacion;
use App\Models\Venta;

class NotificationService
{
    /**
     * Enviar notificación a usuario específico
     */
    public function enviarAUsuario($usuarioId, $tipo, $titulo, $mensaje, $opciones = [])
    {
        return Notificacion::crear($tipo, $usuarioId, $titulo, $mensaje, $opciones);
    }

    /**
     * Enviar notificación a múltiples usuarios
     */
    public function enviarAUsuarios(array $usuariosIds, $tipo, $titulo, $mensaje, $opciones = [])
    {
        $notificaciones = [];
        
        foreach ($usuariosIds as $usuarioId) {
            $notificaciones[] = $this->enviarAUsuario($usuarioId, $tipo, $titulo, $mensaje, $opciones);
        }
        
        return $notificaciones;
    }

    /**
     * Enviar notificación a usuarios con roles específicos
     */
    public function enviarAPorRoles(array $roles, $tipo, $titulo, $mensaje, $opciones = [])
    {
        $usuarios = Usuario::whereHas('roles', function($q) use ($roles) {
            $q->whereIn('name', $roles);
        })->pluck('id');

        return $this->enviarAUsuarios($usuarios->toArray(), $tipo, $titulo, $mensaje, $opciones);
    }

    /**
     * Notificaciones específicas del sistema
     */
    public function notificarStockBajo(Producto $producto)
    {
        $roles = ['Super Admin', 'Gerente', 'Supervisor'];
        
        return $this->enviarAPorRoles(
            $roles,
            Notificacion::TIPO_STOCK_BAJO,
            'Stock Bajo: ' . $producto->nombre,
            "El producto '{$producto->nombre}' (Código: {$producto->codigo}) tiene stock bajo: {$producto->stock} unidades disponibles (Mínimo: {$producto->stock_minimo})",
            [
                'prioridad' => $producto->stock <= 0 ? Notificacion::PRIORIDAD_CRITICA : Notificacion::PRIORIDAD_ALTA,
                'entidad' => 'producto',
                'entidad_id' => $producto->id,
                'enlace' => route('productos.show', $producto->id)
            ]
        );
    }

    public function notificarReparacionCompletada(Reparacion $reparacion)
    {
        // Notificar al cliente si tiene usuario
        $notificaciones = [];
        
        if ($reparacion->cliente?->usuario_id) {
            $notificaciones[] = $this->enviarAUsuario(
                $reparacion->cliente->usuario_id,
                Notificacion::TIPO_REPARACION_COMPLETADA,
                'Tu Reparación está Lista',
                "La reparación de tu {$reparacion->equipo->descripcion_completa} está completada y lista para recoger.",
                [
                    'prioridad' => Notificacion::PRIORIDAD_ALTA,
                    'entidad' => 'reparacion',
                    'entidad_id' => $reparacion->id,
                    'enlace' => route('reparaciones.show', $reparacion->id)
                ]
            );
        }

        // Notificar a supervisores
        $supervisores = Usuario::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Gerente', 'Supervisor']);
        })->pluck('id');

        foreach ($supervisores as $supervisorId) {
            $notificaciones[] = $this->enviarAUsuario(
                $supervisorId,
                Notificacion::TIPO_REPARACION_COMPLETADA,
                'Reparación Completada',
                "La reparación {$reparacion->codigo_ticket} ha sido completada por {$reparacion->empleado->nombre_completo}",
                [
                    'prioridad' => Notificacion::PRIORIDAD_NORMAL,
                    'entidad' => 'reparacion',
                    'entidad_id' => $reparacion->id,
                    'enlace' => route('reparaciones.show', $reparacion->id)
                ]
            );
        }

        return $notificaciones;
    }

    public function notificarVentaRealizada(Venta $venta)
    {
        $roles = ['Super Admin', 'Gerente', 'Supervisor'];
        
        return $this->enviarAPorRoles(
            $roles,
            Notificacion::TIPO_VENTA_REALIZADA,
            'Nueva Venta Registrada',
            "Se registró una venta por {$venta->total} realizada por {$venta->empleado->nombre_completo}",
            [
                'prioridad' => $venta->total >= 1000 ? Notificacion::PRIORIDAD_ALTA : Notificacion::PRIORIDAD_NORMAL,
                'entidad' => 'venta',
                'entidad_id' => $venta->id,
                'enlace' => route('ventas.show', $venta->id)
            ]
        );
    }

    public function notificarUsuarioBloqueado(Usuario $usuario)
    {
        $admins = Usuario::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Gerente']);
        })->pluck('id');

        $notificaciones = [];
        
        foreach ($admins as $adminId) {
            $notificaciones[] = $this->enviarAUsuario(
                $adminId,
                Notificacion::TIPO_USUARIO_BLOQUEADO,
                'Usuario Bloqueado por Seguridad',
                "El usuario {$usuario->username} ha sido bloqueado por múltiples intentos de login fallidos",
                [
                    'prioridad' => Notificacion::PRIORIDAD_CRITICA,
                    'entidad' => 'usuario',
                    'entidad_id' => $usuario->id,
                    'enlace' => route('admin.usuarios.show', $usuario->id)
                ]
            );
        }

        return $notificaciones;
    }

    public function notificarBackupCompletado($resultado)
    {
        $admins = Usuario::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Gerente']);
        })->pluck('id');

        $exito = $resultado['exito'] ?? false;
        $tamaño = $resultado['tamaño'] ?? 'N/A';
        
        return $this->enviarAUsuarios(
            $admins->toArray(),
            Notificacion::TIPO_BACKUP_COMPLETADO,
            $exito ? 'Backup Completado' : 'Error en Backup',
            $exito 
                ? "El backup automático se completó exitosamente. Tamaño: {$tamaño}"
                : "El backup automático falló. Revisa los logs del sistema.",
            [
                'prioridad' => $exito ? Notificacion::PRIORIDAD_NORMAL : Notificacion::PRIORIDAD_CRITICA,
                'entidad' => 'sistema'
            ]
        );
    }

    /**
     * Notificaciones masivas del sistema
     */
    public function notificarMantenimientoProgramado($fechaInicio, $duracionEstimada)
    {
        $todosUsuarios = Usuario::activos()->pluck('id');
        
        return $this->enviarAUsuarios(
            $todosUsuarios->toArray(),
            Notificacion::TIPO_SISTEMA,
            'Mantenimiento Programado',
            "Se realizará mantenimiento del sistema el {$fechaInicio}. Duración estimada: {$duracionEstimada}. El sistema no estará disponible durante este período.",
            [
                'prioridad' => Notificacion::PRIORIDAD_ALTA,
                'entidad' => 'sistema'
            ]
        );
    }

    public function notificarActualizacionSistema($version, $cambios = [])
    {
        $admins = Usuario::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Gerente', 'Supervisor']);
        })->pluck('id');

        $mensaje = "El sistema ha sido actualizado a la versión {$version}.";
        if (!empty($cambios)) {
            $mensaje .= " Principales cambios: " . implode(', ', array_slice($cambios, 0, 3));
        }

        return $this->enviarAUsuarios(
            $admins->toArray(),
            Notificacion::TIPO_SISTEMA,
            'Sistema Actualizado',
            $mensaje,
            [
                'prioridad' => Notificacion::PRIORIDAD_NORMAL,
                'entidad' => 'sistema'
            ]
        );
    }

    /**
     * Gestión de notificaciones
     */
    public function marcarComoLeidaPorUsuario($usuarioId, $notificacionId = null)
    {
        $query = Notificacion::where('usuario_destino_id', $usuarioId)->noLeidas();
        
        if ($notificacionId) {
            $query->where('id', $notificacionId);
        }
        
        return $query->update([
            'leida' => true,
            'fecha_lectura' => now()
        ]);
    }

    public function marcarComoResueltaPorUsuario($usuarioId, $notificacionId)
    {
        $notificacion = Notificacion::where('usuario_destino_id', $usuarioId)
                                   ->where('id', $notificacionId)
                                   ->first();

        if ($notificacion) {
            return $notificacion->marcarComoResuelta();
        }

        return false;
    }

    public function limpiarNotificacionesAntiguas($dias = 90)
    {
        return Notificacion::limpiarAntiguas($dias);
    }

    public function getResumenPorUsuario($usuarioId)
    {
        $usuario = Usuario::find($usuarioId);
        
        if (!$usuario) {
            return null;
        }

        return [
            'total_no_leidas' => $usuario->contarNotificacionesNoLeidas(),
            'criticas' => $usuario->contarNotificacionesCriticas(),
            'recientes' => $usuario->notificaciones()
                                   ->recientes(24)
                                   ->ordenadaPorPrioridad()
                                   ->limit(5)
                                   ->get(),
            'por_tipo' => $usuario->notificaciones()
                                  ->noLeidas()
                                  ->selectRaw('tipo_notificacion, count(*) as total')
                                  ->groupBy('tipo_notificacion')
                                  ->pluck('total', 'tipo_notificacion')
                                  ->toArray()
        ];
    }

    /**
     * Notificaciones automáticas del sistema
     */
    public function verificarYNotificarStockBajo()
    {
        $productosStockBajo = Producto::stockBajo()->activos()->get();
        $notificacionesEnviadas = 0;

        foreach ($productosStockBajo as $producto) {
            // Verificar si ya se notificó hoy
            $yaNotificado = Notificacion::where('tipo_notificacion', Notificacion::TIPO_STOCK_BAJO)
                                       ->where('entidad_id', $producto->id)
                                       ->where('fecha_creacion', '>=', now()->startOfDay())
                                       ->exists();

            if (!$yaNotificado) {
                $this->notificarStockBajo($producto);
                $notificacionesEnviadas++;
            }
        }

        return $notificacionesEnviadas;
    }

    public function verificarPasswordsExpiradas()
    {
        $usuarios = Usuario::debenCambiarPassword()->get();
        $notificacionesEnviadas = 0;

        foreach ($usuarios as $usuario) {
            // Verificar si ya se notificó esta semana
            $yaNotificado = Notificacion::where('usuario_destino_id', $usuario->id)
                                       ->where('tipo_notificacion', Notificacion::TIPO_SISTEMA)
                                       ->where('titulo', 'LIKE', '%contraseña%')
                                       ->where('fecha_creacion', '>=', now()->subWeek())
                                       ->exists();

            if (!$yaNotificado) {
                $this->enviarAUsuario(
                    $usuario->id,
                    Notificacion::TIPO_SISTEMA,
                    'Contraseña Próxima a Expirar',
                    'Tu contraseña expirará pronto. Se recomienda cambiarla para mantener la seguridad de tu cuenta.',
                    [
                        'prioridad' => Notificacion::PRIORIDAD_ALTA,
                        'entidad' => 'usuario',
                        'entidad_id' => $usuario->id,
                        'enlace' => route('perfil.password')
                    ]
                );
                $notificacionesEnviadas++;
            }
        }

        return $notificacionesEnviadas;
    }

    /**
     * Estadísticas de notificaciones
     */
    public function getEstadisticasGlobales($dias = 30)
    {
        $fechaInicio = now()->subDays($dias);

        return [
            'total_enviadas' => Notificacion::where('fecha_creacion', '>=', $fechaInicio)->count(),
            'por_tipo' => Notificacion::where('fecha_creacion', '>=', $fechaInicio)
                                     ->selectRaw('tipo_notificacion, count(*) as total')
                                     ->groupBy('tipo_notificacion')
                                     ->pluck('total', 'tipo_notificacion')
                                     ->toArray(),
            'por_prioridad' => Notificacion::where('fecha_creacion', '>=', $fechaInicio)
                                          ->selectRaw('prioridad, count(*) as total')
                                          ->groupBy('prioridad')
                                          ->pluck('total', 'prioridad')
                                          ->toArray(),
            'tasa_lectura' => $this->calcularTasaLectura($dias),
            'tiempo_promedio_lectura' => $this->calcularTiempoPromedioLectura($dias)
        ];
    }

    private function calcularTasaLectura($dias)
    {
        $total = Notificacion::where('fecha_creacion', '>=', now()->subDays($dias))->count();
        $leidas = Notificacion::where('fecha_creacion', '>=', now()->subDays($dias))
                             ->where('leida', true)
                             ->count();

        return $total > 0 ? round(($leidas / $total) * 100, 2) : 0;
    }

    private function calcularTiempoPromedioLectura($dias)
    {
        $notificaciones = Notificacion::where('fecha_creacion', '>=', now()->subDays($dias))
                                     ->whereNotNull('fecha_lectura')
                                     ->get();

        if ($notificaciones->isEmpty()) {
            return 0;
        }

        $tiempoTotal = $notificaciones->sum(function($notificacion) {
            return $notificacion->fecha_lectura->diffInMinutes($notificacion->fecha_creacion);
        });

        return round($tiempoTotal / $notificaciones->count(), 2);
    }
}