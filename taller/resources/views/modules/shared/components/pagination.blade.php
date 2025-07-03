{{-- resources/views/shared/components/pagination.blade.php --}}
@props([
    'paginator',
    'simple' => false,
    'showInfo' => true,
    'showPerPage' => true,
    'perPageOptions' => [10, 25, 50, 100],
    'size' => 'md'
])

@php
$sizeClasses = [
    'sm' => 'px-2 py-1 text-xs',
    'md' => 'px-3 py-2 text-sm',
    'lg' => 'px-4 py-3 text-base'
];

$buttonSize = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

@if($paginator->hasPages())
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 py-4">
    {{-- Información de resultados --}}
    @if($showInfo)
        <div class="text-sm text-gray-700 dark:text-gray-300">
            <span>Mostrando</span>
            <span class="font-medium">{{ $paginator->firstItem() }}</span>
            <span>a</span>
            <span class="font-medium">{{ $paginator->lastItem() }}</span>
            <span>de</span>
            <span class="font-medium">{{ $paginator->total() }}</span>
            <span>resultados</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        {{-- Selector de elementos por página --}}
        @if($showPerPage && !$simple)
            <div class="flex items-center space-x-2">
                <label for="perPage" class="text-sm text-gray-700 dark:text-gray-300">Mostrar:</label>
                <select id="perPage" 
                        name="perPage" 
                        class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md text-sm focus:ring-gestion-500 focus:border-gestion-500"
                        onchange="window.location.href = updateUrlParameter(window.location.href, 'per_page', this.value)">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" {{ request('per_page', 10) == $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Navegación de páginas --}}
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Paginación">
            @if($simple)
                {{-- Paginación simple --}}
                @if($paginator->onFirstPage())
                    <span class="relative inline-flex items-center {{ $buttonSize }} font-medium text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 cursor-default rounded-l-md">
                        Anterior
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" 
                       class="relative inline-flex items-center {{ $buttonSize }} font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-l-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Anterior
                    </a>
                @endif

                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" 
                       class="relative inline-flex items-center {{ $buttonSize }} font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-r-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Siguiente
                    </a>
                @else
                    <span class="relative inline-flex items-center {{ $buttonSize }} font-medium text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 cursor-default rounded-r-md">
                        Siguiente
                    </span>
                @endif
            @else
                {{-- Paginación completa --}}
                {{-- Botón Anterior --}}
                @if($paginator->onFirstPage())
                    <span class="relative inline-flex items-center {{ $buttonSize }} font-medium text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 cursor-default rounded-l-md">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Anterior
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" 
                       class="relative inline-flex items-center {{ $buttonSize }} font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-l-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Anterior
                    </a>
                @endif

                {{-- Enlaces de página --}}
                @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                    @if($page == $paginator->currentPage())
                        <span class="relative inline-flex items-center {{ $buttonSize }} font-medium text-white bg-gestion-600 border border-gestion-600 cursor-default">
                            {{ $page }}
                        </span>
                    @elseif($page == 1 || $page == $paginator->lastPage() || ($page >= $paginator->currentPage() - 2 && $page <= $paginator->currentPage() + 2))
                        <a href="{{ $url }}" 
                           class="relative inline-flex items-center {{ $buttonSize }} font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            {{ $page }}
                        </a>
                    @elseif($page == 2 || $page == $paginator->lastPage() - 1)
                        <span class="relative inline-flex items-center {{ $buttonSize }} font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600">
                            ...
                        </span>
                    @endif
                @endforeach

                {{-- Botón Siguiente --}}
                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" 
                       class="relative inline-flex items-center {{ $buttonSize }} font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-r-md hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Siguiente
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <span class="relative inline-flex items-center {{ $buttonSize }} font-medium text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 cursor-default rounded-r-md">
                        Siguiente
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                @endif
            @endif
        </nav>
    </div>
</div>

<script>
function updateUrlParameter(url, param, value) {
    const urlObj = new URL(url);
    urlObj.searchParams.set(param, value);
    urlObj.searchParams.delete('page'); // Reset to first page when changing per_page
    return urlObj.toString();
}
</script>
@endif