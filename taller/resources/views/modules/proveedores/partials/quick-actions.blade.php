{{-- resources/views/modules/proveedores/partials/quick-actions.blade.php --}}
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Acciones Rápidas
        </h3>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Gestión de proveedores
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Nuevo Proveedor -->
        @can('proveedores.crear')
        <a href="{{ route('proveedores.create') }}" 
           class="group flex flex-col items-center p-4 bg-gradient-to-br from-gestion-50 to-gestion-100 dark:from-gestion-900/20 dark:to-gestion-800/20 border border-gestion-200 dark:border-gestion-700 rounded-lg hover:shadow-md transition-all duration-200 hover:scale-105">
            <div class="p-3 bg-gestion-500 text-white rounded-full mb-3 group-hover:bg-gestion-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Nuevo Proveedor</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 text-center">
                Registrar un nuevo proveedor
            </p>
        </a>
        @endcan
        
        <!-- Importar Proveedores -->
        @can('proveedores.crear')
        <button type="button" 
                @click="openImportModal()"
                class="group flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border border-blue-200 dark:border-blue-700 rounded-lg hover:shadow-md transition-all duration-200 hover:scale-105">
            <div class="p-3 bg-blue-500 text-white rounded-full mb-3 group-hover:bg-blue-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                </svg>
            </div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Importar</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 text-center">
                Cargar desde Excel/CSV
            </p>
        </button>
        @endcan
        
        <!-- Exportar Proveedores -->
        <button type="button" 
                @click="exportData()"
                class="group flex flex-col items-center p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border border-green-200 dark:border-green-700 rounded-lg hover:shadow-md transition-all duration-200 hover:scale-105">
            <div class="p-3 bg-green-500 text-white rounded-full mb-3 group-hover:bg-green-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Exportar</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 text-center">
                Descargar lista completa
            </p>
        </button>
        
        <!-- Generar Reporte -->
        <button type="button" 
                @click="generateReport()"
                class="group flex flex-col items-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border border-purple-200 dark:border-purple-700 rounded-lg hover:shadow-md transition-all duration-200 hover:scale-105">
            <div class="p-3 bg-purple-500 text-white rounded-full mb-3 group-hover:bg-purple-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Reportes</h4>
            <p class="text-xs text-gray-600 dark:text-gray-400 text-center">
                Análisis detallado
            </p>
        </button>
        
    </div>
    
    <!-- Acciones Masivas -->
    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Acciones Masivas</h4>
        
        <div class="flex flex-wrap gap-2">
            
            @can('proveedores.editar')
            <button type="button" 
                    @click="bulkActivate()"
                    :disabled="selectedProveedores.length === 0"
                    class="inline-flex items-center px-3 py-2 text-xs font-medium text-green-700 dark:text-green-400 bg-green-100 dark:bg-green-900/20 border border-green-300 dark:border-green-700 rounded-md hover:bg-green-200 dark:hover:bg-green-900/40 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Activar Seleccionados
            </button>
            
            <button type="button" 
                    @click="bulkDeactivate()"
                    :disabled="selectedProveedores.length === 0"
                    class="inline-flex items-center px-3 py-2 text-xs font-medium text-yellow-700 dark:text-yellow-400 bg-yellow-100 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-md hover:bg-yellow-200 dark:hover:bg-yellow-900/40 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14L5 9m0 0l5-5m-5 5h14" />
                </svg>
                Desactivar Seleccionados
            </button>
            @endcan
            
            <button type="button" 
                    @click="bulkExport()"
                    :disabled="selectedProveedores.length === 0"
                    class="inline-flex items-center px-3 py-2 text-xs font-medium text-blue-700 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/20 border border-blue-300 dark:border-blue-700 rounded-md hover:bg-blue-200 dark:hover:bg-blue-900/40 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar Seleccionados
            </button>
            
            @can('proveedores.eliminar')
            <button type="button" 
                    @click="bulkDelete()"
                    :disabled="selectedProveedores.length === 0"
                    class="inline-flex items-center px-3 py-2 text-xs font-medium text-red-700 dark:text-red-400 bg-red-100 dark:bg-red-900/20 border border-red-300 dark:border-red-700 rounded-md hover:bg-red-200 dark:hover:bg-red-900/40 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Eliminar Seleccionados
            </button>
            @endcan
            
        </div>
        
        <!-- Contador de seleccionados -->
        <div x-show="selectedProveedores.length > 0" class="mt-3">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-medium" x-text="selectedProveedores.length"></span>
                <span x-text="selectedProveedores.length === 1 ? 'proveedor seleccionado' : 'proveedores seleccionados'"></span>
                <button type="button" 
                        @click="clearSelection()"
                        class="ml-2 text-gestion-600 dark:text-gestion-400 hover:text-gestion-700 dark:hover:text-gestion-300 text-sm underline">
                    Limpiar selección
                </button>
            </p>
        </div>
        
    </div>
    
</div>

<!-- Modal de Importación -->
<div x-show="showImportModal" 
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    
    <div x-transition:enter="transition-all ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition-all ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
        
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Importar Proveedores
            </h3>
            <button @click="closeImportModal()" 
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Carga masiva de proveedores desde un archivo Excel o CSV.
            </p>
            
            <!-- Drag & Drop Area -->
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-gestion-400 dark:hover:border-gestion-500 transition-colors"
                 @dragover.prevent
                 @drop.prevent="handleFileDrop($event)">
                
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                    Arrastra y suelta tu archivo aquí, o
                </p>
                
                <input type="file" 
                       id="importFile" 
                       accept=".xlsx,.xls,.csv"
                       @change="handleFileSelect($event)"
                       class="hidden">
                
                <button type="button" 
                        onclick="document.getElementById('importFile').click()"
                        class="text-gestion-600 dark:text-gestion-400 hover:text-gestion-700 dark:hover:text-gestion-300 font-medium">
                    selecciona un archivo
                </button>
                
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Formatos soportados: .xlsx, .xls, .csv (máx. 10MB)
                </p>
            </div>
        </div>
        
        <!-- Opciones de importación -->
        <div class="mb-6">
            <label class="flex items-center mb-2">
                <input type="checkbox" 
                       x-model="importOptions.updateExisting"
                       class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Actualizar proveedores existentes
                </span>
            </label>
            
            <label class="flex items-center">
                <input type="checkbox" 
                       x-model="importOptions.skipErrors"
                       class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Omitir filas con errores
                </span>
            </label>
        </div>
        
        <!-- Botones -->
        <div class="flex justify-end space-x-3">
            <button @click="closeImportModal()" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                Cancelar
            </button>
            
            <button @click="processImport()" 
                    :disabled="!selectedFile || importing"
                    class="px-4 py-2 text-sm font-medium text-white bg-gestion-600 hover:bg-gestion-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-md transition-colors">
                <span x-show="!importing">Importar</span>
                <span x-show="importing" class="flex items-center">
                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                    Importando...
                </span>
            </button>
        </div>
        
    </div>
</div>

@push('scripts')
<script>
// Funciones para quick actions
function openImportModal() {
    if (typeof Alpine !== 'undefined') {
        // Usar Alpine si está disponible
        this.showImportModal = true;
    }
}

function closeImportModal() {
    if (typeof Alpine !== 'undefined') {
        this.showImportModal = false;
        this.selectedFile = null;
    }
}

function handleFileDrop(event) {
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        this.selectedFile = files[0];
        this.validateFile();
    }
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        this.selectedFile = file;
        this.validateFile();
    }
}

async function generateReport() {
    try {
        // Mostrar indicador de carga
        if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
            Alpine.store('notifications').add({
                type: 'info',
                message: 'Generando reporte...'
            });
        }
        
        const response = await fetch('/api/proveedores/report', {
            method: 'GET',
            headers: {
                'Accept': 'application/pdf'
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `reporte_proveedores_${new Date().toISOString().split('T')[0]}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
                Alpine.store('notifications').add({
                    type: 'success',
                    message: 'Reporte generado correctamente'
                });
            }
        } else {
            throw new Error('Error en la respuesta del servidor');
        }
    } catch (error) {
        console.error('Error generating report:', error);
        if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
            Alpine.store('notifications').add({
                type: 'error',
                message: 'Error al generar el reporte'
            });
        }
    }
}

// Funciones para acciones masivas
async function bulkActivate() {
    if (this.selectedProveedores.length === 0) return;
    
    try {
        const response = await fetch('/api/proveedores/bulk-activate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                proveedores: this.selectedProveedores
            })
        });
        
        if (response.ok) {
            await this.loadProveedores();
            this.clearSelection();
            this.showSuccess('Proveedores activados correctamente');
        } else {
            throw new Error('Error en la respuesta del servidor');
        }
    } catch (error) {
        this.showError('Error al activar los proveedores');
    }
}

async function bulkDeactivate() {
    if (this.selectedProveedores.length === 0) return;
    
    if (!confirm(`¿Desactivar ${this.selectedProveedores.length} proveedores seleccionados?`)) {
        return;
    }
    
    try {
        const response = await fetch('/api/proveedores/bulk-deactivate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                proveedores: this.selectedProveedores
            })
        });
        
        if (response.ok) {
            await this.loadProveedores();
            this.clearSelection();
            this.showSuccess('Proveedores desactivados correctamente');
        } else {
            throw new Error('Error en la respuesta del servidor');
        }
    } catch (error) {
        this.showError('Error al desactivar los proveedores');
    }
}

function clearSelection() {
    if (typeof Alpine !== 'undefined') {
        this.selectedProveedores = [];
    }
}
</script>
@endpush