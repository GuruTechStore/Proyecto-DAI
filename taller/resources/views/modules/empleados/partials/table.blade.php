{{-- resources/views/modules/empleados/partials/table.blade.php --}}
@props([
    'empleados' => collect(),
    'showActions' => true,
    'showFilters' => true,
    'showPagination' => true,
    'tableId' => 'empleados-table'
])

<div x-data="empleadosTable()" x-init="init()" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
    
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
                           placeholder="Buscar por nombre, DNI, teléfono o email..."
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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Especialidad</label>
                <select x-model="especialidadFilter" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todas las especialidades</option>
                    <option value="Técnico en Reparaciones">Técnico en Reparaciones</option>
                    <option value="Vendedor">Vendedor</option>
                    <option value="Administrador">Administrador</option>
                    <option value="Gerente">Gerente</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Usuario del Sistema</label>
                <select x-model="userStatusFilter" 
                        @change="applyFilters()"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos</option>
                    <option value="1">Con usuario</option>
                    <option value="0">Sin usuario</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de Contratación</label>
                <input type="date" 
                       x-model="dateFromFilter"
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
            Cargando empleados...
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
                               :checked="selectedIds.length === filteredEmpleados.length && filteredEmpleados.length > 0"
                               class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                    </th>
                    @endif
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <button @click="sort('nombres')" class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-300">
                            <span>Empleado</span>
                            <svg class="w-4 h-4" :class="getSortIcon('nombres')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        </button>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Contacto
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Especialidad
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Usuario Sistema
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <button @click="sort('fecha_contratacion')" class="flex items-center space-x-1 hover:text-gray-700 dark:hover:text-gray-300">
                            <span>Contratación</span>
                            <svg class="w-4 h-4" :class="getSortIcon('fecha_contratacion')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <template x-for="empleado in paginatedEmpleados" :key="empleado.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        @if($showActions)
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" 
                                   :value="empleado.id"
                                   @change="toggleSelect(empleado.id)"
                                   :checked="selectedIds.includes(empleado.id)"
                                   class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gestion-100 dark:bg-gestion-900 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gestion-700 dark:text-gestion-300" 
                                              x-text="getInitials(empleado.nombres + ' ' + empleado.apellidos)"></span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <span x-text="empleado.nombres + ' ' + empleado.apellidos"></span>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        DNI: <span x-text="empleado.dni"></span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <div class="flex items-center" x-show="empleado.telefono">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <a :href="'tel:' + empleado.telefono" 
                                       class="hover:text-gestion-600 transition-colors" 
                                       x-text="empleado.telefono"></a>
                                </div>
                                <div x-show="!empleado.telefono" class="text-gray-400 text-sm">Sin teléfono</div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400" x-show="empleado.email">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <a :href="'mailto:' + empleado.email" 
                                       class="hover:text-gestion-600 transition-colors" 
                                       x-text="empleado.email"></a>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="empleado.especialidad ? 
                                         'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                         'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'">
                                <span x-text="empleado.especialidad || 'Sin especialidad'"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div x-show="empleado.usuario">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="empleado.usuario?.activo ? 
                                                 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                                        <span x-text="empleado.usuario?.username"></span>
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span x-text="empleado.usuario?.tipo_usuario"></span>
                                </div>
                            </div>
                            <div x-show="!empleado.usuario">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    Sin usuario
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div x-text="formatDate(empleado.fecha_contratacion)"></div>
                            <div class="text-xs" x-text="getTimeWorking(empleado.fecha_contratacion)"></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="empleado.activo ? 
                                         'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                         'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                                <span x-text="empleado.activo ? 'Activo' : 'Inactivo'"></span>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    <span x-text="empleado.reparaciones_count || 0"></span>
                                    <span class="ml-1">reparaciones</span>
                                </span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <span x-text="empleado.ventas_count || 0"></span>
                                    <span class="ml-1">ventas</span>
                                </span>
                            </div>
                        </td>
                        @if($showActions)
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                @can('empleados.ver')
                                <a :href="`/empleados/${empleado.id}`" 
                                   class="text-gestion-600 hover:text-gestion-900 dark:text-gestion-400 dark:hover:text-gestion-200 p-1"
                                   title="Ver empleado">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('empleados.editar')
                                <a :href="`/empleados/${empleado.id}/edit`" 
                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-200 p-1"
                                   title="Editar empleado">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                @endcan
                                
                                @can('usuarios.crear')
                                <button @click="createUser(empleado)" 
                                        x-show="!empleado.usuario"
                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-200 p-1"
                                        title="Crear usuario del sistema">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                </button>
                                @endcan
                                
                                @can('empleados.eliminar')
                                <button @click="confirmDelete(empleado)" 
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200 p-1"
                                        title="Eliminar empleado">
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
                <tr x-show="filteredEmpleados.length === 0 && !loading">
                    <td :colspan="@if($showActions) 9 @else 7 @endif" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay empleados</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">
                                <span x-show="search || statusFilter || especialidadFilter || userStatusFilter || dateFromFilter">
                                    No se encontraron empleados con los filtros aplicados
                                </span>
                                <span x-show="!search && !statusFilter && !especialidadFilter && !userStatusFilter && !dateFromFilter">
                                    Comienza agregando tu primer empleado
                                </span>
                            </p>
                            <div x-show="search || statusFilter || especialidadFilter || userStatusFilter || dateFromFilter">
                                <button @click="clearFilters()" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Limpiar Filtros
                                </button>
                            </div>
                            <div x-show="!search && !statusFilter && !especialidadFilter && !userStatusFilter && !dateFromFilter">
                                @can('empleados.crear')
                                <a href="{{ route('empleados.create') }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Agregar Empleado
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
                    <span x-text="selectedIds.length"></span> empleado(s) seleccionado(s)
                </span>
            </div>
            <div class="flex items-center space-x-3">
                @can('empleados.eliminar')
                <button @click="bulkDelete()" 
                        class="inline-flex items-center px-3 py-1 border border-red-300 text-sm font-medium rounded text-red-700 bg-white hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                </button>
                @endcan
                
                @can('usuarios.crear')
                <button @click="bulkCreateUsers()" 
                        class="inline-flex items-center px-3 py-1 border border-green-300 text-sm font-medium rounded text-green-700 bg-white hover:bg-green-50 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Crear Usuarios
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
                        <span class="font-medium" x-text="Math.min(currentPage * parseInt(perPage), filteredEmpleados.length)"></span>
                        de
                        <span class="font-medium" x-text="filteredEmpleados.length"></span>
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
function empleadosTable() {
    return {
        loading: false,
        empleados: @json($empleados),
        filteredEmpleados: [],
        paginatedEmpleados: [],
        
        // Filters
        search: '',
        statusFilter: '',
        especialidadFilter: '',
        userStatusFilter: '',
        dateFromFilter: '',
        showAdvancedFilters: false,
        
        // Sorting
        sortField: 'nombres',
        sortDirection: 'asc',
        
        // Pagination
        currentPage: 1,
        perPage: '25',
        totalPages: 1,
        
        // Selection
        selectedIds: [],

        init() {
            this.filteredEmpleados = [...this.empleados];
            this.applyFilters();
            console.log('Empleados table initialized');
        },

        applyFilters() {
            let filtered = [...this.empleados];

            // Search filter
            if (this.search) {
                const searchTerm = this.search.toLowerCase();
                filtered = filtered.filter(empleado => 
                    empleado.nombres.toLowerCase().includes(searchTerm) ||
                    empleado.apellidos.toLowerCase().includes(searchTerm) ||
                    empleado.dni.includes(searchTerm) ||
                    (empleado.telefono && empleado.telefono.includes(searchTerm)) ||
                    (empleado.email && empleado.email.toLowerCase().includes(searchTerm)) ||
                    (empleado.especialidad && empleado.especialidad.toLowerCase().includes(searchTerm))
                );
            }

            // Status filter
            if (this.statusFilter !== '') {
                filtered = filtered.filter(empleado => 
                    empleado.activo.toString() === this.statusFilter
                );
            }

            // Especialidad filter
            if (this.especialidadFilter) {
                filtered = filtered.filter(empleado => 
                    empleado.especialidad === this.especialidadFilter
                );
            }

            // User status filter
            if (this.userStatusFilter !== '') {
                filtered = filtered.filter(empleado => {
                    const hasUser = empleado.usuario ? '1' : '0';
                    return hasUser === this.userStatusFilter;
                });
            }

            // Date range filter
            if (this.dateFromFilter) {
                filtered = filtered.filter(empleado => 
                    new Date(empleado.fecha_contratacion) >= new Date(this.dateFromFilter)
                );
            }

            this.filteredEmpleados = filtered;
            this.applySorting();
            this.updatePagination();
        },

        applySorting() {
            this.filteredEmpleados.sort((a, b) => {
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
            this.totalPages = Math.ceil(this.filteredEmpleados.length / parseInt(this.perPage));
            this.currentPage = Math.min(this.currentPage, this.totalPages || 1);
            this.updatePaginatedEmpleados();
        },

        updatePaginatedEmpleados() {
            const start = (this.currentPage - 1) * parseInt(this.perPage);
            const end = start + parseInt(this.perPage);
            this.paginatedEmpleados = this.filteredEmpleados.slice(start, end);
        },

        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.updatePaginatedEmpleados();
            }
        },

        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.updatePaginatedEmpleados();
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.updatePaginatedEmpleados();
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
            if (this.selectedIds.length === this.paginatedEmpleados.length) {
                this.selectedIds = [];
            } else {
                this.selectedIds = this.paginatedEmpleados.map(empleado => empleado.id);
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

        getTimeWorking(startDate) {
            const start = new Date(startDate);
            const now = new Date();
            const diffTime = Math.abs(now - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays < 30) {
                return `${diffDays} días`;
            } else if (diffDays < 365) {
                const months = Math.floor(diffDays / 30);
                return `${months} mes${months > 1 ? 'es' : ''}`;
            } else {
                const years = Math.floor(diffDays / 365);
                return `${years} año${years > 1 ? 's' : ''}`;
            }
        },

        clearFilters() {
            this.search = '';
            this.statusFilter = '';
            this.especialidadFilter = '';
            this.userStatusFilter = '';
            this.dateFromFilter = '';
            this.showAdvancedFilters = false;
            this.currentPage = 1;
            this.applyFilters();
        },

        // Actions
        async confirmDelete(empleado) {
            const result = await Swal.fire({
                title: '¿Eliminar empleado?',
                text: `¿Estás seguro de que deseas eliminar a ${empleado.nombres} ${empleado.apellidos}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                await this.deleteEmpleado(empleado.id);
            }
        },

        async deleteEmpleado(id) {
            try {
                const response = await fetch(`/empleados/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });

                if (response.ok) {
                    // Remove from local data
                    this.empleados = this.empleados.filter(empleado => empleado.id !== id);
                    this.applyFilters();
                    this.showSuccess('Empleado eliminado correctamente');
                } else {
                    throw new Error('Error al eliminar empleado');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showError('Error al eliminar el empleado');
            }
        },

        async bulkDelete() {
            if (this.selectedIds.length === 0) return;

            const result = await Swal.fire({
                title: '¿Eliminar empleados seleccionados?',
                text: `¿Estás seguro de que deseas eliminar ${this.selectedIds.length} empleado(s)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('/empleados/bulk-delete', {
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
                        this.empleados = this.empleados.filter(empleado => !this.selectedIds.includes(empleado.id));
                        this.selectedIds = [];
                        this.applyFilters();
                        this.showSuccess('Empleados eliminados correctamente');
                    } else {
                        throw new Error('Error al eliminar empleados');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showError('Error al eliminar los empleados');
                }
            }
        },

        async createUser(empleado) {
            const result = await Swal.fire({
                title: 'Crear usuario del sistema',
                text: `¿Crear usuario para ${empleado.nombres} ${empleado.apellidos}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/empleados/${empleado.id}/create-user`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        // Update local data
                        const empleadoIndex = this.empleados.findIndex(e => e.id === empleado.id);
                        if (empleadoIndex !== -1) {
                            this.empleados[empleadoIndex].usuario = data.usuario;
                        }
                        this.applyFilters();
                        this.showSuccess('Usuario creado correctamente');
                    } else {
                        throw new Error('Error al crear usuario');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showError('Error al crear el usuario');
                }
            }
        },

        async bulkCreateUsers() {
            const employeesWithoutUser = this.selectedIds.filter(id => {
                const empleado = this.empleados.find(e => e.id === id);
                return empleado && !empleado.usuario;
            });

            if (employeesWithoutUser.length === 0) {
                this.showError('Los empleados seleccionados ya tienen usuario del sistema');
                return;
            }

            const result = await Swal.fire({
                title: 'Crear usuarios del sistema',
                text: `¿Crear usuarios para ${employeesWithoutUser.length} empleado(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('/empleados/bulk-create-users', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            ids: employeesWithoutUser
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        // Update local data
                        data.usuarios.forEach(usuario => {
                            const empleadoIndex = this.empleados.findIndex(e => e.id === usuario.empleado_id);
                            if (empleadoIndex !== -1) {
                                this.empleados[empleadoIndex].usuario = usuario;
                            }
                        });
                        this.selectedIds = [];
                        this.applyFilters();
                        this.showSuccess('Usuarios creados correctamente');
                    } else {
                        throw new Error('Error al crear usuarios');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showError('Error al crear los usuarios');
                }
            }
        },

        exportSelected() {
            if (this.selectedIds.length === 0) {
                this.showError('Selecciona al menos un empleado para exportar');
                return;
            }

            const params = new URLSearchParams({
                ids: this.selectedIds.join(','),
                format: 'excel'
            });

            window.location.href = `/empleados/export?${params}`;
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