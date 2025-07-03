@extends('layouts.app')

@section('title', 'Detalles del Producto')

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
        <a href="{{ route('productos.index') }}" class="text-gray-400 hover:text-gray-500">Productos</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <span class="text-gray-500 font-medium">{{ $producto->nombre }}</span>
    </div>
</li>
@endsection

@push('styles')
<style>
    .status-badge {
        @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
    }
    .status-active { @apply bg-green-100 text-green-800; }
    .status-inactive { @apply bg-red-100 text-red-800; }
    .stock-low { @apply bg-red-100 text-red-800; }
    .stock-medium { @apply bg-yellow-100 text-yellow-800; }
    .stock-good { @apply bg-green-100 text-green-800; }
    .tab-content { @apply hidden; }
    .tab-content.active { @apply block; }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'general' }">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <div class="flex items-center">
                <div class="flex-shrink-0 h-16 w-16">
                    @if($producto->imagen_url)
                        <img class="h-16 w-16 rounded-lg object-cover" src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}">
                    @else
                        <div class="h-16 w-16 rounded-lg bg-gray-300 flex items-center justify-center">
                            <svg class="h-8 w-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl sm:truncate">
                        {{ $producto->nombre }}
                    </h1>
                    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
                        <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Código: {{ $producto->codigo ?? 'Sin código' }}
                        </div>
                        @if($producto->categoria)
                        <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            {{ $producto->categoria->nombre }}
                        </div>
                        @endif
                        <div class="mt-2 flex items-center">
                            @php
                                $stockStatus = 'stock-good';
                                $stockText = 'Stock OK';
                                if($producto->stock <= 0) {
                                    $stockStatus = 'stock-low';
                                    $stockText = 'Agotado';
                                } elseif($producto->stock <= $producto->stock_minimo) {
                                    $stockStatus = 'stock-medium';
                                    $stockText = 'Stock Bajo';
                                }
                            @endphp
                            <span class="status-badge {{ $stockStatus }}">
                                {{ $stockText }}
                            </span>
                        </div>
                        <div class="mt-2 flex items-center">
                            <span class="status-badge {{ $producto->activo ? 'status-active' : 'status-inactive' }}">
                                {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
            @can('productos.editar')
            <a href="{{ route('productos.edit', $producto) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gestion-600 hover:bg-gestion-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar
            </a>
            @endcan
            <a href="{{ route('productos.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver a la lista
            </a>
        </div>
    </div>

    <!-- Información de Precios y Stock -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-md">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Precio de Venta</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">S/ {{ number_format($producto->precio_venta, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-md">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Precio de Compra</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">S/ {{ number_format($producto->precio_compra, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-purple-100 rounded-md">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Margen de Ganancia</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format((($producto->precio_venta - $producto->precio_compra) / $producto->precio_compra) * 100, 1) }}%
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-8 h-8 bg-yellow-100 rounded-md">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Stock Actual</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $producto->stock }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button @click="activeTab = 'general'" 
                        :class="activeTab === 'general' ? 'border-gestion-500 text-gestion-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Información General
                </button>
                
                <button @click="activeTab = 'movimientos'" 
                        :class="activeTab === 'movimientos' ? 'border-gestion-500 text-gestion-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                    Movimientos de Inventario
                </button>
                
                <button @click="activeTab = 'ventas'" 
                        :class="activeTab === 'ventas' ? 'border-gestion-500 text-gestion-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Historial de Ventas
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- General Tab -->
            <div x-show="activeTab === 'general'" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Información del Producto -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Información del Producto</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Código</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $producto->codigo ?? 'No asignado' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $producto->nombre }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $producto->descripcion ?: 'Sin descripción' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Categoría</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $producto->categoria->nombre ?? 'Sin categoría' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Proveedor</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $producto->proveedor->nombre ?? 'Sin proveedor' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Información de Inventario -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Control de Inventario</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Stock Actual</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $producto->stock }} unidades</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Stock Mínimo</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $producto->stock_minimo }} unidades</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Valor Total en Stock</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">S/ {{ number_format($producto->stock * $producto->precio_compra, 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Garantía</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">
                                    @if($producto->garantia_dias)
                                        {{ $producto->garantia_dias }} días
                                    @else
                                        Sin garantía
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                                <dd class="text-sm">
                                    <span class="status-badge {{ $producto->activo ? 'status-active' : 'status-inactive' }}">
                                        {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Precios y Márgenes -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 lg:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Análisis de Precios</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Precio de Compra</p>
                                <p class="text-xl font-bold text-blue-600 dark:text-blue-400">S/ {{ number_format($producto->precio_compra, 2) }}</p>
                            </div>
                            <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Precio de Venta</p>
                                <p class="text-xl font-bold text-green-600 dark:text-green-400">S/ {{ number_format($producto->precio_venta, 2) }}</p>
                            </div>
                            <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ganancia por Unidad</p>
                                <p class="text-xl font-bold text-purple-600 dark:text-purple-400">S/ {{ number_format($producto->precio_venta - $producto->precio_compra, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Movimientos Tab -->
            <div x-show="activeTab === 'movimientos'" class="tab-content">
                @if($producto->movimientosInventario && $producto->movimientosInventario->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($producto->movimientosInventario as $movimiento)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $movimiento->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="status-badge {{ $movimiento->tipo_ajuste === 'entrada' ? 'status-active' : 'status-inactive' }}">
                                                {{ ucfirst($movimiento->tipo_ajuste ?? 'Movimiento') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $movimiento->diferencia > 0 ? '+' : '' }}{{ $movimiento->diferencia }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $movimiento->usuario->name ?? 'Sistema' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                            {{ $movimiento->observaciones ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay movimientos registrados</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Los movimientos de inventario aparecerán aquí.</p>
                    </div>
                @endif
            </div>

            <!-- Ventas Tab -->
            <div x-show="activeTab === 'ventas'" class="tab-content">
                @if($producto->detalleVentas && $producto->detalleVentas->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Venta</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Precio Unitario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($producto->detalleVentas as $detalle)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $detalle->venta->fecha->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $detalle->venta->codigo_venta }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $detalle->venta->cliente->nombre ?? 'Cliente General' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $detalle->cantidad }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            S/ {{ number_format($detalle->precio_unitario, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            S/ {{ number_format($detalle->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay ventas registradas</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Este producto no se ha vendido aún.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Acciones Rápidas</h3>
        </div>
        
        <div class="px-6 py-4">
            <div class="flex flex-wrap gap-4">
                @can('productos.editar')
                <button onclick="openStockModal({{ $producto->id }}, '{{ $producto->nombre }}', {{ $producto->stock }})" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                    Ajustar Stock
                </button>
                @endcan

                @can('ventas.crear')
                <a href="{{ route('ventas.create', ['producto' => $producto->id]) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Vender Producto
                </a>
                @endcan

                @can('productos.editar')
                <button class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    Actualizar Precios
                </button>
                @endcan

                <button class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Generar Reporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Ajuste de Stock -->
<div id="stockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Ajustar Stock</h3>
                <button onclick="closeStockModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="stockForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Producto</label>
                    <p id="productName" class="text-sm text-gray-900 dark:text-white font-medium">{{ $producto->nombre }}</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Stock Actual</label>
                    <p id="currentStock" class="text-sm text-gray-900 dark:text-white">{{ $producto->stock }} unidades</p>
                </div>
                
                <div class="mb-4">
                    <label for="tipo_ajuste" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Ajuste</label>
                    <select id="tipo_ajuste" name="tipo_ajuste" required class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                        <option value="entrada">Entrada (+)</option>
                        <option value="salida">Salida (-)</option>
                        <option value="ajuste">Ajuste Manual</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="cantidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad</label>
                    <input type="number" id="cantidad" name="cantidad" required min="1" 
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                </div>
                
                <div class="mb-4">
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="3" 
                              class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeStockModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-gestion-600 text-white rounded-md text-sm font-medium hover:bg-gestion-700">
                        Ajustar Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openStockModal(productId, productName, currentStock) {
        document.getElementById('stockForm').action = `/productos/${productId}/adjust-stock`;
        document.getElementById('stockModal').classList.remove('hidden');
    }

    function closeStockModal() {
        document.getElementById('stockModal').classList.add('hidden');
        document.getElementById('stockForm').reset();
    }

    // Cerrar modal al hacer clic fuera
    document.getElementById('stockModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeStockModal();
        }
    });
</script>
@endpush