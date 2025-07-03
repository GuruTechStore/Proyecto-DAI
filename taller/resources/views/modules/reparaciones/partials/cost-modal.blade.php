{{-- resources/views/modules/reparaciones/partials/cost-modal.blade.php --}}
<!-- Modal para agregar costo -->
<div x-show="showAddCostModal" 
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
        <div x-show="showAddCostModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             @click.away="showAddCostModal = false"
             class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-xl">
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Agregar Costo de Reparación
                    </h3>
                    <button @click="showAddCostModal = false" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Form -->
            <form @submit.prevent="submitCost()">
                <div class="px-6 py-4 space-y-4">
                    
                    <!-- Información de la reparación -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Reparación</h4>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p><strong>Código:</strong> {{ $reparacion->codigo_ticket ?? '' }}</p>
                            <p><strong>Cliente:</strong> {{ $reparacion->cliente->nombre ?? '' }} {{ $reparacion->cliente->apellido ?? '' }}</p>
                        </div>
                    </div>
                    
                    <!-- Tipo de costo -->
                    <div>
                        <label for="cost_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tipo de Costo <span class="text-red-500">*</span>
                        </label>
                        <select id="cost_type" 
                                x-model="costType"
                                @change="updateConceptSuggestions()"
                                required
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="">Seleccionar tipo</option>
                            <option value="repuesto">Repuesto/Componente</option>
                            <option value="mano_obra">Mano de Obra</option>
                            <option value="servicio">Servicio Especializado</option>
                            <option value="herramienta">Herramienta/Equipo</option>
                            <option value="envio">Envío/Transporte</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    
                    <!-- Concepto -->
                    <div>
                        <label for="cost_concept" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Concepto <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="cost_concept" 
                               x-model="costConcept"
                               required
                               list="concept_suggestions"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Ej: Pantalla LCD, Soldadura, Limpieza">
                        
                        <!-- Sugerencias dinámicas -->
                        <datalist id="concept_suggestions">
                            <template x-for="suggestion in conceptSuggestions" :key="suggestion">
                                <option :value="suggestion" x-text="suggestion"></option>
                            </template>
                        </datalist>
                    </div>
                    
                    <!-- Descripción -->
                    <div>
                        <label for="cost_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Descripción Detallada
                        </label>
                        <textarea id="cost_description" 
                                  x-model="costDescription"
                                  rows="3"
                                  class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500"
                                  placeholder="Detalles adicionales sobre el costo..."></textarea>
                    </div>
                    
                    <!-- Cantidad y precio unitario -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="cost_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cantidad
                            </label>
                            <input type="number" 
                                   id="cost_quantity" 
                                   x-model="costQuantity"
                                   @input="calculateTotal()"
                                   min="1"
                                   step="1"
                                   class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500"
                                   placeholder="1">
                        </div>
                        
                        <div>
                            <label for="cost_unit_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Precio Unitario
                            </label>
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">$</div>
                                <input type="number" 
                                       id="cost_unit_price" 
                                       x-model="costUnitPrice"
                                       @input="calculateTotal()"
                                       step="0.01"
                                       min="0"
                                       class="pl-8 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total calculado -->
                    <div class="bg-gestion-50 dark:bg-gestion-900/20 border border-gestion-200 dark:border-gestion-800 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gestion-800 dark:text-gestion-200">Total:</span>
                            <span class="text-lg font-bold text-gestion-600 dark:text-gestion-400" x-text="'$' + costTotal.toFixed(2)"></span>
                        </div>
                        <p class="text-xs text-gestion-600 dark:text-gestion-400 mt-1">
                            <span x-text="costQuantity || 1"></span> × $<span x-text="(costUnitPrice || 0).toFixed(2)"></span>
                        </p>
                    </div>
                    
                    <!-- Proveedor -->
                    <div>
                        <label for="cost_supplier" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Proveedor/Origen
                        </label>
                        <input type="text" 
                               id="cost_supplier" 
                               x-model="costSupplier"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Nombre del proveedor o tienda">
                    </div>
                    
                    <!-- Fecha del gasto -->
                    <div>
                        <label for="cost_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fecha del Gasto
                        </label>
                        <input type="date" 
                               id="cost_date" 
                               x-model="costDate"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    </div>
                    
                    <!-- Facturado al cliente -->
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" 
                               id="billable_to_client" 
                               x-model="billableToClient"
                               checked
                               class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
                        <label for="billable_to_client" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Facturable al cliente
                        </label>
                    </div>
                    
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                    <button type="button" 
                            @click="showAddCostModal = false"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="!costType || !costConcept || costTotal <= 0"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Agregar Costo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Variables para el modal de costos
const costModalData = {
    showAddCostModal: false,
    costType: '',
    costConcept: '',
    costDescription: '',
    costQuantity: 1,
    costUnitPrice: 0,
    costTotal: 0,
    costSupplier: '',
    costDate: new Date().toISOString().split('T')[0],
    billableToClient: true,
    conceptSuggestions: [],
    
    suggestions: {
        repuesto: [
            'Pantalla LCD', 'Pantalla OLED', 'Batería', 'Cargador',
            'Cable USB', 'Altavoz', 'Micrófono', 'Cámara trasera',
            'Cámara frontal', 'Puerto de carga', 'Botón home',
            'Botones volumen', 'Vibrador', 'Flex de carga',
            'Tapa trasera', 'Marco intermedio'
        ],
        mano_obra: [
            'Desmontaje completo', 'Cambio de pantalla',
            'Cambio de batería', 'Soldadura de componentes',
            'Reballing de chip', 'Limpieza interna',
            'Diagnóstico avanzado', 'Calibración',
            'Instalación de software', 'Recuperación de datos'
        ],
        servicio: [
            'Recuperación de datos', 'Desbloqueado de red',
            'Actualización de software', 'Instalación de ROM',
            'Calibración de pantalla', 'Test de componentes',
            'Limpieza por ultrasonido', 'Secado por humedad'
        ],
        herramienta: [
            'Destornilladores especiales', 'Ventosas',
            'Palancas de apertura', 'Pistola de calor',
            'Estaño', 'Flux', 'Alcohol isopropílico',
            'Adhesivo doble cara'
        ],
        envio: [
            'Envío de repuesto', 'Courier especializado',
            'Transporte al laboratorio', 'Devolución al cliente'
        ],
        otro: [
            'Consultoría técnica', 'Inspección especializada',
            'Certificación', 'Garantía extendida'
        ]
    },
    
    updateConceptSuggestions() {
        this.conceptSuggestions = this.suggestions[this.costType] || [];
    },
    
    calculateTotal() {
        const quantity = parseFloat(this.costQuantity) || 1;
        const unitPrice = parseFloat(this.costUnitPrice) || 0;
        this.costTotal = quantity * unitPrice;
    },
    
    async submitCost() {
        if (!this.costType || !this.costConcept || this.costTotal <= 0) return;
        
        try {
            const response = await fetch(`/reparaciones/{{ $reparacion->id ?? '' }}/add-cost`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tipo: this.costType,
                    concepto: this.costConcept,
                    descripcion: this.costDescription,
                    cantidad: this.costQuantity,
                    precio_unitario: this.costUnitPrice,
                    monto: this.costTotal,
                    proveedor: this.costSupplier,
                    fecha_gasto: this.costDate,
                    facturable_cliente: this.billableToClient
                })
            });
            
            if (response.ok) {
                // Limpiar formulario
                this.costType = '';
                this.costConcept = '';
                this.costDescription = '';
                this.costQuantity = 1;
                this.costUnitPrice = 0;
                this.costTotal = 0;
                this.costSupplier = '';
                this.costDate = new Date().toISOString().split('T')[0];
                this.billableToClient = true;
                this.conceptSuggestions = [];
                this.showAddCostModal = false;
                
                // Recargar la página para mostrar el nuevo costo
                window.location.reload();
            } else {
                const errorData = await response.json();
                alert('Error: ' + (errorData.message || 'No se pudo guardar el costo'));
            }
        } catch (error) {
            console.error('Error saving cost:', error);
            alert('Error de conexión. Inténtalo de nuevo.');
        }
    }
};
</script>