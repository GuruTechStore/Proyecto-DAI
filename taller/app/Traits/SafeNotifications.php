<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;

trait SafeNotifications
{
    /**
     * Obtener notificaciones de forma segura
     */
    public function getSafeNotifications($limit = 5)
    {
        try {
            if (Schema::hasTable('notifications')) {
                return $this->notifications()->latest()->limit($limit)->get();
            }
        } catch (\Exception $e) {
            // Log del error si es necesario
        }
        
        // Retornar colección vacía si no existe la tabla
        return collect();
    }
    
    /**
     * Obtener notificaciones no leídas de forma segura
     */
    public function getSafeUnreadNotifications()
    {
        try {
            if (Schema::hasTable('notifications')) {
                return $this->unreadNotifications;
            }
        } catch (\Exception $e) {
            // Log del error si es necesario
        }
        
        return collect();
    }
    
    /**
     * Contar notificaciones no leídas de forma segura
     */
    public function getSafeUnreadNotificationsCount()
    {
        try {
            if (Schema::hasTable('notifications')) {
                return $this->unreadNotifications->count();
            }
        } catch (\Exception $e) {
            // Log del error si es necesario
        }
        
        return 0;
    }
}