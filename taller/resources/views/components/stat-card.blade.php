{{-- 
Archivo: resources/views/components/stat-card.blade.php
Componente especializado para mostrar estadísticas
--}}

@props([
    'title' => '',               // Título de la estadística
    'value' => 0,                // Valor principal
    'change' => null,            // Cambio/variación (+12%, -5%, etc.)
    'changeType' => 'neutral',   // positive, negative, neutral
    'trend' => 'neutral',        // up, down, neutral
    'icon' => null,              // Icono para la estadística
    'iconColor' => 'blue',       // Color del icono
    'description' => null,       // Descripción adicional
    'loading' => false,          // Estado de carga
    'href' => null,              // Link opcional
    'format' => 'number',        // number, currency, percentage
    'currency' => 'PEN',         // Moneda si format es currency
    'size' => 'md',              // sm, md, lg
    'variant' => 'default',      // default, minimal, detailed
])

@php
// Formatear el valor según el tipo
$formattedValue = $value;
switch ($format) {
    case 'currency':
        $formattedValue = 'S/ ' . number_format($value, 2);
        break;
    case 'percentage':
        $formattedValue = number_format($value, 1) . '%';
        break;
    case 'number':
    default:
        $formattedValue = number_format($value);
        break;
}

// Determinar colores del cambio
$changeColors = [
    'positive' => 'text-green-600 bg-green-50',
    'negative' => 'text-red-600 bg-red-50',
    'neutral' => 'text-gray-600 bg-gray-50',
];

// Colores de iconos
$iconColors = [
    'blue' => 'bg-blue-500 text-white',
    'green' => 'bg-green-500 text-white',
    'red' => 'bg-red-500 text-white',
    'yellow' => 'bg-yellow-500 text-white',
    'purple' => 'bg-purple-500 text-white',
    'indigo' => 'bg-indigo-500 text-white',
    'pink' => 'bg-pink-500 text-white',
    'gray' => 'bg-gray-500 text-white',
];

// Tamaños
$sizes = [
    'sm' => [
        'padding' => 'p-4',
        'iconSize' => 'w-8 h-8',
        'titleSize' => 'text-xs',
        'valueSize' => 'text-lg',
        'changeSize' => 'text-xs',
    ],
    'md' => [
        'padding' => 'p-6',
        'iconSize' => 'w-10 h-10',
        'titleSize' => 'text-sm',
        'valueSize' => 'text-2xl',
        'changeSize' => 'text-sm',
    ],
    'lg' => [
        'padding' => 'p-8',
        'iconSize' => 'w-12 h-12',
        'titleSize' => 'text-base',
        'valueSize' => 'text-3xl',
        'changeSize' => 'text-base',
    ],
];

$currentSize = $sizes[$size ?? ''];

// Determinar el elemento contenedor
$tag = $href ? 'a' : 'div';
$containerClasses = [
    'bg-white',
    'rounded-lg',
    'shadow-sm',
    'border',
    'border-gray-200',
    'transition-all',
    'duration-200',
    $currentSize['padding']
];

if ($href) {
    $containerClasses[] = 'hover:shadow-md';
    $containerClasses[] = 'hover:border-gray-300';
    $containerClasses[] = 'focus-ring';
}

if ($loading) {
    $containerClasses[] = 'opacity-50';
    $containerClasses[] = 'pointer-events-none';
}

$containerClass = implode(' ', $containerClasses);

// Atributos del contenedor
$containerAttributes = ['class' => $containerClass];
if ($href) {
    $containerAttributes['href'] = $href;
}
@endphp

<{{ $tag }} {{ $attributes->merge($containerAttributes) }}>
    {{-- Loading Overlay --}}
    @if($loading)
        <div class="absolute inset-0 bg-white/70 flex items-center justify-center rounded-lg">
            <div class="animate-spin w-5 h-5 border-2 border-gestion-600 border-t-transparent rounded-full"></div>
        </div>
    @endif

    @if($variant === 'minimal')
        {{-- Variante Minimal --}}
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600">{{ $title }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $formattedValue }}</p>
                @if($change)
                    <div class="flex items-center mt-1">
                        <span class="text-xs {{ $changeColors[$changeType ?? ''] }} px-2 py-0.5 rounded-full">
                            {{ $change }}
                        </span>
                    </div>
                @endif
            </div>
            @if($icon)
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 {{ $iconColors[$iconColor ?? ''] }} rounded-lg flex items-center justify-center">
                        <x-icon :name="$icon" class="w-5 h-5" />
                    </div>
                </div>
            @endif
        </div>

    @elseif($variant === 'detailed')
        {{-- Variante Detallada --}}
        <div class="space-y-4">
            {{-- Header con icono --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    @if($icon)
                        <div class="{{ $currentSize['iconSize'] }} {{ $iconColors[$iconColor ?? ''] }} rounded-lg flex items-center justify-center">
                            <x-icon :name="$icon" class="w-6 h-6" />
                        </div>
                    @endif
                    <h3 class="{{ $currentSize['titleSize'] }} font-medium text-gray-900">{{ $title }}</h3>
                </div>
                @if($change)
                    <div class="flex items-center space-x-1">
                        @if($trend === 'up')
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        @elseif($trend === 'down')
                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @endif
                        <span class="{{ $currentSize['changeSize'] }} font-medium {{ $changeColors[$changeType ?? ''] }}">
                            {{ $change }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- Valor principal --}}
            <div>
                <p class="{{ $currentSize['valueSize'] }} font-bold text-gray-900">{{ $formattedValue }}</p>
                @if($description)
                    <p class="text-sm text-gray-500 mt-1">{{ $description }}</p>
                @endif
            </div>
        </div>

    @else
        {{-- Variante Default --}}
        <div class="flex items-center">
            {{-- Icono --}}
            @if($icon)
                <div class="flex-shrink-0">
                    <div class="{{ $currentSize['iconSize'] }} {{ $iconColors[$iconColor ?? ''] }} rounded-lg flex items-center justify-center">
                        <x-icon :name="$icon" class="w-6 h-6" />
                    </div>
                </div>
            @endif

            {{-- Contenido --}}
            <div class="{{ $icon ? 'ml-4' : '' }} flex-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="{{ $currentSize['titleSize'] }} font-medium text-gray-500">{{ $title }}</p>
                        <p class="{{ $currentSize['valueSize'] }} font-semibold text-gray-900">{{ $formattedValue }}</p>
                    </div>
                </div>

                {{-- Cambio/Trend --}}
                @if($change)
                    <div class="mt-2 flex items-center {{ $currentSize['changeSize'] }}">
                        @if($trend === 'up')
                            <svg class="w-4 h-4 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        @elseif($trend === 'down')
                            <svg class="w-4 h-4 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @endif
                        <span class="font-medium {{ $changeColors[$changeType ?? ''] }}">
                            {{ $change }}
                        </span>
                        @if($description)
                            <span class="text-gray-500 ml-1">{{ $description }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
</{{ $tag }}>
