{{-- resources/views/shared/components/alert.blade.php --}}
@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
    'icon' => true,
    'size' => 'md',
    'animated' => false
])

@php
$typeClasses = [
    'success' => 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/50 dark:border-green-800 dark:text-green-200',
    'error' => 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/50 dark:border-red-800 dark:text-red-200',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800 dark:bg-yellow-900/50 dark:border-yellow-800 dark:text-yellow-200',
    'info' => 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/50 dark:border-blue-800 dark:text-blue-200',
    'neutral' => 'bg-gray-50 border-gray-200 text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200'
];

$iconClasses = [
    'success' => 'text-green-400 dark:text-green-500',
    'error' => 'text-red-400 dark:text-red-500',
    'warning' => 'text-yellow-400 dark:text-yellow-500',
    'info' => 'text-blue-400 dark:text-blue-500',
    'neutral' => 'text-gray-400 dark:text-gray-500'
];

$icons = [
    'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />',
    'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    'neutral' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
];

$sizeClasses = [
    'sm' => 'p-3 text-sm',
    'md' => 'p-4',
    'lg' => 'p-6 text-lg'
];

$baseClasses = 'border rounded-lg shadow-sm ' . ($typeClasses[$type] ?? $typeClasses['info']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);

if ($animated) {
    $baseClasses .= ' animate-fadeIn';
}

if ($dismissible) {
    $baseClasses .= ' pr-12 relative';
}
@endphp

<div {{ $attributes->merge(['class' => $baseClasses]) }}
     @if($dismissible) x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @endif>
    
    <div class="flex items-start">
        @if($icon)
            <div class="flex-shrink-0 mr-3">
                <svg class="w-5 h-5 {{ $iconClasses[$type] ?? $iconClasses['info'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icons[$type] ?? $icons['info'] !!}
                </svg>
            </div>
        @endif
        
        <div class="flex-1">
            @if($title)
                <h3 class="font-medium mb-1">{{ $title }}</h3>
            @endif
            
            <div class="{{ $title ? 'text-sm' : '' }}">
                {{ $slot }}
            </div>
        </div>
        
        @if($dismissible)
            <div class="absolute top-0 right-0 pt-3 pr-3">
                <button @click="show = false" 
                        class="inline-flex rounded-md p-1.5 transition-colors duration-200 hover:bg-black/5 dark:hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
                    <span class="sr-only">Cerrar alerta</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
</div>

@if($animated)
<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>
@endif