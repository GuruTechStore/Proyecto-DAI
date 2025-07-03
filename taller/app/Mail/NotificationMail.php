<?php

namespace App\Mail;

use App\Models\Notificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Notificacion $notificacion;

    /**
     * Create a new message instance.
     */
    public function __construct(Notificacion $notificacion)
    {
        $this->notificacion = $notificacion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject(),
            tags: ['notification', $this->notificacion->tipo_notificacion],
            metadata: [
                'notification_id' => $this->notificacion->id,
                'user_id' => $this->notificacion->usuario_destino_id,
                'priority' => $this->notificacion->prioridad,
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            text: 'emails.notification-text',
            with: [
                'notificacion' => $this->notificacion,
                'usuario' => $this->notificacion->usuario,
                'priorityText' => $this->getPriorityText(),
                'priorityColor' => $this->getPriorityColor(),
                'actionButton' => $this->getActionButton(),
                'companyName' => config('app.name'),
                'companyLogo' => $this->getCompanyLogo(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    private function getSubject(): string
    {
        $priority = $this->notificacion->prioridad >= 4 ? '[URGENTE] ' : '';
        return $priority . $this->notificacion->titulo;
    }

    private function getPriorityText(): string
    {
        $priorities = [
            1 => 'Baja',
            2 => 'Normal',
            3 => 'Alta',
            4 => 'Crítica',
            5 => 'Urgente'
        ];

        return $priorities[$this->notificacion->prioridad] ?? 'Normal';
    }

    private function getPriorityColor(): string
    {
        $colors = [
            1 => '#10B981', // Verde
            2 => '#3B82F6', // Azul
            3 => '#F59E0B', // Amarillo
            4 => '#F97316', // Naranja
            5 => '#EF4444', // Rojo
        ];

        return $colors[$this->notificacion->prioridad] ?? '#6B7280';
    }

    private function getActionButton(): ?array
    {
        if (!$this->notificacion->enlace_accion) {
            return null;
        }

        $typeButtons = [
            'stock_bajo' => ['text' => 'Ver Producto', 'color' => '#F59E0B'],
            'reparacion_completada' => ['text' => 'Ver Reparación', 'color' => '#10B981'],
            'venta_realizada' => ['text' => 'Ver Venta', 'color' => '#3B82F6'],
            'nuevo_cliente' => ['text' => 'Ver Cliente', 'color' => '#8B5CF6'],
            'reparacion_vencida' => ['text' => 'Ver Reparación', 'color' => '#EF4444'],
            'producto_agotado' => ['text' => 'Ver Inventario', 'color' => '#EF4444'],
        ];

        $button = $typeButtons[$this->notificacion->tipo_notificacion] ?? [
            'text' => 'Ver Detalles',
            'color' => '#6B7280'
        ];

        return [
            'url' => $this->notificacion->enlace_accion,
            'text' => $button['text'],
            'color' => $button['color'],
        ];
    }

    private function getCompanyLogo(): ?string
    {
        // Retornar URL del logo de la empresa si existe
        $logoPath = app(\App\Services\ConfigurationService::class)->get('empresa.logo');
        
        if ($logoPath && \Storage::exists($logoPath)) {
            return \Storage::url($logoPath);
        }
        
        return null;
    }
}