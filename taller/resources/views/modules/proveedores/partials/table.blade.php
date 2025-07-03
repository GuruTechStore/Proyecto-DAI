{{-- resources/views/modules/proveedores/partials/table.blade.php --}}
@props([
    'proveedores' => collect(),
    'showActions' => true,
    'showFilters' => true,
    'showPagination' => true,
    'tableId' => 'proveedores-table'
])

<div x-data="proveedoresTable()" x-init="init()" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
    
    @if($showFilters)
    <!-- Filters & Search -->
    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            
            <!-- Search -->
            <div class="lg:col-span-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input x-model="search" 
                           @input.debounce.300ms="applyFilters()"
                           type="text" 
                           placeholder="Buscar por nombre, RUC, teléfono o email..."
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                </div>
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
             class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Con RUC</label>
                <select x-model="rucFilter" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos</option>
                    <option value="1">Con RUC</option>
                    <option value="0">Sin RUC</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Con Email</label>
                <select x-model="emailFilter" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos</option>
                    <option value="1">Con email</option>
                    <option value="0">Sin email</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Información Bancaria</label>
                <select x-model="bancoFilter" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos</option>
                    <option value="1">Con info bancaria</option>
                    <option value="0">Sin info bancaria</option>
                </select>
            </div>
        </div>
    </div>
    @endif

    <!-- Loading State -->
    <div x-show="loading" class="p-8 text-center">
        <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-gestion-500 transition ease-in-out duration-150">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Cargando proveedores...
        </div>
    </div>

    <!-- Table -->
    <div x-show="!loading" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    @if($showActions)
                    <th scope="col" class="px-6 py-3 text-left">
                        <input type="checkbox" 
                               @change="toggleSelectAll()"
                               :checked="selectedIds.length === filteredProveedores.length && filteredProveedores.length > 0"
                               class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                    </th>
                    @endif
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <button @click="sort('nombre')" class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-300">
                            <span>Proveedor</span>
                            <svg class="w-4 h-4" :class="getSortIcon('nombre')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        </button>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        RUC
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Contacto
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Información Bancaria
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <button @click="sort('created_at')" class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-300">
                            <span>Registro</span>
                            <svg class="w-4 h-4" :class="getSortIcon('created_at')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        </button>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Actividad
                    </th>
                    @if($showActions)
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Acciones
                    </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <template x-for="proveedor in paginatedProveedores" :key="proveedor.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        @if($showActions)
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" 
                                   :value="proveedor.id"
                                   @change="toggleSelect(proveedor.id)"
                                   :checked="selectedIds.includes(proveedor.id)"
                                   class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gestion-100 dark:bg-gestion-900 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gestion-700 dark:text-gestion-300" 
                                              x-text="getInitials(proveedor.nombre)"></span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <span x-text="proveedor.nombre"></span>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        ID: <span x-text="proveedor.id"></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div x-show="proveedor.ruc">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <span x-text="proveedor.ruc"></span>
                                </span>
                            </div>
                            <div x-show="!proveedor.ruc">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    Sin RUC
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-1">
                                <div class="flex items-center" x-show="proveedor.telefono">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <a :href="'tel:' + proveedor.telefono" 
                                       class="text-sm text-gray-900 dark:text-white hover:text-gestion-600 transition-colors" 
                                       x-text="proveedor.telefono"></a>
                                </div>
                                <div x-show="!proveedor.telefono" class="text-gray-400 text-sm">Sin teléfono</div>
                                
                                <div class="flex items-center" x-show="proveedor.email">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <a :href="'mailto:' + proveedor.email" 
                                       class="text-sm text-gray-600 dark:text-gray-400 hover:text-gestion-600 transition-colors truncate max-w-xs" 
                                       x-text="proveedor.email"
                                       :title="proveedor.email"></a>
                                </div>
                                <div x-show="!proveedor.email" class="text-gray-400 text-sm">Sin email</div>
                                
                                <div x-show="proveedor.contacto" class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="font-medium">Contacto:</span> <span x-text="proveedor.contacto"></span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div class="space-y-1">
                                <div x-show="proveedor.banco" class="text-sm font-medium text-gray-900 dark:text-white">
                                    <span x-text="proveedor.banco"></span>
                                </div>
                                <div x-show="proveedor.numero_cuenta" class="text-xs text-gray-600 dark:text-gray-400 font-mono">
                                    <span x-text="proveedor.numero_cuenta"></span>
                                </div>
                                <div x-show="proveedor.tipo_cuenta" class="text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                          :class="proveedor.tipo_cuenta === 'corriente' ? 
                                                 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                                 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'">
                                        <span x-text="proveedor.tipo_cuenta === 'corriente' ? 'Cta. Corriente' : 'Cta. Ahorros'"></span>
                                    </span>
                                </div>
                                <div x-show="!proveedor.banco && !proveedor.numero_cuenta" class="text-gray-400 text-sm">
                                    Sin información bancaria
                                </div>
                                <div x-show="proveedor.direccion" class="text-xs text-gray-500 dark:text-gray-400 mt-2 border-t pt-1">
                                    <div class="flex items-start">
                                        <svg class="w-3 h-3 mr-1 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="max-w-xs truncate" :title="proveedor.direccion" x-text="proveedor.direccion"></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div x-text="formatDate(proveedor.created_at)"></div>
                            <div class="text-xs" x-text="formatTime(proveedor.created_at)"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="getStatusBadge(proveedor)">
                                <span x-text="getStatusText(proveedor)"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <span x-text="proveedor.productos_count || 0"></span>
                                    <span class="ml-1">productos</span>
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <span x-text="proveedor.compras_mes || 0"></span>
                                    <span class="ml-1">compras</span>
                                </span>
                            </div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="proveedor.total_facturado">
                                Total: <span class="font-medium" x-text="formatCurrency(proveedor.total_facturado)"></span>
                            </div>
                        </td>
                        @if($showActions)
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                @can('proveedores.ver')
                                <a :href="`/proveedores/${proveedor.id}`" 
                                   class="text-gestion-600 hover:text-gestion-900 dark:text-gestion-400 dark:hover:text-gestion-200 p-1"
                                   title="Ver proveedor">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('proveedores.editar')
                                <a :href="`/proveedores/${proveedor.id}/edit`" 
                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-200 p-1"
                                   title="Editar proveedor">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('productos.crear')
                                <a :href="`/productos/create?proveedor_id=${proveedor.id}`" 
                                   class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-200 p-1"
                                   title="Agregar producto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('inventario.entradas.crear')
                                <a :href="`/inventario/entradas/create?proveedor_id=${proveedor.id}`" 
                                   class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-200 p-1"
                                   title="Registrar compra">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5L12 8m0 5l3-3M9 21h6a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                    </svg>
                                </a>
                                @endcan
                                
                                <button @click="toggleStatus(proveedor)" 
                                        class="p-1"
                                        :class="proveedor.deleted_at ? 
                                               'text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-200' : 
                                               'text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-200'"
                                        :title="proveedor.deleted_at ? 'Activar proveedor' : 'Desactivar proveedor'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path x-show="!proveedor.deleted_at" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14L5 9m0 0l5-5m-5 5h14" />
                                        <path x-show="proveedor.deleted_at" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                
                                @can('proveedores.eliminar')
                                <button @click="confirmDelete(proveedor)" 
                                        x-show="proveedor.deleted_at"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200 p-1"
                                        title="Eliminar permanentemente">
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
                <tr x-show="filteredProveedores.length === 0 && !loading">
                    <td :colspan="@if($showActions) 9 @else 7 @endif" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay proveedores</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">
                                <span x-show="search || statusFilter || rucFilter || emailFilter || bancoFilter || dateFromFilter">
                                    No se encontraron proveedores con los filtros aplicados
                                </span>
                                <span x-show="!search && !statusFilter && !rucFilter && !emailFilter && !bancoFilter && !dateFromFilter">
                                    Comienza agregando tu primer proveedor
                                </span>
                            </p>
                            <div x-show="search || statusFilter || rucFilter || emailFilter || bancoFilter || dateFromFilter">
                                <button @click="clearFilters()" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Limpiar Filtros
                                </button>
                            </div>
                            <div x-show="!search && !statusFilter && !rucFilter && !emailFilter && !bancoFilter && !dateFromFilter">
                                @can('proveedores.crear')
                                <a href="{{ route('proveedores.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Agregar Proveedor
                                </a>
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($showActions)
    <!-- Bulk Actions -->
    <div x-show="selectedIds.length > 0" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform translate-y-1"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="px-6 py-3 bg-gestion-50 dark:bg-gestion-900/20 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    <span x-text="selectedIds.length"></span> proveedor(es) seleccionado(s)
                </span>
            </div>
            <div class="flex items-center space-x-3">
                @can('proveedores.editar')
                <button @click="bulkActivate()" 
                        class="inline-flex items-center px-3 py-1 border border-green-300 text-sm font-medium rounded text-green-700 bg-white hover:bg-green-50 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Activar
                </button>
                
                <button @click="bulkDeactivate()" 
                        class="inline-flex items-center px-3 py-1 border border-yellow-300 text-sm font-medium rounded text-yellow-700 bg-white hover:bg-yellow-50 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14L5 9m0 0l5-5m-5 5h14" />
                    </svg>
                    Desactivar
                </button>
                @endcan
                
                @can('proveedores.eliminar')
                <button @click="bulkDelete()" 
                        class="inline-flex items-center px-3 py-1 border border-red-300 text-sm font-medium rounded text-red-700 bg-white hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                </button>
                @endcan
                
                <button @click="exportSelected()" 
                        class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm font-medium rounded text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exportar
                </button>
                
                <button @click="selectedIds = []" 
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($showPagination)
    <!-- Pagination -->
    <div x-show="totalPages > 1" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                <button @click="previousPage()" 
                        :disabled="currentPage === 1"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Anterior
                </button>
                <button @click="nextPage()" 
                        :disabled="currentPage === totalPages"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Siguiente
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Mostrando
                        <span class="font-medium" x-text="((currentPage - 1) * parseInt(perPage)) + 1"></span>
                        a
                        <span class="font-medium" x-text="Math.min(currentPage * parseInt(perPage), filteredProveedores.length)"></span>
                        de
                        <span class="font-medium" x-text="filteredProveedores.length"></span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <!-- Previous Page Link -->
                        <button @click="previousPage()" 
                                :disabled="currentPage === 1"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Page Numbers -->
                        <template x-for="page in getPageNumbers()" :key="page">
                            <button @click="goToPage(page)" 
                                    :class="page === currentPage ? 
                                           'z-10 bg-gestion-50 border-gestion-500 text-gestion-600' : 
                                           'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                <span x-text="page"></span>
                            </button>
                        </template>

                        <!-- Next Page Link -->
                        <button @click="nextPage()" 
                                :disabled="currentPage === totalPages"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function proveedoresTable() {
    return {
        loading: false,
        proveedores: @json($proveedores),
        filteredProveedores: [],
        paginatedProveedores: [],
        
        // Filters
        search: '',
        statusFilter: '',
        rucFilter: '',
        emailFilter: '',
        bancoFilter: '',
        dateFromFilter: '',
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
            this.filteredProveedores = [...this.proveedores];
            this.applyFilters();
            console.log('Proveedores table initialized');
        },

        applyFilters() {
            let filtered = [...this.proveedores];

            // Search filter
            if (this.search) {
                const searchTerm = this.search.toLowerCase();
                filtered = filtered.filter(proveedor => 
                    proveedor.nombre.toLowerCase().includes(searchTerm) ||
                    (proveedor.ruc && proveedor.ruc.includes(searchTerm)) ||
                    (proveedor.telefono && proveedor.telefono.includes(searchTerm)) ||
                    (proveedor.email && proveedor.email.toLowerCase().includes(searchTerm)) ||
                    (proveedor.direccion && proveedor.direccion.toLowerCase().includes(searchTerm)) ||
                    (proveedor.contacto && proveedor.contacto.toLowerCase().includes(searchTerm)) ||
                    (proveedor.banco && proveedor.banco.toLowerCase().includes(searchTerm))
                );
            }

            // Status filter (considerando soft deletes)
            if (this.statusFilter !== '') {
                filtered = filtered.filter(proveedor => {
                    const isActive = !proveedor.deleted_at;
                    return isActive.toString() === this.statusFilter;
                });
            }

            // RUC filter
            if (this.rucFilter !== '') {
                filtered = filtered.filter(proveedor => {
                    const hasRuc = proveedor.ruc ? '1' : '0';
                    return hasRuc === this.rucFilter;
                });
            }

            // Email filter
            if (this.emailFilter !== '') {
                filtered = filtered.filter(proveedor => {
                    const hasEmail = proveedor.email ? '1' : '0';
                    return hasEmail === this.emailFilter;
                });
            }

            // Banco filter
            if (this.bancoFilter !== '') {
                filtered = filtered.filter(proveedor => {
                    const hasBanco = (proveedor.banco || proveedor.numero_cuenta) ? '1' : '0';
                    return hasBanco === this.bancoFilter;
                });
            }

            // Date range filter
            if (this.dateFromFilter) {
                filtered = filtered.filter(proveedor => 
                    new Date(proveedor.created_at) >= new Date(this.dateFromFilter)
                );
            }

            this.filteredProveedores = filtered;
            this.applySorting();
            this.updatePagination();
        },

        applySorting() {
            this.filteredProveedores.sort((a, b) => {
                let aValue = a[this.sortField];
                let bValue = b[this.sortField];
                
                // Handle null/undefined values
                if (aValue == null) aValue = '';
                if (bValue == null) bValue = '';
                
                // Convert to string for comparison
                aValue = aValue.toString().toLowerCase();
                bValue = bValue.toString().toLowerCase();
                
                if (this.sortDirection === 'asc') {
                    return aValue.localeCompare(bValue);
                } else {
                    return bValue.localeCompare(aValue);
                }
            });
        },

        sort(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            this.applySorting();
            this.updatePagination();
        },

        getSortIcon(field) {
            if (this.sortField !== field) return 'text-gray-400';
            return this.sortDirection === 'asc' ? 'text-gestion-600 transform rotate-180' : 'text-gestion-600';
        },

        updatePagination() {
            this.totalPages = Math.ceil(this.filteredProveedores.length / parseInt(this.perPage));
            this.currentPage = Math.min(this.currentPage, this.totalPages || 1);
            this.updatePaginatedProveedores();
        },

        updatePaginatedProveedores() {
            const start = (this.currentPage - 1) * parseInt(this.perPage);
            const end = start + parseInt(this.perPage);
            this.paginatedProveedores = this.filteredProveedores.slice(start, end);
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.updatePaginatedProveedores();
            }
        },

        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.updatePaginatedProveedores();
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.updatePaginatedProveedores();
            }
        },

        getPageNumbers() {
            const current = this.currentPage;
            const total = this.totalPages;
            const pages = [];
            
            if (total <= 7) {
                for (let i = 1; i <= total; i++) {
                    pages.push(i);
                }
            } else {
                if (current <= 4) {
                    for (let i = 1; i <= 5; i++) pages.push(i);
                    pages.push('...');
                    pages.push(total);
                } else if (current >= total - 3) {
                    pages.push(1);
                    pages.push('...');
                    for (let i = total - 4; i <= total; i++) pages.push(i);
                } else {
                    pages.push(1);
                    pages.push('...');
                    for (let i = current - 1; i <= current + 1; i++) pages.push(i);
                    pages.push('...');
                    pages.push(total);
                }
            }
            
            return pages.filter(page => page !== '...' || pages.indexOf(page) === pages.lastIndexOf(page));
        },

        // Selection methods
        toggleSelect(id) {
            const index = this.selectedIds.indexOf(id);
            if (index > -1) {
                this.selectedIds.splice(index, 1);
            } else {
                this.selectedIds.push(id);
            }
        },

        toggleSelectAll() {
            if (this.selectedIds.length === this.paginatedProveedores.length) {
                this.selectedIds = [];
            } else {
                this.selectedIds = this.paginatedProveedores.map(proveedor => proveedor.id);
            }
        },

        // Utility methods
        getInitials(name) {
            return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        formatTime(date) {
            return new Date(date).toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        getStatusBadge(proveedor) {
            if (proveedor.deleted_at) {
                return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            }
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        },

        getStatusText(proveedor) {
            return proveedor.deleted_at ? 'Inactivo' : 'Activo';
        },

        clearFilters() {
            this.search = '';
            this.statusFilter = '';
            this.rucFilter = '';
            this.emailFilter = '';
            this.bancoFilter = '';
            this.dateFromFilter = '';
            this.showAdvancedFilters = false;
            this.currentPage = 1;
            this.applyFilters();
        },

        // Actions
        async toggleStatus(proveedor) {
            const action = proveedor.deleted_at ? 'restore' : 'deactivate';
            const actionText = proveedor.deleted_at ? 'activar' : 'desactivar';
            
            const result = await Swal.fire({
                title: `¿${actionText.charAt(0).toUpperCase() + actionText.slice(1)} proveedor?`,
                text: `¿Estás seguro de que deseas ${actionText} a ${proveedor.nombre}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: proveedor.deleted_at ? '#059669' : '#f59e0b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: `Sí, ${actionText}`,
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/proveedores/${proveedor.id}/${action}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        // Update local data
                        const proveedorIndex = this.proveedores.findIndex(p => p.id === proveedor.id);
                        if (proveedorIndex !== -1) {
                            this.proveedores[proveedorIndex] = data.proveedor;
                        }
                        this.applyFilters();
                        this.showSuccess(`Proveedor ${actionText}do correctamente`);
                    } else {
                        throw new Error(`Error al ${actionText} proveedor`);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showError(`Error al ${actionText} el proveedor`);
                }
            }
        },

        async confirmDelete(proveedor) {
            const result = await Swal.fire({
                title: '¿Eliminar proveedor permanentemente?',
                text: `¿Estás seguro de que deseas eliminar permanentemente a ${proveedor.nombre}? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                await this.deleteProveedor(proveedor.id);
            }
        },

        async deleteProveedor(id) {
            try {
                const response = await fetch(`/proveedores/${id}/force-delete`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });

                if (response.ok) {
                    // Remove from local data
                    this.proveedores = this.proveedores.filter(proveedor => proveedor.id !== id);
                    this.applyFilters();
                    this.showSuccess('Proveedor eliminado permanentemente');
                } else {
                    throw new Error('Error al eliminar proveedor');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showError('Error al eliminar el proveedor');
            }
        },

        async bulkActivate() {
            if (this.selectedIds.length === 0) return;

            const result = await Swal.fire({
                title: '¿Activar proveedores seleccionados?',
                text: `¿Estás seguro de que deseas activar ${this.selectedIds.length} proveedor(es)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, activar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                await this.bulkAction('activate');
            }
        },

        async bulkDeactivate() {
            if (this.selectedIds.length === 0) return;

            const result = await Swal.fire({
                title: '¿Desactivar proveedores seleccionados?',
                text: `¿Estás seguro de que deseas desactivar ${this.selectedIds.length} proveedor(es)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                await this.bulkAction('deactivate');
            }
        },

        async bulkDelete() {
            if (this.selectedIds.length === 0) return;

            const result = await Swal.fire({
                title: '¿Eliminar proveedores seleccionados?',
                text: `¿Estás seguro de que deseas eliminar permanentemente ${this.selectedIds.length} proveedor(es)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                await this.bulkAction('delete');
            }
        },

        async bulkAction(action) {
            try {
                const response = await fetch(`/proveedores/bulk-${action}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        ids: this.selectedIds
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    if (action === 'delete') {
                        // Remove from local data
                        this.proveedores = this.proveedores.filter(proveedor => !this.selectedIds.includes(proveedor.id));
                    } else {
                        // Update local data
                        data.proveedores.forEach(updatedProveedor => {
                            const proveedorIndex = this.proveedores.findIndex(p => p.id === updatedProveedor.id);
                            if (proveedorIndex !== -1) {
                                this.proveedores[proveedorIndex] = updatedProveedor;
                            }
                        });
                    }
                    
                    this.selectedIds = [];
                    this.applyFilters();
                    this.showSuccess(`Proveedores ${action === 'activate' ? 'activados' : action === 'deactivate' ? 'desactivados' : 'eliminados'} correctamente`);
                } else {
                    throw new Error(`Error al ${action} proveedores`);
                }
            } catch (error) {
                console.error('Error:', error);
                this.showError(`Error al ${action} los proveedores`);
            }
        },

        exportSelected() {
            if (this.selectedIds.length === 0) {
                this.showError('Selecciona al menos un proveedor para exportar');
                return;
            }

            const params = new URLSearchParams({
                ids: this.selectedIds.join(','),
                format: 'excel'
            });

            window.location.href = `/proveedores/export?${params}`;
            this.showSuccess('Exportación iniciada');
        },

        showSuccess(message) {
            Swal.fire({
                title: '¡Éxito!',
                text: message,
                icon: 'success',
                timer: 3000,
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
        }
    }
}
</script>
@endpush