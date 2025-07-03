{{-- resources/views/shared/components/badge.blade.php --}}
@props([
    'variant' => 'default',
    'size' => 'md',
    'rounded' => 'md',
    'removable' => false,
    'icon' => null,
    'dot' => false,
    'pulse' => false
])

@php
$variants = [
    'default' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'primary' => 'bg-gestion-100 text-gestion-800 dark:bg-gestion-900/50 dark:text-gestion-200',
    'secondary' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
    'success' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
    'error' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
    'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
    'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
    'dark' => 'bg-gray-800 text-white dark:bg-gray-200 dark:text-gray-800',
    'light' => 'bg-white text-gray-800 border border-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700'
];

$sizes = [
    'xs' => 'px-2 py-0.5 text-xs',
    'sm' => 'px-2.5 py-0.5 text-xs',
    'md' => 'px-3 py-1 text-sm',
    'lg' => 'px-3 py-1.5 text-sm',
    'xl' => 'px-4 py-2 text-base'
];

$roundedStyles = [
    'none' => 'rounded-none',
    'sm' => 'rounded-sm',
    'md' => 'rounded-md',
    'lg' => 'rounded-lg',
    'full' => 'rounded-full'
];

$baseClasses = [
    'inline-flex',
    'items-center',
    'font-medium',
    'transition-colors',
    'duration-200'
];

$variantClass = $variants[$variant] ?? $variants['default'];
$sizeClass = $sizes[$size] ?? $sizes['md'];
$roundedClass = $roundedStyles[$rounded] ?? $roundedStyles['md'];

$classes = array_merge($baseClasses, [$variantClass, $sizeClass, $roundedClass]);

if ($removable) {
    $classes[] = 'pr-1';
}

$classString = implode(' ', $classes);

// Colores para el dot indicator
$dotColors = [
    'default' => 'bg-gray-400',
    'primary' => 'bg-gestion-500',
    'secondary' => 'bg-gray-500',
    'success' => 'bg-green-500',
    'error' => 'bg-red-500',
    'warning' => 'bg-yellow-500',
    'info' => 'bg-blue-500',
    'dark' => 'bg-gray-700',
    'light' => 'bg-gray-300'
];

$dotColor = $dotColors[$variant] ?? $dotColors['default'];
@endphp

<span {{ $attributes->merge(['class' => $classString]) }}>
    @if($dot)
        <span class="w-2 h-2 {{ $dotColor }} rounded-full mr-2 {{ $pulse ? 'animate-pulse' : '' }}"></span>
    @endif
    
    @if($icon)
        <svg class="w-4 h-4 {{ $dot || $slot->isNotEmpty() ? 'mr-1' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icon !!}
        </svg>
    @endif
    
    {{ $slot }}
    
    @if($removable)
        <button type="button" 
                class="ml-1 inline-flex items-center p-0.5 rounded-full hover:bg-black/10 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500 transition-colors"
                onclick="this.parentElement.remove()">
            <span class="sr-only">Remover</span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</span>