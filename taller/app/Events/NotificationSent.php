<?php

namespace App\Events;

use App\Models\Notificacion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Notificacion $notificacion;

    /**
     * Create a new event instance.
     */
    public function __construct(Notificacion $notificacion)
    {
        $this->notificacion = $notificacion;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->notificacion->usuario_destino_id),
            new Channel('notifications.global'), // Para administradores
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notificacion->id,
            'tipo' => $this->notificacion->tipo_notificacion,
            'titulo' => $this->notificacion->titulo,
            'mensaje' => $this->notificacion->mensaje,
            'prioridad' => $this->notificacion->prioridad,
            'enlace_accion' => $this->notificacion->enlace_accion,
            'created_at' => $this->notificacion->created_at->toISOString(),
            'icon' => $this->getIconForType($this->notificacion->tipo_notificacion),
            'color' => $this->getColorForPriority($this->notificacion->prioridad),
        ];
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        return $this->notificacion->usuario_destino_id !== null;
    }

    private function getIconForType(string $type): string
    {
        $icons = [
            'stock_bajo' => 'exclamation-triangle',
            'reparacion_completada' => 'check-circle',
            'venta_realizada' => 'currency-dollar',
            'nuevo_cliente' => 'user-plus',
            'reparacion_vencida' => 'clock',
            'producto_agotado' => 'x-circle',
            'empleado_inactivo' => 'user-minus',
            'mantenimiento_sistema' => 'cog',
        ];

        return $icons[$type] ?? 'bell';
    }

    private function getColorForPriority(int $priority): string
    {
        $colors = [
            1 => 'green',
            2 => 'blue',
            3 => 'yellow',
            4 => 'orange',
            5 => 'red',
        ];

        return $colors[$priority] ?? 'gray';
    }
}