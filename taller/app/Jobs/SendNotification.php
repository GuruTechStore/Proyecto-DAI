<?php

namespace App\Jobs;

use App\Models\Notificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Notificacion $notificacion;
    
    public int $tries = 3;
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(Notificacion $notificacion)
    {
        $this->notificacion = $notificacion;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Log de la notificación enviada
            Log::info('Enviando notificación', [
                'id' => $this->notificacion->id,
                'tipo' => $this->notificacion->tipo_notificacion,
                'usuario_destino' => $this->notificacion->usuario_destino_id,
                'prioridad' => $this->notificacion->prioridad,
            ]);

            // Aquí puedes agregar diferentes canales de notificación
            $this->sendToWebSocket();
            
            // Si es de alta prioridad, enviar también por email
            if ($this->notificacion->prioridad >= 4) {
                $this->sendEmailNotification();
            }
            
            // Marcar como enviada en el log
            Log::info('Notificación enviada exitosamente', [
                'id' => $this->notificacion->id,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al enviar notificación', [
                'id' => $this->notificacion->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-lanzar la excepción para que el job se marque como fallido
            throw $e;
        }
    }

    /**
     * Enviar notificación via WebSocket (tiempo real)
     */
    private function sendToWebSocket(): void
    {
        // Aquí implementarías la lógica para Pusher, Soketi, o WebSockets
        // Por ahora usaremos eventos de Livewire
        
        try {
            // Simular envío de notificación en tiempo real
            // En producción, aquí usarías Pusher o similar
            broadcast(new \App\Events\NotificationSent($this->notificacion));
            
        } catch (\Exception $e) {
            Log::warning('Error enviando notificación via WebSocket', [
                'id' => $this->notificacion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Enviar notificación por email para alta prioridad
     */
    private function sendEmailNotification(): void
    {
        try {
            $usuario = $this->notificacion->usuario;
            
            if (!$usuario || !$usuario->email) {
                Log::warning('Usuario sin email para notificación', [
                    'notification_id' => $this->notificacion->id,
                    'user_id' => $this->notificacion->usuario_destino_id,
                ]);
                return;
            }

            // Enviar email usando las clases de Mail de Laravel
            \Mail::to($usuario->email)->send(
                new \App\Mail\NotificationMail($this->notificacion)
            );
            
            Log::info('Email de notificación enviado', [
                'notification_id' => $this->notificacion->id,
                'email' => $usuario->email,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error enviando email de notificación', [
                'id' => $this->notificacion->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de notificación falló completamente', [
            'notification_id' => $this->notificacion->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
        
        // Marcar la notificación con error
        $this->notificacion->update([
            'error_enviando' => true,
            'mensaje_error' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the retry delay for the job.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [1, 5, 10]; // Segundos entre reintentos
    }
}