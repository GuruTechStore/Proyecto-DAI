{{-- resources/views/shared/components/card.blade.php --}}
@props([
    'title' => null,
    'subtitle' => null,
    'headerActions' => null,
    'footer' => null,
    'variant' => 'default',
    'padding' => 'default',
    'shadow' => 'default',
    'border' => true,
    'hover' => false,
    'loading' => false
])

@php
$variants = [
    'default' => 'bg-white dark:bg-gray-800',
    'secondary' => 'bg-gray-50 dark:bg-gray-900',
    'primary' => 'bg-gestion-50 dark:bg-gestion-900/20 border-gestion-200 dark:border-gestion-800',
    'success' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
    'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
    'error' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'
];

$paddings = [
    'none' => '',
    'sm' => 'p-4',
    'default' => 'p-6',
    'lg' => 'p-8',
    'xl' => 'p-10'
];

$shadows = [
    'none' => '',
    'sm' => 'shadow-sm',
    'default' => 'shadow',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
    'xl' => 'shadow-xl'
];

$baseClasses = [
    'rounded-lg',
    'transition-all',
    'duration-200'
];

// Variant classes
$variantClass = $variants[$variant] ?? $variants['default'];
$baseClasses[] = $variantClass;

// Shadow classes
if ($shadow !== 'none') {
    $shadowClass = $shadows[$shadow] ?? $shadows['default'];
    $baseClasses[] = $shadowClass;
}

// Border classes
if ($border) {
    if ($variant === 'default') {
        $baseClasses[] = 'border border-gray-200 dark:border-gray-700';
    }
}

// Hover effect
if ($hover) {
    $baseClasses[] = 'hover:shadow-lg hover:-translate-y-0.5 cursor-pointer';
}

// Padding
$paddingClass = $paddings[$padding] ?? $paddings['default'];

$cardClasses = implode(' ', $baseClasses);
@endphp

<div {{ $attributes->merge(['class' => $cardClasses]) }}>
    @if($loading)
        <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm z-10 flex items-center justify-center rounded-lg">
            <div class="flex flex-col items-center space-y-3">
                <div class="animate-spin rounded-full h-8 w-8 border-4 border-gestion-600 border-t-transparent"></div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Cargando...</p>
            </div>
        </div>
    @endif

    @if($title || $subtitle || $headerActions)
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    @if($title)
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white truncate">
                            {{ $title }}
                        </h3>
                    @endif
                    @if($subtitle)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 truncate">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
                
                @if($headerActions)
                    <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                        {{ $headerActions }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <div class="{{ $paddingClass }}">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 rounded-b-lg">
            {{ $footer }}
        </div>
    @endif
</div>