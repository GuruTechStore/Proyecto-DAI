{{-- resources/views/modules/reparaciones/index.blade.php - PARTE 1 --}}
@extends('layouts.app')

@section('title', 'Gestión de Reparaciones')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span class="ml-2 text-sm font-medium text-gray-700">Reparaciones</span>
    </div>
</li>
@endsection

@push('styles')
<style>
    .fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .table-hover tbody tr:hover { background-color: rgb(249 250 251); }
    .dark .table-hover tbody tr:hover { background-color: rgb(31 41 55); }
    .status-badge { @apply px-2 py-1 text-xs font-semibold rounded-full; }
    .status-recibido { @apply bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200; }
    .status-diagnosticando { @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200; }
    .status-diagnosticado { @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200; }
    .status-reparando { @apply bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200; }
    .status-completado { @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200; }
    .status-entregado { @apply bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200; }
    .status-cancelado { @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200; }
    .priority-badge { @apply px-2 py-1 text-xs font-medium rounded; }
    .priority-baja { @apply bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200; }
    .priority-media { @apply bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200; }
    .priority-alta { @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200; }
    .priority-urgente { @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200; }
</style>
@endpush

@section('content')
<div x-data="reparacionesManager()" x-init="init()" class="space-y-6">
    
    <!-- Header con estadísticas -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Gestión de Reparaciones
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Administra todas las órdenes de reparación del taller
                    </p>
                </div>
                
                <div class="mt-4 lg:mt-0 flex space-x-3">
                    @can('reparaciones.crear')
                    <a href="{{ route('reparaciones.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Nueva Reparación
                    </a>
                    @endcan
                    
                    <button @click="showFilters = !showFilters"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z" />
                        </svg>
                        Filtros
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas rápidas -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Total de reparaciones -->
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total</p>
                            <p class="text-2xl font-bold" x-text="stats.total">0</p>
                        </div>
                        <div class="p-3 bg-blue-600 bg-opacity-50 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- En proceso -->
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium">En Proceso</p>
                            <p class="text-2xl font-bold" x-text="stats.en_proceso">0</p>
                        </div>
                        <div class="p-3 bg-yellow-600 bg-opacity-50 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Completadas -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Completadas</p>
                            <p class="text-2xl font-bold" x-text="stats.completadas">0</p>
                        </div>
                        <div class="p-3 bg-green-600 bg-opacity-50 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Urgentes -->
                <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-100 text-sm font-medium">Urgentes</p>
                            <p class="text-2xl font-bold" x-text="stats.urgentes">0</p>
                        </div>
                        <div class="p-3 bg-red-600 bg-opacity-50 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Panel de filtros -->
    <div x-show="showFilters" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Filtros de Búsqueda</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Búsqueda general -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" 
                               x-model="filters.search"
                               @input="debouncedSearch()"
                               placeholder="Código, cliente, equipo..."
                               class="pl-10 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                    </div>
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                    <select x-model="filters.estado" 
                            @change="loadReparaciones()"
                            class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="">Todos los estados</option>
                        <option value="recibido">Recibido</option>
                        <option value="diagnosticando">Diagnosticando</option>
                        <option value="diagnosticado">Diagnosticado</option>
                        <option value="reparando">Reparando</option>
                        <option value="completado">Completado</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <!-- Prioridad -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prioridad</label>
                    <select x-model="filters.prioridad" 
                            @change="loadReparaciones()"
                            class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="">Todas las prioridades</option>
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="urgente">Urgente</option>
                    </select>
                </div>

                <!-- Técnico asignado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Técnico</label>
                    <select x-model="filters.tecnico" 
                            @change="loadReparaciones()"
                            class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="">Todos los técnicos</option>
                        <template x-for="tecnico in tecnicos" :key="tecnico.id">
                            <option :value="tecnico.id" x-text="tecnico.nombre + ' ' + tecnico.apellido"></option>
                        </template>
                    </select>
                </div>

            </div>
            
            <!-- Filtros de fecha -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha desde</label>
                    <input type="date" 
                           x-model="filters.fecha_desde"
                           @change="loadReparaciones()"
                           class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha hasta</label>
                    <input type="date" 
                           x-model="filters.fecha_hasta"
                           @change="loadReparaciones()"
                           class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                </div>

                <div class="flex items-end">
                    <button @click="clearFilters()"
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Limpiar Filtros
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Tabla de reparaciones -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        
        <!-- Header de tabla -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Lista de Reparaciones
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        <span x-text="pagination.total">0</span> reparaciones encontradas
                    </p>
                </div>
                
                <!-- Acciones en lote -->
                <div x-show="selectedReparaciones.length > 0" class="mt-3 sm:mt-0">
                    <div class="flex space-x-2">
                        <button @click="showBulkActions = true"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <span x-text="selectedReparaciones.length"></span> seleccionadas
                            <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Loading state -->
        <div x-show="loading" class="px-6 py-12 text-center">
            <div class="inline-flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gestion-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Cargando reparaciones...
            </div>
        </div>
        
        <!-- Tabla -->
        <div x-show="!loading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-hover">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="relative px-6 py-3">
                            <input type="checkbox" 
                                   @change="toggleAllReparaciones($event.target.checked)"
                                   class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                        </th>
                        <th scope="col" 
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                            @click="sortBy('codigo_ticket')">
                            <div class="flex items-center space-x-1">
                                <span>Código</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th scope="col" 
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                            @click="sortBy('cliente')">
                            <div class="flex items-center space-x-1">
                                <span>Cliente</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Equipo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Prioridad
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Técnico
                        </th>
                        <th scope="col" 
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                            @click="sortBy('created_at')">
                            <div class="flex items-center space-x-1">
                                <span>Fecha</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                            </div>
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="reparacion in reparaciones" :key="reparacion.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" 
                                       :value="reparacion.id"
                                       @change="toggleReparacionSelection(reparacion.id, $event.target.checked)"
                                       class="h-4 w-4 rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gestion-100 dark:bg-gestion-800 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <a :href="`/reparaciones/${reparacion.id}`" 
                                               class="hover:text-gestion-600 transition-colors"
                                               x-text="reparacion.codigo_ticket"></a>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="'REP-' + String(reparacion.id).padStart(6, '0')"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="reparacion.cliente?.nombre + ' ' + reparacion.cliente?.apellido"></div>
                                <div class="text-sm text-gray-500 dark:text-gray-400" x-text="reparacion.cliente?.telefono"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    <div x-text="reparacion.tipo_equipo"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="reparacion.marca + ' ' + reparacion.modelo"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="status-badge"
                                      :class="'status-' + reparacion.estado"
                                      x-text="reparacion.estado.charAt(0).toUpperCase() + reparacion.estado.slice(1)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="priority-badge"
                                      :class="'priority-' + reparacion.prioridad"
                                      x-text="reparacion.prioridad.charAt(0).toUpperCase() + reparacion.prioridad.slice(1)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div x-show="reparacion.tecnico" class="flex items-center">
                                    <div class="flex-shrink-0 h-6 w-6">
                                        <div class="h-6 w-6 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300"
                                                  x-text="reparacion.tecnico?.nombre?.charAt(0) + reparacion.tecnico?.apellido?.charAt(0)"></span>
                                        </div>
                                    </div>
                                    <div class="ml-2">
                                        <div class="text-sm text-gray-900 dark:text-white" x-text="reparacion.tecnico?.nombre + ' ' + reparacion.tecnico?.apellido"></div>
                                    </div>
                                </div>
                                <span x-show="!reparacion.tecnico" class="text-sm text-gray-500 dark:text-gray-400">Sin asignar</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div x-text="new Date(reparacion.created_at).toLocaleDateString()"></div>
                                <div class="text-xs" x-text="new Date(reparacion.created_at).toLocaleTimeString()"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    
                                    <!-- Ver detalles -->
                                    <a :href="`/reparaciones/${reparacion.id}`" 
                                       class="text-gestion-600 hover:text-gestion-900 dark:hover:text-gestion-400 transition-colors"
                                       title="Ver detalles">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <!-- Editar -->
                                    @can('reparaciones.editar')
                                    <a :href="`/reparaciones/${reparacion.id}/edit`" 
                                       class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400 transition-colors"
                                       title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    @endcan

                                    <!-- Cambiar estado -->
                                    @can('reparaciones.editar')
                                    <button @click="showChangeStatusModal(reparacion)" 
                                            class="text-orange-600 hover:text-orange-900 dark:hover:text-orange-400 transition-colors"
                                            title="Cambiar estado">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                    @endcan

                                    <!-- Menú de opciones -->
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" 
                                                @click.away="open = false"
                                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>
                                        
                                        <div x-show="open" 
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                            <div class="py-1">
                                                <a :href="`/reparaciones/${reparacion.id}/print`" 
                                                   class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                    </svg>
                                                    Imprimir Ticket
                                                </a>
                                                
                                                @can('reparaciones.editar')
                                                <button @click="duplicateReparacion(reparacion)" 
                                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                    Duplicar
                                                </button>
                                                @endcan
                                                
                                                @can('reparaciones.eliminar')
                                                <button @click="confirmDeleteReparacion(reparacion)" 
                                                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Eliminar
                                                </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Estado vacío -->
                    <tr x-show="!loading && reparaciones.length === 0">
                        <td colspan="9" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay reparaciones</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comienza creando una nueva orden de reparación.</p>
                            @can('reparaciones.crear')
                            <div class="mt-6">
                                <a href="{{ route('reparaciones.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Nueva Reparación
                                </a>
                            </div>
                            @endcan
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div x-show="pagination.last_page > 1" class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <button @click="previousPage()" 
                        :disabled="!pagination.prev_page_url"
                        :class="pagination.prev_page_url ? 'text-gray-700 bg-white hover:bg-gray-50' : 'text-gray-400 bg-gray-100 cursor-not-allowed'"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md">
                    Anterior
                </button>
                <button @click="nextPage()" 
                        :disabled="!pagination.next_page_url"
                        :class="pagination.next_page_url ? 'text-gray-700 bg-white hover:bg-gray-50' : 'text-gray-400 bg-gray-100 cursor-not-allowed'"
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md">
                    Siguiente
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Mostrando 
                        <span class="font-medium" x-text="pagination.from"></span>
                        a 
                        <span class="font-medium" x-text="pagination.to"></span>
                        de 
                        <span class="font-medium" x-text="pagination.total"></span>
                        resultados
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <!-- Botón anterior -->
                        <button @click="previousPage()" 
                                :disabled="!pagination.prev_page_url"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        
                        <!-- Números de página -->
                        <template x-for="page in paginationPages" :key="page">
                            <button @click="goToPage(page)"
                                    :class="page === pagination.current_page ? 
                                        'z-10 bg-gestion-50 border-gestion-500 text-gestion-600' : 
                                        'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600'"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                    x-text="page">
                            </button>
                        </template>
                        
                        <!-- Botón siguiente -->
                        <button @click="nextPage()" 
                                :disabled="!pagination.next_page_url"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function reparacionesManager() {
    return {
        // Estados
        loading: false,
        showFilters: false,
        showBulkActions: false,
        
        // Datos
        reparaciones: [],
        tecnicos: [],
        selectedReparaciones: [],
        
        // Filtros
        filters: {
            search: '',
            estado: '',
            prioridad: '',
            tecnico: '',
            fecha_desde: '',
            fecha_hasta: ''
        },
        
        // Ordenamiento
        sortField: 'created_at',
        sortDirection: 'desc',
        
        // Paginación
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
            from: 0,
            to: 0,
            prev_page_url: null,
            next_page_url: null
        },
        
        // Estadísticas
        stats: {
            total: 0,
            en_proceso: 0,
            completadas: 0,
            urgentes: 0
        },
        
        // Búsqueda con debounce
        searchTimeout: null,
        
        async init() {
            await this.loadTecnicos();
            await this.loadReparaciones();
            await this.loadStats();
        },
        
        async loadReparaciones() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    sort_field: this.sortField,
                    sort_direction: this.sortDirection,
                    ...this.filters
                });
                
                const response = await fetch(`/reparaciones/api/search?${params}`);
                const data = await response.json();
                
                this.reparaciones = data.data;
                this.pagination = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    per_page: data.per_page,
                    total: data.total,
                    from: data.from,
                    to: data.to,
                    prev_page_url: data.prev_page_url,
                    next_page_url: data.next_page_url
                };
            } catch (error) {
                console.error('Error loading reparaciones:', error);
                this.showNotification('Error al cargar las reparaciones', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        async loadTecnicos() {
            try {
                const response = await fetch('/empleados/api/tecnicos');
                const data = await response.json();
                this.tecnicos = data;
            } catch (error) {
                console.error('Error loading tecnicos:', error);
            }
        },
        
        async loadStats() {
            try {
                const response = await fetch('/reparaciones/api/stats');
                const data = await response.json();
                this.stats = data;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },
        
        debouncedSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.pagination.current_page = 1;
                this.loadReparaciones();
            }, 500);
        },
        
        clearFilters() {
            this.filters = {
                search: '',
                estado: '',
                prioridad: '',
                tecnico: '',
                fecha_desde: '',
                fecha_hasta: ''
            };
            this.pagination.current_page = 1;
            this.loadReparaciones();
        },
        
        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            this.loadReparaciones();
        },
        
        // Paginación
        get paginationPages() {
            const pages = [];
            const start = Math.max(1, this.pagination.current_page - 2);
            const end = Math.min(this.pagination.last_page, this.pagination.current_page + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },
        
        goToPage(page) {
            this.pagination.current_page = page;
            this.loadReparaciones();
        },
        
        previousPage() {
            if (this.pagination.prev_page_url) {
                this.pagination.current_page--;
                this.loadReparaciones();
            }
        },
        
        nextPage() {
            if (this.pagination.next_page_url) {
                this.pagination.current_page++;
                this.loadReparaciones();
            }
        },
        
        // Selección
        toggleAllReparaciones(checked) {
            if (checked) {
                this.selectedReparaciones = this.reparaciones.map(r => r.id);
            } else {
                this.selectedReparaciones = [];
            }
        },
        
        toggleReparacionSelection(id, checked) {
            if (checked) {
                if (!this.selectedReparaciones.includes(id)) {
                    this.selectedReparaciones.push(id);
                }
            } else {
                this.selectedReparaciones = this.selectedReparaciones.filter(selectedId => selectedId !== id);
            }
        },
        
        // Acciones
        showChangeStatusModal(reparacion) {
            // Implementar modal de cambio de estado
            console.log('Change status for:', reparacion);
        },
        
        async duplicateReparacion(reparacion) {
            if (confirm('¿Deseas duplicar esta reparación?')) {
                try {
                    const response = await fetch(`/reparaciones/${reparacion.id}/duplicate`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (response.ok) {
                        this.showNotification('Reparación duplicada correctamente', 'success');
                        this.loadReparaciones();
                    }
                } catch (error) {
                    this.showNotification('Error al duplicar la reparación', 'error');
                }
            }
        },
        
        async confirmDeleteReparacion(reparacion) {
            if (confirm('¿Estás seguro de que deseas eliminar esta reparación? Esta acción no se puede deshacer.')) {
                try {
                    const response = await fetch(`/reparaciones/${reparacion.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (response.ok) {
                        this.showNotification('Reparación eliminada correctamente', 'success');
                        this.loadReparaciones();
                        this.loadStats();
                    }
                } catch (error) {
                    this.showNotification('Error al eliminar la reparación', 'error');
                }
            }
        },
        
        showNotification(message, type = 'info') {
            // Implementar sistema de notificaciones
            console.log(`${type}: ${message}`);
        }
    }
}
</script>
@endpush
@endsection