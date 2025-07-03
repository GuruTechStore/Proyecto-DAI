{{-- 
Archivo: resources/views/components/button.blade.php
Componente Button altamente reutilizable y accesible
--}}

@props([
    'variant' => 'primary',      // primary, secondary, ghost, danger, success, warning
    'size' => 'md',              // xs, sm, md, lg, xl
    'type' => 'button',          // button, submit, reset
    'href' => null,              // Si se proporciona, renderiza como link
    'loading' => false,          // Estado de carga
    'disabled' => false,         // Estado deshabilitado
    'icon' => null,              // Icono opcional
    'iconPosition' => 'left',    // left, right
    'fullWidth' => false,        // Ancho completo
    'rounded' => 'md',           // none, sm, md, lg, xl, full
    'shadow' => false,           // Agregar sombra
    'outlined' => false,         // Versión outline
    'tag' => null                // Tag personalizado (button, a, div, etc.)
])

@php
$tag = $tag ?? ($href ? 'a' : 'button');
$isDisabled = $disabled || $loading;

// Clases base
$baseClasses = [
    'inline-flex',
    'items-center',
    'justify-center',
    'font-medium',
    'focus-ring',
    'transition-all',
    'duration-200',
    'border',
    'select-none',
    'relative',
    'overflow-hidden',
];

// Tamaños
$sizeClasses = [
    'xs' => 'px-2 py-1 text-xs gap-1',
    'sm' => 'px-3 py-1.5 text-sm gap-1.5',
    'md' => 'px-4 py-2 text-sm gap-2',
    'lg' => 'px-6 py-3 text-base gap-2',
    'xl' => 'px-8 py-4 text-lg gap-3',
];

// Bordes redondeados
$roundedClasses = [
    'none' => 'rounded-none',
    'sm' => 'rounded-sm',
    'md' => 'rounded-md',
    'lg' => 'rounded-lg',
    'xl' => 'rounded-xl',
    'full' => 'rounded-full',
];

// Variantes de color (outlined y filled)
$variantClasses = [
    'primary' => $outlined ? [
        'border-gestion-600 text-gestion-600 bg-transparent',
        'hover:bg-gestion-50 hover:border-gestion-700 hover:text-gestion-700',
        'focus:ring-gestion-500 focus:border-gestion-700',
        'active:bg-gestion-100'
    ] : [
        'border-gestion-600 text-white bg-gestion-600',
        'hover:bg-gestion-700 hover:border-gestion-700',
        'focus:ring-gestion-500',
        'active:bg-gestion-800'
    ],
    
    'secondary' => $outlined ? [
        'border-gray-300 text-gray-700 bg-transparent',
        'hover:bg-gray-50 hover:border-gray-400 hover:text-gray-800',
        'focus:ring-gray-500 focus:border-gray-400',
        'active:bg-gray-100'
    ] : [
        'border-gray-300 text-gray-700 bg-white',
        'hover:bg-gray-50 hover:border-gray-400',
        'focus:ring-gray-500',
        'active:bg-gray-100'
    ],
    
    'ghost' => [
        'border-transparent text-gray-700 bg-transparent',
        'hover:bg-gray-100 hover:text-gray-900',
        'focus:ring-gray-500',
        'active:bg-gray-200'
    ],
    
    'danger' => $outlined ? [
        'border-red-600 text-red-600 bg-transparent',
        'hover:bg-red-50 hover:border-red-700 hover:text-red-700',
        'focus:ring-red-500 focus:border-red-700',
        'active:bg-red-100'
    ] : [
        'border-red-600 text-white bg-red-600',
        'hover:bg-red-700 hover:border-red-700',
        'focus:ring-red-500',
        'active:bg-red-800'
    ],
    
    'success' => $outlined ? [
        'border-green-600 text-green-600 bg-transparent',
        'hover:bg-green-50 hover:border-green-700 hover:text-green-700',
        'focus:ring-green-500 focus:border-green-700',
        'active:bg-green-100'
    ] : [
        'border-green-600 text-white bg-green-600',
        'hover:bg-green-700 hover:border-green-700',
        'focus:ring-green-500',
        'active:bg-green-800'
    ],
    
    'warning' => $outlined ? [
        'border-yellow-600 text-yellow-600 bg-transparent',
        'hover:bg-yellow-50 hover:border-yellow-700 hover:text-yellow-700',
        'focus:ring-yellow-500 focus:border-yellow-700',
        'active:bg-yellow-100'
    ] : [
        'border-yellow-600 text-white bg-yellow-600',
        'hover:bg-yellow-700 hover:border-yellow-700',
        'focus:ring-yellow-500',
        'active:bg-yellow-800'
    ]
];

// Estados
$stateClasses = [];
if ($isDisabled) {
    $stateClasses[] = 'opacity-50';
    $stateClasses[] = 'cursor-not-allowed';
    $stateClasses[] = 'pointer-events-none';
}

if ($fullWidth) {
    $stateClasses[] = 'w-full';
}

if ($shadow) {
    $stateClasses[] = 'shadow-md';
    $stateClasses[] = 'hover:shadow-lg';
}

// Construir clases finales
$classes = array_merge(
    $baseClasses,
    [$sizeClasses[$size ?? '']],
    [$roundedClasses[$rounded ?? '']],
    $variantClasses[$variant ?? ''],
    $stateClasses
);

$classString = implode(' ', array_filter($classes));

// Atributos del elemento
$elementAttributes = [
    'class' => $classString
];

if ($tag === 'button') {
    $elementAttributes['type'] = $type;
    if ($isDisabled) {
        $elementAttributes['disabled'] = true;
    }
}

if ($tag === 'a' && $href) {
    $elementAttributes['href'] = $href;
    if ($isDisabled) {
        $elementAttributes['tabindex'] = -1;
        $elementAttributes['aria-disabled'] = 'true';
    }
}

// Loading state
$loadingSize = [
    'xs' => 'w-3 h-3',
    'sm' => 'w-4 h-4',
    'md' => 'w-4 h-4',
    'lg' => 'w-5 h-5',
    'xl' => 'w-6 h-6',
][$size ?? ''];
@endphp

<{{ $tag }} {{ $attributes->merge($elementAttributes) }}>
    {{-- Loading Spinner --}}
    @if($loading)
        <svg class="animate-spin {{ $loadingSize }} mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif

    {{-- Icono izquierdo --}}
    @if($icon && $iconPosition === 'left' && !$loading)
        @if(is_string($icon))
            <x-icon :name="$icon" class="{{ $loadingSize }}" />
        @else
            {{ $icon }}
        @endif
    @endif

    {{-- Contenido del botón --}}
    <span class="{{ $loading ? 'opacity-0' : '' }}">
        {{ $slot }}
    </span>

    {{-- Icono derecho --}}
    @if($icon && $iconPosition === 'right' && !$loading)
        @if(is_string($icon))
            <x-icon :name="$icon" class="{{ $loadingSize }}" />
        @else
            {{ $icon }}
        @endif
    @endif

    {{-- Ripple effect (opcional) --}}
    @if(!$isDisabled)
        <span class="absolute inset-0 overflow-hidden rounded-inherit">
            <span class="absolute inset-0 bg-white opacity-0 transition-opacity duration-200 hover:opacity-10"></span>
        </span>
    @endif
</{{ $tag }}>

