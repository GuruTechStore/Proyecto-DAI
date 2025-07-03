{{-- resources/views/modules/usuarios/index.blade.php - PARTE 1 --}}
@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
        </svg>
        <span class="ml-2 text-sm font-medium text-gray-700">Usuarios</span>
    </div>
</li>
@endsection

@push('styles')
<style>
    .fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .table-hover tbody tr:hover { background-color: rgb(249 250 251); }
    .dark .table-hover tbody tr:hover { background-color: rgb(31 41 55); }
    .role-badge { @apply px-2 py-1 text-xs font-semibold rounded-full; }
    .role-super-admin { @apply bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200; }
    .role-gerente { @apply bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200; }
    .role-supervisor { @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200; }
    .role-vendedor { @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200; }
    .role-tecnico { @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200; }
    .role-default { @apply bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200; }
</style>
@endpush

@section('content')
<div x-data="usuariosManager()" x-init="init()" class="space-y-6">
    
    <!-- Header con estadísticas -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Gestión de Usuarios
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Administra usuarios, roles y permisos del sistema
                    </p>
                </div>
                
                <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row gap-3">
                    @can('usuarios.crear')
                    <a href="{{ route('admin.users.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Usuario
                    </a>
                    @endcan
                    
                    <button @click="exportData()" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas rápidas -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-semibold text-gestion-600" x-text="stats.total">0</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Usuarios</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-semibold text-green-600" x-text="stats.activos">0</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Activos</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-semibold text-yellow-600" x-text="stats.conectados">0</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">En Línea</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-semibold text-red-600" x-text="stats.bloqueados">0</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Bloqueados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Búsqueda -->
                <div class="col-span-1 md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Buscar usuario
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" 
                               id="search" 
                               x-model="filters.search"
                               @input.debounce.300ms="searchUsers()"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Buscar por usuario, email o nombre...">
                    </div>
                </div>
                
                <!-- Filtro por rol -->
                <div>
                    <label for="role_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Rol
                    </label>
                    <select id="role_filter" 
                            x-model="filters.role"
                            @change="applyFilters()"
                            class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="">Todos los roles</option>
                        <option value="Super Admin">Super Admin</option>
                        <option value="Gerente">Gerente</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Vendedor">Vendedor</option>
                        <option value="Técnico">Técnico</option>
                    </select>
                </div>
                
                <!-- Filtro por estado -->
                <div>
                    <label for="status_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Estado
                    </label>
                    <select id="status_filter" 
                            x-model="filters.status"
                            @change="applyFilters()"
                            class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-gestion-500 focus:border-gestion-500">
                        <option value="">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                        <option value="blocked">Bloqueados</option>
                        <option value="unverified">Sin verificar</option>
                    </select>
                </div>
            </div>
            
            <!-- Filtros adicionales -->
            <div x-show="showAdvancedFilters" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-1 transform scale-100"
                 class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <!-- Fecha de creación desde -->
                    <div>
                        <label for="created_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Creado desde
                        </label>
                        <input type="date" 
                               id="created_from" 
                               x-model="filters.created_from"
                               @change="applyFilters()"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-gestion-500 focus:border-gestion-500">
                    </div>
                    
                    <!-- Fecha de creación hasta -->
                    <div>
                        <label for="created_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Creado hasta
                        </label>
                        <input type="date" 
                               id="created_to" 
                               x-model="filters.created_to"
                               @change="applyFilters()"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-gestion-500 focus:border-gestion-500">
                    </div>
                    
                    <!-- 2FA -->
                    <div>
                        <label for="has_2fa" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Autenticación 2FA
                        </label>
                        <select id="has_2fa" 
                                x-model="filters.has_2fa"
                                @change="applyFilters()"
                                class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="">Todos</option>
                            <option value="true">Con 2FA</option>
                            <option value="false">Sin 2FA</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Toggle filtros avanzados -->
            <div class="mt-4 flex justify-between items-center">
                <button @click="showAdvancedFilters = !showAdvancedFilters"
                        class="text-sm text-gestion-600 hover:text-gestion-700 font-medium">
                    <span x-show="!showAdvancedFilters">Mostrar filtros avanzados</span>
                    <span x-show="showAdvancedFilters">Ocultar filtros avanzados</span>
                </button>
                
                <button @click="clearFilters()"
                        class="text-sm text-gray-600 hover:text-gray-700 font-medium">
                    Limpiar filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        
        <!-- Header de tabla -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Lista de Usuarios
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        <span x-text="pagination.total">0</span> usuarios encontrados
                    </p>
                </div>
                
                <!-- Acciones en lote -->
                <div x-show="selectedUsers.length > 0" class="mt-3 sm:mt-0">
                    <div class="flex space-x-2">
                        <button @click="showBulkActions = true"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <span x-text="selectedUsers.length"></span> seleccionados
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
                Cargando usuarios...
            </div>
        </div>
        
        <!-- Tabla -->
        <div x-show="!loading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 table-hover">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" 
                                   @change="toggleAllUsers($event.target.checked)"
                                   class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer"
                            @click="sortBy('username')">
                            Usuario
                            <span x-show="sortField === 'username'">
                                <svg x-show="sortDirection === 'asc'" class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"/>
                                </svg>
                                <svg x-show="sortDirection === 'desc'" class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M14.77 12.79a.75.75 0 01-1.06-.02L10 8.832 6.29 12.77a.75.75 0 11-1.08-1.04l4.25-4.5a.75.75 0 011.08 0l4.25 4.5a.75.75 0 01-.02 1.06z"/>
                                </svg>
                            </span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Información Personal
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Roles & Permisos
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer"
                            @click="sortBy('ultimo_login')">
                            Último Acceso
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="user in users" :key="user.id">
                        <tr class="fade-in">
                            <!-- Checkbox de selección -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" 
                                       :value="user.id"
                                       @change="toggleUserSelection(user.id, $event.target.checked)"
                                       class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500">
                            </td>
                            
                            <!-- Usuario y avatar -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gestion-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white" 
                                                  x-text="user.username.charAt(0).toUpperCase()"></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" 
                                             x-text="user.username"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" 
                                             x-text="user.email"></div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Información personal -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    <span x-text="user.nombres"></span> <span x-text="user.apellidos"></span>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    ID: <span x-text="user.id"></span>
                                </div>
                            </td>
                            
                            <!-- Roles -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="role in user.roles" :key="role.id">
                                        <span class="role-badge"
                                              :class="getRoleClass(role.name)"
                                              x-text="role.display_name || role.name"></span>
                                    </template>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span x-text="user.permissions_count"></span> permisos
                                </div>
                            </td>
                            
                            <!-- Último acceso -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <div x-show="user.ultimo_login">
                                    <div x-text="formatDate(user.ultimo_login)"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" 
                                         x-text="formatRelativeTime(user.ultimo_login)"></div>
                                </div>
                                <div x-show="!user.ultimo_login" class="text-gray-500 dark:text-gray-400">
                                    Nunca
                                </div>
                            </td>
                            
                            <!-- Estado -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1">
                                    <!-- Estado principal -->
                                    <span x-show="user.activo && !user.bloqueado" 
                                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Activo
                                    </span>
                                    <span x-show="!user.activo" 
                                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                        Inactivo
                                    </span>
                                    <span x-show="user.bloqueado" 
                                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Bloqueado
                                    </span>
                                    
                                    <!-- Indicadores adicionales -->
                                    <div class="flex space-x-1">
                                        <span x-show="user.email_verified_at" 
                                              class="inline-flex items-center px-1.5 py-0.5 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded"
                                              title="Email verificado">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                        <span x-show="user.two_factor_secret" 
                                              class="inline-flex items-center px-1.5 py-0.5 text-xs bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 rounded"
                                              title="2FA habilitado">
                                            2FA
                                        </span>
                                        <span x-show="user.is_online" 
                                              class="inline-flex items-center px-1.5 py-0.5 text-xs bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded"
                                              title="En línea">
                                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Acciones -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    
                                    <!-- Ver usuario -->
                                    <a :href="`{{ route('admin.users.index') }}/${user.id}`"
                                       class="text-gestion-600 hover:text-gestion-700 p-1 rounded hover:bg-gestion-50"
                                       title="Ver detalles">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    
                                    <!-- Editar usuario -->
                                    @can('usuarios.editar')
                                    <a :href="`{{ route('admin.users.index') }}/${user.id}/edit`"
                                       class="text-blue-600 hover:text-blue-700 p-1 rounded hover:bg-blue-50"
                                       title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    @endcan
                                    
                                    <!-- Toggle estado -->
                                    @can('usuarios.editar')
                                    <button @click="toggleUserStatus(user)"
                                            class="p-1 rounded hover:bg-yellow-50"
                                            :class="user.activo ? 'text-yellow-600 hover:text-yellow-700' : 'text-green-600 hover:text-green-700'"
                                            :title="user.activo ? 'Desactivar' : 'Activar'">
                                        <svg x-show="user.activo" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 12M6 6l12 12" />
                                        </svg>
                                        <svg x-show="!user.activo" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    @endcan
                                    
                                    <!-- Resetear contraseña -->
                                    @can('usuarios.editar')
                                    <button @click="showResetPasswordModal(user)"
                                            class="text-purple-600 hover:text-purple-700 p-1 rounded hover:bg-purple-50"
                                            title="Resetear contraseña">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                    </button>
                                    @endcan
                                    
                                    <!-- Más opciones -->
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open"
                                                class="text-gray-600 hover:text-gray-700 p-1 rounded hover:bg-gray-50"
                                                title="Más opciones">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>
                                        <div x-show="open" 
                                             @click.away="open = false"
                                             class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg z-10 border border-gray-200 dark:border-gray-600">
                                            <div class="py-1">
                                                <button @click="viewUserActivity(user); open = false"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    Ver actividad
                                                </button>
                                                <button @click="viewUserSessions(user); open = false"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    Sesiones activas
                                                </button>
                                                @can('usuarios.editar')
                                                <button @click="manageUserRoles(user); open = false"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    Gestionar roles
                                                </button>
                                                <button @click="lockUser(user); open = false"
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                    Bloquear usuario
                                                </button>
                                                @endcan
                                                @can('usuarios.eliminar')
                                                <hr class="my-1 border-gray-200 dark:border-gray-600">
                                                <button @click="deleteUser(user); open = false"
                                                        class="block w-full text-left px-4 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                    Eliminar usuario
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
                    <tr x-show="!loading && users.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No se encontraron usuarios</h3>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">
                                    No hay usuarios que coincidan con los criterios de búsqueda.
                                </p>
                                <button @click="clearFilters()" 
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                                    Limpiar filtros
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div x-show="!loading && users.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Mostrando 
                    <span class="font-medium" x-text="pagination.from"></span>
                    a 
                    <span class="font-medium" x-text="pagination.to"></span>
                    de 
                    <span class="font-medium" x-text="pagination.total"></span>
                    resultados
                </div>
                
                <div class="flex space-x-2">
                    <button @click="previousPage()" 
                            :disabled="!pagination.prev_page_url"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Anterior
                    </button>
                    
                    <!-- Números de página -->
                    <template x-for="page in paginationPages" :key="page">
                        <button @click="goToPage(page)"
                                class="inline-flex items-center px-3 py-2 border text-sm font-medium rounded-lg"
                                :class="page === pagination.current_page 
                                    ? 'border-gestion-500 bg-gestion-600 text-white' 
                                    : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50'">
                            <span x-text="page"></span>
                        </button>
                    </template>
                    
                    <button @click="nextPage()" 
                            :disabled="!pagination.next_page_url"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar usuario -->
    <div x-show="showDeleteModal" 
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-2">Eliminar Usuario</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        ¿Estás seguro de que deseas eliminar al usuario 
                        <span class="font-medium" x-text="userToDelete?.username"></span>?
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <div class="flex space-x-3">
                        <button @click="confirmDeleteUser()"
                                :disabled="deleting"
                                class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50">
                            <span x-show="!deleting">Eliminar</span>
                            <span x-show="deleting">Eliminando...</span>
                        </button>
                        <button @click="showDeleteModal = false; userToDelete = null"
                                class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para resetear contraseña -->
    <div x-show="showResetModal" 
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-2 text-center">Resetear Contraseña</h3>
                <div class="mt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-4">
                        Resetear la contraseña del usuario 
                        <span class="font-medium" x-text="userToReset?.username"></span>
                    </p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nueva contraseña (opcional)
                            </label>
                            <input type="password" 
                                   x-model="resetPassword.password"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500"
                                   placeholder="Dejar vacío para generar automáticamente">
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       x-model="resetPassword.notify_user"
                                       class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Notificar al usuario por email
                                </span>
                            </label>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       x-model="resetPassword.force_change"
                                       class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Forzar cambio en el próximo login
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex space-x-3">
                    <button @click="confirmResetPassword()"
                            :disabled="resettingPassword"
                            class="flex-1 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50">
                        <span x-show="!resettingPassword">Resetear</span>
                        <span x-show="resettingPassword">Reseteando...</span>
                    </button>
                    <button @click="showResetModal = false; userToReset = null"
                            class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 text-sm font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function usuariosManager() {
    return {
        // Estado
        loading: false,
        users: [],
        stats: {
            total: 0,
            activos: 0,
            conectados: 0,
            bloqueados: 0
        },
        
        // Filtros
        filters: {
            search: '',
            role: '',
            status: '',
            created_from: '',
            created_to: '',
            has_2fa: ''
        },
        showAdvancedFilters: false,
        
        // Paginación
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0,
            from: 0,
            to: 0,
            next_page_url: null,
            prev_page_url: null
        },
        
        // Ordenamiento
        sortField: 'created_at',
        sortDirection: 'desc',
        
        // Selección
        selectedUsers: [],
        showBulkActions: false,
        
        // Modales
        showDeleteModal: false,
        userToDelete: null,
        deleting: false,
        
        showResetModal: false,
        userToReset: null,
        resettingPassword: false,
        resetPassword: {
            password: '',
            notify_user: true,
            force_change: true
        },
        
        // Inicialización
        init() {
            this.loadUsers();
            this.loadStats();
        },
        
        // Cargar usuarios
        async loadUsers() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    sort_by: this.sortField,
                    sort_order: this.sortDirection,
                    ...this.filters
                });
                
                const response = await fetch(`{{ route('admin.users.index') }}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.users = data.data;
                    this.pagination = data.pagination;
                }
            } catch (error) {
                console.error('Error loading users:', error);
                this.showNotification('Error al cargar usuarios', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        // Cargar estadísticas
        async loadStats() {
            try {
                const response = await fetch('{{ route("admin.users.stats") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.stats = data.stats;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },
        
        // Búsqueda
        searchUsers() {
            this.pagination.current_page = 1;
            this.loadUsers();
        },
        
        // Filtros
        applyFilters() {
            this.pagination.current_page = 1;
            this.loadUsers();
        },
        
        clearFilters() {
            this.filters = {
                search: '',
                role: '',
                status: '',
                created_from: '',
                created_to: '',
                has_2fa: ''
            };
            this.pagination.current_page = 1;
            this.loadUsers();
        },
        
        // Ordenamiento
        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            this.loadUsers();
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
            this.loadUsers();
        },
        
        previousPage() {
            if (this.pagination.prev_page_url) {
                this.pagination.current_page--;
                this.loadUsers();
            }
        },
        
        nextPage() {
            if (this.pagination.next_page_url) {
                this.pagination.current_page++;
                this.loadUsers();
            }
        },
        
        // Selección
        toggleAllUsers(checked) {
            if (checked) {
                this.selectedUsers = this.users.map(user => user.id);
            } else {
                this.selectedUsers = [];
            }
        },
        
        toggleUserSelection(userId, checked) {
            if (checked) {
                if (!this.selectedUsers.includes(userId)) {
                    this.selectedUsers.push(userId);
                }
            } else {
                this.selectedUsers = this.selectedUsers.filter(id => id !== userId);
            }
        },
        
        // Acciones de usuario
        async toggleUserStatus(user) {
            try {
                const response = await fetch(`{{ route('admin.users.index') }}/${user.id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    user.activo = data.data.activo;
                    this.showNotification(data.message, 'success');
                    this.loadStats();
                }
            } catch (error) {
                console.error('Error toggling user status:', error);
                this.showNotification('Error al cambiar estado del usuario', 'error');
            }
        },
        
        // Modal de eliminar usuario
        deleteUser(user) {
            this.userToDelete = user;
            this.showDeleteModal = true;
        },
        
        async confirmDeleteUser() {
            if (!this.userToDelete) return;
            
            this.deleting = true;
            try {
                const response = await fetch(`{{ route('admin.users.index') }}/${this.userToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.showNotification(data.message, 'success');
                    this.loadUsers();
                    this.loadStats();
                    this.showDeleteModal = false;
                    this.userToDelete = null;
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                this.showNotification('Error al eliminar usuario', 'error');
            } finally {
                this.deleting = false;
            }
        },
        
        // Modal de resetear contraseña
        showResetPasswordModal(user) {
            this.userToReset = user;
            this.resetPassword = {
                password: '',
                notify_user: true,
                force_change: true
            };
            this.showResetModal = true;
        },
        
        async confirmResetPassword() {
            if (!this.userToReset) return;
            
            this.resettingPassword = true;
            try {
                const response = await fetch(`{{ route('admin.users.index') }}/${this.userToReset.id}/reset-password`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.resetPassword)
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.showNotification(data.message, 'success');
                    this.showResetModal = false;
                    this.userToReset = null;
                }
            } catch (error) {
                console.error('Error resetting password:', error);
                this.showNotification('Error al resetear contraseña', 'error');
            } finally {
                this.resettingPassword = false;
            }
        },
        
        // Utilidades
        getRoleClass(roleName) {
            const roleClasses = {
                'Super Admin': 'role-super-admin',
                'Gerente': 'role-gerente',
                'Supervisor': 'role-supervisor',
                'Vendedor': 'role-vendedor',
                'Técnico': 'role-tecnico'
            };
            return roleClasses[roleName] || 'role-default';
        },
        
        formatDate(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        
        formatRelativeTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            
            if (days === 0) return 'Hoy';
            if (days === 1) return 'Ayer';
            if (days < 7) return `Hace ${days} días`;
            if (days < 30) return `Hace ${Math.floor(days / 7)} semanas`;
            return `Hace ${Math.floor(days / 30)} meses`;
        },
        
        exportData() {
            window.open(`{{ route('admin.users.export') }}?${new URLSearchParams(this.filters)}`, '_blank');
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