{{-- resources/views/modules/ventas/edit.blade.php - PARTE 1 --}}
@extends('layouts.app')

@section('title', 'Editar Venta #' . $venta->numero_venta)

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8" x-data="ventaEdit({{ $venta->toJson() }})">
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
                                <a href="{{ route('ventas.show', $venta) }}" class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 md:ml-2">{{ $venta->numero_venta }}</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-800 dark:text-gray-200 md:ml-2">Editar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                
                <div class="mt-2 flex items-center space-x-4">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Venta</h1>
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
                            @default
                                bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                        @endswitch
                    ">
                        {{ ucfirst($venta->estado) }}
                    </span>
                </div>
                
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Venta #{{ $venta->numero_venta }} - {{ $venta->fecha_venta->format('d/m/Y H:i') }}
                </p>
            </div>
            
            <div class="flex space-x-3">
                <a href="{{ route('ventas.show', $venta) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Ver Venta
                </a>
                
                <a href="{{ route('ventas.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver al Listado
                </a>
            </div>
        </div>
    </div>

    <!-- Warning for completed sales -->
    @if($venta->estado === 'completado')
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
                        Venta Completada
                    </h3>
                    <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>Esta venta ya está completada. Los cambios deben ser cuidadosamente revisados ya que pueden afectar el inventario y los reportes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Form -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form @submit.prevent="submitForm" class="space-y-8" x-ref="form">
            @csrf
            @method('PUT')
            
            <!-- Customer Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Información del Cliente
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Cliente asignado a esta venta
                    </p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Cliente Selector -->
                        <div class="col-span-1">
                            <label for="cliente_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cliente <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select id="cliente_id" 
                                        name="cliente_id" 
                                        x-model="form.cliente_id"
                                        @change="selectCustomer"
                                        :disabled="form.estado === 'completado'"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="errors.cliente_id ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''">
                                    <option value="">Seleccionar cliente...</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ $cliente->id == $venta->cliente_id ? 'selected' : '' }}>
                                            {{ $cliente->nombre }} - {{ $cliente->documento }}
                                        </option>
                                    @endforeach
                                </select>
                                <div x-show="errors.cliente_id" class="mt-1 text-sm text-red-600" x-text="errors.cliente_id"></div>
                            </div>
                        </div>

                        <!-- Customer Details -->
                        <div class="col-span-1">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Datos del Cliente</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Nombre:</span>
                                        <span class="text-gray-900 dark:text-white">{{ $venta->cliente->nombre }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Documento:</span>
                                        <span class="text-gray-900 dark:text-white">{{ $venta->cliente->documento }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Teléfono:</span>
                                        <span class="text-gray-900 dark:text-white">{{ $venta->cliente->telefono ?: 'No registrado' }}</span>
                                    </div>
                                    @if($venta->cliente->email)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Email:</span>
                                        <span class="text-gray-900 dark:text-white">{{ $venta->cliente->email }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Products Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Productos de la Venta
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Modifica los productos de la venta
                            </p>
                        </div>
                        
                        @if($venta->estado !== 'completado')
                        <button type="button" 
                                @click="showAddProductModal = true"
                                class="inline-flex items-center px-4 py-2 bg-gestion-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-gestion-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Agregar Producto
                        </button>
                        @endif
                    </div>
                </div>
                
                <div class="px-6 py-6">
                    <!-- Products Table -->
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
                                    @if($venta->estado !== 'completado')
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(item, index) in form.items" :key="item.id || index">
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
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="item.nombre"></div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400" x-text="item.codigo"></div>
                                                    <div x-show="item.id" class="text-xs text-gray-400">
                                                        Stock disponible: <span x-text="item.stock_disponible"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div x-show="form.estado !== 'completado'">
                                                <input type="number" 
                                                       :value="item.precio_unitario"
                                                       @input="updateItemPrice(index, $event.target.value)"
                                                       min="0.01" 
                                                       step="0.01"
                                                       class="w-24 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 text-sm">
                                            </div>
                                            <div x-show="form.estado === 'completado'" class="text-sm font-medium text-gray-900 dark:text-white">
                                                S/ <span x-text="parseFloat(item.precio_unitario).toFixed(2)"></span>
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div x-show="form.estado !== 'completado'">
                                                <input type="number" 
                                                       :value="item.cantidad"
                                                       @input="updateItemQuantity(index, $event.target.value)"
                                                       min="1" 
                                                       :max="item.stock_disponible + item.cantidad_original"
                                                       class="w-20 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 text-sm">
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Máx: <span x-text="(item.stock_disponible || 0) + (item.cantidad_original || 0)"></span>
                                                </div>
                                            </div>
                                            <div x-show="form.estado === 'completado'" class="text-sm font-medium text-gray-900 dark:text-white" x-text="item.cantidad"></div>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div x-show="form.estado !== 'completado'" class="flex items-center space-x-2">
                                                <input type="number" 
                                                       :value="item.descuento"
                                                       @input="updateItemDiscount(index, $event.target.value)"
                                                       min="0" 
                                                       max="100"
                                                       step="0.01"
                                                       class="w-16 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 text-sm">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">%</span>
                                            </div>
                                            <div x-show="form.estado === 'completado'" class="text-sm font-medium text-gray-900 dark:text-white">
                                                <span x-text="item.descuento"></span>%
                                            </div>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                S/ <span x-text="calculateItemSubtotal(item).toFixed(2)"></span>
                                            </div>
                                            <div x-show="item.descuento > 0" class="text-xs text-green-600 dark:text-green-400">
                                                Ahorro: S/ <span x-text="(item.cantidad * item.precio_unitario * (item.descuento / 100)).toFixed(2)"></span>
                                            </div>
                                        </td>
                                        
                                        @if($venta->estado !== 'completado')
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <!-- Save individual item changes -->
                                                <button type="button" 
                                                        @click="saveItemChanges(index)"
                                                        x-show="item.hasChanges"
                                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                                        title="Guardar cambios">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                                
                                                <!-- Remove item -->
                                                <button type="button" 
                                                        @click="removeItem(index)"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                        title="Eliminar producto">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Changes Summary -->
                    <div x-show="hasChanges" class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                    Cambios Detectados
                                </h3>
                                <div class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                    <p>Se han detectado cambios en los productos. Asegúrate de guardar la venta para aplicar los cambios.</p>
                                </div>
                                <div class="mt-3 flex space-x-3">
                                    <button @click="calculateTotalChanges" 
                                            class="text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                        Recalcular Totales
                                    </button>
                                    <button @click="resetChanges" 
                                            class="text-sm bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700">
                                        Deshacer Cambios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Payment and Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Payment Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Información de Pago
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Método y condiciones de pago
                        </p>
                    </div>
                    
                    <div class="px-6 py-6 space-y-6">
                        <!-- Sale Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Estado de la Venta
                            </label>
                            <div class="grid grid-cols-1 gap-3">
                                <label class="relative flex items-center p-3 cursor-pointer border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                       :class="form.estado === 'pendiente' ? 'border-gestion-500 bg-gestion-50 dark:bg-gestion-900/20' : ''">
                                    <input type="radio" 
                                           name="estado" 
                                           value="pendiente"
                                           x-model="form.estado"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
                                    <div class="ml-3 flex items-center">
                                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Pendiente</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">La venta está en proceso</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-3 cursor-pointer border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                       :class="form.estado === 'completado' ? 'border-gestion-500 bg-gestion-50 dark:bg-gestion-900/20' : ''">
                                    <input type="radio" 
                                           name="estado" 
                                           value="completado"
                                           x-model="form.estado"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
                                    <div class="ml-3 flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Completado</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Venta finalizada y pagada</div>
                                        </div>
                                    </div>
                                </label>

                                @can('ventas.cancelar')
                                <label class="relative flex items-center p-3 cursor-pointer border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                       :class="form.estado === 'cancelado' ? 'border-gestion-500 bg-gestion-50 dark:bg-gestion-900/20' : ''">
                                    <input type="radio" 
                                           name="estado" 
                                           value="cancelado"
                                           x-model="form.estado"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
                                    <div class="ml-3 flex items-center">
                                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Cancelado</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Venta cancelada</div>
                                        </div>
                                    </div>
                                </label>
                                @endcan
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Método de Pago <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 gap-3">
                                <label class="relative flex items-center p-3 cursor-pointer border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                       :class="form.metodo_pago === 'efectivo' ? 'border-gestion-500 bg-gestion-50 dark:bg-gestion-900/20' : ''">
                                    <input type="radio" 
                                           name="metodo_pago" 
                                           value="efectivo"
                                           x-model="form.metodo_pago"
                                           :disabled="form.estado === 'completado'"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 disabled:opacity-50">
                                    <div class="ml-3 flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Efectivo</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Pago inmediato en efectivo</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-3 cursor-pointer border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                       :class="form.metodo_pago === 'tarjeta' ? 'border-gestion-500 bg-gestion-50 dark:bg-gestion-900/20' : ''">
                                    <input type="radio" 
                                           name="metodo_pago" 
                                           value="tarjeta"
                                           x-model="form.metodo_pago"
                                           :disabled="form.estado === 'completado'"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 disabled:opacity-50">
                                    <div class="ml-3 flex items-center">
                                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Tarjeta</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Débito o crédito</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-3 cursor-pointer border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                       :class="form.metodo_pago === 'transferencia' ? 'border-gestion-500 bg-gestion-50 dark:bg-gestion-900/20' : ''">
                                    <input type="radio" 
                                           name="metodo_pago" 
                                           value="transferencia"
                                           x-model="form.metodo_pago"
                                           :disabled="form.estado === 'completado'"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 disabled:opacity-50">
                                    <div class="ml-3 flex items-center">
                                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Transferencia</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Transferencia bancaria</div>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-3 cursor-pointer border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                       :class="form.metodo_pago === 'credito' ? 'border-gestion-500 bg-gestion-50 dark:bg-gestion-900/20' : ''">
                                    <input type="radio" 
                                           name="metodo_pago" 
                                           value="credito"
                                           x-model="form.metodo_pago"
                                           :disabled="form.estado === 'completado'"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 disabled:opacity-50">
                                    <div class="ml-3 flex items-center">
                                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Crédito</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Pago a plazo</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div x-show="errors.metodo_pago" class="mt-1 text-sm text-red-600" x-text="errors.metodo_pago"></div>
                        </div>

                        <!-- Credit Terms (only for credit) -->
                        <div x-show="form.metodo_pago === 'credito'" x-transition class="space-y-4">
                            <div>
                                <label for="dias_credito" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Días de Crédito
                                </label>
                                <select id="dias_credito" 
                                        name="dias_credito" 
                                        x-model="form.dias_credito"
                                        :disabled="form.estado === 'completado'"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 disabled:opacity-50">
                                    <option value="7">7 días</option>
                                    <option value="15">15 días</option>
                                    <option value="30">30 días</option>
                                    <option value="45">45 días</option>
                                    <option value="60">60 días</option>
                                    <option value="90">90 días</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="observaciones_credito" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Observaciones del Crédito
                                </label>
                                <textarea id="observaciones_credito" 
                                          name="observaciones_credito" 
                                          x-model="form.observaciones_credito"
                                          :disabled="form.estado === 'completado'"
                                          rows="3"
                                          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 disabled:opacity-50"
                                          placeholder="Condiciones adicionales del crédito..."></textarea>
                            </div>

                            @if($venta->metodo_pago === 'credito' && $venta->fecha_vencimiento)
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div>
                                        <div class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                            Fecha de Vencimiento Original
                                        </div>
                                        <div class="text-xs text-yellow-600 dark:text-yellow-400">
                                            {{ $venta->fecha_vencimiento->format('d/m/Y') }}
                                            @if($venta->fecha_vencimiento->isPast())
                                                <span class="text-red-600 font-semibold">(VENCIDO)</span>
                                            @else
                                                ({{ $venta->fecha_vencimiento->diffForHumans() }})
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notas" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notas de la Venta
                            </label>
                            <textarea id="notas" 
                                      name="notas" 
                                      x-model="form.notas"
                                      rows="3"
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                      placeholder="Información adicional sobre la venta..."></textarea>
                        </div>

                        <!-- Reason for changes (if editing completed sale) -->
                        <div x-show="originalEstado === 'completado' && hasChanges">
                            <label for="razon_cambio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Razón del Cambio <span class="text-red-500">*</span>
                            </label>
                            <textarea id="razon_cambio" 
                                      name="razon_cambio" 
                                      x-model="form.razon_cambio"
                                      rows="3"
                                      required
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                      placeholder="Explica la razón por la cual se modifica esta venta completada..."></textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Se requiere justificación para modificar una venta completada
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sale Summary -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Resumen de la Venta
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Detalles de cálculo y totales
                        </p>
                    </div>
                    
                    <div class="px-6 py-6">
                        <div class="space-y-4">
                            <!-- Original vs Current Comparison -->
                            <div x-show="hasChanges" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-4">
                                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">Comparación de Cambios</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-blue-700 dark:text-blue-300">Total Original:</span>
                                        <span class="font-medium text-blue-900 dark:text-blue-100">S/ {{ number_format($venta->total, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-700 dark:text-blue-300">Total Actual:</span>
                                        <span class="font-medium text-blue-900 dark:text-blue-100">S/ <span x-text="calculos.total.toFixed(2)"></span></span>
                                    </div>
                                    <div class="flex justify-between border-t border-blue-200 dark:border-blue-700 pt-2">
                                        <span class="text-blue-700 dark:text-blue-300">Diferencia:</span>
                                        <span class="font-semibold" 
                                              :class="calculos.total - {{ $venta->total }} >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                            <span x-text="(calculos.total - {{ $venta->total }}) >= 0 ? '+' : ''"></span>S/ <span x-text="Math.abs(calculos.total - {{ $venta->total }}).toFixed(2)"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Count -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Productos:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="form.items.length + ' artículo(s)'"></span>
                            </div>

                            <!-- Subtotal -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Subtotal:</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    S/ <span x-text="calculos.subtotal.toFixed(2)"></span>
                                </span>
                            </div>

                            <!-- Discount -->
                            <div class="flex justify-between items-center" x-show="calculos.descuento_total > 0">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Descuento total:</span>
                                <span class="text-sm font-medium text-red-600">
                                    -S/ <span x-text="calculos.descuento_total.toFixed(2)"></span>
                                </span>
                            </div>

                            <!-- Tax -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">IGV (18%):</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    S/ <span x-text="calculos.igv.toFixed(2)"></span>
                                </span>
                            </div>

                            <hr class="border-gray-200 dark:border-gray-700">

                            <!-- Total -->
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">Total:</span>
                                <span class="text-2xl font-bold text-gestion-600">
                                    S/ <span x-text="calculos.total.toFixed(2)"></span>
                                </span>
                            </div>

                            <!-- Payment Status -->
                            <div x-show="form.metodo_pago === 'credito'" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 mt-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <div class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Venta a Crédito</div>
                                        <div class="text-xs text-yellow-600 dark:text-yellow-400" x-show="form.dias_credito">
                                            Vencimiento en <span x-text="form.dias_credito"></span> días
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sale History -->
                            @if($venta->created_at != $venta->updated_at)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mt-4">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Historial</h4>
                                <div class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                                    <div>Creada: {{ $venta->created_at->format('d/m/Y H:i') }}</div>
                                    <div>Última modificación: {{ $venta->updated_at->format('d/m/Y H:i') }}</div>
                                    @if($venta->empleado)
                                    <div>Vendedor: {{ $venta->empleado->nombre }}</div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
{{-- resources/views/modules/ventas/edit.blade.php - PARTE 4 --}}

            <!-- Action Buttons -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex space-x-3">
                    <a href="{{ route('ventas.show', $venta) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Ver Venta
                    </a>

                    <button type="button" 
                            @click="resetChanges"
                            x-show="hasChanges"
                            class="inline-flex items-center px-4 py-2 border border-yellow-300 dark:border-yellow-600 shadow-sm text-sm font-medium rounded-lg text-yellow-700 dark:text-yellow-300 bg-yellow-50 dark:bg-yellow-900/20 hover:bg-yellow-100 dark:hover:bg-yellow-900/40 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Deshacer Cambios
                    </button>
                </div>

                <div class="flex space-x-3">
                    <a href="{{ route('ventas.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Cancelar
                    </a>
                    
                    <button type="submit" 
                            :disabled="loading || !hasChanges || (originalEstado === 'completado' && hasChanges && !form.razon_cambio)"
                            :class="(loading || !hasChanges || (originalEstado === 'completado' && hasChanges && !form.razon_cambio)) ? 'opacity-50 cursor-not-allowed' : ''"
                            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-gestion-500 transition-colors">
                        <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="loading ? 'Guardando...' : 'Guardar Cambios'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modals -->
    @include('modules.ventas.partials.add-product-modal')
</div>
@endsection

@push('scripts')
<script>
function ventaEdit(ventaData) {
    return {
        loading: false,
        showAddProductModal: false,
        originalData: null,
        originalEstado: ventaData.estado,
        
        form: {
            cliente_id: ventaData.cliente_id,
            estado: ventaData.estado,
            metodo_pago: ventaData.metodo_pago,
            dias_credito: ventaData.dias_credito || 30,
            observaciones_credito: ventaData.observaciones_credito || '',
            notas: ventaData.notas || '',
            razon_cambio: '',
            items: []
        },

        errors: {},

        init() {
            this.loadVentaData(ventaData);
            this.originalData = JSON.parse(JSON.stringify(this.form));
        },

        loadVentaData(venta) {
            // Load existing sale items
            this.form.items = venta.detalles.map(detalle => ({
                id: detalle.id,
                producto_id: detalle.producto_id,
                nombre: detalle.producto.nombre,
                codigo: detalle.producto.codigo,
                precio_unitario: parseFloat(detalle.precio_unitario),
                cantidad: parseInt(detalle.cantidad),
                cantidad_original: parseInt(detalle.cantidad),
                descuento: parseFloat(detalle.descuento || 0),
                stock_disponible: detalle.producto.stock,
                hasChanges: false
            }));
        },

        get hasChanges() {
            if (!this.originalData) return false;
            
            // Check form changes
            const formChanged = JSON.stringify(this.form) !== JSON.stringify(this.originalData);
            
            // Check individual item changes
            const itemsChanged = this.form.items.some(item => item.hasChanges);
            
            return formChanged || itemsChanged;
        },

        get calculos() {
            let subtotal = 0;
            let descuento_total = 0;
            
            this.form.items.forEach(item => {
                const itemSubtotal = item.cantidad * item.precio_unitario;
                const itemDescuento = itemSubtotal * (item.descuento / 100);
                subtotal += itemSubtotal;
                descuento_total += itemDescuento;
            });
            
            const subtotal_con_descuento = subtotal - descuento_total;
            const igv = subtotal_con_descuento * 0.18;
            const total = subtotal_con_descuento + igv;
            
            return {
                subtotal,
                descuento_total,
                igv,
                total
            };
        },

        selectCustomer(event) {
            const clienteId = event.target.value;
            this.form.cliente_id = clienteId;
        },

        calculateItemSubtotal(item) {
            const subtotal = item.cantidad * item.precio_unitario;
            const descuento = subtotal * (item.descuento / 100);
            return subtotal - descuento;
        },

        updateItemPrice(index, price) {
            const newPrice = parseFloat(price) || 0;
            this.form.items[index].precio_unitario = newPrice;
            this.form.items[index].hasChanges = true;
        },

        updateItemQuantity(index, quantity) {
            const qty = parseInt(quantity) || 1;
            const item = this.form.items[index];
            const maxStock = (item.stock_disponible || 0) + (item.cantidad_original || 0);
            
            if (qty > maxStock) {
                alert(`No hay suficiente stock. Máximo disponible: ${maxStock}`);
                this.form.items[index].cantidad = maxStock;
                return;
            }
            
            this.form.items[index].cantidad = qty;
            this.form.items[index].hasChanges = true;
        },

        updateItemDiscount(index, discount) {
            const disc = parseFloat(discount) || 0;
            if (disc > 100) {
                alert('El descuento no puede ser mayor al 100%');
                this.form.items[index].descuento = 100;
                return;
            }
            this.form.items[index].descuento = disc;
            this.form.items[index].hasChanges = true;
        },

        removeItem(index) {
            if (confirm('¿Estás seguro de que deseas eliminar este producto de la venta?')) {
                this.form.items.splice(index, 1);
            }
        },

        saveItemChanges(index) {
            // Mark item as saved
            this.form.items[index].hasChanges = false;
            this.calculateTotalChanges();
        },

        calculateTotalChanges() {
            // Recalculate all totals
            console.log('Recalculating totals...');
        },

        resetChanges() {
            if (confirm('¿Estás seguro de que deseas deshacer todos los cambios?')) {
                this.form = JSON.parse(JSON.stringify(this.originalData));
                this.loadVentaData(ventaData);
            }
        },

        validateForm() {
            this.errors = {};
            let isValid = true;

            // Validate customer
            if (!this.form.cliente_id) {
                this.errors.cliente_id = 'Debe seleccionar un cliente';
                isValid = false;
            }

            // Validate payment method
            if (!this.form.metodo_pago) {
                this.errors.metodo_pago = 'Debe seleccionar un método de pago';
                isValid = false;
            }

            // Validate items
            if (this.form.items.length === 0) {
                alert('Debe tener al menos un producto en la venta');
                isValid = false;
            }

            // Validate reason for changes if editing completed sale
            if (this.originalEstado === 'completado' && this.hasChanges && !this.form.razon_cambio) {
                alert('Debe proporcionar una razón para modificar una venta completada');
                isValid = false;
            }

            // Validate each item
            this.form.items.forEach((item, index) => {
                if (item.cantidad <= 0) {
                    alert(`La cantidad del producto "${item.nombre}" debe ser mayor a 0`);
                    isValid = false;
                }
                
                const maxStock = (item.stock_disponible || 0) + (item.cantidad_original || 0);
                if (item.cantidad > maxStock) {
                    alert(`No hay suficiente stock para "${item.nombre}". Disponible: ${maxStock}`);
                    isValid = false;
                }

                if (item.precio_unitario <= 0) {
                    alert(`El precio del producto "${item.nombre}" debe ser mayor a 0`);
                    isValid = false;
                }
            });

            return isValid;
        },

        async submitForm() {
            if (!this.validateForm()) {
                return;
            }

            if (!this.hasChanges) {
                alert('No hay cambios para guardar');
                return;
            }

            this.loading = true;
            this.errors = {};

            try {
                const formData = {
                    ...this.form,
                    total: this.calculos.total,
                    subtotal: this.calculos.subtotal - this.calculos.descuento_total,
                    igv: this.calculos.igv,
                    descuento_total: this.calculos.descuento_total,
                    _method: 'PUT'
                };

                const response = await fetch('{{ route("ventas.update", $venta) }}', {
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
                    // Success
                    alert('Venta actualizada exitosamente');
                    
                    // Redirect to show page
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = '{{ route("ventas.show", $venta) }}';
                    }
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        this.errors = data.errors;
                    }
                    
                    // Show general error message
                    if (data.message) {
                        alert(data.message);
                    } else {
                        alert('Ocurrió un error al actualizar la venta. Por favor, revise los datos ingresados.');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ocurrió un error de conexión. Por favor, inténtelo nuevamente.');
            } finally {
                this.loading = false;
            }
        },

        // Helper methods for modals
        openAddProductModal() {
            this.showAddProductModal = true;
        },

        closeAddProductModal() {
            this.showAddProductModal = false;
        }
    };
}
</script>
@endpush