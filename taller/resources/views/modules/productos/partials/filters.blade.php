{{-- Product Filters Component --}}
<div x-data="productFilters()" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
    
    <!-- Main Filter Bar -->
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
            
            <!-- Search and Quick Filters -->
            <div class="flex flex-col space-y-3 lg:flex-row lg:items-center lg:space-y-0 lg:space-x-4">
                
                <!-- Search -->
                <div class="relative flex-1 lg:w-80">
                    <input type="text" 
                           x-model="filters.search" 
                           @input="applyFilters()"
                           class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500" 
                           placeholder="Buscar por nombre, código, marca...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div x-show="filters.search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button @click="filters.search = ''; applyFilters()" 
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Quick Status Filter -->
                <div class="flex space-x-2">
                    <button @click="setQuickFilter('all')" 
                            :class="quickFilter === 'all' ? 'bg-gestion-100 text-gestion-700 border-gestion-300' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            class="px-3 py-2 text-sm font-medium border rounded-lg transition-colors">
                        Todos
                        <span x-show="counts.all > 0" 
                              :class="quickFilter === 'all' ? 'bg-gestion-200 text-gestion-800' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'"
                              class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium"
                              x-text="counts.all">
                        </span>
                    </button>
                    
                    <button @click="setQuickFilter('active')" 
                            :class="quickFilter === 'active' ? 'bg-green-100 text-green-700 border-green-300' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            class="px-3 py-2 text-sm font-medium border rounded-lg transition-colors">
                        Activos
                        <span x-show="counts.active > 0" 
                              :class="quickFilter === 'active' ? 'bg-green-200 text-green-800' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'"
                              class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium"
                              x-text="counts.active">
                        </span>
                    </button>
                    
                    <button @click="setQuickFilter('low_stock')" 
                            :class="quickFilter === 'low_stock' ? 'bg-yellow-100 text-yellow-700 border-yellow-300' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            class="px-3 py-2 text-sm font-medium border rounded-lg transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L4.348 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        Stock Bajo
                        <span x-show="counts.low_stock > 0" 
                              :class="quickFilter === 'low_stock' ? 'bg-yellow-200 text-yellow-800' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'"
                              class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium"
                              x-text="counts.low_stock">
                        </span>
                    </button>
                    
                    <button @click="setQuickFilter('out_of_stock')" 
                            :class="quickFilter === 'out_of_stock' ? 'bg-red-100 text-red-700 border-red-300' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            class="px-3 py-2 text-sm font-medium border rounded-lg transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                        </svg>
                        Sin Stock
                        <span x-show="counts.out_of_stock > 0" 
                              :class="quickFilter === 'out_of_stock' ? 'bg-red-200 text-red-800' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'"
                              class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium"
                              x-text="counts.out_of_stock">
                        </span>
                    </button>
                </div>
            </div>
            
            <!-- Filter Actions -->
            <div class="flex items-center space-x-3">
                <!-- Active Filters Count -->
                <div x-show="activeFiltersCount > 0" class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        <span x-text="activeFiltersCount"></span> filtros activos
                    </span>
                    <button @click="clearAllFilters()" 
                            class="text-sm text-gestion-600 hover:text-gestion-700 dark:text-gestion-400 dark:hover:text-gestion-300">
                        Limpiar todo
                    </button>
                </div>
                
                <!-- Advanced Filters Toggle -->
                <button @click="showAdvanced = !showAdvanced" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4 mr-2 transform transition-transform" :class="showAdvanced ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
                    </svg>
                    Filtros Avanzados
                </button>
                
                <!-- Save Filter Preset -->
                <button @click="showSavePresetModal = true" 
                        :disabled="activeFiltersCount === 0"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                    Guardar Filtro
                </button>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Panel -->
    <div x-show="showAdvanced" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Categoría -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Categoría
                </label>
                <select x-model="filters.categoria_id" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Marca -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Marca
                </label>
                <select x-model="filters.marca" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todas las marcas</option>
                    <template x-for="marca in availableMarcas" :key="marca">
                        <option :value="marca" x-text="marca"></option>
                    </template>
                </select>
            </div>
            
            <!-- Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Estado
                </label>
                <select x-model="filters.activo" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos los estados</option>
                    <option value="1">Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
            
            <!-- Proveedor -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Proveedor
                </label>
                <select x-model="filters.proveedor_id" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos los proveedores</option>
                    @foreach($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <!-- Second Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            
            <!-- Rango de Precio -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Rango de Precio
                </label>
                <div class="flex space-x-2">
                    <input type="number" 
                           x-model="filters.precio_min"
                           @input="applyFilters()"
                           step="0.01"
                           min="0"
                           placeholder="Mín"
                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <input type="number" 
                           x-model="filters.precio_max"
                           @input="applyFilters()"
                           step="0.01"
                           min="0"
                           placeholder="Máx"
                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                </div>
            </div>
            
            <!-- Stock -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Condición de Stock
                </label>
                <select x-model="filters.stock_condition" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Cualquier stock</option>
                    <option value="available">Con stock</option>
                    <option value="low">Stock bajo</option>
                    <option value="out">Sin stock</option>
                    <option value="overstock">Sobrestock</option>
                </select>
            </div>
            
            <!-- Fecha de Creación -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Creado desde
                </label>
                <input type="date" 
                       x-model="filters.fecha_desde"
                       @change="applyFilters()"
                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
            </div>
            
            <!-- Fecha de Creación Hasta -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Creado hasta
                </label>
                <input type="date" 
                       x-model="filters.fecha_hasta"
                       @change="applyFilters()"
                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
            </div>
        </div>
        
        <!-- Sorting Options -->
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Ordenar por
                        </label>
                        <select x-model="filters.sort_by" 
                                @change="applyFilters()"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="nombre">Nombre</option>
                            <option value="codigo">Código</option>
                            <option value="precio_venta">Precio</option>
                            <option value="stock_actual">Stock</option>
                            <option value="created_at">Fecha de creación</option>
                            <option value="updated_at">Última actualización</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Dirección
                        </label>
                        <select x-model="filters.sort_direction" 
                                @change="applyFilters()"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="asc">Ascendente</option>
                            <option value="desc">Descendente</option>
                        </select>
                    </div>
                </div>
                
                <!-- Per Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Elementos por página
                    </label>
                    <select x-model="filters.per_page" 
                            @change="applyFilters()"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Presets -->
    <div x-show="filterPresets.length > 0" class="px-6 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                <span class="text-sm font-medium text-blue-900 dark:text-blue-100">Filtros Guardados:</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <template x-for="preset in filterPresets" :key="preset.id">
                    <div class="flex items-center bg-white dark:bg-blue-900/50 border border-blue-200 dark:border-blue-700 rounded-lg px-3 py-1">
                        <button @click="applyPreset(preset)" 
                                class="text-sm text-blue-800 dark:text-blue-200 hover:text-blue-900 dark:hover:text-blue-100"
                                x-text="preset.name">
                        </button>
                        <button @click="deletePreset(preset.id)" 
                                class="ml-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Active Filters Display -->
    <div x-show="activeFiltersCount > 0" class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
                </svg>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtros Activos:</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <template x-for="(filter, key) in activeFilters" :key="key">
                    <div class="flex items-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1">
                        <span class="text-xs text-gray-700 dark:text-gray-300" x-text="filter.label"></span>
                        <button @click="removeFilter(key)" 
                                class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </template>
                <button @click="clearAllFilters()" 
                        class="text-xs text-gestion-600 hover:text-gestion-700 dark:text-gestion-400 dark:hover:text-gestion-300 px-2 py-1">
                    Limpiar todo
                </button>
            </div>
        </div>
    </div>

    <!-- Save Preset Modal -->
    <div x-show="showSavePresetModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         @click.self="showSavePresetModal = false">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Guardar Configuración de Filtros
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nombre del filtro
                        </label>
                        <input type="text" 
                               x-model="newPreset.name"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Ej: Productos con stock bajo">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Descripción (opcional)
                        </label>
                        <textarea x-model="newPreset.description"
                                  rows="2"
                                  class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                  placeholder="Descripción del filtro"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button @click="showSavePresetModal = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg">
                        Cancelar
                    </button>
                    <button @click="savePreset()" 
                            :disabled="!newPreset.name.trim()"
                            class="px-4 py-2 text-sm font-medium text-white bg-gestion-600 hover:bg-gestion-700 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        Guardar Filtro
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function productFilters() {
    return {
        showAdvanced: false,
        showSavePresetModal: false,
        quickFilter: 'all',
        
        filters: {
            search: '',
            categoria_id: '',
            marca: '',
            activo: '',
            proveedor_id: '',
            precio_min: '',
            precio_max: '',
            stock_condition: '',
            fecha_desde: '',
            fecha_hasta: '',
            sort_by: 'nombre',
            sort_direction: 'asc',
            per_page: '25'
        },
        
        counts: {
            all: 0,
            active: 0,
            low_stock: 0,
            out_of_stock: 0
        },
        
        availableMarcas: [],
        filterPresets: [],
        
        newPreset: {
            name: '',
            description: ''
        },

        init() {
            this.loadCounts();
            this.loadAvailableMarcas();
            this.loadFilterPresets();
            
            // Apply initial filters
            this.applyFilters();
        },

        get activeFiltersCount() {
            let count = 0;
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key] && key !== 'sort_by' && key !== 'sort_direction' && key !== 'per_page') {
                    count++;
                }
            });
            return count;
        },

        get activeFilters() {
            const active = {};
            
            if (this.filters.search) {
                active.search = { label: `Búsqueda: "${this.filters.search}"` };
            }
            if (this.filters.categoria_id) {
                const categoria = @json($categorias->pluck('nombre', 'id'));
                active.categoria_id = { label: `Categoría: ${categoria[this.filters.categoria_id]}` };
            }
            if (this.filters.marca) {
                active.marca = { label: `Marca: ${this.filters.marca}` };
            }
            if (this.filters.activo !== '') {
                active.activo = { label: `Estado: ${this.filters.activo === '1' ? 'Activo' : 'Inactivo'}` };
            }
            if (this.filters.proveedor_id) {
                const proveedores = @json($proveedores->pluck('nombre', 'id'));
                active.proveedor_id = { label: `Proveedor: ${proveedores[this.filters.proveedor_id]}` };
            }
            if (this.filters.precio_min || this.filters.precio_max) {
                const min = this.filters.precio_min ? `S/ ${this.filters.precio_min}` : '0';
                const max = this.filters.precio_max ? `S/ ${this.filters.precio_max}` : '∞';
                active.precio_range = { label: `Precio: ${min} - ${max}` };
            }
            if (this.filters.stock_condition) {
                const conditions = {
                    available: 'Con stock',
                    low: 'Stock bajo',
                    out: 'Sin stock',
                    overstock: 'Sobrestock'
                };
                active.stock_condition = { label: `Stock: ${conditions[this.filters.stock_condition]}` };
            }
            if (this.filters.fecha_desde || this.filters.fecha_hasta) {
                const desde = this.filters.fecha_desde || 'inicio';
                const hasta = this.filters.fecha_hasta || 'ahora';
                active.fecha_range = { label: `Fecha: ${desde} - ${hasta}` };
            }
            
            return active;
        },

        setQuickFilter(type) {
            this.quickFilter = type;
            this.clearAllFilters();
            
            switch (type) {
                case 'active':
                    this.filters.activo = '1';
                    break;
                case 'low_stock':
                    this.filters.stock_condition = 'low';
                    break;
                case 'out_of_stock':
                    this.filters.stock_condition = 'out';
                    break;
            }
            
            this.applyFilters();
        },

        removeFilter(key) {
            if (key === 'precio_range') {
                this.filters.precio_min = '';
                this.filters.precio_max = '';
            } else if (key === 'fecha_range') {
                this.filters.fecha_desde = '';
                this.filters.fecha_hasta = '';
            } else {
                this.filters[key] = '';
            }
            this.applyFilters();
        },

        clearAllFilters() {
            this.filters = {
                search: '',
                categoria_id: '',
                marca: '',
                activo: '',
                proveedor_id: '',
                precio_min: '',
                precio_max: '',
                stock_condition: '',
                fecha_desde: '',
                fecha_hasta: '',
                sort_by: 'nombre',
                sort_direction: 'asc',
                per_page: '25'
            };
            this.quickFilter = 'all';
            this.applyFilters();
        },

        applyFilters() {
            // Build query string
            const params = new URLSearchParams();
            
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    params.append(key, this.filters[key]);
                }
            });
            
            // Update URL without page reload
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.replaceState({}, '', newUrl);
            
            // Emit event for table component to listen
            window.dispatchEvent(new CustomEvent('filtersChanged', { 
                detail: { filters: this.filters } 
            }));
        },

        loadCounts() {
            // This would typically be loaded from the backend
            fetch('/productos/counts')
                .then(response => response.json())
                .then(data => {
                    this.counts = data;
                })
                .catch(error => {
                    console.error('Error loading counts:', error);
                });
        },

        loadAvailableMarcas() {
            // This would typically come from the backend
            this.availableMarcas = [
                'Samsung', 'Apple', 'Huawei', 'Xiaomi', 'LG', 'Sony', 
                'HP', 'Dell', 'Lenovo', 'Asus', 'Canon', 'Epson'
            ];
        },

        loadFilterPresets() {
            // Load from localStorage or backend
            const saved = localStorage.getItem('product_filter_presets');
            if (saved) {
                this.filterPresets = JSON.parse(saved);
            }
        },

        savePreset() {
            if (!this.newPreset.name.trim()) return;
            
            const preset = {
                id: Date.now(),
                name: this.newPreset.name,
                description: this.newPreset.description,
                filters: { ...this.filters },
                created_at: new Date().toISOString()
            };
            
            this.filterPresets.push(preset);
            localStorage.setItem('product_filter_presets', JSON.stringify(this.filterPresets));
            
            this.newPreset = { name: '', description: '' };
            this.showSavePresetModal = false;
            
            this.showSuccess('Filtro guardado correctamente');
        },

        applyPreset(preset) {
            this.filters = { ...preset.filters };
            this.quickFilter = 'all';
            this.applyFilters();
            
            this.showSuccess(`Filtro "${preset.name}" aplicado`);
        },

        deletePreset(presetId) {
            this.filterPresets = this.filterPresets.filter(p => p.id !== presetId);
            localStorage.setItem('product_filter_presets', JSON.stringify(this.filterPresets));
            
            this.showSuccess('Filtro eliminado');
        },

        showSuccess(message) {
            // Show success notification
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    }
}
</script>