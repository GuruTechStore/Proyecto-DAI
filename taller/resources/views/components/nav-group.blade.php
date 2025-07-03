{{-- 
Archivo: resources/views/components/nav-group.blade.php
Componente para grupos de navegación con sub-elementos
--}}

@props([
    'title' => '',
    'icon' => null,
    'active' => false,
    'collapsed' => false,
    'defaultOpen' => false,
])

@php
// Generar ID único para el grupo
$groupId = 'nav-group-' . Str::slug($title);

// Determinar si debe estar abierto por defecto
$shouldBeOpen = $active || $defaultOpen;
@endphp

<div x-data="{ 
        open: {{ $shouldBeOpen ? 'true' : 'false' }},
        groupId: '{{ $groupId }}'
     }" 
     x-init="
        // Restaurar estado desde localStorage
        if (localStorage.getItem(groupId + '-open') !== null) {
            open = localStorage.getItem(groupId + '-open') === 'true';
        }
        // Guardar estado cuando cambie
        $watch('open', value => {
            localStorage.setItem(groupId + '-open', value);
        });
     "
     class="nav-group">
    
    {{-- Header del grupo --}}
    <button @click="open = !open" 
            class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 group
                   {{ $active ? 'bg-gestion-100 text-gestion-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}">
        
        {{-- Indicador de activo --}}
        @if($active)
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-gestion-600 rounded-r-full"></div>
        @endif
        
        {{-- Icono --}}
        @if($icon)
            <div class="flex-shrink-0 {{ $collapsed ? 'mr-0' : 'mr-3' }}">
                <x-icon 
                    :name="$icon" 
                    size="sm" 
                    :color="$active ? 'primary' : 'current'"
                    class="transition-colors duration-200 group-hover:scale-110" />
            </div>
        @endif
        
        {{-- Título y flecha --}}
        <div x-show="!collapsed" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="flex-1 flex items-center justify-between min-w-0">
            
            <span class="truncate">{{ $title }}</span>
            
            {{-- Flecha de expansión --}}
            <div class="flex-shrink-0 ml-2">
                <x-icon 
                    name="chevron-down" 
                    size="sm" 
                    :class="open ? 'rotate-180' : ''"
                    class="transition-transform duration-200" />
            </div>
        </div>
        
        {{-- Tooltip para modo colapsado --}}
        @if($collapsed)
            <div class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 whitespace-nowrap z-50">
                {{ $title }}
                <div class="absolute right-full top-1/2 transform -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
            </div>
        @endif
    </button>
    
    {{-- Sub-elementos --}}
    <div x-show="open && !collapsed" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="mt-1 space-y-1"
         style="display: none;">
        
        <div class="ml-6 space-y-1 border-l-2 border-gray-200 dark:border-gray-700 pl-4">
            {{ $slot }}
        </div>
    </div>
    
    {{-- Menu expandido para modo colapsado (aparece al hover) --}}
    @if($collapsed)
        <div x-show="collapsed" 
             class="absolute left-full top-0 ml-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 min-w-48 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
            
            {{-- Header del popup --}}
            <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-2">
                    @if($icon)
                        <x-icon :name="$icon" size="sm" :color="$active ? 'primary' : 'current'" />
                    @endif
                    <span class="font-medium text-gray-900 dark:text-white">{{ $title }}</span>
                </div>
            </div>
            
            {{-- Sub-elementos en popup --}}
            <div class="py-1">
                {{ $slot }}
            </div>
            
            {{-- Flecha del tooltip --}}
            <div class="absolute right-full top-4 border-4 border-transparent border-r-white dark:border-r-gray-800"></div>
        </div>
    @endif
</div>
