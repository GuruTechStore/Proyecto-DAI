{{-- resources/views/modules/proveedores/show.blade.php - PARTE 1 --}}
@extends('layouts.app')

@section('title', 'Detalles del Proveedor')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12" />
        </svg>
        <a href="{{ route('proveedores.index') }}" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gestion-600 dark:hover:text-gestion-400">
            Proveedores
        </a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $proveedor->nombre }}</span>
    </div>
</li>
@endsection

@push('styles')
<style>
    .info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .dark .info-card {
        background: linear-gradient(135deg, #4c1d95 0%, #581c87 100%);
    }
    .stat-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    .dark .stat-card:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }
    .badge-status {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .badge-activo {
        background-color: #dcfce7;
        color: #166534;
    }
    .dark .badge-activo {
        background-color: rgba(34, 197, 94, 0.2);
        color: #4ade80;
    }
    .badge-inactivo {
        background-color: #fef2f2;
        color: #991b1b;
    }
    .dark .badge-inactivo {
        background-color: rgba(239, 68, 68, 0.2);
        color: #f87171;
    }
    .loading-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    .dark .loading-skeleton {
        background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
        background-size: 200% 100%;
    }
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
@endpush

@section('content')
<div x-data="proveedorDetails()" x-init="init()" class="space-y-6">
    
    <!-- Header -->
    <div class="info-card rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-16 w-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-6">
                        <h1 class="text-3xl font-bold text-white">
                            {{ $proveedor->nombre }}
                        </h1>
                        <p class="mt-1 text-lg text-white text-opacity-90">
                            @if($proveedor->contacto)
                                {{ $proveedor->contacto }}
                            @else
                                Proveedor de servicios
                            @endif
                        </p>
                        <div class="mt-2 flex items-center space-x-4">
                            <span class="badge-status {{ !$proveedor->deleted_at ? 'badge-activo' : 'badge-inactivo' }}">
                                {{ !$proveedor->deleted_at ? 'Activo' : 'Inactivo' }}
                            </span>
                            @if($proveedor->ruc)
                                <span class="text-white text-opacity-75 text-sm">
                                    RUC: {{ $proveedor->ruc }}
                                </span>
                            @endif
                            <span class="text-white text-opacity-75 text-sm">
                                Registrado: {{ $proveedor->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-3">
                    @can('proveedores.editar')
                    <a href="{{ route('proveedores.edit', $proveedor) }}" 
                       class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Editar
                    </a>
                    @endcan
                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                @click.away="open = false"
                                class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                            Más Acciones
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-10">
                            <div class="py-1">
                                @if($proveedor->email)
                                <a href="mailto:{{ $proveedor->email }}" 
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Enviar Email
                                </a>
                                @endif
                                
                                @if($proveedor->telefono)
                                <a href="tel:{{ $proveedor->telefono }}" 
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    Llamar
                                </a>
                                @endif
                                
                                <button @click="exportProveedor()" 
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Exportar Datos
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('proveedores.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="stat-card bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Productos</p>
                    <div x-show="loading.stats" class="h-8 w-16 loading-skeleton rounded mt-1"></div>
                    <p x-show="!loading.stats" class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.productos || '0'">0</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5L12 8m0 5l3-3M9 21h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Compras Este Mes</p>
                    <div x-show="loading.stats" class="h-8 w-16 loading-skeleton rounded mt-1"></div>
                    <p x-show="!loading.stats" class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.compras_mes || '0'">0</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Facturado</p>
                    <div x-show="loading.stats" class="h-8 w-24 loading-skeleton rounded mt-1"></div>
                    <p x-show="!loading.stats" class="text-2xl font-bold text-gray-900 dark:text-white" x-text="formatCurrency(stats.total_facturado || 0)">S/ 0.00</p>
                </div>
            </div>
        </div>
        
        <div class="stat-card bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Último Pedido</p>
                    <div x-show="loading.stats" class="h-8 w-20 loading-skeleton rounded mt-1"></div>
                    <p x-show="!loading.stats" class="text-lg font-semibold text-gray-900 dark:text-white" x-text="stats.ultimo_pedido || 'Sin pedidos'">-</p>
                </div>
            </div>
        </div>
    </div>
{{-- resources/views/modules/proveedores/show.blade.php - PARTE 2 --}}
    
    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Información de Contacto -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        Información de Contacto
                    </h2>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Contacto</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $proveedor->contacto ?: 'No especificado' }}</p>
                        </div>
                    </div>
                    
                    @if($proveedor->telefono)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Teléfono</p>
                            <a href="tel:{{ $proveedor->telefono }}" class="text-sm text-gestion-600 dark:text-gestion-400 hover:underline">
                                {{ $proveedor->telefono }}
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    @if($proveedor->email)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 7.89a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Email</p>
                            <a href="mailto:{{ $proveedor->email }}" class="text-sm text-gestion-600 dark:text-gestion-400 hover:underline">
                                {{ $proveedor->email }}
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    @if($proveedor->direccion)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Dirección</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $proveedor->direccion }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Información Comercial -->
            @if($proveedor->banco || $proveedor->numero_cuenta || $proveedor->tipo_cuenta)
            <div class="mt-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Información Bancaria
                    </h2>
                </div>
                
                <div class="p-6 space-y-4">
                    @if($proveedor->banco)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Banco</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $proveedor->banco }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($proveedor->tipo_cuenta)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Tipo de Cuenta</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $proveedor->tipo_cuenta === 'corriente' ? 'Cuenta Corriente' : 'Cuenta de Ahorros' }}
                            </p>
                        </div>
                    </div>
                    @endif
                    
                    @if($proveedor->numero_cuenta)
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Número de Cuenta</p>
                            <p class="text-sm font-mono text-gray-600 dark:text-gray-400">{{ $proveedor->numero_cuenta }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
        
        <!-- Productos y Actividad -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Productos del Proveedor -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Productos Asociados
                        </h2>
                        <span x-show="!loading.productos" class="text-sm text-gray-500 dark:text-gray-400" x-text="`${productos.length} productos`"></span>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- Loading State -->
                    <div x-show="loading.productos" class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 loading-skeleton rounded-lg"></div>
                                <div class="ml-4">
                                    <div class="h-4 w-32 loading-skeleton rounded mb-2"></div>
                                    <div class="h-3 w-24 loading-skeleton rounded"></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="h-4 w-20 loading-skeleton rounded mb-2"></div>
                                <div class="h-3 w-16 loading-skeleton rounded"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 loading-skeleton rounded-lg"></div>
                                <div class="ml-4">
                                    <div class="h-4 w-32 loading-skeleton rounded mb-2"></div>
                                    <div class="h-3 w-24 loading-skeleton rounded"></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="h-4 w-20 loading-skeleton rounded mb-2"></div>
                                <div class="h-3 w-16 loading-skeleton rounded"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div x-show="!loading.productos && productos.length === 0" class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Sin productos</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Este proveedor no tiene productos asociados.</p>
                        @can('productos.crear')
                        <a href="{{ route('productos.create') }}?proveedor={{ $proveedor->id }}" 
                           class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Agregar Producto
                        </a>
                        @endcan
                    </div>
                    
                    <!-- Products List -->
                    <div x-show="!loading.productos && productos.length > 0" class="space-y-3">
                        <template x-for="producto in productos" :key="producto.id">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600/50 transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 bg-gestion-100 dark:bg-gestion-800 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white" x-text="producto.nombre"></h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400" x-text="producto.codigo"></p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-6">
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatCurrency(producto.precio_compra)"></p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Precio compra</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="producto.stock_actual"></p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Stock</p>
                                    </div>
                                    @can('productos.ver')
                                    <div>
                                        <a :href="`/productos/${producto.id}`" 
                                           class="text-gestion-600 hover:text-gestion-700 dark:text-gestion-400 dark:hover:text-gestion-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </div>
                                    @endcan
                                </div>
                            </div>
                        </template>
                        
                        <!-- Ver todos los productos -->
                        <div x-show="productos.length >= 5" class="text-center pt-4">
                            <a href="{{ route('productos.index') }}?proveedor={{ $proveedor->id }}" 
                               class="text-gestion-600 hover:text-gestion-700 dark:text-gestion-400 dark:hover:text-gestion-300 text-sm font-medium">
                                Ver todos los productos →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Historial de Compras -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Historial de Compras
                        </h2>
                        <span x-show="!loading.compras" class="text-sm text-gray-500 dark:text-gray-400" x-text="`${compras.length} registros`"></span>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- Loading State -->
                    <div x-show="loading.compras" class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 loading-skeleton rounded-lg"></div>
                                <div class="ml-4">
                                    <div class="h-4 w-32 loading-skeleton rounded mb-2"></div>
                                    <div class="h-3 w-24 loading-skeleton rounded"></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="h-4 w-20 loading-skeleton rounded mb-2"></div>
                                <div class="h-3 w-16 loading-skeleton rounded"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div x-show="!loading.compras && compras.length === 0" class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 6h6m-6 4h6" />
                        </svg>
                        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Sin historial</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">No se han registrado compras con este proveedor.</p>
                        @can('inventario.entradas')
                        <a href="{{ route('inventario.entradas.create') }}?proveedor={{ $proveedor->id }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Registrar Compra
                        </a>
                        @endcan
                    </div>
                    
                    <!-- Purchases List -->
                    <div x-show="!loading.compras && compras.length > 0" class="space-y-3">
                        <template x-for="compra in compras" :key="compra.id">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600/50 transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 bg-green-100 dark:bg-green-800 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5L12 8m0 5l3-3M9 21h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white" x-text="compra.numero_factura || 'Sin número'"></h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400" x-text="formatDate(compra.fecha)"></p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-6">
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="formatCurrency(compra.total)"></p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400" x-text="compra.tipo_movimiento"></p>
                                    </div>
                                    @can('inventario.ver')
                                    <div>
                                        <a :href="`/inventario/entradas/${compra.id}`" 
                                           class="text-gestion-600 hover:text-gestion-700 dark:text-gestion-400 dark:hover:text-gestion-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </div>
                                    @endcan
                                </div>
                            </div>
                        </template>
                        
                        <!-- Ver todo el historial -->
                        <div x-show="compras.length >= 5" class="text-center pt-4">
                            <a href="{{ route('inventario.entradas.index') }}?proveedor={{ $proveedor->id }}" 
                               class="text-gestion-600 hover:text-gestion-700 dark:text-gestion-400 dark:hover:text-gestion-300 text-sm font-medium">
                                Ver historial completo →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
    
</div>

@push('scripts')
<script>
function proveedorDetails() {
    return {
        loading: {
            productos: true,
            compras: true,
            stats: true
        },
        
        stats: {
            productos: 0,
            compras_mes: 0,
            total_facturado: 0,
            ultimo_pedido: null
        },
        
        productos: [],
        compras: [],
        
        async init() {
            await Promise.all([
                this.loadStats(),
                this.loadProductos(),
                this.loadCompras()
            ]);
        },
        
        async loadStats() {
            this.loading.stats = true;
            try {
                const response = await fetch(`/api/proveedores/{{ $proveedor->id }}/stats`);
                if (response.ok) {
                    this.stats = await response.json();
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            } finally {
                this.loading.stats = false;
            }
        },
        
        async loadProductos() {
            this.loading.productos = true;
            try {
                const response = await fetch(`/api/proveedores/{{ $proveedor->id }}/productos`);
                if (response.ok) {
                    const data = await response.json();
                    this.productos = data.data || data;
                }
            } catch (error) {
                console.error('Error loading productos:', error);
            } finally {
                this.loading.productos = false;
            }
        },
        
        async loadCompras() {
            this.loading.compras = true;
            try {
                const response = await fetch(`/api/proveedores/{{ $proveedor->id }}/compras`);
                if (response.ok) {
                    const data = await response.json();
                    this.compras = data.data || data;
                }
            } catch (error) {
                console.error('Error loading compras:', error);
            } finally {
                this.loading.compras = false;
            }
        },
        
        async exportProveedor() {
            try {
                const response = await fetch(`/api/proveedores/{{ $proveedor->id }}/export`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    }
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `proveedor_{{ $proveedor->id }}_${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    this.showSuccess('Datos exportados correctamente');
                } else {
                    this.showError('Error al exportar los datos');
                }
            } catch (error) {
                console.error('Error exporting data:', error);
                this.showError('Error al exportar los datos');
            }
        },
        
        formatCurrency(value) {
            if (!value) return 'S/ 0.00';
            return new Intl.NumberFormat('es-PE', {
                style: 'currency',
                currency: 'PEN'
            }).format(value);
        },
        
        formatDate(dateString) {
            if (!dateString) return '-';
            return new Date(dateString).toLocaleDateString('es-PE', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },
        
        showSuccess(message) {
            if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
                Alpine.store('notifications').add({
                    type: 'success',
                    message: message
                });
            } else {
                alert(message);
            }
        },
        
        showError(message) {
            if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
                Alpine.store('notifications').add({
                    type: 'error',
                    message: message
                });
            } else {
                alert(message);
            }
        }
    };
}
</script>
@endpush

@endsection