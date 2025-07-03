{{-- resources/views/modules/clientes/partials/table.blade.php --}}
@props([
    'clientes' => collect(),
    'showActions' => true,
    'showFilters' => true,
    'showPagination' => true,
    'tableId' => 'clientes-table'
])

<div x-data="clientesTable()" x-init="init()" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
    
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
                           placeholder="Buscar por nombre, teléfono o email..."
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Documento</label>
                <select x-model="documentTypeFilter" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos</option>
                    <option value="dni">DNI</option>
                    <option value="ruc">RUC</option>
                    <option value="pasaporte">Pasaporte</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de Registro</label>
                <input type="date" 
                       x-model="dateFromFilter"
                       @change="applyFilters()"
                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hasta</label>
                <input type="date" 
                       x-model="dateToFilter"
                       @change="applyFilters()"
                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
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
            Cargando clientes...
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
                               :checked="selectedIds.length === filteredClientes.length && filteredClientes.length > 0"
                               class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                    </th>
                    @endif
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <button @click="sort('nombre')" class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-300">
                            <span>Cliente</span>
                            <svg class="w-4 h-4" :class="getSortIcon('nombre')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        </button>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Contacto
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Documento
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
                <template x-for="cliente in paginatedClientes" :key="cliente.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        @if($showActions)
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" 
                                   :value="cliente.id"
                                   @change="toggleSelect(cliente.id)"
                                   :checked="selectedIds.includes(cliente.id)"
                                   class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gestion-100 dark:bg-gestion-900 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gestion-700 dark:text-gestion-300" 
                                              x-text="getInitials(cliente.nombre + ' ' + (cliente.apellido || ''))"></span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <span x-text="cliente.nombre + ' ' + (cliente.apellido || '')"></span>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        ID: <span x-text="cliente.id"></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <a :href="'tel:' + cliente.telefono" 
                                       class="hover:text-gestion-600 transition-colors" 
                                       x-text="cliente.telefono"></a>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400" x-show="cliente.email">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <a :href="'mailto:' + cliente.email" 
                                       class="hover:text-gestion-600 transition-colors" 
                                       x-text="cliente.email"></a>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div x-show="cliente.tipo_documento && cliente.numero_documento">
                                <div class="font-medium" x-text="(cliente.tipo_documento || '').toUpperCase()"></div>
                                <div x-text="cliente.numero_documento"></div>
                            </div>
                            <div x-show="!cliente.tipo_documento || !cliente.numero_documento" 
                                 class="text-gray-400">Sin documento</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div x-text="formatDate(cliente.created_at)"></div>
                            <div class="text-xs" x-text="formatTime(cliente.created_at)"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="cliente.activo ? 
                                         'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                         'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                                <span x-text="cliente.activo ? 'Activo' : 'Inactivo'"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <span x-text="cliente.equipos_count || 0"></span>
                                    <span class="ml-1">equipos</span>
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    <span x-text="cliente.reparaciones_count || 0"></span>
                                    <span class="ml-1">reparaciones</span>
                                </span>
                            </div>
                        </td>
                        @if($showActions)
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                @can('clientes.ver')
                                <a :href="`/clientes/${cliente.id}`" 
                                   class="text-gestion-600 hover:text-gestion-900 dark:text-gestion-400 dark:hover:text-gestion-200 p-1"
                                   title="Ver cliente">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('clientes.editar')
                                <a :href="`/clientes/${cliente.id}/edit`" 
                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-200 p-1"
                                   title="Editar cliente">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('reparaciones.crear')
                                <a :href="`/reparaciones/create?cliente_id=${cliente.id}`" 
                                   class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-200 p-1"
                                   title="Nueva reparación">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('clientes.eliminar')
                                <button @click="confirmDelete(cliente)" 
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200 p-1"
                                        title="Eliminar cliente">
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
                <tr x-show="filteredClientes.length === 0 && !loading">
                    <td :colspan="@if($showActions) 8 @else 6 @endif" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m0 0V9a3 3 0 00-6 0v4.294z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay clientes</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">
                                <span x-show="search || statusFilter || documentTypeFilter || dateFromFilter || dateToFilter">
                                    No se encontraron clientes con los filtros aplicados
                                </span>
                                <span x-show="!search && !statusFilter && !documentTypeFilter && !dateFromFilter && !dateToFilter">
                                    Comienza agregando tu primer cliente
                                </span>
                            </p>
                            <div x-show="search || statusFilter || documentTypeFilter || dateFromFilter || dateToFilter">
                                <button @click="clearFilters()" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Limpiar Filtros
                                </button>
                            </div>
                            <div x-show="!search && !statusFilter && !documentTypeFilter && !dateFromFilter && !dateToFilter">
                                @can('clientes.crear')
                                <a href="{{ route('clientes.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Agregar Cliente
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
                    <span x-text="selectedIds.length"></span> cliente(s) seleccionado(s)
                </span>
            </div>
            <div class="flex items-center space-x-3">
                @can('clientes.eliminar')
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
                        <span class="font-medium" x-text="Math.min(currentPage * parseInt(perPage), filteredClientes.length)"></span>
                        de
                        <span class="font-medium" x-text="filteredClientes.length"></span>
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
function clientesTable() {
    return {
        loading: false,
        clientes: @json($clientes),
        filteredClientes: [],
        paginatedClientes: [],
        
        // Filters
        search: '',
        statusFilter: '',
        documentTypeFilter: '',
        dateFromFilter: '',
        dateToFilter: '',
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
            this.filteredClientes = [...this.clientes];
            this.applyFilters();
            console.log('Clientes table initialized');
        },

        applyFilters() {
            let filtered = [...this.clientes];

            // Search filter
            if (this.search) {
                const searchTerm = this.search.toLowerCase();
                filtered = filtered.filter(cliente => 
                    cliente.nombre.toLowerCase().includes(searchTerm) ||
                    (cliente.apellido && cliente.apellido.toLowerCase().includes(searchTerm)) ||
                    cliente.telefono.includes(searchTerm) ||
                    (cliente.email && cliente.email.toLowerCase().includes(searchTerm)) ||
                    (cliente.numero_documento && cliente.numero_documento.includes(searchTerm))
                );
            }

            // Status filter
            if (this.statusFilter !== '') {
                filtered = filtered.filter(cliente => 
                    cliente.activo.toString() === this.statusFilter
                );
            }

            // Document type filter
            if (this.documentTypeFilter) {
                filtered = filtered.filter(cliente => 
                    cliente.tipo_documento === this.documentTypeFilter
                );
            }

            // Date range filter
            if (this.dateFromFilter) {
                filtered = filtered.filter(cliente => 
                    new Date(cliente.created_at) >= new Date(this.dateFromFilter)
                );
            }
            if (this.dateToFilter) {
                filtered = filtered.filter(cliente => 
                    new Date(cliente.created_at) <= new Date(this.dateToFilter + 'T23:59:59')
                );
            }

            this.filteredClientes = filtered;
            this.applySorting();
            this.updatePagination();
        },

        applySorting() {
            this.filteredClientes.sort((a, b) => {
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
            this.totalPages = Math.ceil(this.filteredClientes.length / parseInt(this.perPage));
            this.currentPage = Math.min(this.currentPage, this.totalPages || 1);
            this.updatePaginatedClientes();
        },

        updatePaginatedClientes() {
            const start = (this.currentPage - 1) * parseInt(this.perPage);
            const end = start + parseInt(this.perPage);
            this.paginatedClientes = this.filteredClientes.slice(start, end);
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.updatePaginatedClientes();
            }
        },

        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.updatePaginatedClientes();
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.updatePaginatedClientes();
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
            if (this.selectedIds.length === this.paginatedClientes.length) {
                this.selectedIds = [];
            } else {
                this.selectedIds = this.paginatedClientes.map(cliente => cliente.id);
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

        clearFilters() {
            this.search = '';
            this.statusFilter = '';
            this.documentTypeFilter = '';
            this.dateFromFilter = '';
            this.dateToFilter = '';
            this.showAdvancedFilters = false;
            this.currentPage = 1;
            this.applyFilters();
        },

        // Actions
        async confirmDelete(cliente) {
            const result = await Swal.fire({
                title: '¿Eliminar cliente?',
                text: `¿Estás seguro de que deseas eliminar a ${cliente.nombre} ${cliente.apellido || ''}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                await this.deleteCliente(cliente.id);
            }
        },

        async deleteCliente(id) {
            try {
                const response = await fetch(`/clientes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });

                if (response.ok) {
                    // Remove from local data
                    this.clientes = this.clientes.filter(cliente => cliente.id !== id);
                    this.applyFilters();
                    this.showSuccess('Cliente eliminado correctamente');
                } else {
                    throw new Error('Error al eliminar cliente');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showError('Error al eliminar el cliente');
            }
        },

        async bulkDelete() {
            if (this.selectedIds.length === 0) return;

            const result = await Swal.fire({
                title: '¿Eliminar clientes seleccionados?',
                text: `¿Estás seguro de que deseas eliminar ${this.selectedIds.length} cliente(s)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('/clientes/bulk-delete', {
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
                        // Remove from local data
                        this.clientes = this.clientes.filter(cliente => !this.selectedIds.includes(cliente.id));
                        this.selectedIds = [];
                        this.applyFilters();
                        this.showSuccess('Clientes eliminados correctamente');
                    } else {
                        throw new Error('Error al eliminar clientes');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showError('Error al eliminar los clientes');
                }
            }
        },

        exportSelected() {
            if (this.selectedIds.length === 0) {
                this.showError('Selecciona al menos un cliente para exportar');
                return;
            }

            const params = new URLSearchParams({
                ids: this.selectedIds.join(','),
                format: 'excel'
            });

            window.location.href = `/clientes/export?${params}`;
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