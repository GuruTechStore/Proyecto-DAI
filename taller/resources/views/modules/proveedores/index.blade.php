{{-- resources/views/modules/proveedores/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gestión de Proveedores')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12" />
        </svg>
        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Proveedores</span>
    </div>
</li>
@endsection

@push('styles')
<style>
    .fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .table-hover tbody tr:hover { background-color: rgb(249 250 251); }
    .dark .table-hover tbody tr:hover { background-color: rgb(31 41 55); }
    .loading-spinner { animation: spin 1s linear infinite; }
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')
<div x-data="proveedoresManager()" x-init="init()" class="space-y-6">
    
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Gestión de Proveedores
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Administra la información de todos tus proveedores
                    </p>
                </div>
                
                <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row gap-3">
                    @can('proveedores.crear')
                    <a href="{{ route('proveedores.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Proveedor
                    </a>
                    @endcan
                    
                    <button @click="exportData()" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar
                    </button>
                </div>
            </div>
        </div>
        
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
                        <input x-model="filters.search" 
                               @input.debounce.300ms="applyFilters()"
                               type="text" 
                               placeholder="Buscar por nombre, RUC o email..."
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div>
                    <select x-model="filters.status" 
                            @change="applyFilters()"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>
                
                <!-- Per Page -->
                <div>
                    <select x-model="filters.perPage" 
                            @change="applyFilters()"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="10">10 por página</option>
                        <option value="25">25 por página</option>
                        <option value="50">50 por página</option>
                        <option value="100">100 por página</option>
                    </select>
                </div>
                
            </div>
        </div>
        
        <!-- Stats -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Proveedores</p>
                            <p class="text-lg font-semibold text-blue-900 dark:text-blue-100" x-text="stats.total || '0'">0</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 dark:bg-green-800 rounded-lg">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">Activos</p>
                            <p class="text-lg font-semibold text-green-900 dark:text-green-100" x-text="stats.activos || '0'">0</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 dark:bg-yellow-800 rounded-lg">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Con RUC</p>
                            <p class="text-lg font-semibold text-yellow-900 dark:text-yellow-100" x-text="stats.con_ruc || '0'">0</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 dark:bg-purple-800 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V2a1 1 0 011-1h1a1 1 0 011 1v14a2 2 0 01-2 2H5a2 2 0 01-2-2V3a1 1 0 011-1h1a1 1 0 011 1v2m0 0h8m-8 0a1 1 0 00-1 1v12a1 1 0 001 1h8a1 1 0 001-1V5a1 1 0 00-1-1" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Agregados Este Mes</p>
                            <p class="text-lg font-semibold text-purple-900 dark:text-purple-100" x-text="stats.este_mes || '0'">0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Table Container -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        
        <!-- Loading Overlay -->
        <div x-show="loading" class="absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-75 flex items-center justify-center z-10 rounded-lg">
            <div class="flex items-center space-x-2">
                <div class="loading-spinner w-5 h-5 border-2 border-gestion-500 border-t-transparent rounded-full"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Cargando proveedores...</span>
            </div>
        </div>
        
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-hover">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th @click="sort('nombre')" 
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>Proveedor</span>
                                <svg class="w-3 h-3" :class="getSortIcon('nombre')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Contacto
                        </th>
                        <th @click="sort('ruc')" 
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>RUC</span>
                                <svg class="w-3 h-3" :class="getSortIcon('ruc')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Estado
                        </th>
                        <th @click="sort('created_at')" 
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>Fecha Registro</span>
                                <svg class="w-3 h-3" :class="getSortIcon('created_at')" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="proveedor in proveedores" :key="proveedor.id">
                        <tr class="fade-in">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gestion-100 dark:bg-gestion-800 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="proveedor.nombre"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="proveedor.email"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white" x-text="proveedor.contacto || '-'"></div>
                                <div class="text-sm text-gray-500 dark:text-gray-400" x-text="proveedor.telefono || '-'"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono text-gray-900 dark:text-white" x-text="proveedor.ruc || '-'"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="proveedor.activo ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'"
                                      x-text="proveedor.activo ? 'Activo' : 'Inactivo'">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div x-text="new Date(proveedor.created_at).toLocaleDateString('es-PE')"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    
                                    @can('proveedores.ver')
                                    <a :href="`{{ route('proveedores.show', '') }}/${proveedor.id}`" 
                                       class="text-gestion-600 hover:text-gestion-900 dark:text-gestion-400 dark:hover:text-gestion-300 transition-colors"
                                       title="Ver detalles">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @endcan
                                    
                                    @can('proveedores.editar')
                                    <a :href="`{{ route('proveedores.edit', '') }}/${proveedor.id}`" 
                                       class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                       title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    @endcan
                                    
                                    @can('proveedores.eliminar')
                                    <button @click="confirmDelete(proveedor)" 
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                            title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    @endcan
                                    
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    <!-- Empty State -->
                    <tr x-show="!loading && proveedores.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay proveedores</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    <span x-show="Object.values(filters).some(f => f !== '' && f !== 10)">
                                        No se encontraron proveedores con los filtros aplicados.
                                    </span>
                                    <span x-show="!Object.values(filters).some(f => f !== '' && f !== 10)">
                                        Comienza agregando tu primer proveedor.
                                    </span>
                                </p>
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
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div x-show="!loading && proveedores.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            @include('modules.proveedores.partials.pagination')
        </div>
        
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" 
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div x-transition:enter="transition-all ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition-all ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Eliminar Proveedor</h3>
                    </div>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        ¿Estás seguro de que deseas eliminar al proveedor 
                        <strong x-text="proveedorToDelete?.nombre" class="font-semibold text-gray-900 dark:text-white"></strong>?
                    </p>
                    <p class="text-sm text-red-600 dark:text-red-400 mt-2">
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button @click="closeDeleteModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Cancelar
                    </button>
                    <button @click="deleteProveedor()" 
                            :disabled="deleting"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-md transition-colors">
                        <span x-show="!deleting">Eliminar</span>
                        <span x-show="deleting" class="flex items-center">
                            <div class="loading-spinner w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></div>
                            Eliminando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
</div>

@push('scripts')
<script>
function proveedoresManager() {
    return {
        // State
        proveedores: [],
        loading: false,
        deleting: false,
        showDeleteModal: false,
        proveedorToDelete: null,
        
        // Filters
        filters: {
            search: '',
            status: '',
            perPage: 10
        },
        
        // Sorting
        sortField: 'nombre',
        sortDirection: 'asc',
        
        // Pagination
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
            from: 0,
            to: 0
        },
        
        // Stats
        stats: {
            total: 0,
            activos: 0,
            con_ruc: 0,
            este_mes: 0
        },
        
        // Methods
        async init() {
            await this.loadStats();
            await this.loadProveedores();
        },
        
        async loadProveedores(page = 1) {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: this.filters.perPage,
                    search: this.filters.search,
                    status: this.filters.status,
                    sort_field: this.sortField,
                    sort_direction: this.sortDirection
                });
                
                const response = await fetch(`/api/proveedores?${params}`);
                const data = await response.json();
                
                this.proveedores = data.data;
                this.pagination = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    per_page: data.per_page,
                    total: data.total,
                    from: data.from,
                    to: data.to
                };
            } catch (error) {
                console.error('Error loading proveedores:', error);
                this.showError('Error al cargar los proveedores');
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                const response = await fetch('/api/proveedores/stats');
                const data = await response.json();
                this.stats = data;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async applyFilters() {
            await this.loadProveedores(1);
        },

        async sort(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            await this.loadProveedores(1);
        },

        getSortIcon(field) {
            if (this.sortField !== field) return 'text-gray-400';
            return this.sortDirection === 'asc' ? 
                'text-gestion-500 transform rotate-180' : 
                'text-gestion-500';
        },

        async goToPage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                await this.loadProveedores(page);
            }
        },

        confirmDelete(proveedor) {
            this.proveedorToDelete = proveedor;
            this.showDeleteModal = true;
        },

        closeDeleteModal() {
            this.showDeleteModal = false;
            this.proveedorToDelete = null;
        },

        async deleteProveedor() {
            if (!this.proveedorToDelete) return;
            
            this.deleting = true;
            try {
                const response = await fetch(`/proveedores/${this.proveedorToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    this.showSuccess('Proveedor eliminado correctamente');
                    await this.loadProveedores();
                    await this.loadStats();
                } else {
                    const errorData = await response.json();
                    this.showError(errorData.message || 'Error al eliminar el proveedor');
                }
            } catch (error) {
                console.error('Error deleting proveedor:', error);
                this.showError('Error al eliminar el proveedor');
            } finally {
                this.deleting = false;
                this.closeDeleteModal();
            }
        },

        async exportData() {
            try {
                const response = await fetch('/api/proveedores/export', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    }
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `proveedores_${new Date().toISOString().split('T')[0]}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    this.showSuccess('Datos exportados correctamente');
                } else {
                    this.showError('Error al exportar los datos');
                }
            } catch (error) {
                console.error('Error exporting data:', error);
                this.showError('Error al exportar los datos');
            }
        },

        showSuccess(message) {
            // Implementar notificación de éxito
            if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
                Alpine.store('notifications').add({
                    type: 'success',
                    message: message
                });
            } else {
                alert(message);
            }
        },

        showError(message) {
            // Implementar notificación de error
            if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
                Alpine.store('notifications').add({
                    type: 'error',
                    message: message
                });
            } else {
                alert(message);
            }
        }
    };
}
</script>
@endpush
@endsection