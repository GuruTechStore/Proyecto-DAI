{{-- resources/views/shared/modals/confirmation.blade.php --}}
@props([
    'id' => 'confirmationModal',
    'title' => '¿Confirmar acción?',
    'message' => '¿Estás seguro de que deseas realizar esta acción? Esta acción no se puede deshacer.',
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'confirmColor' => 'danger',
    'icon' => 'warning',
    'size' => 'md'
])

@php
$sizeClasses = [
    'sm' => 'max-w-md',
    'md' => 'max-w-lg',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl'
];

$confirmColors = [
    'primary' => 'bg-gestion-600 hover:bg-gestion-700 focus:ring-gestion-500',
    'danger' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
    'success' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
    'warning' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500'
];

$icons = [
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />',
    'danger' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />',
    'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'question' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'trash' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />'
];

$iconColors = [
    'warning' => 'text-yellow-600 bg-yellow-100',
    'danger' => 'text-red-600 bg-red-100',
    'info' => 'text-blue-600 bg-blue-100',
    'question' => 'text-indigo-600 bg-indigo-100',
    'trash' => 'text-red-600 bg-red-100'
];

$sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
$confirmColorClass = $confirmColors[$confirmColor] ?? $confirmColors['danger'];
$iconPath = $icons[$icon] ?? $icons['warning'];
$iconColorClass = $iconColors[$icon] ?? $iconColors['warning'];
@endphp

<!-- Modal -->
<div x-data="{ 
    show: false, 
    onConfirm: null,
    onCancel: null,
    open(confirmCallback, cancelCallback) {
        this.onConfirm = confirmCallback;
        this.onCancel = cancelCallback;
        this.show = true;
        this.$nextTick(() => {
            this.$refs.confirmButton?.focus();
        });
    },
    close() {
        this.show = false;
        this.onConfirm = null;
        this.onCancel = null;
    },
    confirm() {
        if (this.onConfirm) {
            this.onConfirm();
        }
        this.close();
    },
    cancel() {
        if (this.onCancel) {
            this.onCancel();
        }
        this.close();
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
     @keydown.escape="cancel()"
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
             @click.away="cancel()"
             class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full {{ $sizeClass }} sm:p-6">
            
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full {{ $iconColorClass }} sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $iconPath !!}
                    </svg>
                </div>
                
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                        {{ $title }}
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $message }}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-5 sm:mt-4 sm:ml-10 sm:pl-4 sm:flex sm:flex-row-reverse">
                <button type="button"
                        x-ref="confirmButton"
                        @click="confirm()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white {{ $confirmColorClass }} focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    {{ $confirmText }}
                </button>
                
                <button type="button"
                        @click="cancel()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                    {{ $cancelText }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Función global para mostrar el modal de confirmación
window.showConfirmationModal = function(options = {}) {
    const modal = document.getElementById('{{ $id }}');
    if (modal && modal.__x) {
        const config = {
            title: options.title || '{{ $title }}',
            message: options.message || '{{ $message }}',
            confirmText: options.confirmText || '{{ $confirmText }}',
            cancelText: options.cancelText || '{{ $cancelText }}',
            onConfirm: options.onConfirm || null,
            onCancel: options.onCancel || null
        };
        
        // Actualizar textos si se proporcionan
        if (options.title) {
            modal.querySelector('#modal-title').textContent = options.title;
        }
        if (options.message) {
            modal.querySelector('.text-gray-500').textContent = options.message;
        }
        if (options.confirmText) {
            modal.querySelector('[x-ref="confirmButton"]').textContent = options.confirmText;
        }
        if (options.cancelText) {
            modal.querySelector('[x-ref="confirmButton"]').nextElementSibling.textContent = options.cancelText;
        }
        
        modal.__x.$data.open(config.onConfirm, config.onCancel);
    }
};

// Función para confirmación rápida
window.confirm = function(message, onConfirm, onCancel) {
    showConfirmationModal({
        message: message,
        onConfirm: onConfirm,
        onCancel: onCancel
    });
};
</script>