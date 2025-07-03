{{-- resources/views/shared/modals/form.blade.php --}}
@props([
    'id' => 'formModal',
    'title' => 'Formulario',
    'action' => '#',
    'method' => 'POST',
    'size' => 'lg',
    'submitText' => 'Guardar',
    'cancelText' => 'Cancelar',
    'submitColor' => 'primary',
    'loading' => false,
    'closable' => true,
    'backdrop' => true
])

@php
$sizeClasses = [
    'sm' => 'max-w-md',
    'md' => 'max-w-lg', 
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl',
    '2xl' => 'max-w-6xl'
];

$submitColors = [
    'primary' => 'bg-gestion-600 hover:bg-gestion-700 focus:ring-gestion-500',
    'success' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
    'warning' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
    'danger' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
];

$sizeClass = $sizeClasses[$size] ?? $sizeClasses['lg'];
$submitColorClass = $submitColors[$submitColor] ?? $submitColors['primary'];
@endphp

<!-- Modal -->
<div x-data="{ 
    show: false,
    loading: {{ $loading ? 'true' : 'false' }},
    open() {
        this.show = true;
        this.$nextTick(() => {
            const firstInput = this.$el.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });
    },
    close() {
        this.show = false;
        this.loading = false;
        // Reset form if needed
        const form = this.$el.querySelector('form');
        if (form) {
            form.reset();
        }
    },
    submit() {
        this.loading = true;
        const form = this.$el.querySelector('form');
        if (form) {
            form.submit();
        }
    },
    setLoading(state) {
        this.loading = state;
    }
}" 
     x-show="show" 
     x-cloak
     id="{{ $id }}"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @if($closable)@keydown.escape="close()"@endif
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    <!-- Background backdrop -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
    
    <!-- Modal panel -->
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @if($backdrop && $closable)@click.away="close()"@endif
             class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full {{ $sizeClass }}">
            
            <!-- Loading overlay -->
            <div x-show="loading" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm z-10 flex items-center justify-center">
                <div class="flex flex-col items-center space-y-3">
                    <div class="animate-spin rounded-full h-8 w-8 border-4 border-gestion-600 border-t-transparent"></div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Procesando...</p>
                </div>
            </div>
            
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 px-4 py-6 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                        {{ $title }}
                    </h3>
                    @if($closable)
                        <button type="button" 
                                @click="close()"
                                class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:ring-offset-2 transition-colors">
                            <span class="sr-only">Cerrar</span>
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
            
            <!-- Form content -->
            <form action="{{ $action }}" method="{{ $method }}" @submit.prevent="submit()" class="bg-white dark:bg-gray-800">
                @if($method !== 'GET')
                    @csrf
                @endif
                
                @if(in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
                    @method($method)
                @endif
                
                <!-- Form body -->
                <div class="px-4 py-6 sm:px-6 max-h-96 overflow-y-auto">
                    {{ $slot }}
                </div>
                
                <!-- Footer actions -->
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                            :disabled="loading"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white {{ $submitColorClass }} focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">{{ $submitText }}</span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Guardando...
                        </span>
                    </button>
                    
                    @if($closable)
                        <button type="button"
                                @click="close()"
                                :disabled="loading"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:mr-3 sm:w-auto sm:text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ $cancelText }}
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Función global para mostrar el modal de formulario
window.showFormModal = function(modalId, options = {}) {
    const modal = document.getElementById(modalId || '{{ $id }}');
    if (modal && modal.__x) {
        // Actualizar título si se proporciona
        if (options.title) {
            modal.querySelector('#modal-title').textContent = options.title;
        }
        
        // Actualizar action del formulario si se proporciona
        if (options.action) {
            const form = modal.querySelector('form');
            if (form) {
                form.action = options.action;
            }
        }
        
        // Prellenar campos si se proporcionan
        if (options.data) {
            Object.keys(options.data).forEach(key => {
                const field = modal.querySelector(`[name="${key}"]`);
                if (field) {
                    field.value = options.data[key];
                }
            });
        }
        
        modal.__x.$data.open();
    }
};

// Función para cerrar el modal
window.closeFormModal = function(modalId) {
    const modal = document.getElementById(modalId || '{{ $id }}');
    if (modal && modal.__x) {
        modal.__x.$data.close();
    }
};

// Función para establecer estado de carga
window.setFormModalLoading = function(modalId, loading) {
    const modal = document.getElementById(modalId || '{{ $id }}');
    if (modal && modal.__x) {
        modal.__x.$data.setLoading(loading);
    }
};
</script>