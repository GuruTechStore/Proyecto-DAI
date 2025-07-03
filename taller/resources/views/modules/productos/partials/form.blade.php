{{-- Product Form Component - Parte 1: Estructura base e información básica --}}
<div x-data="productForm({{ isset($producto) ? 'true' : 'false' }})" class="space-y-8">
    
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
                Datos básicos e identificación del producto
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
                               @input="validateField('nombre')"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               :class="errors.nombre ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                               placeholder="Ingrese el nombre del producto">
                    </div>
                    <p x-show="errors.nombre" x-text="errors.nombre" class="mt-1 text-sm text-red-600"></p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nombre descriptivo y único del producto</p>
                </div>
                
                <!-- Código -->
                <div class="col-span-1">
                    <label for="codigo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Código del Producto <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 flex">
                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-sm">
                            PRD-
                        </span>
                        <input type="text" 
                               id="codigo" 
                               name="codigo" 
                               x-model="form.codigo"
                               @input="validateField('codigo')"
                               class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-none rounded-r-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               :class="errors.codigo ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                               placeholder="001">
                    </div>
                    <p x-show="errors.codigo" x-text="errors.codigo" class="mt-1 text-sm text-red-600"></p>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Código único para identificar el producto</p>
                        <button type="button" 
                                @click="generateCode()"
                                class="text-xs text-gestion-600 hover:text-gestion-700 dark:text-gestion-400 dark:hover:text-gestion-300">
                            Generar automático
                        </button>
                    </div>
                </div>
                
                <!-- Categoría -->
                <div class="col-span-1">
                    <label for="categoria_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Categoría <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 flex">
                        <select id="categoria_id" 
                                name="categoria_id" 
                                x-model="form.categoria_id"
                                @change="validateField('categoria_id')"
                                class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-l-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                :class="errors.categoria_id ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''">
                            <option value="">Seleccione una categoría</option>
                            @if(isset($categorias))
                                @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" 
                                        {{ (isset($producto) && $producto->categoria_id == $categoria->id) ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                                @endforeach
                            @endif
                        </select>
                        <button type="button" 
                                @click="showCategoryModal = true"
                                class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-r-lg hover:bg-gray-100 dark:hover:bg-gray-600"
                                title="Nueva categoría">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
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
                               list="marcas-list"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Marca del producto">
                        <datalist id="marcas-list">
                            <template x-for="marca in availableMarcas" :key="marca">
                                <option :value="marca"></option>
                            </template>
                        </datalist>
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
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Los productos inactivos no aparecerán en las ventas
                    </p>
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
                                  maxlength="500"
                                  class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                  placeholder="Descripción detallada del producto, características técnicas, etc."></textarea>
                    </div>
                    <div class="flex justify-between mt-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Descripción visible para los clientes</p>
                        <span class="text-xs text-gray-400" x-text="`${(form.descripcion || '').length}/500`"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Pricing and Financial -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                Precios y Costos
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Gestión de precios de compra, venta y análisis de rentabilidad
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
                               @input="validateField('precio_compra'); calculateMargin()"
                               step="0.01"
                               min="0"
                               class="block w-full pl-8 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               :class="errors.precio_compra ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                               placeholder="0.00">
                    </div>
                    <p x-show="errors.precio_compra" x-text="errors.precio_compra" class="mt-1 text-sm text-red-600"></p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Costo de adquisición del producto</p>
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
                               @input="validateField('precio_venta'); calculateMargin()"
                               step="0.01"
                               min="0"
                               class="block w-full pl-8 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               :class="errors.precio_venta ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                               placeholder="0.00">
                    </div>
                    <p x-show="errors.precio_venta" x-text="errors.precio_venta" class="mt-1 text-sm text-red-600"></p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Precio de venta al público</p>
                </div>
                
                <!-- Margen de Ganancia -->
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Margen de Ganancia
                    </label>
                    <div class="mt-1">
                        <div class="flex items-center px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg">
                            <span x-text="marginData.percentage" class="text-lg font-semibold text-gray-900 dark:text-white"></span>
                            <span class="ml-1 text-sm text-gray-500 dark:text-gray-400">%</span>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs">
                        <span class="text-gray-500 dark:text-gray-400">
                            Ganancia: S/ <span x-text="marginData.profit"></span>
                        </span>
                        <span :class="marginData.isGood ? 'text-green-600' : 'text-red-600'" 
                              x-text="marginData.status"></span>
                    </div>
                </div>
            </div>
            
            <!-- Price Calculator -->
            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-3">Calculadora de Precio</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-blue-800 dark:text-blue-200 mb-1">
                            Margen deseado (%)
                        </label>
                        <input type="number" 
                               x-model="desiredMargin"
                               @input="calculatePriceByMargin()"
                               min="0"
                               max="100"
                               step="1"
                               class="block w-full px-3 py-2 text-sm border-blue-300 dark:border-blue-600 dark:bg-blue-900/50 dark:text-blue-100 rounded-md focus:ring-blue-500 focus:border-blue-500"
                               placeholder="30">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-blue-800 dark:text-blue-200 mb-1">
                            Precio sugerido
                        </label>
                        <div class="flex items-center px-3 py-2 bg-blue-100 dark:bg-blue-900/50 border border-blue-300 dark:border-blue-600 rounded-md">
                            <span class="text-sm text-blue-900 dark:text-blue-100">S/ </span>
                            <span x-text="suggestedPrice" class="text-sm font-medium text-blue-900 dark:text-blue-100"></span>
                            <button @click="form.precio_venta = suggestedPrice; calculateMargin()" 
                                    class="ml-2 text-xs text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200">
                                Usar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Inventory Management -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                Control de Inventario
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Gestión de stock y ubicación en almacén
            </p>
        </div>
        
        <div class="px-6 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
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
                               @input="validateField('stock_actual')"
                               min="0"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               :class="errors.stock_actual ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                               placeholder="0">
                    </div>
                    <p x-show="errors.stock_actual" x-text="errors.stock_actual" class="mt-1 text-sm text-red-600"></p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cantidad disponible en inventario</p>
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
                               @input="validateField('stock_minimo')"
                               min="0"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               :class="errors.stock_minimo ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                               placeholder="0">
                    </div>
                    <p x-show="errors.stock_minimo" x-text="errors.stock_minimo" class="mt-1 text-sm text-red-600"></p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Alerta cuando el stock baje de este nivel</p>
                </div>
                
                <!-- Stock Status -->
                <div class="col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Estado del Stock
                    </label>
                    <div class="mt-1">
                        <div class="flex items-center px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg">
                            <div :class="{
                                'bg-red-500': stockStatus.color === 'red',
                                'bg-yellow-500': stockStatus.color === 'yellow', 
                                'bg-green-500': stockStatus.color === 'green'
                            }" class="w-2 h-2 rounded-full mr-2"></div>
                            <span x-text="stockStatus.text" class="text-sm font-medium text-gray-900 dark:text-white"></span>
                        </div>
                    </div>
                    <div x-show="stockStatus.warning" class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
                        <p class="text-xs text-yellow-800 dark:text-yellow-200" x-text="stockStatus.warning"></p>
                    </div>
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
                               list="ubicaciones-list"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Ej: Estante A-1, Pasillo 3">
                        <datalist id="ubicaciones-list">
                            <option value="Estante A-1">
                            <option value="Estante A-2">
                            <option value="Pasillo 1">
                            <option value="Pasillo 2">
                            <option value="Almacén Principal">
                            <option value="Almacén Secundario">
                        </datalist>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ubicación física del producto</p>
                </div>
                
                <!-- Unidad de Medida -->
                <div class="col-span-1">
                    <label for="unidad_medida" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Unidad de Medida
                    </label>
                    <div class="mt-1">
                        <select id="unidad_medida" 
                                name="unidad_medida" 
                                x-model="form.unidad_medida"
                                class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="unidad">Unidad</option>
                            <option value="metro">Metro</option>
                            <option value="kilogramo">Kilogramo</option>
                            <option value="litro">Litro</option>
                            <option value="caja">Caja</option>
                            <option value="paquete">Paquete</option>
                            <option value="docena">Docena</option>
                        </select>
                    </div>
                </div>
                
                <!-- Stock Máximo -->
                <div class="col-span-1">
                    <label for="stock_maximo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Stock Máximo
                    </label>
                    <div class="mt-1">
                        <input type="number" 
                               id="stock_maximo" 
                               name="stock_maximo" 
                               x-model="form.stock_maximo"
                               min="0"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="0">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Capacidad máxima de almacenamiento</p>
                </div>
            </div>
            
            <!-- Stock Movement History -->
            <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600" x-show="isEdit">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Movimientos de Stock Recientes
                </h4>
                <div class="space-y-2">
                    <div class="flex items-center justify-between py-2 px-3 bg-white dark:bg-gray-800 rounded border">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-900 dark:text-white">Entrada de stock</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">+50 unidades</span>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Hace 2 días</span>
                    </div>
                    <div class="flex items-center justify-between py-2 px-3 bg-white dark:bg-gray-800 rounded border">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            <span class="text-sm text-gray-900 dark:text-white">Venta</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">-3 unidades</span>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Hace 1 día</span>
                    </div>
                    <div class="text-center">
                        <button type="button" class="text-xs text-gestion-600 hover:text-gestion-700 dark:text-gestion-400 dark:hover:text-gestion-300">
                            Ver historial completo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Supplier Information -->
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
                    <div class="mt-1 flex">
                        <select id="proveedor_id" 
                                name="proveedor_id" 
                                x-model="form.proveedor_id"
                                class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-l-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="">Sin proveedor asignado</option>
                            @if(isset($proveedores))
                                @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" 
                                        {{ (isset($producto) && $producto->proveedor_id == $proveedor->id) ? 'selected' : '' }}>
                                    {{ $proveedor->nombre }}
                                </option>
                                @endforeach
                            @endif
                        </select>
                        <button type="button" 
                                @click="showProviderModal = true"
                                class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-r-lg hover:bg-gray-100 dark:hover:bg-gray-600"
                                title="Nuevo proveedor">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Selecciona el proveedor principal de este producto</p>
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
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Referencia del proveedor para este producto</p>
                </div>
                
                <!-- Código de Barras -->
                <div class="col-span-1">
                    <label for="codigo_barras" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Código de Barras
                    </label>
                    <div class="mt-1 flex">
                        <input type="text" 
                               id="codigo_barras" 
                               name="codigo_barras" 
                               x-model="form.codigo_barras"
                               class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-l-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Código de barras EAN/UPC">
                        <button type="button" 
                                @click="generateBarcode()"
                                class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-r-lg hover:bg-gray-100 dark:hover:bg-gray-600 text-sm"
                                title="Generar código de barras">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Código de barras para escaneo rápido</p>
                </div>
                
                <!-- Tiempo de Entrega -->
                <div class="col-span-1">
                    <label for="tiempo_entrega" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tiempo de Entrega (días)
                    </label>
                    <div class="mt-1 relative">
                        <input type="number" 
                               id="tiempo_entrega" 
                               name="tiempo_entrega" 
                               x-model="form.tiempo_entrega"
                               min="0"
                               class="block w-full pr-12 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="0">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">días</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Días hábiles de entrega del proveedor</p>
                </div>
                
                <!-- Precio de Lista del Proveedor -->
                <div class="col-span-1">
                    <label for="precio_lista_proveedor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Precio de Lista del Proveedor
                    </label>
                    <div class="mt-1 relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400">S/</span>
                        <input type="number" 
                               id="precio_lista_proveedor" 
                               name="precio_lista_proveedor" 
                               x-model="form.precio_lista_proveedor"
                               step="0.01"
                               min="0"
                               class="block w-full pl-8 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="0.00">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Precio oficial del proveedor (referencial)</p>
                </div>
                
                <!-- Descuento del Proveedor -->
                <div class="col-span-1">
                    <label for="descuento_proveedor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Descuento del Proveedor
                    </label>
                    <div class="mt-1 relative">
                        <input type="number" 
                               id="descuento_proveedor" 
                               name="descuento_proveedor" 
                               x-model="form.descuento_proveedor"
                               step="0.01"
                               min="0"
                               max="100"
                               class="block w-full pr-8 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="0">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">%</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Porcentaje de descuento negociado</p>
                </div>
                
                <!-- Notas del Proveedor -->
                <div class="col-span-2">
                    <label for="notas_proveedor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Notas del Proveedor
                    </label>
                    <div class="mt-1">
                        <textarea id="notas_proveedor" 
                                  name="notas_proveedor" 
                                  x-model="form.notas_proveedor"
                                  rows="3"
                                  class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                  placeholder="Condiciones especiales, términos de pago, garantías, etc."></textarea>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Información específica sobre este producto del proveedor</p>
                </div>
            </div>
            
            <!-- Supplier Summary Card -->
            <div x-show="form.proveedor_id" class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Resumen del Proveedor
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-blue-700 dark:text-blue-300 font-medium">Tiempo de entrega:</span>
                        <span class="ml-1 text-blue-900 dark:text-blue-100" x-text="form.tiempo_entrega ? form.tiempo_entrega + ' días' : 'No especificado'"></span>
                    </div>
                    <div x-show="form.precio_lista_proveedor">
                        <span class="text-blue-700 dark:text-blue-300 font-medium">Precio lista:</span>
                        <span class="ml-1 text-blue-900 dark:text-blue-100">S/ <span x-text="parseFloat(form.precio_lista_proveedor || 0).toFixed(2)"></span></span>
                    </div>
                    <div x-show="form.descuento_proveedor">
                        <span class="text-blue-700 dark:text-blue-300 font-medium">Descuento:</span>
                        <span class="ml-1 text-blue-900 dark:text-blue-100" x-text="form.descuento_proveedor + '%'"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Product Images -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Imágenes del Producto
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Agregar imágenes del producto para mostrar a los clientes
            </p>
        </div>
        
        <div class="px-6 py-6">
            <!-- Upload Area -->
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-gestion-400 dark:hover:border-gestion-500 transition-colors"
                 @dragover.prevent
                 @drop.prevent="handleDrop($event)">
                <div class="space-y-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600 dark:text-gray-400">
                        <label for="product-images" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-gestion-600 hover:text-gestion-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-gestion-500">
                            <span>Subir imágenes</span>
                            <input id="product-images" 
                                   name="product-images" 
                                   type="file" 
                                   class="sr-only" 
                                   multiple 
                                   accept="image/*"
                                   @change="handleImageUpload($event)">
                        </label>
                        <p class="pl-1">o arrastra y suelta</p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        PNG, JPG, GIF hasta 10MB cada una (máximo 5 imágenes)
                    </p>
                </div>
            </div>
            
            <!-- Image Preview -->
            <div x-show="uploadedImages.length > 0" class="mt-6">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Imágenes cargadas</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4">
                    <template x-for="(image, index) in uploadedImages" :key="index">
                        <div class="relative group">
                            <img :src="image.url" 
                                 :alt="image.name"
                                 class="w-full h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 rounded-lg transition-all duration-200 flex items-center justify-center">
                                <div class="opacity-0 group-hover:opacity-100 flex space-x-2">
                                    <button @click="setMainImage(index)" 
                                            :class="index === 0 ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-600 hover:bg-gray-700'"
                                            class="text-white p-1 rounded-full transition-all text-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </button>
                                    <button @click="removeImage(index)" 
                                            class="bg-red-600 text-white p-1 rounded-full hover:bg-red-700 transition-all">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div x-show="index === 0" class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                Principal
                            </div>
                        </div>
                    </template>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    La primera imagen será la imagen principal del producto. Haz clic en la estrella para cambiar la imagen principal.
                </p>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Información Adicional
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Notas internas y observaciones del producto
            </p>
        </div>
        
        <div class="px-6 py-6">
            <div class="space-y-6">
                <!-- Notas Internas -->
                <div>
                    <label for="notas" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Notas Internas
                    </label>
                    <div class="mt-1">
                        <textarea id="notas" 
                                  name="notas" 
                                  x-model="form.notas"
                                  rows="4"
                                  class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                  placeholder="Notas internas sobre el producto, instrucciones especiales, observaciones del personal, etc."></textarea>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Esta información es solo para uso interno y no será visible para los clientes</p>
                </div>

                <!-- Tags/Etiquetas -->
                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Etiquetas
                    </label>
                    <div class="mt-1">
                        <input type="text" 
                               id="tags" 
                               name="tags" 
                               x-model="form.tags"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="nuevo, popular, oferta, reparacion, accesorio">
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Separa las etiquetas con comas para facilitar la búsqueda y organización</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Modal -->
    <div x-show="showCategoryModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         @click.self="showCategoryModal = false">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Nueva Categoría</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nombre de la categoría <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               x-model="newCategory.nombre"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Ej: Repuestos, Accesorios">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Descripción
                        </label>
                        <textarea x-model="newCategory.descripcion"
                                  rows="2"
                                  class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                  placeholder="Descripción opcional"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="showCategoryModal = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        Cancelar
                    </button>
                    <button @click="createCategory()" 
                            :disabled="!newCategory.nombre"
                            class="px-4 py-2 text-sm font-medium text-white bg-gestion-600 hover:bg-gestion-700 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        Crear Categoría
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Provider Modal -->
    <div x-show="showProviderModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         @click.self="showProviderModal = false">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Nuevo Proveedor</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nombre del proveedor <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               x-model="newProvider.nombre"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Nombre de la empresa">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Contacto
                        </label>
                        <input type="text" 
                               x-model="newProvider.contacto"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Teléfono o email">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="showProviderModal = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        Cancelar
                    </button>
                    <button @click="createProvider()" 
                            :disabled="!newProvider.nombre"
                            class="px-4 py-2 text-sm font-medium text-white bg-gestion-600 hover:bg-gestion-700 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        Crear Proveedor
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function productForm(isEdit = false) {
    return {
        isEdit: isEdit,
        errors: {},
        
        // Form data
        form: {
            nombre: '{{ isset($producto) ? $producto->nombre : "" }}',
            codigo: '{{ isset($producto) ? $producto->codigo : "" }}',
            descripcion: '{{ isset($producto) ? $producto->descripcion : "" }}',
            categoria_id: '{{ isset($producto) ? $producto->categoria_id : "" }}',
            marca: '{{ isset($producto) ? $producto->marca : "" }}',
            modelo: '{{ isset($producto) ? $producto->modelo : "" }}',
            precio_compra: '{{ isset($producto) ? $producto->precio_compra : "" }}',
            precio_venta: '{{ isset($producto) ? $producto->precio_venta : "" }}',
            stock_actual: '{{ isset($producto) ? $producto->stock_actual : "0" }}',
            stock_minimo: '{{ isset($producto) ? $producto->stock_minimo : "0" }}',
            stock_maximo: '{{ isset($producto) ? $producto->stock_maximo : "" }}',
            ubicacion: '{{ isset($producto) ? $producto->ubicacion : "" }}',
            unidad_medida: '{{ isset($producto) ? $producto->unidad_medida : "unidad" }}',
            proveedor_id: '{{ isset($producto) ? $producto->proveedor_id : "" }}',
            codigo_proveedor: '{{ isset($producto) ? $producto->codigo_proveedor : "" }}',
            codigo_barras: '{{ isset($producto) ? $producto->codigo_barras : "" }}',
            tiempo_entrega: '{{ isset($producto) ? $producto->tiempo_entrega : "" }}',
            precio_lista_proveedor: '{{ isset($producto) ? $producto->precio_lista_proveedor : "" }}',
            descuento_proveedor: '{{ isset($producto) ? $producto->descuento_proveedor : "" }}',
            notas_proveedor: '{{ isset($producto) ? $producto->notas_proveedor : "" }}',
            notas: '{{ isset($producto) ? $producto->notas : "" }}',
            tags: '{{ isset($producto) ? $producto->tags : "" }}',
            activo: '{{ isset($producto) ? ($producto->activo ? "1" : "0") : "1" }}'
        },
        
        // Images
        uploadedImages: [],
        
        // Calculadora de margen
        marginData: {
            percentage: '0.00',
            profit: '0.00',
            isGood: false,
            status: 'Sin datos'
        },
        
        desiredMargin: 30,
        suggestedPrice: '0.00',
        
        // Stock status
        stockStatus: {
            text: 'Normal',
            color: 'green',
            warning: null
        },
        
        // Modals
        showCategoryModal: false,
        showProviderModal: false,
        
        // New category/provider
        newCategory: {
            nombre: '',
            descripcion: ''
        },
        
        newProvider: {
            nombre: '',
            contacto: ''
        },
        
        // Available data
        availableMarcas: [],

        init() {
            this.calculateMargin();
            this.updateStockStatus();
            this.loadAvailableMarcas();
            this.calculatePriceByMargin();
            
            // Watch stock changes
            this.$watch('form.stock_actual', () => this.updateStockStatus());
            this.$watch('form.stock_minimo', () => this.updateStockStatus());
            this.$watch('form.precio_compra', () => this.calculatePriceByMargin());
            
            // Load existing images if editing
            if (this.isEdit) {
                this.loadExistingImages();
            }
        },

        loadAvailableMarcas() {
            this.availableMarcas = [
                'Samsung', 'Apple', 'Huawei', 'Xiaomi', 'LG', 'Sony', 
                'HP', 'Dell', 'Lenovo', 'Asus', 'Canon', 'Epson',
                'Brother', 'Xerox', 'Ricoh', 'Panasonic', 'Toshiba',
                'Microsoft', 'Logitech', 'Corsair', 'Razer', 'SteelSeries'
            ];
        },

        loadExistingImages() {
            // Load existing images if editing
            @if(isset($producto) && $producto->imagenes)
                @foreach($producto->imagenes as $imagen)
                    this.uploadedImages.push({
                        id: {{ $imagen->id }},
                        name: '{{ $imagen->nombre }}',
                        url: '{{ $imagen->url }}',
                        is_main: {{ $imagen->is_main ? 'true' : 'false' }},
                        existing: true
                    });
                @endforeach
            @endif
        },

        handleImageUpload(event) {
            const files = event.target.files;
            this.processFiles(files);
        },

        handleDrop(event) {
            const files = event.dataTransfer.files;
            this.processFiles(files);
        },

        processFiles(files) {
            if (this.uploadedImages.length >= 5) {
                this.showError('Máximo 5 imágenes permitidas');
                return;
            }

            for (let i = 0; i < files.length && this.uploadedImages.length < 5; i++) {
                const file = files[i];
                
                if (!file.type.startsWith('image/')) {
                    this.showError(`${file.name} no es una imagen válida`);
                    continue;
                }
                
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    this.showError(`${file.name} es demasiado grande (máximo 10MB)`);
                    continue;
                }
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.uploadedImages.push({
                        file: file,
                        name: file.name,
                        url: e.target.result,
                        is_main: this.uploadedImages.length === 0,
                        existing: false
                    });
                };
                reader.readAsDataURL(file);
            }
        },

        setMainImage(index) {
            // Reset all images as not main
            this.uploadedImages.forEach((img, i) => {
                img.is_main = (i === index);
            });
            
            // Move the selected image to first position
            if (index !== 0) {
                const mainImage = this.uploadedImages.splice(index, 1)[0];
                this.uploadedImages.unshift(mainImage);
            }
        },

        removeImage(index) {
            this.uploadedImages.splice(index, 1);
            
            // If we removed the main image, set the first one as main
            if (this.uploadedImages.length > 0) {
                this.uploadedImages[0].is_main = true;
            }
        },

        validateField(field) {
            // Clear previous error
            delete this.errors[field];
            
            const value = this.form[field];
            
            switch (field) {
                case 'nombre':
                    if (!value || value.trim() === '') {
                        this.errors[field] = 'El nombre es obligatorio';
                    } else if (value.length < 3) {
                        this.errors[field] = 'El nombre debe tener al menos 3 caracteres';
                    } else if (value.length > 255) {
                        this.errors[field] = 'El nombre no puede exceder 255 caracteres';
                    }
                    break;
                    
                case 'codigo':
                    if (!value || value.trim() === '') {
                        this.errors[field] = 'El código es obligatorio';
                    } else if (value.length < 2) {
                        this.errors[field] = 'El código debe tener al menos 2 caracteres';
                    } else if (!/^[A-Za-z0-9\-_]+$/.test(value)) {
                        this.errors[field] = 'El código solo puede contener letras, números, guiones y guiones bajos';
                    }
                    break;
                    
                case 'categoria_id':
                    if (!value) {
                        this.errors[field] = 'La categoría es obligatoria';
                    }
                    break;
                    
                case 'precio_compra':
                    if (!value || parseFloat(value) <= 0) {
                        this.errors[field] = 'El precio de compra debe ser mayor a 0';
                    } else if (parseFloat(value) > 999999.99) {
                        this.errors[field] = 'El precio de compra es demasiado alto';
                    }
                    break;
                    
                case 'precio_venta':
                    if (!value || parseFloat(value) <= 0) {
                        this.errors[field] = 'El precio de venta debe ser mayor a 0';
                    } else if (this.form.precio_compra && parseFloat(value) <= parseFloat(this.form.precio_compra)) {
                        this.errors[field] = 'El precio de venta debe ser mayor al precio de compra';
                    } else if (parseFloat(value) > 999999.99) {
                        this.errors[field] = 'El precio de venta es demasiado alto';
                    }
                    break;
                    
                case 'stock_actual':
                    if (value === '' || parseFloat(value) < 0) {
                        this.errors[field] = 'El stock actual no puede ser negativo';
                    } else if (parseFloat(value) > 999999) {
                        this.errors[field] = 'El stock actual es demasiado alto';
                    }
                    break;
                    
                case 'stock_minimo':
                    if (value === '' || parseFloat(value) < 0) {
                        this.errors[field] = 'El stock mínimo no puede ser negativo';
                    } else if (parseFloat(value) > 999999) {
                        this.errors[field] = 'El stock mínimo es demasiado alto';
                    }
                    break;
            }
        },

        validateAllFields() {
            const requiredFields = ['nombre', 'codigo', 'categoria_id', 'precio_compra', 'precio_venta', 'stock_actual', 'stock_minimo'];
            
            requiredFields.forEach(field => {
                this.validateField(field);
            });
            
            return Object.keys(this.errors).length === 0;
        },

        calculateMargin() {
            const compra = parseFloat(this.form.precio_compra) || 0;
            const venta = parseFloat(this.form.precio_venta) || 0;
            
            if (compra === 0 || venta === 0) {
                this.marginData = {
                    percentage: '0.00',
                    profit: '0.00',
                    isGood: false,
                    status: 'Sin datos'
                };
                return;
            }
            
            const profit = venta - compra;
            const percentage = ((profit / compra) * 100);
            
            this.marginData = {
                percentage: percentage.toFixed(2),
                profit: profit.toFixed(2),
                isGood: percentage >= 20,
                status: percentage >= 50 ? 'Excelente' : 
                       percentage >= 30 ? 'Muy bueno' : 
                       percentage >= 20 ? 'Bueno' : 
                       percentage >= 10 ? 'Regular' : 'Bajo'
            };
        },

        calculatePriceByMargin() {
            const compra = parseFloat(this.form.precio_compra) || 0;
            const margin = parseFloat(this.desiredMargin) || 0;
            
            if (compra === 0 || margin === 0) {
                this.suggestedPrice = '0.00';
                return;
            }
            
            const precio = compra * (1 + (margin / 100));
            this.suggestedPrice = precio.toFixed(2);
        },

        updateStockStatus() {
            const actual = parseFloat(this.form.stock_actual) || 0;
            const minimo = parseFloat(this.form.stock_minimo) || 0;
            const maximo = parseFloat(this.form.stock_maximo) || 0;
            
            if (actual <= 0) {
                this.stockStatus = {
                    text: 'Sin stock',
                    color: 'red',
                    warning: 'Producto sin existencias disponibles'
                };
            } else if (actual <= minimo) {
                this.stockStatus = {
                    text: 'Stock bajo',
                    color: 'yellow',
                    warning: `Stock por debajo del mínimo (${minimo}). Considere reabastecer.`
                };
            } else if (maximo > 0 && actual >= maximo) {
                this.stockStatus = {
                    text: 'Sobrestock',
                    color: 'yellow',
                    warning: `Stock por encima del máximo (${maximo}). Considere reducir pedidos.`
                };
            } else {
                this.stockStatus = {
                    text: 'Stock normal',
                    color: 'green',
                    warning: null
                };
            }
        },

        generateCode() {
            const timestamp = Date.now().toString().slice(-4);
            const category = this.form.categoria_id ? this.form.categoria_id.toString().padStart(2, '0') : '00';
            const random = Math.floor(Math.random() * 99).toString().padStart(2, '0');
            this.form.codigo = `${category}${timestamp}${random}`;
        },

        generateBarcode() {
            // Generate a simple EAN-13 like code
            const timestamp = Date.now().toString();
            const random = Math.floor(Math.random() * 999).toString().padStart(3, '0');
            this.form.codigo_barras = '978' + timestamp.slice(-7) + random;
        },

        async createCategory() {
            if (!this.newCategory.nombre.trim()) {
                this.showError('El nombre de la categoría es obligatorio');
                return;
            }
            
            try {
                const response = await fetch('/productos/categorias', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.newCategory)
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    // Add to select
                    const select = document.getElementById('categoria_id');
                    const option = document.createElement('option');
                    option.value = data.id;
                    option.textContent = data.nombre;
                    option.selected = true;
                    select.appendChild(option);
                    
                    // Update form
                    this.form.categoria_id = data.id;
                    
                    // Reset and close modal
                    this.newCategory = { nombre: '', descripcion: '' };
                    this.showCategoryModal = false;
                    
                    this.showSuccess('Categoría creada exitosamente');
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Error al crear categoría');
                }
            } catch (error) {
                console.error('Error creating category:', error);
                this.showError(error.message || 'No se pudo crear la categoría');
            }
        },

        async createProvider() {
            if (!this.newProvider.nombre.trim()) {
                this.showError('El nombre del proveedor es obligatorio');
                return;
            }
            
            try {
                const response = await fetch('/proveedores', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.newProvider)
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    // Add to select
                    const select = document.getElementById('proveedor_id');
                    const option = document.createElement('option');
                    option.value = data.id;
                    option.textContent = data.nombre;
                    option.selected = true;
                    select.appendChild(option);
                    
                    // Update form
                    this.form.proveedor_id = data.id;
                    
                    // Reset and close modal
                    this.newProvider = { nombre: '', contacto: '' };
                    this.showProviderModal = false;
                    
                    this.showSuccess('Proveedor creado exitosamente');
                } else {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Error al crear proveedor');
                }
            } catch (error) {
                console.error('Error creating provider:', error);
                this.showError(error.message || 'No se pudo crear el proveedor');
            }
        },

        async submitForm() {
            // Validate all fields first
            if (!this.validateAllFields()) {
                this.showError('Por favor corrige los errores en el formulario');
                this.scrollToFirstError();
                return false;
            }

            try {
                const formData = new FormData();
                
                // Add form fields
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });
                
                // Add images
                this.uploadedImages.forEach((image, index) => {
                    if (!image.existing && image.file) {
                        formData.append(`images[${index}]`, image.file);
                        formData.append(`image_is_main[${index}]`, image.is_main ? '1' : '0');
                    }
                });
                
                // Add CSRF token
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                if (this.isEdit) {
                    formData.append('_method', 'PUT');
                }

                const url = this.isEdit ? 
                    `{{ isset($producto) ? route('productos.update', $producto) : '/productos' }}` : 
                    '/productos';

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.showSuccess(this.isEdit ? 'Producto actualizado exitosamente' : 'Producto creado exitosamente');
                    
                    // Redirect after success
                    setTimeout(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = '/productos';
                        }
                    }, 1500);
                    
                    return true;
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                        this.scrollToFirstError();
                    }
                    throw new Error(data.message || 'Error al procesar el formulario');
                }
            } catch (error) {
                console.error('Form submission error:', error);
                this.showError(error.message || 'Error de conexión. Inténtalo de nuevo.');
                return false;
            }
        },

        scrollToFirstError() {
            const firstErrorField = Object.keys(this.errors)[0];
            if (firstErrorField) {
                const element = document.getElementById(firstErrorField);
                if (element) {
                    element.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    element.focus();
                }
            }
        },

        showSuccess(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                alert(message);
            }
        },

        showError(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error',
                    text: message,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            } else {
                alert('Error: ' + message);
            }
        },

        showInfo(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Información',
                    text: message,
                    icon: 'info',
                    confirmButtonColor: '#3b82f6'
                });
            } else {
                alert('Info: ' + message);
            }
        }
    }
}
</script>