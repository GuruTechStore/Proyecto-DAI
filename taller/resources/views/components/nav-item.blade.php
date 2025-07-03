{{-- 
Archivo: resources/views/components/nav-item.blade.php
Componente para elementos individuales de navegación
--}}

@props([
    'href' => '#',
    'active' => false,
    'icon' => null,
    'collapsed' => false,
    'subItem' => false,
    'badge' => null,
    'badgeColor' => 'primary',
])

@php
// Clases base
$baseClasses = [
    'flex',
    'items-center',
    'px-3',
    'py-2',
    'text-sm',
    'font-medium',
    'rounded-lg',
    'transition-all',
    'duration-200',
    'group',
    'relative'
];

// Clases según el estado
if ($active) {
    $stateClasses = [
        'bg-gestion-100',
        'text-gestion-900',
        'border-r-2',
        'border-gestion-600',
        'shadow-sm'
    ];
} else {
    $stateClasses = [
        'text-gray-600',
        'hover:bg-gray-50',
        'hover:text-gray-900',
        'dark:text-gray-300',
        'dark:hover:bg-gray-700',
        'dark:hover:text-white'
    ];
}

// Clases para sub-items
if ($subItem) {
    $baseClasses[] = 'ml-6';
    $baseClasses[] = 'text-xs';
}

// Construir clases finales
$classes = array_merge($baseClasses, $stateClasses);
$classString = implode(' ', $classes);

// Badge colors
$badgeColors = [
    'primary' => 'bg-gestion-600 text-white',
    'secondary' => 'bg-gray-600 text-white',
    'success' => 'bg-green-600 text-white',
    'warning' => 'bg-yellow-600 text-white',
    'danger' => 'bg-red-600 text-white',
    'info' => 'bg-blue-600 text-white',
];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classString]) }}>
    
    {{-- Indicador de activo --}}
    @if($active)
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gestion-600 rounded-r-full"></div>
    @endif
    
    {{-- Icono --}}
    @if($icon)
        <div class="flex-shrink-0 {{ $collapsed && !$subItem ? 'mr-0' : 'mr-3' }}">
            <x-icon 
                :name="$icon" 
                size="sm" 
                :color="$active ? 'primary' : 'current'"
                class="transition-colors duration-200 group-hover:scale-110" />
        </div>
    @endif
    
    {{-- Contenido del enlace --}}
    <div x-show="!collapsed || subItem" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="flex-1 flex items-center justify-between min-w-0">
        
        <span class="truncate">{{ $slot }}</span>
        
        {{-- Badge --}}
        @if($badge)
            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeColors[$badgeColor ?? ''] ?? $badgeColors['primary'] }}">
                {{ $badge }}
            </span>
        @endif
    </div>
    
    {{-- Tooltip para modo colapsado --}}
    @if($collapsed && !$subItem)
        <div class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50">
            {{ $slot }}
            <div class="absolute right-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
        </div>
    @endif
    
</a>
