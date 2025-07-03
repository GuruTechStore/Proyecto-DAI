{{-- resources/views/modules/reparaciones/partials/diagnostic-modal.blade.php --}}
<!-- Modal para agregar diagnóstico -->
<div x-show="showAddDiagnosticModal" 
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
        <div x-show="showAddDiagnosticModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             @click.away="showAddDiagnosticModal = false"
             class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-lg shadow-xl">
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Agregar Diagnóstico
                    </h3>
                    <button @click="showAddDiagnosticModal = false" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Form -->
            <form @submit.prevent="submitDiagnostic()">
                <div class="px-6 py-4 space-y-6">
                    
                    <!-- Información de la reparación -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Reparación</h4>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p><strong>Código:</strong> {{ $reparacion->codigo_ticket ?? '' }}</p>
                            <p><strong>Equipo:</strong> {{ $reparacion->marca ?? '' }} {{ $reparacion->modelo ?? '' }}</p>
                            <p><strong>Cliente:</strong> {{ $reparacion->cliente->nombre ?? '' }} {{ $reparacion->cliente->apellido ?? '' }}</p>
                        </div>
                    </div>
                    
                    <!-- Tipo de diagnóstico -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Tipo de Diagnóstico
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" 
                                       x-model="diagnosticType" 
                                       value="inicial"
                                       class="sr-only peer">
                                <div class="p-3 border border-gray-300 dark:border-gray-600 rounded-lg peer-checked:border-gestion-500 peer-checked:bg-gestion-50 dark:peer-checked:bg-gestion-900/20 hover:border-gestion-300 transition-colors">
                                    <div class="text-center">
                                        <svg class="w-6 h-6 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Inicial</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Primera evaluación</p>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" 
                                       x-model="diagnosticType" 
                                       value="intermedio"
                                       class="sr-only peer">
                                <div class="p-3 border border-gray-300 dark:border-gray-600 rounded-lg peer-checked:border-gestion-500 peer-checked:bg-gestion-50 dark:peer-checked:bg-gestion-900/20 hover:border-gestion-300 transition-colors">
                                    <div class="text-center">
                                        <svg class="w-6 h-6 mx-auto mb-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Intermedio</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Durante reparación</p>
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" 
                                       x-model="diagnosticType" 
                                       value="final"
                                       class="sr-only peer">
                                <div class="p-3 border border-gray-300 dark:border-gray-600 rounded-lg peer-checked:border-gestion-500 peer-checked:bg-gestion-50 dark:peer-checked:bg-gestion-900/20 hover:border-gestion-300 transition-colors">
                                    <div class="text-center">
                                        <svg class="w-6 h-6 mx-auto mb-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">Final</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Evaluación final</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Descripción del diagnóstico -->
                    <div>
                        <label for="diagnostic_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Descripción del Diagnóstico <span class="text-red-500">*</span>
                        </label>
                        <textarea id="diagnostic_description" 
                                  x-model="diagnosticDescription"
                                  rows="5"
                                  required
                                  class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500"
                                  placeholder="Describe detalladamente los hallazgos del diagnóstico..."></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Incluye: problema identificado, componentes afectados, causa raíz, solución propuesta
                        </p>
                    </div>

                    <!-- Problema identificado -->
                    <div>
                        <label for="problem_identified" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Problema Principal Identificado
                        </label>
                        <select id="problem_identified" 
                                x-model="problemIdentified"
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="">Seleccionar problema</option>
                            <option value="hardware">Problema de Hardware</option>
                            <option value="software">Problema de Software</option>
                            <option value="liquido">Daño por Líquido</option>
                            <option value="impacto">Daño por Impacto</option>
                            <option value="desgaste">Desgaste Normal</option>
                            <option value="componente">Falla de Componente</option>
                            <option value="conexion">Problema de Conexión</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <!-- Componentes afectados -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Componentes Afectados
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <template x-for="component in availableComponents" :key="component.value">
                                <label class="flex items-center p-2 border border-gray-200 dark:border-gray-600 rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <input type="checkbox" 
                                           :value="component.value"
                                           x-model="affectedComponents"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300" x-text="component.label"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                    
                    <!-- Costo estimado -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="diagnostic_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Costo Estimado de Reparación
                            </label>
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">$</div>
                                <input type="number" 
                                       id="diagnostic_cost" 
                                       x-model="diagnosticCost"
                                       step="0.01"
                                       min="0"
                                       class="pl-8 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <div>
                            <label for="estimated_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tiempo Estimado (horas)
                            </label>
                            <input type="number" 
                                   id="estimated_time" 
                                   x-model="estimatedTime"
                                   step="0.5"
                                   min="0"
                                   class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500"
                                   placeholder="0">
                        </div>
                    </div>

                    <!-- Requiere aprobación del cliente -->
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" 
                               id="requires_approval" 
                               x-model="requiresApproval"
                               class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
                        <label for="requires_approval" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Requiere aprobación del cliente antes de proceder
                        </label>
                    </div>
                    
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                    <button type="button" 
                            @click="showAddDiagnosticModal = false"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="!diagnosticDescription || !diagnosticType"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Guardar Diagnóstico
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variables para el modal de diagnóstico
const diagnosticModalData = {
    showAddDiagnosticModal: false,
    diagnosticType: 'inicial',
    diagnosticDescription: '',
    problemIdentified: '',
    affectedComponents: [],
    diagnosticCost: '',
    estimatedTime: '',
    requiresApproval: false,
    
    availableComponents: [
        { value: 'pantalla', label: 'Pantalla' },
        { value: 'bateria', label: 'Batería' },
        { value: 'altavoz', label: 'Altavoz' },
        { value: 'microfono', label: 'Micrófono' },
        { value: 'camara', label: 'Cámara' },
        { value: 'puerto_carga', label: 'Puerto de Carga' },
        { value: 'teclado', label: 'Teclado' },
        { value: 'placa_madre', label: 'Placa Madre' },
        { value: 'memoria', label: 'Memoria' },
        { value: 'procesador', label: 'Procesador' },
        { value: 'disco_duro', label: 'Disco Duro' },
        { value: 'ventilador', label: 'Ventilador' }
    ],
    
    async submitDiagnostic() {
        if (!this.diagnosticDescription || !this.diagnosticType) return;
        
        try {
            const response = await fetch(`/reparaciones/{{ $reparacion->id ?? '' }}/add-diagnostic`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tipo: this.diagnosticType,
                    descripcion: this.diagnosticDescription,
                    problema_identificado: this.problemIdentified,
                    componentes_afectados: this.affectedComponents,
                    costo_estimado: this.diagnosticCost,
                    tiempo_estimado: this.estimatedTime,
                    requiere_aprobacion: this.requiresApproval
                })
            });
            
            if (response.ok) {
                // Limpiar formulario
                this.diagnosticType = 'inicial';
                this.diagnosticDescription = '';
                this.problemIdentified = '';
                this.affectedComponents = [];
                this.diagnosticCost = '';
                this.estimatedTime = '';
                this.requiresApproval = false;
                this.showAddDiagnosticModal = false;
                
                // Recargar la página para mostrar el nuevo diagnóstico
                window.location.reload();
            } else {
                const errorData = await response.json();
                alert('Error: ' + (errorData.message || 'No se pudo guardar el diagnóstico'));
            }
        } catch (error) {
            console.error('Error saving diagnostic:', error);
            alert('Error de conexión. Inténtalo de nuevo.');
        }
    }
};
</script>