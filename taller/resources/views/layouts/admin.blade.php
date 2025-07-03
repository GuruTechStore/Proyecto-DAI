@extends('layouts.app')

@section('title', 'Panel de Administración')

@section('page-title', 'Administración')

@section('page-description')
    Panel de control administrativo con herramientas avanzadas de gestión
@endsection

@push('styles')
<style>
    .admin-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    
    .admin-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-left-color: #3b82f6;
    }
    
    .admin-stat {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .admin-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .admin-warning {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    }
    
    .admin-success {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    }
    
    .admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .pulse-dot {
        animation: pulse-dot 2s infinite;
    }
    
    @keyframes pulse-dot {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
</style>
@endpush

@section('breadcrumbs')
    <li>
        <div class="flex items-center">
            <svg class="flex-shrink-0 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="ml-4 text-sm font-medium text-gray-500">Administración</span>
        </div>
    </li>
@endsection

@section('page-actions')
    <div class="flex space-x-3">
        <!-- Security Center -->
        @can('seguridad.ver')
            <a href="{{ route('admin.security.dashboard') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Centro de Seguridad
            </a>
        @endcan

        <!-- System Settings -->
        @can('configuracion.ver')
            <a href="{{ route('admin.settings') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Configuración
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div x-data="adminDashboard()" x-init="loadDashboardData()">
    <!-- System Status Alert -->
    <div x-show="systemAlert.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700">
                    <span class="font-medium">Alerta del Sistema:</span>
                    <span x-text="systemAlert.message"></span>
                </p>
            </div>
            <div class="ml-auto pl-3">
                <button @click="systemAlert.show = false" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100">
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Admin Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="admin-card bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="admin-stat w-8 h-8 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Usuarios</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" x-text="stats.totalUsers || 0"></div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600" x-show="stats.userGrowth > 0">
                                    <svg class="self-center flex-shrink-0 h-3 w-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="sr-only">Aumentó en</span>
                                    <span x-text="stats.userGrowth"></span>%
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.users.index') }}" class="font-medium text-indigo-700 hover:text-indigo-900">
                        Gestionar usuarios
                    </a>
                </div>
            </div>
        </div>

        <!-- Security Events -->
        <div class="admin-card bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="admin-danger w-8 h-8 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Eventos de Seguridad</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" x-text="stats.securityEvents || 0"></div>
                                <div class="ml-2 flex items-center" x-show="stats.criticalEvents > 0">
                                    <span class="pulse-dot inline-block w-2 h-2 bg-red-500 rounded-full"></span>
                                    <span class="ml-1 text-sm font-medium text-red-600" x-text="stats.criticalEvents"></span>
                                    <span class="ml-1 text-sm text-red-600">críticos</span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.security.logs') }}" class="font-medium text-indigo-700 hover:text-indigo-900">
                        Ver logs de seguridad
                    </a>
                </div>
            </div>
        </div>

        <!-- System Performance -->
        <div class="admin-card bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="admin-success w-8 h-8 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Rendimiento del Sistema</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" x-text="stats.systemPerformance || 0">%</div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold" 
                                     :class="stats.systemPerformance >= 90 ? 'text-green-600' : stats.systemPerformance >= 70 ? 'text-yellow-600' : 'text-red-600'">
                                    <span x-text="stats.systemPerformance >= 90 ? 'Excelente' : stats.systemPerformance >= 70 ? 'Bueno' : 'Atención'"></span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.system.monitor') }}" class="font-medium text-indigo-700 hover:text-indigo-900">
                        Monitor del sistema
                    </a>
                </div>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="admin-card bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="admin-warning w-8 h-8 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Sesiones Activas</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900" x-text="stats.activeSessions || 0"></div>
                                <div class="ml-2 flex items-center">
                                    <span class="pulse-dot inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                                    <span class="ml-1 text-sm text-gray-500">en línea</span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.sessions.active') }}" class="font-medium text-indigo-700 hover:text-indigo-900">
                        Ver sesiones activas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Tools Grid -->
    <div class="admin-grid mb-8">
        <!-- User Management -->
        @can('usuarios.gestionar')
        <div class="admin-card bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Gestión de Usuarios</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Administrar usuarios, roles y permisos del sistema
                        </p>
                    </div>
                </div>
                <div class="mt-5">
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('admin.users.index') }}" 
                           class="text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                            Ver Usuarios
                        </a>
                        <a href="{{ route('admin.users.create') }}" 
                           class="text-center px-4 py-2 border border-indigo-600 text-indigo-600 text-sm font-medium rounded-md hover:bg-indigo-50 transition-colors">
                            Crear Usuario
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        <!-- Security Center -->
        @can('seguridad.gestionar')
        <div class="admin-card bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Centro de Seguridad</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Monitoreo, logs y configuración de seguridad
                        </p>
                    </div>
                </div>
                <div class="mt-5">
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('admin.security.dashboard') }}" 
                           class="text-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.security.logs') }}" 
                           class="text-center px-4 py-2 border border-red-600 text-red-600 text-sm font-medium rounded-md hover:bg-red-50 transition-colors">
                            Ver Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        <!-- System Monitor -->
        @can('sistema.monitorear')
        <div class="admin-card bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Monitor del Sistema</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Rendimiento, recursos y estadísticas del sistema
                        </p>
                    </div>
                </div>
                <div class="mt-5">
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('admin.system.monitor') }}" 
                           class="text-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                            Ver Monitor
                        </a>
                        <a href="{{ route('admin.system.reports') }}" 
                           class="text-center px-4 py-2 border border-green-600 text-green-600 text-sm font-medium rounded-md hover:bg-green-50 transition-colors">
                            Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        <!-- Backup & Maintenance -->
        @can('sistema.mantener')
        <div class="admin-card bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Respaldos y Mantenimiento</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Gestión de copias de seguridad y tareas de mantenimiento
                        </p>
                    </div>
                </div>
                <div class="mt-5">
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('admin.backup.index') }}" 
                           class="text-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 transition-colors">
                            Respaldos
                        </a>
                        <a href="{{ route('admin.maintenance.index') }}" 
                           class="text-center px-4 py-2 border border-yellow-600 text-yellow-600 text-sm font-medium rounded-md hover:bg-yellow-50 transition-colors">
                            Mantenimiento
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endcan
    </div>

    <!-- Recent Activity Section -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Actividad Reciente del Sistema</h3>
            
            <div class="flow-root">
                <ul x-show="recentActivity.length > 0" class="-mb-8">
                    <template x-for="(activity, index) in recentActivity" :key="activity.id">
                        <li>
                            <div class="relative pb-8" :class="index === recentActivity.length - 1 ? '' : 'pb-8'">
                                <span x-show="index !== recentActivity.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                              :class="{
                                                'bg-green-500': activity.type === 'success',
                                                'bg-red-500': activity.type === 'error',
                                                'bg-yellow-500': activity.type === 'warning',
                                                'bg-blue-500': activity.type === 'info'
                                              }">
                                            <!-- Success Icon -->
                                            <svg x-show="activity.type === 'success'" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            <!-- Error Icon -->
                                            <svg x-show="activity.type === 'error'" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            <!-- Warning Icon -->
                                            <svg x-show="activity.type === 'warning'" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            <!-- Info Icon -->
                                            <svg x-show="activity.type === 'info'" class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500">
                                                <span class="font-medium text-gray-900" x-text="activity.user"></span>
                                                <span x-text="activity.action"></span>
                                            </p>
                                            <p class="text-sm text-gray-500" x-text="activity.description"></p>
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                            <time x-text="activity.time"></time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>
                
                <!-- Empty State -->
                <div x-show="recentActivity.length === 0" class="text-center py-6">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay actividad reciente</h3>
                    <p class="mt-1 text-sm text-gray-500">La actividad del sistema aparecerá aquí.</p>
                </div>
            </div>
            
            <div class="mt-6">
                <a href="{{ route('admin.activity.index') }}" 
                   class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Ver toda la actividad
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('adminDashboard', () => ({
        stats: {
            totalUsers: 0,
            userGrowth: 0,
            securityEvents: 0,
            criticalEvents: 0,
            systemPerformance: 0,
            activeSessions: 0
        },
        recentActivity: [],
        systemAlert: {
            show: false,
            message: ''
        },
        
        async loadDashboardData() {
            try {
                // Load dashboard stats
                const statsResponse = await fetch('/api/v1/admin/dashboard/stats');
                if (statsResponse.ok) {
                    this.stats = await statsResponse.json();
                }
                
                // Load recent activity
                const activityResponse = await fetch('/api/v1/admin/dashboard/activity');
                if (activityResponse.ok) {
                    this.recentActivity = await activityResponse.json();
                }
                
                // Check for system alerts
                const alertsResponse = await fetch('/api/v1/admin/dashboard/alerts');
                if (alertsResponse.ok) {
                    const alerts = await alertsResponse.json();
                    if (alerts.length > 0) {
                        this.systemAlert.show = true;
                        this.systemAlert.message = alerts[0].message;
                    }
                }
                
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                this.showToast('Error al cargar datos del dashboard', 'error');
            }
        },
        
        showToast(message, type = 'info') {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    }));
});

// Auto-refresh dashboard data every 30 seconds
setInterval(() => {
    const dashboardComponent = Alpine.$data(document.querySelector('[x-data="adminDashboard()"]'));
    if (dashboardComponent) {
        dashboardComponent.loadDashboardData();
    }
}, 30000);
</script>
@endpush
@endsection
