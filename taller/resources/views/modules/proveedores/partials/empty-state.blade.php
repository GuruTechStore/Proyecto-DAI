{{-- resources/views/modules/proveedores/partials/empty-state.blade.php --}}
<div class="text-center py-12">
    
    <!-- Icono -->
    <div class="mx-auto h-24 w-24 mb-6">
        <svg class="h-24 w-24 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12" />
        </svg>
    </div>
    
    <!-- Título y descripción -->
    <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">
        {{ $title ?? 'No hay proveedores registrados' }}
    </h3>
    
    <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">
        {{ $description ?? 'Comienza agregando tu primer proveedor para gestionar mejor tu inventario y compras.' }}
    </p>
    
    <!-- Botones de acción -->
    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        
        @can('proveedores.crear')
        <a href="{{ route('proveedores.create') }}" 
           class="inline-flex items-center px-6 py-3 bg-gestion-600 hover:bg-gestion-700 text-white font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            {{ $buttonText ?? 'Agregar Primer Proveedor' }}
        </a>
        @endcan
        
        <!-- Botón secundario opcional -->
        @if(isset($secondaryAction))
        <a href="{{ $secondaryAction['url'] }}" 
           class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
            @if(isset($secondaryAction['icon']))
                {!! $secondaryAction['icon'] !!}
            @endif
            {{ $secondaryAction['text'] }}
        </a>
        @endif
        
    </div>
    
    <!-- Información adicional -->
    @if(isset($helpText) || isset($features))
    <div class="mt-8 max-w-2xl mx-auto">
        
        @if(isset($helpText))
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            {{ $helpText }}
        </p>
        @endif
        
        @if(isset($features))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($features as $feature)
            <div class="text-center">
                <div class="mx-auto h-12 w-12 mb-3">
                    {!! $feature['icon'] !!}
                </div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                    {{ $feature['title'] }}
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $feature['description'] }}
                </p>
            </div>
            @endforeach
        </div>
        @endif
        
    </div>
    @endif
    
</div>

<!-- Variantes del empty state -->
@if(isset($variant))

    @if($variant === 'filtered')
    <!-- Empty state para búsqueda/filtros -->
    <div class="text-center py-12">
        <div class="mx-auto h-24 w-24 mb-6">
            <svg class="h-24 w-24 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        
        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">
            No se encontraron proveedores
        </h3>
        
        <p class="text-gray-500 dark:text-gray-400 mb-6">
            No hay proveedores que coincidan con los filtros aplicados.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button type="button" 
                    onclick="clearFilters()"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gestion-600 dark:text-gestion-400 hover:text-gestion-700 dark:hover:text-gestion-300">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Limpiar filtros
            </button>
            
            @can('proveedores.crear')
            <a href="{{ route('proveedores.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Agregar Proveedor
            </a>
            @endcan
        </div>
    </div>
    @endif

    @if($variant === 'error')
    <!-- Empty state para errores -->
    <div class="text-center py-12">
        <div class="mx-auto h-24 w-24 mb-6">
            <svg class="h-24 w-24 text-red-300 dark:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        
        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-2">
            Error al cargar proveedores
        </h3>
        
        <p class="text-gray-500 dark:text-gray-400 mb-6">
            Ocurrió un problema al cargar la información. Por favor, intenta nuevamente.
        </p>
        
        <button type="button" 
                onclick="location.reload()"
                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Intentar nuevamente
        </button>
    </div>
    @endif

@endif

@push('scripts')
<script>
function clearFilters() {
    // Limpiar filtros si se está usando Alpine.js
    if (typeof Alpine !== 'undefined') {
        const proveedoresComponent = Alpine.data('proveedoresManager');
        if (proveedoresComponent) {
            // Reset filters
            proveedoresComponent.filters = {
                search: '',
                status: '',
                perPage: 10
            };
            
            // Reload data
            proveedoresComponent.loadProveedores(1);
        }
    } else {
        // Fallback: reload page without query parameters
        window.location.href = window.location.pathname;
    }
}
</script>
@endpush