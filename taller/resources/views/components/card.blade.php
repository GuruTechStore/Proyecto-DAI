{{-- 
Archivo: resources/views/components/card.blade.php
Componente Card flexible y reutilizable
--}}

@props([
    'padding' => 'md',           // none, xs, sm, md, lg, xl
    'shadow' => 'sm',            // none, xs, sm, md, lg, xl
    'rounded' => 'lg',           // none, sm, md, lg, xl, 2xl
    'border' => true,            // true, false
    'hover' => false,            // Efectos hover
    'clickable' => false,        // Si es clickeable
    'loading' => false,          // Estado de carga
    'variant' => 'default',      // default, outlined, filled, glass
    'href' => null,              // Link si es clickeable
    'header' => null,            // Slot para header
    'footer' => null,            // Slot para footer
    'actions' => null,           // Slot para acciones
])

@php
// Clases base
$baseClasses = [
    'bg-white',
    'transition-all',
    'duration-200',
    'relative',
    'overflow-hidden'
];

// Padding
$paddingClasses = [
    'none' => '',
    'xs' => 'p-2',
    'sm' => 'p-3',
    'md' => 'p-4',
    'lg' => 'p-6',
    'xl' => 'p-8',
];

// Sombras
$shadowClasses = [
    'none' => '',
    'xs' => 'shadow-xs',
    'sm' => 'shadow-sm',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
    'xl' => 'shadow-xl',
];

// Bordes redondeados
$roundedClasses = [
    'none' => '',
    'sm' => 'rounded-sm',
    'md' => 'rounded-md',
    'lg' => 'rounded-lg',
    'xl' => 'rounded-xl',
    '2xl' => 'rounded-2xl',
];

// Variantes
$variantClasses = [
    'default' => 'bg-white',
    'outlined' => 'bg-white border-2 border-gray-200',
    'filled' => 'bg-gray-50',
    'glass' => 'bg-white/80 backdrop-blur-sm border border-white/20',
];

// Estados
$stateClasses = [];

if ($border && $variant !== 'outlined') {
    $stateClasses[] = 'border border-gray-200';
}

if ($hover) {
    $stateClasses[] = 'hover:shadow-lg hover:-translate-y-1';
}

if ($clickable) {
    $stateClasses[] = 'cursor-pointer';
    $stateClasses[] = 'hover:shadow-md';
    $stateClasses[] = 'focus-ring';
    $stateClasses[] = 'focus:outline-none';
}

if ($loading) {
    $stateClasses[] = 'opacity-75';
    $stateClasses[] = 'pointer-events-none';
}

// Construir clases
$classes = array_merge(
    $baseClasses,
    [$paddingClasses[$padding ?? '']],
    [$shadowClasses[$shadow ?? '']],
    [$roundedClasses[$rounded ?? '']],
    [$variantClasses[$variant]],
    $stateClasses
);

$classString = implode(' ', array_filter($classes));

// Determinar el tag del elemento
$tag = $href ? 'a' : ($clickable ? 'div' : 'div');

// Atributos
$elementAttributes = ['class' => $classString];
if ($href) {
    $elementAttributes['href'] = $href;
}
if ($clickable) {
    $elementAttributes['tabindex'] = '0';
    $elementAttributes['role'] = 'button';
}
@endphp

<{{ $tag }} {{ $attributes->merge($elementAttributes) }}>
    {{-- Loading Overlay --}}
    @if($loading)
        <div class="absolute inset-0 bg-white/50 backdrop-blur-sm flex items-center justify-center z-10">
            <div class="flex items-center space-x-2">
                <svg class="animate-spin w-5 h-5 text-gestion-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-gray-600">Cargando...</span>
            </div>
        </div>
    @endif

    {{-- Header --}}
    @if($header)
        <div class="card-header {{ $padding !== 'none' ? '-m-' . ($padding === 'xs' ? '2' : ($padding === 'sm' ? '3' : ($padding === 'md' ? '4' : ($padding === 'lg' ? '6' : '8')))) . ' mb-' . ($padding === 'xs' ? '2' : ($padding === 'sm' ? '3' : ($padding === 'md' ? '4' : ($padding === 'lg' ? '6' : '8')))) . ' p-' . ($padding === 'xs' ? '2' : ($padding === 'sm' ? '3' : ($padding === 'md' ? '4' : ($padding === 'lg' ? '6' : '8')))) : '' }} border-b border-gray-200 bg-gray-50/50">
            {{ $header }}
        </div>
    @endif

    {{-- Main Content --}}
    <div class="card-content">
        {{ $slot }}
    </div>

    {{-- Footer --}}
    @if($footer)
        <div class="card-footer {{ $padding !== 'none' ? '-m-' . ($padding === 'xs' ? '2' : ($padding === 'sm' ? '3' : ($padding === 'md' ? '4' : ($padding === 'lg' ? '6' : '8')))) . ' mt-' . ($padding === 'xs' ? '2' : ($padding === 'sm' ? '3' : ($padding === 'md' ? '4' : ($padding === 'lg' ? '6' : '8')))) . ' p-' . ($padding === 'xs' ? '2' : ($padding === 'sm' ? '3' : ($padding === 'md' ? '4' : ($padding === 'lg' ? '6' : '8')))) : '' }} border-t border-gray-200 bg-gray-50/50">
            {{ $footer }}
        </div>
    @endif

    {{-- Actions --}}
    @if($actions)
        <div class="card-actions {{ $padding !== 'none' ? '-m-' . ($padding === 'xs' ? '2' : ($padding === 'sm' ? '3' : ($padding === 'md' ? '4' : ($padding === 'lg' ? '6' : '8')))) . ' mt-' . ($padding === 'xs' ? '2' : ($padding === 'sm' ? '3' : ($padding === 'md' ? '4' : ($padding === 'lg' ? '6' : '8')))) . ' p-' . ($padding === 'xs' ? '2' : ($padding === 'sm' ? '3' : ($padding === 'md' ? '4' : ($padding === 'lg' ? '6' : '8')))) : '' }} border-t border-gray-200 flex justify-end space-x-2">
            {{ $actions }}
        </div>
    @endif
</{{ $tag }}>

