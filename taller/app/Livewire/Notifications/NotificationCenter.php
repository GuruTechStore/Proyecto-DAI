<?php

namespace App\Livewire\Notifications;

use App\Services\NotificationService;
use App\Models\Notificacion;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationCenter extends Component
{
    use WithPagination;

    public bool $showOnlyUnread = false;
    public string $filterType = 'all';
    public bool $showDropdown = false;
    public int $unreadCount = 0;
    
    protected NotificationService $notificationService;
    
    protected $listeners = [
        'notificationReceived' => 'refreshNotifications',
        'markAllAsRead' => 'markAllAsRead',
    ];

    public function boot(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function mount()
    {
        $this->updateUnreadCount();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        
        if ($this->showDropdown) {
            $this->updateUnreadCount();
        }
    }

    public function markAsRead($notificationId)
    {
        $success = $this->notificationService->markAsRead($notificationId, auth()->id());
        
        if ($success) {
            $this->updateUnreadCount();
            $this->dispatch('notify', [
                'message' => 'Notificación marcada como leída',
                'type' => 'success'
            ]);
        }
    }

    public function markAsResolved($notificationId)
    {
        $success = $this->notificationService->markAsResolved($notificationId, auth()->id());
        
        if ($success) {
            $this->updateUnreadCount();
            $this->dispatch('notify', [
                'message' => 'Notificación resuelta',
                'type' => 'success'
            ]);
        }
    }

    public function markAllAsRead()
    {
        $count = $this->notificationService->markAllAsRead(auth()->id());
        $this->updateUnreadCount();
        
        $this->dispatch('notify', [
            'message' => "Se marcaron {$count} notificaciones como leídas",
            'type' => 'success'
        ]);
    }

    public function deleteNotification($notificationId)
    {
        $success = $this->notificationService->deleteNotification($notificationId, auth()->id());
        
        if ($success) {
            $this->updateUnreadCount();
            $this->dispatch('notify', [
                'message' => 'Notificación eliminada',
                'type' => 'success'
            ]);
        }
    }

    public function bulkMarkAsRead($notificationIds)
    {
        $count = $this->notificationService->bulkMarkAsRead($notificationIds, auth()->id());
        $this->updateUnreadCount();
        
        $this->dispatch('notify', [
            'message' => "Se marcaron {$count} notificaciones como leídas",
            'type' => 'success'
        ]);
    }

    public function updatedShowOnlyUnread()
    {
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function refreshNotifications()
    {
        $this->updateUnreadCount();
        $this->resetPage();
    }

    private function updateUnreadCount()
    {
        $this->unreadCount = $this->notificationService->getUnreadCount(auth()->id());
    }

    public function getNotificationsProperty()
    {
        $query = Notificacion::where('usuario_destino_id', auth()->id());
        
        if ($this->showOnlyUnread) {
            $query->where('leida', false);
        }
        
        if ($this->filterType !== 'all') {
            $query->where('tipo_notificacion', $this->filterType);
        }
        
        return $query->orderBy('created_at', 'desc')
                    ->paginate(10);
    }

    public function getNotificationTypesProperty()
    {
        return Notificacion::where('usuario_destino_id', auth()->id())
            ->select('tipo_notificacion')
            ->distinct()
            ->pluck('tipo_notificacion')
            ->mapWithKeys(function ($type) {
                return [$type => $this->formatNotificationType($type)];
            });
    }

    public function getNotificationStatsProperty()
    {
        return $this->notificationService->getNotificationStats(auth()->id());
    }

    private function formatNotificationType(string $type): string
    {
        $types = [
            'stock_bajo' => 'Stock Bajo',
            'reparacion_completada' => 'Reparación Completada',
            'venta_realizada' => 'Venta Realizada',
            'nuevo_cliente' => 'Nuevo Cliente',
            'reparacion_vencida' => 'Reparación Vencida',
            'producto_agotado' => 'Producto Agotado',
            'empleado_inactivo' => 'Empleado Inactivo',
            'mantenimiento_sistema' => 'Mantenimiento del Sistema',
        ];
        
        return $types[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    public function getPriorityColor(int $priority): string
    {
        return match($priority) {
            1 => 'green',
            2 => 'blue', 
            3 => 'yellow',
            4 => 'orange',
            5 => 'red',
            default => 'gray',
        };
    }

    public function getPriorityIcon(int $priority): string
    {
        return match($priority) {
            1 => 'information-circle',
            2 => 'bell',
            3 => 'exclamation-triangle',
            4 => 'exclamation',
            5 => 'fire',
            default => 'bell',
        };
    }

    public function getTypeIcon(string $type): string
    {
        $icons = [
            'stock_bajo' => 'cube',
            'reparacion_completada' => 'wrench-screwdriver',
            'venta_realizada' => 'banknotes',
            'nuevo_cliente' => 'user-plus',
            'reparacion_vencida' => 'clock',
            'producto_agotado' => 'x-circle',
            'empleado_inactivo' => 'user-minus',
            'mantenimiento_sistema' => 'cog-6-tooth',
        ];
        
        return $icons[$type] ?? 'bell';
    }

    public function render()
    {
        return view('livewire.notifications.notification-center', [
            'notifications' => $this->notifications,
            'notificationTypes' => $this->notificationTypes,
            'stats' => $this->notificationStats,
        ]);
    }
}