{{-- resources/views/modules/ventas/create.blade.php - PARTE 1 --}}
@extends('layouts.app')

@section('title', 'Nueva Venta')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8" x-data="ventaCreate()">
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
                                <span class="ml-1 text-gray-800 dark:text-gray-200 md:ml-2">Nueva Venta</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">Nueva Venta</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Registra una nueva venta al sistema</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('ventas.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form @submit.prevent="submitForm" class="space-y-8" x-ref="form">
            @csrf
            
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
                        Selecciona el cliente para esta venta
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
                                <div class="flex">
                                    <select id="cliente_id" 
                                            name="cliente_id" 
                                            x-model="form.cliente_id"
                                            @change="selectCustomer"
                                            class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-l-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                            :class="errors.cliente_id ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''">
                                        <option value="">Seleccionar cliente...</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}">{{ $cliente->nombre }} - {{ $cliente->documento }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" 
                                            @click="showNewCustomerModal = true"
                                            class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 dark:border-gray-600 rounded-r-lg bg-gray-50 dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        <span class="ml-1 hidden sm:block">Nuevo</span>
                                    </button>
                                </div>
                                <div x-show="errors.cliente_id" class="mt-1 text-sm text-red-600" x-text="errors.cliente_id"></div>
                            </div>
                        </div>

                        <!-- Customer Details -->
                        <div class="col-span-1" x-show="selectedCustomer">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Datos del Cliente</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Nombre:</span>
                                        <span class="text-gray-900 dark:text-white" x-text="selectedCustomer?.nombre"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Documento:</span>
                                        <span class="text-gray-900 dark:text-white" x-text="selectedCustomer?.documento"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Teléfono:</span>
                                        <span class="text-gray-900 dark:text-white" x-text="selectedCustomer?.telefono || 'No registrado'"></span>
                                    </div>
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
                                Agrega los productos a vender
                            </p>
                        </div>
                        <button type="button" 
                                @click="showAddProductModal = true"
                                class="inline-flex items-center px-4 py-2 bg-gestion-600 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-gestion-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Agregar Producto
                        </button>
                    </div>
                </div>
                
                <div class="px-6 py-6">
                    <!-- Products Table -->
                    <div x-show="form.items.length > 0" class="overflow-x-auto">
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(item, index) in form.items" :key="index">
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
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                S/ <span x-text="parseFloat(item.precio_unitario).toFixed(2)"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" 
                                                   :value="item.cantidad"
                                                   @input="updateItemQuantity(index, $event.target.value)"
                                                   min="1" 
                                                   :max="item.stock_disponible"
                                                   class="w-20 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 text-sm">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <input type="number" 
                                                       :value="item.descuento"
                                                       @input="updateItemDiscount(index, $event.target.value)"
                                                       min="0" 
                                                       max="100"
                                                       step="0.01"
                                                       class="w-16 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 text-sm">
                                                <span class="text-sm text-gray-500 dark:text-gray-400">%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                S/ <span x-text="calculateItemSubtotal(item).toFixed(2)"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button type="button" 
                                                    @click="removeItem(index)"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div x-show="form.items.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay productos</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Agrega productos para comenzar la venta
                        </p>
                        <div class="mt-6">
                            <button type="button" 
                                    @click="showAddProductModal = true"
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Agregar Producto
                            </button>
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
                            Configura el método y condiciones de pago
                        </p>
                    </div>
                    
                    <div class="px-6 py-6 space-y-6">
                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Método de Pago <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 gap-3">
                                <label class="relative flex items-center p-3 cursor-pointer border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <input type="radio" 
                                           name="metodo_pago" 
                                           value="efectivo"
                                           x-model="form.metodo_pago"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
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

                                <label class="relative flex items-center p-3 cursor-pointer border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <input type="radio" 
                                           name="metodo_pago" 
                                           value="tarjeta"
                                           x-model="form.metodo_pago"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
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

                                <label class="relative flex items-center p-3 cursor-pointer border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <input type="radio" 
                                           name="metodo_pago" 
                                           value="transferencia"
                                           x-model="form.metodo_pago"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
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

                                <label class="relative flex items-center p-3 cursor-pointer border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <input type="radio" 
                                           name="metodo_pago" 
                                           value="credito"
                                           x-model="form.metodo_pago"
                                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300">
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
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
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
                                          rows="3"
                                          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                          placeholder="Condiciones adicionales del crédito..."></textarea>
                            </div>
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

                            <!-- Payment Status for Credit -->
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
                        </div>
                    </div>
                </div>
            </div>
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="button" 
                        @click="$refs.form.reset(); window.location.href = '{{ route('ventas.index') }}'"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancelar
                </button>
                
                <button type="submit" 
                        :disabled="loading || form.items.length === 0 || !form.cliente_id || !form.metodo_pago"
                        :class="loading || form.items.length === 0 || !form.cliente_id || !form.metodo_pago ? 'opacity-50 cursor-not-allowed' : ''"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-gestion-500 transition-colors">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-text="loading ? 'Procesando...' : 'Registrar Venta'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Modals -->
    @include('modules.ventas.partials.new-customer-modal')
    @include('modules.ventas.partials.add-product-modal')
</div>
@endsection

@push('scripts')
<script>
function ventaCreate() {
    return {
        loading: false,
        showNewCustomerModal: false,
        showAddProductModal: false,
        selectedCustomer: null,
        
        form: {
            cliente_id: '',
            metodo_pago: 'efectivo',
            dias_credito: 30,
            observaciones_credito: '',
            notas: '',
            items: []
        },

        errors: {},

        init() {
            // Initialize component
            this.loadInitialData();
        },

        loadInitialData() {
            // Load any initial data if needed
            console.log('Venta create form initialized');
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
            if (clienteId) {
                // Find customer in the options
                const select = event.target;
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const optionText = selectedOption.text;
                    const parts = optionText.split(' - ');
                    this.selectedCustomer = {
                        id: clienteId,
                        nombre: parts[0],
                        documento: parts[1] || 'N/A',
                        telefono: 'No registrado' // This could be loaded via API
                    };
                }
            } else {
                this.selectedCustomer = null;
            }
        },

        calculateItemSubtotal(item) {
            const subtotal = item.cantidad * item.precio_unitario;
            const descuento = subtotal * (item.descuento / 100);
            return subtotal - descuento;
        },

        updateItemQuantity(index, quantity) {
            const qty = parseInt(quantity) || 1;
            const maxStock = this.form.items[index].stock_disponible;
            
            if (qty > maxStock) {
                alert(`No hay suficiente stock. Máximo disponible: ${maxStock}`);
                this.form.items[index].cantidad = maxStock;
                return;
            }
            
            this.form.items[index].cantidad = qty;
        },

        updateItemDiscount(index, discount) {
            const disc = parseFloat(discount) || 0;
            if (disc > 100) {
                alert('El descuento no puede ser mayor al 100%');
                this.form.items[index].descuento = 100;
                return;
            }
            this.form.items[index].descuento = disc;
        },

        removeItem(index) {
            if (confirm('¿Estás seguro de que deseas eliminar este producto de la venta?')) {
                this.form.items.splice(index, 1);
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
                alert('Debe agregar al menos un producto a la venta');
                isValid = false;
            }

            // Validate each item
            this.form.items.forEach((item, index) => {
                if (item.cantidad <= 0) {
                    alert(`La cantidad del producto "${item.nombre}" debe ser mayor a 0`);
                    isValid = false;
                }
                if (item.cantidad > item.stock_disponible) {
                    alert(`No hay suficiente stock para "${item.nombre}". Disponible: ${item.stock_disponible}`);
                    isValid = false;
                }
            });

            return isValid;
        },

        async submitForm() {
            if (!this.validateForm()) {
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
                    descuento_total: this.calculos.descuento_total
                };

                const response = await fetch('{{ route("ventas.store") }}', {
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
                    alert('Venta registrada exitosamente');
                    
                    // Redirect to show page or index
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.venta && data.venta.id) {
                        window.location.href = `{{ route("ventas.show", ":id") }}`.replace(':id', data.venta.id);
                    } else {
                        window.location.href = '{{ route("ventas.index") }}';
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
                        alert('Ocurrió un error al procesar la venta. Por favor, revise los datos ingresados.');
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
        openNewCustomerModal() {
            this.showNewCustomerModal = true;
        },

        closeNewCustomerModal() {
            this.showNewCustomerModal = false;
        },

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