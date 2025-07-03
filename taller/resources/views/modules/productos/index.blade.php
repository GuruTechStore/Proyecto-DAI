@extends('layouts.app')

@section('title', 'Gestión de Productos')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-500">Dashboard</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <span class="text-gray-500 font-medium">Productos</span>
    </div>
</li>
@endsection

@push('styles')
<style>
    .filter-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .status-badge {
        @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
    }
    .status-active { @apply bg-green-100 text-green-800; }
    .status-inactive { @apply bg-red-100 text-red-800; }
    .stock-low { @apply bg-red-100 text-red-800; }
    .stock-medium { @apply bg-yellow-100 text-yellow-800; }
    .stock-good { @apply bg-green-100 text-green-800; }
    .category-badge { @apply bg-blue-100 text-blue-800; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestión de Productos</h1>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                Administra el catálogo de productos y controla el inventario.
            </p>
        </div>
        @can('productos.crear')
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('productos.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-gestion-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gestion-700 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nuevo Producto
            </a>
        </div>
        @endcan
    </div>

    <!-- Filtros y búsqueda -->
    <div class="filter-section rounded-lg p-6 shadow-sm">
        <form method="GET" action="{{ route('productos.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-white mb-2">Buscar</label>
                <input type="text" 
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Nombre, SKU, descripción..."
                       class="w-full rounded-md border-white/20 bg-white/10 text-white placeholder-white/70 focus:border-white focus:ring-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-white mb-2">Estado</label>
                <select name="estado" class="w-full rounded-md border-white/20 bg-white/10 text-white focus:border-white focus:ring-white">
                    <option value="">Todos</option>
                    <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activos</option>
                    <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-white mb-2">Categoría</label>
                <select name="categoria" class="w-full rounded-md border-white/20 bg-white/10 text-white focus:border-white focus:ring-white">
                    <option value="">Todas</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ request('categoria') == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-white mb-2">Stock</label>
                <select name="stock" class="w-full rounded-md border-white/20 bg-white/10 text-white focus:border-white focus:ring-white">
                    <option value="">Todos</option>
                    <option value="bajo" {{ request('stock') === 'bajo' ? 'selected' : '' }}>Stock Bajo</option>
                    <option value="normal" {{ request('stock') === 'normal' ? 'selected' : '' }}>Stock Normal</option>
                    <option value="sin_stock" {{ request('stock') === 'sin_stock' ? 'selected' : '' }}>Sin Stock</option>
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-md transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('productos.index') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-md transition-colors">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-md">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Productos</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-md">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Activos</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['activos'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-red-100 rounded-md">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.502 0L4.34 12.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Stock Bajo</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['stock_bajo'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-purple-100 rounded-md">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Categorías</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['categorias'] ?? 0}}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-yellow-100 rounded-md">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Valor Inventario</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">S/ {{ number_format($stats['valor_inventario'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de productos -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Lista de Productos</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Producto
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Categoría
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Precio
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Stock
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Proveedor
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Creación
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($productos as $producto)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($producto->imagen)
                                            <img class="h-10 w-10 rounded-lg object-cover" src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}">
                                        @else
                                            <div class="h-10 w-10 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $producto->nombre }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ $producto->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($producto->categoria)
                                    <span class="status-badge category-badge">
                                        {{ $producto->categoria->nombre }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Sin categoría</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">S/ {{ number_format($producto->precio, 2) }}</div>
                                @if($producto->precio_costo)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Costo: S/ {{ number_format($producto->precio_costo, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $stockLevel = 'good';
                                    if ($producto->stock <= $producto->stock_minimo) {
                                        $stockLevel = 'low';
                                    } elseif ($producto->stock <= ($producto->stock_minimo * 2)) {
                                        $stockLevel = 'medium';
                                    }
                                @endphp
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $producto->stock }} unidades</div>
                                <span class="status-badge stock-{{ $stockLevel }}">
                                    @if($stockLevel === 'low')
                                        Stock Bajo
                                    @elseif($stockLevel === 'medium')
                                        Stock Medio
                                    @else
                                        Stock Normal
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($producto->proveedor)
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $producto->proveedor->nombre }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $producto->proveedor->telefono }}</div>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Sin proveedor</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="status-badge {{ $producto->activo ? 'status-active' : 'status-inactive' }}">
                                    {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $producto->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs">{{ $producto->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('productos.show', $producto) }}" 
                                       class="text-gestion-600 hover:text-gestion-900 dark:text-gestion-400 dark:hover:text-gestion-300"
                                       title="Ver detalles">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    @can('productos.editar')
                                    <a href="{{ route('productos.edit', $producto) }}" 
                                       class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                       title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    @endcan
                                    @can('inventario.ajustes')
                                    {{-- <a href="{{ route('inventario.ajustes.create', ['producto_id' => $producto->id]) }}" 
                                       class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300"
                                       title="Ajustar stock">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                                        </svg>
                                    </a> --}}
                                    @endcan
                                    @can('productos.eliminar')
                                    <form action="{{ route('productos.destroy', $producto) }}" method="POST" class="inline-block"
                                          onsubmit="return confirm('¿Está seguro que desea eliminar este producto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay productos</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comienza agregando tu primer producto.</p>
                                @can('productos.crear')
                                <div class="mt-6">
                                    <a href="{{ route('productos.create') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Nuevo Producto
                                    </a>
                                </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($productos->hasPages())
        <div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                {{ $productos->previousPageUrl() ? 
                    '<a href="'.$productos->previousPageUrl().'" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Anterior</a>' : 
                    '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100">Anterior</span>' 
                }}
                {{ $productos->nextPageUrl() ? 
                    '<a href="'.$productos->nextPageUrl().'" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Siguiente</a>' : 
                    '<span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100">Siguiente</span>' 
                }}
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Mostrando {{ $productos->firstItem() }} a {{ $productos->lastItem() }} de {{ $productos->total() }} resultados
                    </p>
                </div>
                <div>
                    {{ $productos->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection