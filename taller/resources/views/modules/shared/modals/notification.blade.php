{{-- resources/views/shared/modals/notification.blade.php --}}
@props([
    'id' => 'notificationModal',
    'type' => 'success',
    'title' => null,
    'message' => '',
    'icon' => null,
    'autoClose' => true,
    'duration' => 5000,
    'position' => 'top-right',
    'showProgress' => true
])

@php
$positions = [
    'top-left' => 'top-4 left-4',
    'top-center' => 'top-4 left-1/2 transform -translate-x-1/2',
    'top-right' => 'top-4 right-4',
    'bottom-left' => 'bottom-4 left-4',
    'bottom-center' => 'bottom-4 left-1/2 transform -translate-x-1/2',
    'bottom-right' => 'bottom-4 right-4'
];

$typeStyles = [
    'success' => [
        'bg' => 'bg-green-50 dark:bg-green-900/20',
        'border' => 'border-green-200 dark:border-green-800',
        'text' => 'text-green-800 dark:text-green-200',
        'icon' => 'text-green-400',
        'progress' => 'bg-green-500'
    ],
    'error' => [
        'bg' => 'bg-red-50 dark:bg-red-900/20',
        'border' => 'border-red-200 dark:border-red-800',
        'text' => 'text-red-800 dark:text-red-200',
        'icon' => 'text-red-400',
        'progress' => 'bg-red-500'
    ],
    'warning' => [
        'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',
        'border' => 'border-yellow-200 dark:border-yellow-800',
        'text' => 'text-yellow-800 dark:text-yellow-200',
        'icon' => 'text-yellow-400',
        'progress' => 'bg-yellow-500'
    ],
    'info' => [
        'bg' => 'bg-blue-50 dark:bg-blue-900/20',
        'border' => 'border-blue-200 dark:border-blue-800',
        'text' => 'text-blue-800 dark:text-blue-200',
        'icon' => 'text-blue-400',
        'progress' => 'bg-blue-500'
    ]
];

$defaultIcons = [
    'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />',
    'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
];

$style = $typeStyles[$type] ?? $typeStyles['info'];
$positionClass = $positions[$position] ?? $positions['top-right'];
$iconPath = $icon ?: ($defaultIcons[$type] ?? $defaultIcons['info']);
@endphp

<!-- Notification Modal -->
<div x-data="{ 
    show: false,
    type: '{{ $type }}',
    title: '{{ $title }}',
    message: '{{ $message }}',
    autoClose: {{ $autoClose ? 'true' : 'false' }},
    duration: {{ $duration }},
    showProgress: {{ $showProgress ? 'true' : 'false' }},
    progress: 100,
    timer: null,
    
    open(options = {}) {
        this.type = options.type || this.type;
        this.title = options.title || this.title;
        this.message = options.message || this.message;
        this.autoClose = options.autoClose !== undefined ? options.autoClose : this.autoClose;
        this.duration = options.duration || this.duration;
        this.showProgress = options.showProgress !== undefined ? options.showProgress : this.showProgress;
        
        this.show = true;
        this.progress = 100;
        
        if (this.autoClose) {
            this.startTimer();
        }
    },
    
    close() {
        this.show = false;
        this.clearTimer();
        this.progress = 100;
    },
    
    startTimer() {
        this.clearTimer();
        const interval = 50;
        const decrement = (interval / this.duration) * 100;
        
        this.timer = setInterval(() => {
            this.progress -= decrement;
            if (this.progress <= 0) {
                this.close();
            }
        }, interval);
    },
    
    clearTimer() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    },
    
    pauseTimer() {
        this.clearTimer();
    },
    
    resumeTimer() {
        if (this.autoClose && this.show) {
            this.startTimer();
        }
    }
}" 
     x-show="show" 
     x-cloak
     id="{{ $id }}"
     class="fixed {{ $positionClass }} z-50 max-w-sm w-full" 
     style="display: none;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 transform translate-y-0"
     x-transition:leave-end="opacity-0 transform translate-y-2"
     @mouseenter="pauseTimer()"
     @mouseleave="resumeTimer()">
    
    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg border {{ $style['border'] }} overflow-hidden">
        <!-- Progress bar -->
        <div x-show="showProgress && autoClose" 
             class="absolute top-0 left-0 h-1 {{ $style['progress'] }} transition-all duration-50 ease-linear"
             :style="`width: ${progress}%`"></div>
        
        <!-- Content -->
        <div class="p-4 {{ $style['bg'] }}">
            <div class="flex items-start">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 {{ $style['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $iconPath !!}
                    </svg>
                </div>
                
                <!-- Content -->
                <div class="ml-3 flex-1">
                    <h4 x-show="title" 
                        x-text="title"
                        class="text-sm font-medium {{ $style['text'] }}"></h4>
                    <p x-text="message" 
                       class="text-sm {{ $style['text'] }}"
                       :class="{ 'mt-1': title }"></p>
                </div>
                
                <!-- Close button -->
                <div class="ml-4 flex-shrink-0">
                    <button @click="close()" 
                            class="inline-flex text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500 transition-colors">
                        <span class="sr-only">Cerrar</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Sistema de notificaciones global
window.notifications = {
    show: function(options) {
        const modal = document.getElementById('{{ $id }}');
        if (modal && modal.__x) {
            modal.__x.$data.open(options);
        }
    },
    
    success: function(message, title = 'Éxito', options = {}) {
        this.show({
            type: 'success',
            title: title,
            message: message,
            ...options
        });
    },
    
    error: function(message, title = 'Error', options = {}) {
        this.show({
            type: 'error',
            title: title,
            message: message,
            autoClose: false, // Los errores no se cierran automáticamente
            ...options
        });
    },
    
    warning: function(message, title = 'Advertencia', options = {}) {
        this.show({
            type: 'warning',
            title: title,
            message: message,
            ...options
        });
    },
    
    info: function(message, title = 'Información', options = {}) {
        this.show({
            type: 'info',
            title: title,
            message: message,
            ...options
        });
    }
};

// Funciones de conveniencia
window.showSuccess = function(message, title) {
    notifications.success(message, title);
};

window.showError = function(message, title) {
    notifications.error(message, title);
};

window.showWarning = function(message, title) {
    notifications.warning(message, title);
};

window.showInfo = function(message, title) {
    notifications.info(message, title);
};
</script>