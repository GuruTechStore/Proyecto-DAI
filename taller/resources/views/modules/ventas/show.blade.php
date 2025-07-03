@extends('layouts.app')

@section('title', 'Venta #' . $venta->numero_venta)

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8" x-data="ventaShow({{ $venta->toJson() }})">
    <!-- Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ route('ventas.index') }}" class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 md:ml-2">Ventas</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-800 dark:text-gray-200 md:ml-2">{{ $venta->numero_venta }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                
                <div class="mt-2 flex items-center space-x-4">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Venta #{{ $venta->numero_venta }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @switch($venta->estado)
                            @case('completado')
                                bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                @break
                            @case('pendiente')
                                bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                @break
                            @case('cancelado')
                                bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                @break
                            @case('devuelto')
                                bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                @break
                            @default
                                bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                        @endswitch
                    ">
                        {{ ucfirst($venta->estado) }}
                    </span>
                    
                    @if($venta->metodo_pago === 'credito')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Crédito
                    </span>
                    @endif
                </div>
                
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $venta->fecha_venta->format('d/m/Y H:i') }} • 
                    Vendedor: {{ $venta->empleado->nombre ?? 'No asignado' }}
                </p>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <!-- Print Invoice -->
                <a href="{{ route('ventas.invoice', $venta) }}" 
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Factura
                </a>

                <!-- Print Receipt -->
                <a href="{{ route('ventas.receipt', $venta) }}" 
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Recibo
                </a>

                @can('ventas.editar')
                @if($venta->estado !== 'cancelado')
                <!-- Edit Sale -->
                <a href="{{ route('ventas.edit', $venta) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gestion-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar
                </a>
                @endif
                @endcan

                <!-- More Actions Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                        Más Acciones
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition
                         class="absolute right-0 z-10 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
                        <div class="py-1">
                            <!-- Duplicate Sale -->
                            @can('ventas.crear')
                            <a href="{{ route('ventas.create') }}?duplicate={{ $venta->id }}" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Duplicar venta
                            </a>
                            @endcan
                            
                            <!-- Export Options -->
                            <a @click="exportSale('pdf')" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Exportar PDF
                            </a>
                            
                            <a @click="exportSale('excel')" 
                               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Exportar Excel
                            </a>

                            @can('ventas.editar')
                            @if($venta->estado === 'completado' && $venta->estado !== 'devuelto')
                            <div class="border-t border-gray-100 dark:border-gray-700"></div>
                            <!-- Process Refund -->
                            <button @click="showRefundModal = true"
                                    class="flex items-center w-full px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50 dark:text-yellow-300 dark:hover:bg-yellow-900/20">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                                </svg>
                                Procesar devolución
                            </button>
                            @endif
                            @endcan
                            
                            @can('ventas.eliminar')
                            @if($venta->estado !== 'completado')
                            <div class="border-t border-gray-100 dark:border-gray-700"></div>
                            <button @click="confirmDelete"
                                    class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-900/20">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Eliminar venta
                            </button>
                            @endif
                            @endcan
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <a href="{{ route('ventas.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Status Alerts -->
    @if($venta->estado === 'pendiente')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.966-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Venta Pendiente
                    </h3>
                    <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>Esta venta está pendiente de completar. No se ha actualizado el inventario.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($venta->metodo_pago === 'credito' && $venta->fecha_vencimiento)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        Venta a Crédito
                    </h3>
                    <div class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                        <p>Fecha de vencimiento: {{ $venta->fecha_vencimiento->format('d/m/Y') }}
                            @if($venta->fecha_vencimiento->isPast())
                                <span class="text-red-600 font-semibold">(VENCIDO - {{ $venta->fecha_vencimiento->diffForHumans() }})</span>
                            @else
                                ({{ $venta->fecha_vencimiento->diffForHumans() }})
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column - Sale Details -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Customer Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Información del Cliente
                        </h3>
                    </div>
                    
                    <div class="px-6 py-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                    <span class="text-lg font-medium text-white">
                                        {{ substr($venta->cliente->nombre, 0, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">{{ $venta->cliente->nombre }}</h4>
                                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Documento:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white font-medium">{{ $venta->cliente->documento }}</span>
                                    </div>
                                    @if($venta->cliente->telefono)
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Teléfono:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $venta->cliente->telefono }}</span>
                                    </div>
                                    @endif
                                    @if($venta->cliente->email)
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Email:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $venta->cliente->email }}</span>
                                    </div>
                                    @endif
                                    @if($venta->cliente->direccion)
                                    <div class="md:col-span-2">
                                        <span class="text-gray-500 dark:text-gray-400">Dirección:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $venta->cliente->direccion }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="{{ route('clientes.show', $venta->cliente) }}" 
                                   class="text-gestion-600 hover:text-gestion-700 text-sm font-medium">
                                    Ver perfil →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Sold -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Productos Vendidos
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gestion-100 text-gestion-800 dark:bg-gestion-900 dark:text-gestion-300">
                                {{ $venta->detalles->count() }} producto(s)
                            </span>
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Producto
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Precio Unit.
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Cantidad
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Descuento
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($venta->detalles as $detalle)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-gestion-400 to-gestion-600 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $detalle->producto->nombre }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    Código: {{ $detalle->producto->codigo }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            S/ {{ number_format($detalle->precio_unitario, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $detalle->cantidad }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($detalle->descuento > 0)
                                        <div class="text-sm font-medium text-green-600 dark:text-green-400">
                                            {{ $detalle->descuento }}%
                                        </div>
                                        <div class="text-xs text-green-500">
                                            -S/ {{ number_format(($detalle->cantidad * $detalle->precio_unitario) * ($detalle->descuento / 100), 2) }}
                                        </div>
                                        @else
                                        <div class="text-sm text-gray-500 dark:text-gray-400">-</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                            S/ {{ number_format($detalle->subtotal, 2) }}
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Sale Notes -->
                @if($venta->notas)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Notas de la Venta
                        </h3>
                    </div>
                    
                    <div class="px-6 py-6">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $venta->notas }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Credit Terms -->
                @if($venta->metodo_pago === 'credito' && $venta->observaciones_credito)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Términos del Crédito
                        </h3>
                    </div>
                    
                    <div class="px-6 py-6">
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200 whitespace-pre-wrap">{{ $venta->observaciones_credito }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Activity Timeline -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Historial de Actividad
                        </h3>
                    </div>
                    
                    <div class="px-6 py-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <!-- Sale Created -->
                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-900 dark:text-white">
                                                        Venta creada por <span class="font-medium">{{ $venta->empleado->nombre ?? 'Sistema' }}</span>
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $venta->created_at->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <!-- Sale Status Updates -->
                                @if($venta->estado === 'completado')
                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-900 dark:text-white">
                                                        Venta completada - Pago procesado
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $venta->updated_at->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif

                                @if($venta->estado === 'cancelado')
                                <li>
                                    <div class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-900 dark:text-white">
                                                        Venta cancelada
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $venta->updated_at->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif

                                <!-- Last Activity -->
                                @if($venta->created_at != $venta->updated_at)
                                <li>
                                    <div class="relative">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-900 dark:text-white">
                                                        Última modificación
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    {{ $venta->updated_at->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right Column - Summary & Actions -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Sale Summary -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 sticky top-8">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Resumen de la Venta
                        </h3>
                    </div>
                    
                    <div class="px-6 py-6">
                        <div class="space-y-4">
                            <!-- Sale Details -->
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Número de venta:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $venta->numero_venta }}</span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Fecha:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $venta->fecha_venta->format('d/m/Y') }}</span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Hora:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $venta->fecha_venta->format('H:i') }}</span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Método de pago:</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @switch($venta->metodo_pago)
                                            @case('efectivo')
                                                bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                                @break
                                            @case('tarjeta')
                                                bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                                @break
                                            @case('transferencia')
                                                bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300
                                                @break
                                            @case('credito')
                                                bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                @break
                                        @endswitch
                                    ">
                                        {{ ucfirst($venta->metodo_pago) }}
                                    </span>
                                </div>
                                
                                @if($venta->metodo_pago === 'credito' && $venta->dias_credito)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Días de crédito:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $venta->dias_credito }} días</span>
                                </div>
                                @endif
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Vendedor:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $venta->empleado->nombre ?? 'N/A' }}</span>
                                </div>
                            </div>

                            <hr class="border-gray-200 dark:border-gray-700">

                            <!-- Financial Summary -->
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Productos:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $venta->detalles->count() }} artículo(s)</span>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">S/ {{ number_format($venta->subtotal, 2) }}</span>
                                </div>
                                
                                @if($venta->descuento_total > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Descuento:</span>
                                    <span class="font-medium text-red-600 dark:text-red-400">-S/ {{ number_format($venta->descuento_total, 2) }}</span>
                                </div>
                                @endif
                                
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">IGV (18%):</span>
                                    <span class="font-medium text-gray-900 dark:text-white">S/ {{ number_format($venta->igv, 2) }}</span>
                                </div>
                            </div>

                            <hr class="border-gray-200 dark:border-gray-700">

                            <!-- Total -->
                            <div class="flex justify-between">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">Total:</span>
                                <span class="text-2xl font-bold text-gestion-600">S/ {{ number_format($venta->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Acciones Rápidas
                        </h3>
                    </div>
                    
                    <div class="px-6 py-6 space-y-3">
                        <!-- Print Actions -->
                        <div class="space-y-2">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Impresión</h4>
                            <div class="space-y-2">
                                <a href="{{ route('ventas.invoice', $venta) }}" 
                                   target="_blank"
                                   class="w-full flex items-center px-3 py-2 text-sm text-purple-700 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-700 dark:hover:bg-purple-900/40">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Imprimir Factura
                                </a>
                                
                                <a href="{{ route('ventas.receipt', $venta) }}" 
                                   target="_blank"
                                   class="w-full flex items-center px-3 py-2 text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Imprimir Recibo
                                </a>
                            </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <!-- Management Actions -->
                        @can('ventas.editar')
                        @if($venta->estado !== 'cancelado')
                        <div class="space-y-2">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Gestión</h4>
                            <div class="space-y-2">
                                <a href="{{ route('ventas.edit', $venta) }}" 
                                   class="w-full flex items-center px-3 py-2 text-sm text-gestion-700 bg-gestion-50 border border-gestion-200 rounded-lg hover:bg-gestion-100 dark:bg-gestion-900/20 dark:text-gestion-300 dark:border-gestion-700 dark:hover:bg-gestion-900/40">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar Venta
                                </a>

                                @if($venta->estado === 'completado')
                                <button @click="showRefundModal = true"
                                        class="w-full flex items-center px-3 py-2 text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-300 dark:border-yellow-700 dark:hover:bg-yellow-900/40">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                                    </svg>
                                    Procesar Devolución
                                </button>
                                @endif
                            </div>
                        </div>
                        @endif
                        @endcan

                        <hr class="border-gray-200 dark:border-gray-700">

                        <!-- Other Actions -->
                        <div class="space-y-2">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Otras Acciones</h4>
                            <div class="space-y-2">
                                @can('ventas.crear')
                                <a href="{{ route('ventas.create') }}?duplicate={{ $venta->id }}" 
                                   class="w-full flex items-center px-3 py-2 text-sm text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-700 dark:hover:bg-blue-900/40">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Duplicar Venta
                                </a>
                                @endcan

                                <button @click="sendEmailInvoice" 
                                        class="w-full flex items-center px-3 py-2 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 dark:bg-green-900/20 dark:text-green-300 dark:border-green-700 dark:hover:bg-green-900/40">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Enviar por Email
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Sales -->
                @if($venta->cliente->ventas->where('id', '!=', $venta->id)->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Otras Ventas del Cliente
                        </h3>
                    </div>
                    
                    <div class="px-6 py-6">
                        <div class="space-y-3">
                            @foreach($venta->cliente->ventas->where('id', '!=', $venta->id)->take(5) as $otraVenta)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-gestion-400 to-gestion-600 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $otraVenta->numero_venta }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $otraVenta->fecha_venta->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">S/ {{ number_format($otraVenta->total, 2) }}</div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @switch($otraVenta->estado)
                                            @case('completado')
                                                bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                                @break
                                            @case('pendiente')
                                                bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                @break
                                            @case('cancelado')
                                                bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                                @break
                                            @default
                                                bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                        @endswitch
                                    ">
                                        {{ ucfirst($otraVenta->estado) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                            
                            @if($venta->cliente->ventas->where('id', '!=', $venta->id)->count() > 5)
                            <div class="text-center pt-2">
                                <a href="{{ route('clientes.show', $venta->cliente) }}" 
                                   class="text-sm text-gestion-600 hover:text-gestion-700 font-medium">
                                    Ver todas las ventas ({{ $venta->cliente->ventas->count() }}) →
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Statistics -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Estadísticas del Cliente
                        </h3>
                    </div>
                    
                    <div class="px-6 py-6">
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Total de ventas:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $venta->cliente->ventas->count() }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Monto total:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    S/ {{ number_format($venta->cliente->ventas->sum('total'), 2) }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Promedio por venta:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    S/ {{ number_format($venta->cliente->ventas->avg('total'), 2) }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Cliente desde:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $venta->cliente->created_at->format('M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Refund Modal -->
    <div x-show="showRefundModal" 
         x-transition.opacity 
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showRefundModal" 
                 x-transition.opacity
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="showRefundModal = false"></div>

            <!-- Modal panel -->
            <div x-show="showRefundModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            Procesar Devolución
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Venta #{{ $venta->numero_venta }} - S/ {{ number_format($venta->total, 2) }}
                        </p>
                    </div>
                </div>

                <!-- Refund Form -->
                <form @submit.prevent="processRefund" class="mt-6">
                    <div class="space-y-4">
                        <!-- Refund Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Tipo de Devolución
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="refund_type" 
                                           value="full"
                                           x-model="refundForm.type"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-900 dark:text-white">Devolución total</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="refund_type" 
                                           value="partial"
                                           x-model="refundForm.type"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-900 dark:text-white">Devolución parcial</span>
                                </label>
                            </div>
                        </div>

                        <!-- Refund Amount (for partial) -->
                        <div x-show="refundForm.type === 'partial'" x-transition>
                            <label for="refund_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Monto a Devolver
                            </label>
                            <div class="mt-1 relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">S/</span>
                                </div>
                                <input type="number" 
                                       id="refund_amount" 
                                       x-model="refundForm.amount"
                                       :max="{{ $venta->total }}"
                                       min="0.01"
                                       step="0.01"
                                       class="block w-full pl-7 pr-3 py-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Máximo: S/ {{ number_format($venta->total, 2) }}
                            </p>
                        </div>

                        <!-- Refund Reason -->
                        <div>
                            <label for="refund_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Motivo de la Devolución <span class="text-red-500">*</span>
                            </label>
                            <textarea id="refund_reason" 
                                      x-model="refundForm.reason"
                                      rows="3"
                                      required
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                      placeholder="Explica el motivo de la devolución..."></textarea>
                        </div>

                        <!-- Refund Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Método de Devolución
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="refund_method" 
                                           value="cash"
                                           x-model="refundForm.method"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-900 dark:text-white">Efectivo</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="refund_method" 
                                           value="transfer"
                                           x-model="refundForm.method"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-900 dark:text-white">Transferencia</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="refund_method" 
                                           value="credit"
                                           x-model="refundForm.method"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-900 dark:text-white">Crédito a cuenta</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" 
                                @click="showRefundModal = false; resetRefundForm()"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                            Cancelar
                        </button>
                        <button type="submit" 
                                :disabled="refundLoading || !refundForm.reason || !refundForm.method"
                                :class="(refundLoading || !refundForm.reason || !refundForm.method) ? 'opacity-50 cursor-not-allowed' : ''"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            <svg x-show="refundLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="refundLoading ? 'Procesando...' : 'Procesar Devolución'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ventaShow(ventaData) {
    return {
        showRefundModal: false,
        refundLoading: false,
        
        refundForm: {
            type: 'full',
            amount: ventaData.total,
            reason: '',
            method: 'cash'
        },

        init() {
            // Initialize component
            console.log('Venta show initialized:', ventaData);
        },

        exportSale(format) {
            const url = `{{ route('ventas.show', $venta) }}/export/${format}`;
            window.open(url, '_blank');
        },

        async sendEmailInvoice() {
            try {
                const response = await fetch('{{ route("ventas.email", $venta) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Factura enviada por email exitosamente');
                } else {
                    alert(data.message || 'Error al enviar el email');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al enviar el email');
            }
        },

        confirmDelete() {
            if (confirm('¿Estás seguro de que deseas eliminar esta venta? Esta acción no se puede deshacer.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("ventas.destroy", $venta) }}';
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        },

        resetRefundForm() {
            this.refundForm = {
                type: 'full',
                amount: {{ $venta->total }},
                reason: '',
                method: 'cash'
            };
        },

        async processRefund() {
            if (!this.refundForm.reason || !this.refundForm.method) {
                alert('Por favor completa todos los campos obligatorios');
                return;
            }

            if (this.refundForm.type === 'partial' && (!this.refundForm.amount || this.refundForm.amount <= 0)) {
                alert('Por favor ingresa un monto válido para la devolución');
                return;
            }

            this.refundLoading = true;

            try {
                const formData = {
                    ...this.refundForm,
                    amount: this.refundForm.type === 'full' ? {{ $venta->total }} : this.refundForm.amount
                };

                const response = await fetch('{{ route("ventas.refund", $venta) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Devolución procesada exitosamente');
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al procesar la devolución');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al procesar la devolución');
            } finally {
                this.refundLoading = false;
            }
        }
    };
}
</script>
@endpush