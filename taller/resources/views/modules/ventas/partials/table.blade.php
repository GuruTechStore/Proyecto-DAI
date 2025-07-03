{{-- resources/views/modules/ventas/partials/table.blade.php --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <!-- Table Header -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Lista de Ventas
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $ventas->total() }} venta(s) encontrada(s)
                </p>
            </div>
            
            <!-- Bulk Actions -->
            <div class="flex items-center space-x-3" x-data="{ selectedItems: [] }">
                <div x-show="selectedItems.length > 0" class="flex items-center space-x-2">
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        <span x-text="selectedItems.length"></span> seleccionada(s)
                    </span>
                    
                    @can('ventas.exportar')
                    <button @click="exportSelected('excel')"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 border border-green-200 rounded-lg hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors dark:bg-green-900 dark:text-green-300 dark:border-green-700">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Excel
                    </button>
                    @endcan
                    
                    @can('ventas.eliminar')
                    <button @click="deleteSelected()"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 border border-red-200 rounded-lg hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors dark:bg-red-900 dark:text-red-300 dark:border-red-700">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Eliminar
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="relative px-6 py-3">
                        <input type="checkbox" 
                               @change="toggleAll($event)"
                               class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="sortBy('numero_venta')">
                        <div class="flex items-center space-x-1">
                            <span>Número</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="sortBy('fecha_venta')">
                        <div class="flex items-center space-x-1">
                            <span>Fecha</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Cliente
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Productos
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" @click="sortBy('total')">
                        <div class="flex items-center space-x-1">
                            <span>Total</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Método Pago
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Vendedor
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Acciones</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($ventas as $venta)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" x-data="{ showDetails: false }">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" 
                               value="{{ $venta->id }}"
                               @change="toggleItem({{ $venta->id }}, $event)"
                               class="h-4 w-4 rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                    </td>
                    
                    <!-- Sale Number -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-gestion-400 to-gestion-600 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $venta->numero_venta }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    ID: {{ $venta->id }}
                                </div>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Date -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-white">
                            {{ $venta->fecha_venta->format('d/m/Y') }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $venta->fecha_venta->format('H:i') }}
                        </div>
                    </td>
                    
                    <!-- Customer -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                    <span class="text-xs font-medium text-white">
                                        {{ substr($venta->cliente->nombre, 0, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $venta->cliente->nombre }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $venta->cliente->documento }}
                                </div>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Products -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                {{ $venta->detalles->count() }} producto(s)
                            </span>
                            <button @click="showDetails = !showDetails" 
                                    class="ml-2 text-gestion-600 hover:text-gestion-700 focus:outline-none">
                                <svg class="w-4 h-4" :class="{ 'rotate-180': showDetails }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                    </td>
                    
                    <!-- Total -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                            S/ {{ number_format($venta->total, 2) }}
                        </div>
                        @if($venta->descuento_total > 0)
                        <div class="text-xs text-green-600 dark:text-green-400">
                            Desc: S/ {{ number_format($venta->descuento_total, 2) }}
                        </div>
                        @endif
                    </td>
                    
                    <!-- Payment Method -->
                    <td class="px-6 py-4 whitespace-nowrap">
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
                                @default
                                    bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                            @endswitch
                        ">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @switch($venta->metodo_pago)
                                    @case('efectivo')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        @break
                                    @case('tarjeta')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        @break
                                    @case('transferencia')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        @break
                                    @case('credito')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        @break
                                @endswitch
                            </svg>
                            {{ ucfirst($venta->metodo_pago) }}
                        </span>
                        
                        @if($venta->metodo_pago === 'credito' && $venta->fecha_vencimiento)
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Vence: {{ $venta->fecha_vencimiento->format('d/m/Y') }}
                        </div>
                        @endif
                    </td>
                    
                    <!-- Status -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
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
                    </td>
                    
                    <!-- Salesperson -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-white">
                            {{ $venta->empleado->nombre ?? 'N/A' }}
                        </div>
                        @if($venta->empleado)
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $venta->empleado->cargo }}
                        </div>
                        @endif
                    </td>
                    
                    <!-- Actions -->
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('ventas.show', $venta) }}" 
                               class="text-gestion-600 hover:text-gestion-700 focus:outline-none"
                               title="Ver detalle">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            
                            @can('ventas.editar')
                            @if($venta->estado !== 'cancelado')
                            <a href="{{ route('ventas.edit', $venta) }}" 
                               class="text-blue-600 hover:text-blue-700 focus:outline-none"
                               title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            @endif
                            @endcan
                            
                            <!-- Print Invoice -->
                            <a href="{{ route('ventas.invoice', $venta) }}" 
                               target="_blank"
                               class="text-purple-600 hover:text-purple-700 focus:outline-none"
                               title="Imprimir factura">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </a>
                            
                            <!-- More Actions Dropdown -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" 
                                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" 
                                     @click.away="open = false"
                                     x-transition
                                     class="absolute right-0 z-10 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700">
                                    <div class="py-1">
                                        <!-- Receipt -->
                                        <a href="{{ route('ventas.receipt', $venta) }}" 
                                           target="_blank"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Imprimir recibo
                                        </a>
                                        
                                        @can('ventas.editar')
                                        @if($venta->estado === 'completado' && $venta->estado !== 'devuelto')
                                        <!-- Refund -->
                                        <button @click="confirmRefund({{ $venta->id }})"
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
                                        <button @click="confirmDelete({{ $venta->id }})"
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
                        </div>
                    </td>
                </tr>
                
                <!-- Expandable Product Details -->
                <tr x-show="showDetails" x-transition class="bg-gray-50 dark:bg-gray-700">
                    <td colspan="10" class="px-6 py-4">
                        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 p-4">
                            <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Productos vendidos:</h5>
                            <div class="space-y-2">
                                @foreach($venta->detalles as $detalle)
                                <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-b-0">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-gestion-400 to-gestion-600 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $detalle->producto->nombre }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Código: {{ $detalle->producto->codigo }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ $detalle->cantidad }} x S/ {{ number_format($detalle->precio_unitario, 2) }}
                                        </div>
                                        @if($detalle->descuento > 0)
                                        <div class="text-xs text-green-600 dark:text-green-400">
                                            Desc: {{ $detalle->descuento }}%
                                        </div>
                                        @endif
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                            S/ {{ number_format($detalle->subtotal, 2) }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <!-- Empty State -->
                <tr>
                    <td colspan="10" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay ventas</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                No se encontraron ventas con los filtros aplicados.
                            </p>
                            @can('ventas.crear')
                            <div class="mt-6">
                                <a href="{{ route('ventas.create') }}"
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Nueva Venta
                                </a>
                            </div>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($ventas->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                @if($ventas->onFirstPage())
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-500 bg-white cursor-default">
                        Anterior
                    </span>
                @else
                    <a href="{{ $ventas->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                        Anterior
                    </a>
                @endif

                @if($ventas->hasMorePages())
                    <a href="{{ $ventas->nextPageUrl() }}" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                        Siguiente
                    </a>
                @else
                    <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-500 bg-white cursor-default">
                        Siguiente
                    </span>
                @endif
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Mostrando <span class="font-medium">{{ $ventas->firstItem() }}</span> a <span class="font-medium">{{ $ventas->lastItem() }}</span> de <span class="font-medium">{{ $ventas->total() }}</span> resultados
                    </p>
                </div>
                <div>
                    {{ $ventas->links() }}
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function ventasTable() {
    return {
        selectedItems: [],
        
        toggleAll(event) {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][value]');
            this.selectedItems = event.target.checked ? Array.from(checkboxes).map(cb => parseInt(cb.value)) : [];
            checkboxes.forEach(cb => cb.checked = event.target.checked);
        },
        
        toggleItem(id, event) {
            if (event.target.checked) {
                if (!this.selectedItems.includes(id)) {
                    this.selectedItems.push(id);
                }
            } else {
                this.selectedItems = this.selectedItems.filter(item => item !== id);
            }
        },
        
        exportSelected(format) {
            if (this.selectedItems.length === 0) {
                alert('Selecciona al menos una venta para exportar');
                return;
            }
            
            const params = new URLSearchParams();
            params.append('export', format);
            params.append('selected', this.selectedItems.join(','));
            
            const exportUrl = `{{ route('ventas.index') }}?${params.toString()}`;
            window.open(exportUrl, '_blank');
        },
        
        deleteSelected() {
            if (this.selectedItems.length === 0) {
                alert('Selecciona al menos una venta para eliminar');
                return;
            }
            
            if (confirm('¿Estás seguro de que deseas eliminar las ventas seleccionadas?')) {
                // Implement bulk delete
                console.log('Deleting:', this.selectedItems);
            }
        },
        
        confirmDelete(id) {
            if (confirm('¿Estás seguro de que deseas eliminar esta venta?')) {
                // Implement delete
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/ventas/${id}`;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        },
        
        confirmRefund(id) {
            if (confirm('¿Estás seguro de que deseas procesar la devolución de esta venta?')) {
                // Implement refund
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/ventas/${id}/refund`;
                form.innerHTML = `@csrf`;
                document.body.appendChild(form);
                form.submit();
            }
        },
        
        sortBy(field) {
            const url = new URL(window.location);
            const currentSort = url.searchParams.get('sort');
            const currentDirection = url.searchParams.get('direction') || 'asc';
            
            if (currentSort === field) {
                url.searchParams.set('direction', currentDirection === 'asc' ? 'desc' : 'asc');
            } else {
                url.searchParams.set('sort', field);
                url.searchParams.set('direction', 'asc');
            }
            
            window.location.href = url.toString();
        }
    };
}
</script>