{{-- resources/views/modules/reparaciones/partials/status-change-modal.blade.php --}}
<!-- Modal para cambiar estado de reparación -->
<div x-show="showChangeStatusModal" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    
    <!-- Modal -->
    <div class="flex min-h-screen items-center justify-center p-4">
        <div x-show="showChangeStatusModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             @click.away="showChangeStatusModal = false"
             class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-xl">
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Cambiar Estado de Reparación
                    </h3>
                    <button @click="showChangeStatusModal = false" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Form -->
            <form @submit.prevent="submitStatusChange()">
                <div class="px-6 py-4 space-y-4">
                    
                    <!-- Estado actual -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado Actual:</span>
                            <span class="status-badge" :class="'status-' + currentStatus" x-text="currentStatus"></span>
                        </div>
                    </div>
                    
                    <!-- Nuevo estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Nuevo Estado <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <template x-for="status in availableStatuses" :key="status.value">
                                <label class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <input type="radio" 
                                           x-model="newStatus" 
                                           :value="status.value"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600">
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="status.label"></span>
                                            <span class="status-badge" :class="'status-' + status.value" x-text="status.label"></span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="status.description"></p>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                    
                    <!-- Observaciones -->
                    <div>
                        <label for="status_observations" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Observaciones
                        </label>
                        <textarea id="status_observations" 
                                  x-model="statusObservations"
                                  rows="3"
                                  class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500"
                                  placeholder="Describe los detalles del cambio de estado..."></textarea>
                    </div>
                    
                    <!-- Notificar al cliente -->
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" 
                               id="notify_client" 
                               x-model="notifyClient"
                               class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
                        <label for="notify_client" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Notificar al cliente sobre el cambio de estado
                        </label>
                    </div>
                    
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                    <button type="button" 
                            @click="showChangeStatusModal = false"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="!newStatus"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Cambiar Estado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variables para el modal de cambio de estado
const statusChangeModalData = {
    showChangeStatusModal: false,
    currentStatus: '{{ $reparacion->estado ?? '' }}',
    newStatus: '',
    statusObservations: '',
    notifyClient: true,
    
    availableStatuses: [
        {
            value: 'recibido',
            label: 'Recibido',
            description: 'El equipo ha sido recibido y está en espera'
        },
        {
            value: 'diagnosticando',
            label: 'Diagnosticando', 
            description: 'El equipo está siendo evaluado para determinar el problema'
        },
        {
            value: 'reparando',
            label: 'Reparando',
            description: 'El equipo está siendo reparado actualmente'
        },
        {
            value: 'completado',  
            label: 'Completado',
            description: 'La reparación ha sido completada satisfactoriamente'
        },
        {
            value: 'entregado', 
            label: 'Entregado', 
            description: 'El equipo ha sido entregado al cliente'
        },
        {
            value: 'cancelado', 
            label: 'Cancelado',
            description: 'La reparación ha sido cancelada'
        }
    ],
    
    async submitStatusChange() {
        if (!this.newStatus) return;
        
        try {
            const response = await fetch(`/reparaciones/{{ $reparacion->id ?? '' }}/change-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    estado: this.newStatus,
                    observaciones: this.statusObservations,
                    notify_client: this.notifyClient
                })
            });
            
            if (response.ok) {
                // Recargar la página para mostrar los cambios
                window.location.reload();
            } else {
                const errorData = await response.json();
                alert('Error: ' + (errorData.message || 'No se pudo cambiar el estado'));
            }
        } catch (error) {
            console.error('Error changing status:', error);
            alert('Error de conexión. Inténtalo de nuevo.');
        }
    }
};
</script>