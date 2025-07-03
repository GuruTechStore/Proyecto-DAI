@extends('layouts.app')

@section('title', 'Editar Producto')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8" x-data="productEditForm()">
    <!-- Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('productos.index') }}" 
                   class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a productos
                </a>
                <div class="text-sm text-gray-400">•</div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Producto</h1>
            </div>
            
            <div class="flex items-center space-x-3">
                @can('productos.ver')
                <a href="{{ route('productos.show', $producto) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Ver Producto
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form @submit.prevent="submitForm" class="space-y-8">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Información del Producto
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Información básica y detalles del producto
                    </p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Nombre -->
                        <div class="col-span-1">
                            <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nombre del Producto <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="nombre" 
                                       name="nombre" 
                                       x-model="form.nombre"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="Ingrese el nombre del producto">
                            </div>
                            <p x-show="errors.nombre" x-text="errors.nombre" class="mt-1 text-sm text-red-600"></p>
                        </div>
                        
                        <!-- Código -->
                        <div class="col-span-1">
                            <label for="codigo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Código del Producto <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="codigo" 
                                       name="codigo" 
                                       x-model="form.codigo"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="Código único del producto">
                            </div>
                            <p x-show="errors.codigo" x-text="errors.codigo" class="mt-1 text-sm text-red-600"></p>
                        </div>
                        
                        <!-- Categoría -->
                        <div class="col-span-1">
                            <label for="categoria_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Categoría <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <select id="categoria_id" 
                                        name="categoria_id" 
                                        x-model="form.categoria_id"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                                    <option value="">Seleccione una categoría</option>
                                    @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" {{ $producto->categoria_id == $categoria->id ? 'selected' : '' }}>
                                        {{ $categoria->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <p x-show="errors.categoria_id" x-text="errors.categoria_id" class="mt-1 text-sm text-red-600"></p>
                        </div>
                        
                        <!-- Marca -->
                        <div class="col-span-1">
                            <label for="marca" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Marca
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="marca" 
                                       name="marca" 
                                       x-model="form.marca"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="Marca del producto">
                            </div>
                            <p x-show="errors.marca" x-text="errors.marca" class="mt-1 text-sm text-red-600"></p>
                        </div>
                        
                        <!-- Modelo -->
                        <div class="col-span-1">
                            <label for="modelo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Modelo
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="modelo" 
                                       name="modelo" 
                                       x-model="form.modelo"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="Modelo del producto">
                            </div>
                            <p x-show="errors.modelo" x-text="errors.modelo" class="mt-1 text-sm text-red-600"></p>
                        </div>
                        
                        <!-- Estado -->
                        <div class="col-span-1">
                            <label for="activo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Estado del Producto
                            </label>
                            <div class="mt-1">
                                <select id="activo" 
                                        name="activo" 
                                        x-model="form.activo"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Descripción -->
                        <div class="col-span-2">
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Descripción
                            </label>
                            <div class="mt-1">
                                <textarea id="descripcion" 
                                          name="descripcion" 
                                          x-model="form.descripcion"
                                          rows="4"
                                          class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                          placeholder="Descripción detallada del producto..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing and Stock -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        Precios e Inventario
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Gestión de precios y control de stock
                    </p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <!-- Precio de Compra -->
                        <div class="col-span-1">
                            <label for="precio_compra" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Precio de Compra <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400">S/</span>
                                <input type="number" 
                                       id="precio_compra" 
                                       name="precio_compra" 
                                       x-model="form.precio_compra"
                                       step="0.01"
                                       min="0"
                                       class="block w-full pl-8 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="0.00">
                            </div>
                            <p x-show="errors.precio_compra" x-text="errors.precio_compra" class="mt-1 text-sm text-red-600"></p>
                        </div>
                        
                        <!-- Precio de Venta -->
                        <div class="col-span-1">
                            <label for="precio_venta" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Precio de Venta <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400">S/</span>
                                <input type="number" 
                                       id="precio_venta" 
                                       name="precio_venta" 
                                       x-model="form.precio_venta"
                                       step="0.01"
                                       min="0"
                                       class="block w-full pl-8 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="0.00">
                            </div>
                            <p x-show="errors.precio_venta" x-text="errors.precio_venta" class="mt-1 text-sm text-red-600"></p>
                        </div>
                        
                        <!-- Margen de Ganancia -->
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Margen de Ganancia
                            </label>
                            <div class="mt-1">
                                <div class="flex items-center px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg">
                                    <span x-text="calculateMargin()" class="text-sm font-medium text-gray-900 dark:text-white"></span>
                                    <span class="ml-1 text-sm text-gray-500 dark:text-gray-400">%</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stock Actual -->
                        <div class="col-span-1">
                            <label for="stock_actual" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Stock Actual <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="number" 
                                       id="stock_actual" 
                                       name="stock_actual" 
                                       x-model="form.stock_actual"
                                       min="0"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="0">
                            </div>
                            <p x-show="errors.stock_actual" x-text="errors.stock_actual" class="mt-1 text-sm text-red-600"></p>
                        </div>
                        
                        <!-- Stock Mínimo -->
                        <div class="col-span-1">
                            <label for="stock_minimo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Stock Mínimo <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="number" 
                                       id="stock_minimo" 
                                       name="stock_minimo" 
                                       x-model="form.stock_minimo"
                                       min="0"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="0">
                            </div>
                            <p x-show="errors.stock_minimo" x-text="errors.stock_minimo" class="mt-1 text-sm text-red-600"></p>
                        </div>
                        
                        <!-- Ubicación -->
                        <div class="col-span-1">
                            <label for="ubicacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ubicación en Almacén
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="ubicacion" 
                                       name="ubicacion" 
                                       x-model="form.ubicacion"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="Ej: Estante A-1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Provider Information -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Información del Proveedor
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Datos del proveedor y códigos adicionales
                    </p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Proveedor -->
                        <div class="col-span-1">
                            <label for="proveedor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Proveedor Principal
                            </label>
                            <div class="mt-1">
                                <select id="proveedor_id" 
                                        name="proveedor_id" 
                                        x-model="form.proveedor_id"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                                    <option value="">Sin proveedor asignado</option>
                                    @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}" {{ $producto->proveedor_id == $proveedor->id ? 'selected' : '' }}>
                                        {{ $proveedor->nombre }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Código del Proveedor -->
                        <div class="col-span-1">
                            <label for="codigo_proveedor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Código del Proveedor
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="codigo_proveedor" 
                                       name="codigo_proveedor" 
                                       x-model="form.codigo_proveedor"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="Código asignado por el proveedor">
                            </div>
                        </div>
                        
                        <!-- Código de Barras -->
                        <div class="col-span-1">
                            <label for="codigo_barras" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Código de Barras
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="codigo_barras" 
                                       name="codigo_barras" 
                                       x-model="form.codigo_barras"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       placeholder="Código de barras del producto">
                            </div>
                        </div>
                        
                        <!-- Notas -->
                        <div class="col-span-2">
                            <label for="notas" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notas Adicionales
                            </label>
                            <div class="mt-1">
                                <textarea id="notas" 
                                          name="notas" 
                                          x-model="form.notas"
                                          rows="3"
                                          class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                          placeholder="Notas internas sobre el producto..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de Auditoría -->
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                <div class="px-6 py-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Información de Registro
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Creado:</span>
                            <span class="ml-2 text-gray-900 dark:text-white">
                                {{ $producto->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Última actualización:</span>
                            <span class="ml-2 text-gray-900 dark:text-white">
                                {{ $producto->updated_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Los campos marcados con <span class="text-red-500 mx-1">*</span> son obligatorios
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('productos.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
                                Cancelar
                            </a>
                            
                            <button type="submit" 
                                    :disabled="loading"
                                    class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="loading ? 'Actualizando...' : 'Actualizar Producto'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function productEditForm() {
    return {
        loading: false,
        errors: {},
        
        form: {
            nombre: '{{ $producto->nombre }}',
            codigo: '{{ $producto->codigo }}',
            descripcion: '{{ $producto->descripcion }}',
            categoria_id: '{{ $producto->categoria_id }}',
            marca: '{{ $producto->marca }}',
            modelo: '{{ $producto->modelo }}',
            precio_compra: '{{ $producto->precio_compra }}',
            precio_venta: '{{ $producto->precio_venta }}',
            stock_actual: '{{ $producto->stock_actual }}',
            stock_minimo: '{{ $producto->stock_minimo }}',
            ubicacion: '{{ $producto->ubicacion }}',
            proveedor_id: '{{ $producto->proveedor_id }}',
            codigo_proveedor: '{{ $producto->codigo_proveedor }}',
            codigo_barras: '{{ $producto->codigo_barras }}',
            notas: '{{ $producto->notas }}',
            activo: '{{ $producto->activo ? 1 : 0 }}'
        },

        calculateMargin() {
            const compra = parseFloat(this.form.precio_compra) || 0;
            const venta = parseFloat(this.form.precio_venta) || 0;
            
            if (compra === 0 || venta === 0) return '0.00';
            
            const margen = ((venta - compra) / compra) * 100;
            return margen.toFixed(2);
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};

            try {
                const formData = new FormData();
                
                // Agregar todos los campos del formulario
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });
                
                formData.append('_method', 'PUT');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                const response = await fetch('{{ route("productos.update", $producto) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.showSuccess('Producto actualizado exitosamente');
                    
                    // Redirigir después de un breve delay
                    setTimeout(() => {
                        window.location.href = '{{ route("productos.show", $producto) }}';
                    }, 1500);
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                        this.showValidationErrors();
                    } else {
                        this.showError(data.message || 'Error al actualizar el producto');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                this.showError('Error de conexión. Inténtalo de nuevo.');
            } finally {
                this.loading = false;
            }
        },

        showSuccess(message) {
            Swal.fire({
                title: '¡Éxito!',
                text: message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        },

        showError(message) {
            Swal.fire({
                title: 'Error',
                text: message,
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        },

        showValidationErrors() {
            const firstError = Object.keys(this.errors)[0];
            if (firstError) {
                const element = document.getElementById(firstError);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    element.focus();
                }
            }
        }
    }
}
</script>
@endpush

