{{-- resources/views/shared/components/table.blade.php --}}
@props([
    'headers' => [],
    'sortable' => false,
    'selectable' => false,
    'responsive' => true,
    'striped' => true,
    'hover' => true,
    'compact' => false,
    'loading' => false,
    'empty' => 'No hay datos disponibles',
    'emptyIcon' => 'table-cells',
    'class' => ''
])

@php
$tableClasses = [
    'min-w-full',
    'divide-y',
    'divide-gray-200',
    'dark:divide-gray-700',
    'bg-white',
    'dark:bg-gray-800',
];

if ($compact) {
    $tableClasses[] = 'text-sm';
}

$tableClass = implode(' ', $tableClasses) . ' ' . $class;

$tbodyClasses = [
    'divide-y',
    'divide-gray-200',
    'dark:divide-gray-700'
];

if ($striped) {
    $tbodyClasses[] = '[&>tr:nth-child(even)]:bg-gray-50';
    $tbodyClasses[] = 'dark:[&>tr:nth-child(even)]:bg-gray-700/30';
}

if ($hover) {
    $tbodyClasses[] = '[&>tr]:hover:bg-gray-50';
    $tbodyClasses[] = 'dark:[&>tr]:hover:bg-gray-700/50';
    $tbodyClasses[] = '[&>tr]:transition-colors';
}

$tbodyClass = implode(' ', $tbodyClasses);
@endphp

<div {{ $attributes->merge(['class' => 'relative']) }}>
    @if($loading)
        <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm z-10 flex items-center justify-center rounded-lg">
            <div class="flex flex-col items-center space-y-3">
                <div class="animate-spin rounded-full h-8 w-8 border-4 border-gestion-600 border-t-transparent"></div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Cargando datos...</p>
            </div>
        </div>
    @endif

    <div class="{{ $responsive ? 'overflow-x-auto' : '' }} shadow ring-1 ring-black ring-opacity-5 rounded-lg">
        <table class="{{ $tableClass }}">
            @if(count($headers) > 0)
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @if($selectable)
                            <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                                <input type="checkbox" 
                                       class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-gestion-600 focus:ring-gestion-500 sm:left-6"
                                       x-model="selectAll"
                                       @change="toggleAll($event.target.checked)">
                            </th>
                        @endif
                        
                        @foreach($headers as $header)
                            @php
                                $headerConfig = is_array($header) ? $header : ['label' => $header];
                                $label = $headerConfig['label'] ?? '';
                                $sortKey = $headerConfig['sort'] ?? null;
                                $width = $headerConfig['width'] ?? null;
                                $align = $headerConfig['align'] ?? 'left';
                                $sticky = $headerConfig['sticky'] ?? false;
                                
                                $thClasses = [
                                    'px-6',
                                    'py-3',
                                    'text-xs',
                                    'font-medium',
                                    'text-gray-500',
                                    'dark:text-gray-400',
                                    'uppercase',
                                    'tracking-wider'
                                ];
                                
                                if ($compact) {
                                    $thClasses = array_map(fn($class) => $class === 'py-3' ? 'py-2' : $class, $thClasses);
                                }
                                
                                switch($align) {
                                    case 'center':
                                        $thClasses[] = 'text-center';
                                        break;
                                    case 'right':
                                        $thClasses[] = 'text-right';
                                        break;
                                    default:
                                        $thClasses[] = 'text-left';
                                }
                                
                                if ($sticky) {
                                    $thClasses[] = 'sticky';
                                    $thClasses[] = 'top-0';
                                    $thClasses[] = 'z-10';
                                    $thClasses[] = 'bg-gray-50';
                                    $thClasses[] = 'dark:bg-gray-700';
                                }
                                
                                $thClass = implode(' ', $thClasses);
                            @endphp
                            
                            <th scope="col" 
                                class="{{ $thClass }}"
                                @if($width) style="width: {{ $width }}" @endif>
                                
                                @if($sortable && $sortKey)
                                    <button type="button" 
                                            class="group inline-flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
                                            @if($sortable) @click="sort('{{ $sortKey }}')" @endif>
                                        <span>{{ $label }}</span>
                                        <span class="ml-2 flex-none rounded text-gray-400 group-hover:text-gray-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                            </svg>
                                        </span>
                                    </button>
                                @else
                                    {{ $label }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            
            <tbody class="{{ $tbodyClass }}">
                {{ $slot }}
            </tbody>
        </table>
        
        @if(isset($empty) && !$slot->isEmpty() && trim($slot) === '')
            <div class="text-center py-12">
                <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                    @if($emptyIcon === 'table-cells')
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v0" />
                        </svg>
                    @elseif($emptyIcon === 'document-text')
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    @else
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-full h-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    @endif
                </div>
                <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Sin datos</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $empty }}</p>
            </div>
        @endif
    </div>
</div>