{{-- Products Table Component --}}
<div x-data="productosTable({{ json_encode($productos->items()) }})" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
    
    <!-- Table Header with Filters -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
            
            <!-- Search and Basic Filters -->
            <div class="flex flex-col space-y-3 lg:flex-row lg:items-center lg:space-y-0 lg:space-x-4">
                <!-- Search -->
                <div class="relative">
                    <input type="text" 
                           x-model="search" 
                           @input="applyFilters()"
                           class="block w-full lg:w-80 pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500" 
                           placeholder="Buscar productos...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
                
                <!-- Category Filter -->
                <div>
                    <select x-model="categoriaFilter" 
                            @change="applyFilters()"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div>
                    <select x-model="statusFilter" 
                            @change="applyFilters()"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
                
                <!-- Stock Filter -->
                <div>
                    <select x-model="stockFilter" 
                            @change="applyFilters()"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="">Stock normal</option>
                        <option value="bajo">Stock bajo</option>
                        <option value="agotado">Sin stock</option>
                    </select>
                </div>
                
                <!-- Per Page -->
                <div>
                    <select x-model="perPage" 
                            @change="applyFilters()"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="10">10 por página</option>
                        <option value="25">25 por página</option>
                        <option value="50">50 por página</option>
                        <option value="100">100 por página</option>
                    </select>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center space-x-2">
                @can('productos.crear')
                <a href="{{ route('productos.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Producto
                </a>
                @endcan
                
                <!-- Export Button -->
                <button @click="exportProducts()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exportar
                </button>
            </div>
        </div>
        
        <!-- Advanced Filters Toggle -->
        <div class="mt-4">
            <button @click="showAdvancedFilters = !showAdvancedFilters" 
                    class="text-sm text-gestion-600 hover:text-gestion-700 dark:text-gestion-400 dark:hover:text-gestion-300 flex items-center">
                <svg class="w-4 h-4 mr-1 transform transition-transform" :class="showAdvancedFilters ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <span x-text="showAdvancedFilters ? 'Ocultar filtros avanzados' : 'Mostrar filtros avanzados'"></span>
            </button>
        </div>
        
        <!-- Advanced Filters -->
        <div x-show="showAdvancedFilters" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Marca</label>
                <select x-model="marcaFilter" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todas las marcas</option>
                    <template x-for="marca in availableMarcas" :key="marca">
                        <option :value="marca" x-text="marca"></option>
                    </template>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rango de Precio</label>
                <select x-model="priceRangeFilter" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos los precios</option>
                    <option value="0-50">S/ 0 - 50</option>
                    <option value="50-100">S/ 50 - 100</option>
                    <option value="100-500">S/ 100 - 500</option>
                    <option value="500-1000">S/ 500 - 1000</option>
                    <option value="1000+">S/ 1000+</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Creación</label>
                <select x-model="dateFilter" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Cualquier fecha</option>
                    <option value="today">Hoy</option>
                    <option value="week">Esta semana</option>
                    <option value="month">Este mes</option>
                    <option value="quarter">Este trimestre</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button @click="clearFilters()" 
                        class="w-full px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                    Limpiar Filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="flex items-center justify-center py-12">
        <div class="flex items-center space-x-2">
            <div class="animate-spin w-5 h-5 border-2 border-gestion-600 border-t-transparent rounded-full"></div>
            <span class="text-sm text-gray-600 dark:text-gray-400">Cargando productos...</span>
        </div>
    </div>

    <!-- Table -->
    <div x-show="!loading" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <!-- Select All -->
                    <th class="px-6 py-3 text-left">
                        <input type="checkbox" 
                               @change="toggleAllSelection($event)"
                               :checked="selectedIds.length === paginatedProductos.length && paginatedProductos.length > 0"
                               class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 rounded">
                    </th>
                    
                    <!-- Producto -->
                    <th @click="sortBy('nombre')" 
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                        <div class="flex items-center space-x-1">
                            <span>Producto</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </div>
                    </th>
                    
                    <!-- Código -->
                    <th @click="sortBy('codigo')" 
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                        <div class="flex items-center space-x-1">
                            <span>Código</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </div>
                    </th>
                    
                    <!-- Categoría -->
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Categoría</th>
                    
                    <!-- Precio -->
                    <th @click="sortBy('precio_venta')" 
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                        <div class="flex items-center space-x-1">
                            <span>Precio</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </div>
                    </th>
                    
                    <!-- Stock -->
                    <th @click="sortBy('stock_actual')" 
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-gray-200">
                        <div class="flex items-center space-x-1">
                            <span>Stock</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                            </svg>
                        </div>
                    </th>
                    
                    <!-- Estado -->
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                    
                    <!-- Acciones -->
                    @if($showActions ?? true)
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <template x-for="producto in paginatedProductos" :key="producto.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <!-- Checkbox -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" 
                                   :value="producto.id"
                                   @change="toggleSelection(producto.id)"
                                   :checked="selectedIds.includes(producto.id)"
                                   class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 rounded">
                        </td>
                        
                        <!-- Producto -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-lg bg-gestion-100 dark:bg-gestion-900 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="producto.nombre"></div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        <span x-text="producto.marca"></span>
                                        <span x-show="producto.modelo" x-text="' - ' + producto.modelo"></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        
                        <!-- Código -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-mono text-gray-900 dark:text-white" x-text="producto.codigo"></div>
                        </td>
                        
                        <!-- Categoría -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span x-show="producto.categoria" 
                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                  x-text="producto.categoria?.nombre || 'Sin categoría'">
                            </span>
                        </td>
                        
                        <!-- Precio -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <span class="font-medium">S/ </span>
                                <span x-text="parseFloat(producto.precio_venta || 0).toFixed(2)"></span>
                            </div>
                            <div x-show="producto.precio_compra" class="text-xs text-gray-500 dark:text-gray-400">
                                Compra: S/ <span x-text="parseFloat(producto.precio_compra || 0).toFixed(2)"></span>
                            </div>
                        </td>
                        
                        <!-- Stock -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="producto.stock_actual || 0"></div>
                            <div class="flex items-center mt-1">
                                <div :class="{
                                    'bg-red-200 dark:bg-red-900': producto.stock_actual <= 0,
                                    'bg-yellow-200 dark:bg-yellow-900': producto.stock_actual > 0 && producto.stock_actual <= (producto.stock_minimo || 0),
                                    'bg-green-200 dark:bg-green-900': producto.stock_actual > (producto.stock_minimo || 0)
                                }" class="flex-1 h-1.5 rounded-full">
                                    <div class="h-full rounded-full"
                                         :class="{
                                             'bg-red-500': producto.stock_actual <= 0,
                                             'bg-yellow-500': producto.stock_actual > 0 && producto.stock_actual <= (producto.stock_minimo || 0),
                                             'bg-green-500': producto.stock_actual > (producto.stock_minimo || 0)
                                         }"
                                         :style="`width: ${Math.min(100, Math.max(10, (producto.stock_actual / Math.max(producto.stock_minimo * 2, 10)) * 100))}%`">
                                    </div>
                                </div>
                            </div>
                            <div x-show="producto.stock_minimo" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Mín: <span x-text="producto.stock_minimo"></span>
                            </div>
                        </td>
                        
                        <!-- Estado -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="producto.activo ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                <span :class="producto.activo ? 'bg-green-400' : 'bg-red-400'" class="w-1.5 h-1.5 rounded-full mr-1.5"></span>
                                <span x-text="producto.activo ? 'Activo' : 'Inactivo'"></span>
                            </span>
                        </td>
                        
                        <!-- Acciones -->
                        @if($showActions ?? true)
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                @can('productos.ver')
                                <a :href="`{{ route('productos.show', '') }}/${producto.id}`" 
                                   class="text-gestion-600 hover:text-gestion-700 dark:text-gestion-400 dark:hover:text-gestion-300"
                                   title="Ver producto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('productos.editar')
                                <a :href="`{{ route('productos.edit', '') }}/${producto.id}`" 
                                   class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                   title="Editar producto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('productos.eliminar')
                                <button @click="deleteProduct(producto)" 
                                        class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                        title="Eliminar producto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                        @endif
                    </tr>
                </template>
                
                <!-- Empty State -->
                <tr x-show="filteredProductos.length === 0 && !loading">
                    <td :colspan="@if($showActions ?? true) 8 @else 7 @endif" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay productos</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">
                                <span x-show="search || categoriaFilter || statusFilter || stockFilter || marcaFilter || priceRangeFilter || dateFilter">
                                    No se encontraron productos con los filtros aplicados
                                </span>
                                <span x-show="!search && !categoriaFilter && !statusFilter && !stockFilter && !marcaFilter && !priceRangeFilter && !dateFilter">
                                    Comienza agregando tu primer producto
                                </span>
                            </p>
                            <div x-show="search || categoriaFilter || statusFilter || stockFilter || marcaFilter || priceRangeFilter || dateFilter">
                                <button @click="clearFilters()" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Limpiar filtros
                                </button>
                            </div>
                            <div x-show="!search && !categoriaFilter && !statusFilter && !stockFilter && !marcaFilter && !priceRangeFilter && !dateFilter">
                                @can('productos.crear')
                                <a href="{{ route('productos.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Agregar Producto
                                </a>
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div x-show="!loading && filteredProductos.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
            <!-- Results Info -->
            <div class="text-sm text-gray-700 dark:text-gray-300">
                Mostrando 
                <span class="font-medium" x-text="((currentPage - 1) * parseInt(perPage)) + 1"></span>
                a 
                <span class="font-medium" x-text="Math.min(currentPage * parseInt(perPage), filteredProductos.length)"></span>
                de 
                <span class="font-medium" x-text="filteredProductos.length"></span>
                productos
            </div>
            
            <!-- Pagination Controls -->
            <div class="flex items-center space-x-2">
                <button @click="goToPage(currentPage - 1)" 
                        :disabled="currentPage === 1"
                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                    Anterior
                </button>
                
                <template x-for="page in visiblePages" :key="page">
                    <button @click="goToPage(page)"
                            :class="page === currentPage ? 'bg-gestion-600 text-white border-gestion-600' : 'bg-white text-gray-500 border-gray-300 hover:bg-gray-50 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200'"
                            class="px-3 py-2 text-sm font-medium border rounded-lg">
                        <span x-text="page"></span>
                    </button>
                </template>
                
                <button @click="goToPage(currentPage + 1)" 
                        :disabled="currentPage === totalPages"
                        class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                    Siguiente
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div x-show="selectedIds.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 px-4 py-3">
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                <span x-text="selectedIds.length"></span> productos seleccionados
            </span>
            
            <div class="flex items-center space-x-2">
                <button @click="bulkToggleStatus()" 
                        class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-colors">
                    Cambiar Estado
                </button>
                
                @can('productos.eliminar')
                <button @click="bulkDelete()" 
                        class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors">
                    Eliminar
                </button>
                @endcan
                
                <button @click="exportSelected()" 
                        class="px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-colors">
                    Exportar
                </button>
                
                <button @click="selectedIds = []" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function productosTable(initialProductos) {
    return {
        loading: false,
        productos: initialProductos,
        filteredProductos: [],
        paginatedProductos: [],
        availableMarcas: [],
        
        // Filters
        search: '',
        categoriaFilter: '',
        statusFilter: '',
        stockFilter: '',
        marcaFilter: '',
        priceRangeFilter: '',
        dateFilter: '',
        showAdvancedFilters: false,
        
        // Sorting
        sortField: 'nombre',
        sortDirection: 'asc',
        
        // Pagination
        currentPage: 1,
        perPage: '25',
        totalPages: 1,
        
        // Selection
        selectedIds: [],

        init() {
            this.filteredProductos = [...this.productos];
            this.extractAvailableMarcas();
            this.applyFilters();
            console.log('Productos table initialized');
        },

        extractAvailableMarcas() {
            const marcas = [...new Set(this.productos.map(p => p.marca).filter(Boolean))];
            this.availableMarcas = marcas.sort();
        },

        applyFilters() {
            let filtered = [...this.productos];

            // Search filter
            if (this.search) {
                const searchTerm = this.search.toLowerCase();
                filtered = filtered.filter(producto => 
                    producto.nombre.toLowerCase().includes(searchTerm) ||
                    producto.codigo.toLowerCase().includes(searchTerm) ||
                    (producto.marca && producto.marca.toLowerCase().includes(searchTerm)) ||
                    (producto.modelo && producto.modelo.toLowerCase().includes(searchTerm)) ||
                    (producto.descripcion && producto.descripcion.toLowerCase().includes(searchTerm))
                );
            }

            // Category filter
            if (this.categoriaFilter) {
                filtered = filtered.filter(producto => 
                    producto.categoria_id == this.categoriaFilter
                );
            }

            // Status filter
            if (this.statusFilter !== '') {
                filtered = filtered.filter(producto => 
                    producto.activo.toString() === this.statusFilter
                );
            }

            // Stock filter
            if (this.stockFilter) {
                filtered = filtered.filter(producto => {
                    if (this.stockFilter === 'agotado') {
                        return producto.stock_actual <= 0;
                    } else if (this.stockFilter === 'bajo') {
                        return producto.stock_actual > 0 && producto.stock_actual <= (producto.stock_minimo || 0);
                    }
                    return true;
                });
            }

            // Marca filter
            if (this.marcaFilter) {
                filtered = filtered.filter(producto => 
                    producto.marca === this.marcaFilter
                );
            }

            // Price range filter
            if (this.priceRangeFilter) {
                filtered = filtered.filter(producto => {
                    const precio = parseFloat(producto.precio_venta) || 0;
                    switch (this.priceRangeFilter) {
                        case '0-50': return precio >= 0 && precio <= 50;
                        case '50-100': return precio > 50 && precio <= 100;
                        case '100-500': return precio > 100 && precio <= 500;
                        case '500-1000': return precio > 500 && precio <= 1000;
                        case '1000+': return precio > 1000;
                        default: return true;
                    }
                });
            }

            // Date filter
            if (this.dateFilter) {
                const now = new Date();
                filtered = filtered.filter(producto => {
                    const createdAt = new Date(producto.created_at);
                    switch (this.dateFilter) {
                        case 'today':
                            return createdAt.toDateString() === now.toDateString();
                        case 'week':
                            const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                            return createdAt >= weekAgo;
                        case 'month':
                            return createdAt.getMonth() === now.getMonth() && createdAt.getFullYear() === now.getFullYear();
                        case 'quarter':
                            const quarter = Math.floor(now.getMonth() / 3);
                            const productQuarter = Math.floor(createdAt.getMonth() / 3);
                            return productQuarter === quarter && createdAt.getFullYear() === now.getFullYear();
                        default:
                            return true;
                    }
                });
            }

            this.filteredProductos = this.sortProducts(filtered);
            this.updatePagination();
        },

        sortProducts(productos) {
            return productos.sort((a, b) => {
                let aVal = a[this.sortField];
                let bVal = b[this.sortField];
                
                if (this.sortField === 'categoria') {
                    aVal = a.categoria?.nombre || '';
                    bVal = b.categoria?.nombre || '';
                }
                
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                
                if (this.sortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
        },

        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            this.applyFilters();
        },

        updatePagination() {
            this.totalPages = Math.ceil(this.filteredProductos.length / parseInt(this.perPage));
            this.currentPage = Math.min(this.currentPage, Math.max(1, this.totalPages));
            
            const start = (this.currentPage - 1) * parseInt(this.perPage);
            const end = start + parseInt(this.perPage);
            this.paginatedProductos = this.filteredProductos.slice(start, end);
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.updatePagination();
            }
        },

        get visiblePages() {
            const pages = [];
            const maxVisible = 5;
            let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
            let end = Math.min(this.totalPages, start + maxVisible - 1);
            
            if (end - start + 1 < maxVisible) {
                start = Math.max(1, end - maxVisible + 1);
            }
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },

        clearFilters() {
            this.search = '';
            this.categoriaFilter = '';
            this.statusFilter = '';
            this.stockFilter = '';
            this.marcaFilter = '';
            this.priceRangeFilter = '';
            this.dateFilter = '';
            this.currentPage = 1;
            this.applyFilters();
        },

        toggleSelection(id) {
            const index = this.selectedIds.indexOf(id);
            if (index > -1) {
                this.selectedIds.splice(index, 1);
            } else {
                this.selectedIds.push(id);
            }
        },

        toggleAllSelection(event) {
            if (event.target.checked) {
                this.selectedIds = this.paginatedProductos.map(p => p.id);
            } else {
                this.selectedIds = [];
            }
        },

        async deleteProduct(producto) {
            const result = await Swal.fire({
                title: '¿Eliminar producto?',
                text: `Se eliminará el producto "${producto.nombre}". Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/productos/${producto.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (response.ok) {
                        this.productos = this.productos.filter(p => p.id !== producto.id);
                        this.applyFilters();
                        
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'El producto ha sido eliminado correctamente.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error('Error al eliminar');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo eliminar el producto. Inténtalo de nuevo.',
                        icon: 'error'
                    });
                }
            }
        },

        async bulkDelete() {
            if (this.selectedIds.length === 0) return;

            const result = await Swal.fire({
                title: '¿Eliminar productos seleccionados?',
                text: `Se eliminarán ${this.selectedIds.length} productos. Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('/productos/bulk-delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ ids: this.selectedIds })
                    });

                    if (response.ok) {
                        this.productos = this.productos.filter(p => !this.selectedIds.includes(p.id));
                        this.selectedIds = [];
                        this.applyFilters();
                        
                        Swal.fire({
                            title: '¡Eliminados!',
                            text: 'Los productos han sido eliminados correctamente.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        throw new Error('Error al eliminar');
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudieron eliminar los productos. Inténtalo de nuevo.',
                        icon: 'error'
                    });
                }
            }
        },

        async bulkToggleStatus() {
            if (this.selectedIds.length === 0) return;

            try {
                const response = await fetch('/productos/bulk-toggle-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ ids: this.selectedIds })
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    // Update local data
                    this.productos = this.productos.map(producto => {
                        if (this.selectedIds.includes(producto.id)) {
                            return { ...producto, activo: data.new_status };
                        }
                        return producto;
                    });
                    
                    this.selectedIds = [];
                    this.applyFilters();
                    
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: 'El estado de los productos ha sido actualizado.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error('Error al actualizar');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo actualizar el estado. Inténtalo de nuevo.',
                    icon: 'error'
                });
            }
        },

        exportProducts() {
            const params = new URLSearchParams({
                search: this.search,
                categoria: this.categoriaFilter,
                status: this.statusFilter,
                stock: this.stockFilter,
                marca: this.marcaFilter,
                price_range: this.priceRangeFilter,
                date: this.dateFilter
            });

            window.open(`/productos/export?${params.toString()}`, '_blank');
        },

        exportSelected() {
            if (this.selectedIds.length === 0) return;
            
            const params = new URLSearchParams();
            this.selectedIds.forEach(id => params.append('ids[]', id));
            
            window.open(`/productos/export?${params.toString()}`, '_blank');
        }
    }
}
</script>