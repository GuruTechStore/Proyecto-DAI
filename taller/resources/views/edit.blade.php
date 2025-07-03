@extends('layouts.app')

@section('title', 'Mi Perfil')

@push('styles')
<style>
    .profile-header {
        @apply bg-white shadow rounded-lg mb-6;
    }
    .profile-section {
        @apply bg-white shadow rounded-lg mb-6;
    }
    .section-header {
        @apply px-6 py-4 border-b border-gray-200;
    }
    .section-content {
        @apply px-6 py-4;
    }
    .info-item {
        @apply py-3 flex justify-between items-center border-b border-gray-100 last:border-b-0;
    }
    .info-label {
        @apply text-sm font-medium text-gray-500 uppercase tracking-wide;
    }
    .info-value {
        @apply text-sm text-gray-900 font-medium;
    }
    .badge {
        @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
    }
    .badge-green {
        @apply bg-green-100 text-green-800;
    }
    .badge-red {
        @apply bg-red-100 text-red-800;
    }
    .badge-blue {
        @apply bg-blue-100 text-blue-800;
    }
    .activity-timeline {
        @apply flow-root;
    }
    .activity-item {
        @apply relative pb-8;
    }
    .activity-line {
        @apply absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200;
    }
</style>
@endpush

@section('content')
<div x-data="profileShow()" x-init="initProfile()" class="max-w-4xl mx-auto">
    <!-- Header del Perfil -->
    <div class="profile-header">
        <div class="px-6 py-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        @if(auth()->user()->avatar)
                            <img class="h-24 w-24 rounded-full object-cover border-4 border-white shadow-lg" 
                                 src="{{ Storage::url(auth()->user()->avatar) }}" 
                                 alt="{{ auth()->user()->first_name }}">
                        @else
                            <div class="h-24 w-24 rounded-full bg-gray-300 flex items-center justify-center border-4 border-white shadow-lg">
                                <svg class="h-12 w-12 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Información Principal -->
                    <div class="flex-1">
                        <div class="flex items-center space-x-3">
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                            </h1>
                            <div class="badge {{ auth()->user()->is_active ? 'badge-green' : 'badge-red' }}">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="10" cy="10" r="3"/>
                                </svg>
                                {{ auth()->user()->is_active ? 'Activo' : 'Inactivo' }}
                            </div>
                        </div>

                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">@</span>{{ auth()->user()->username }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <svg class="inline mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ auth()->user()->email }}
                            </p>
                            @if(auth()->user()->phone)
                            <p class="text-sm text-gray-600">
                                <svg class="inline mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ auth()->user()->phone }}
                            </p>
                            @endif
                        </div>

                        <!-- Roles y Permisos -->
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse(auth()->user()->roles as $role)
                                <span class="badge badge-blue">
                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $role->name }}
                                </span>
                            @empty
                                <span class="badge bg-gray-100 text-gray-800">Sin rol asignado</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="flex flex-col space-y-2">
                    <a href="{{ route('profile.edit') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar perfil
                    </a>
                    <button @click="downloadProfileData()" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Exportar datos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información Personal -->
            <div class="profile-section">
                <div class="section-header">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Información Personal</h3>
                </div>
                <div class="section-content">
                    <dl class="divide-y divide-gray-100">
                        <div class="info-item">
                            <dt class="info-label">DNI</dt>
                            <dd class="info-value">{{ auth()->user()->dni ?: 'No especificado' }}</dd>
                        </div>
                        <div class="info-item">
                            <dt class="info-label">Nombres completos</dt>
                            <dd class="info-value">
                                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                                @if(auth()->user()->mother_last_name)
                                    {{ auth()->user()->mother_last_name }}
                                @endif
                            </dd>
                        </div>
                        <div class="info-item">
                            <dt class="info-label">Especialidad</dt>
                            <dd class="info-value">{{ auth()->user()->specialty ?: 'No especificada' }}</dd>
                        </div>
                        <div class="info-item">
                            <dt class="info-label">Fecha de nacimiento</dt>
                            <dd class="info-value">
                                @if(auth()->user()->birth_date)
                                    {{ auth()->user()->birth_date->format('d/m/Y') }}
                                    ({{ auth()->user()->birth_date->age }} años)
                                @else
                                    No especificada
                                @endif
                            </dd>
                        </div>
                        <div class="info-item">
                            <dt class="info-label">Dirección</dt>
                            <dd class="info-value">{{ auth()->user()->address ?: 'No especificada' }}</dd>
                        </div>
                        <div class="info-item">
                            <dt class="info-label">Fecha de registro</dt>
                            <dd class="info-value">{{ auth()->user()->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div class="info-item">
                            <dt class="info-label">Último acceso</dt>
                            <dd class="info-value">
                                @if(auth()->user()->last_login_at)
                                    {{ auth()->user()->last_login_at->diffForHumans() }}
                                @else
                                    Nunca
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Estadísticas de Actividad -->
            <div class="profile-section">
                <div class="section-header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Estadísticas de Actividad</h3>
                        <button @click="refreshStats()" 
                                :disabled="loadingStats"
                                class="text-sm text-gestion-600 hover:text-gestion-500 font-medium">
                            <svg x-show="!loadingStats" class="inline mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <svg x-show="loadingStats" class="inline mr-1 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Actualizar
                        </button>
                    </div>
                </div>
                <div class="section-content">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div class="text-center">
                            <dt class="text-sm font-medium text-gray-500">Sesiones</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900" x-text="stats.total_sessions || 0"></dd>
                            <dd class="text-xs text-gray-500">Total</dd>
                        </div>
                        <div class="text-center">
                            <dt class="text-sm font-medium text-gray-500">Tiempo online</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900" x-text="formatHours(stats.total_time_online || 0)"></dd>
                            <dd class="text-xs text-gray-500">Horas</dd>
                        </div>
                        <div class="text-center">
                            <dt class="text-sm font-medium text-gray-500">Acciones</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900" x-text="stats.total_actions || 0"></dd>
                            <dd class="text-xs text-gray-500">Este mes</dd>
                        </div>
                        <div class="text-center">
                            <dt class="text-sm font-medium text-gray-500">Dispositivos</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900" x-text="stats.devices_used || 0"></dd>
                            <dd class="text-xs text-gray-500">Únicos</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actividad Reciente -->
            <div class="profile-section">
                <div class="section-header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Actividad Reciente</h3>
                        <a href="{{ route('profile.show') }}"
                           class="text-sm text-gestion-600 hover:text-gestion-500 font-medium">
                            Ver todo →
                        </a>
                    </div>
                </div>
                <div class="section-content">
                    <div class="activity-timeline">
                        <ul x-show="recentActivity.length > 0" class="-mb-8">
                            <template x-for="(activity, index) in recentActivity.slice(0, 10)" :key="activity.id">
                                <li class="activity-item">
                                    <div x-show="index < recentActivity.slice(0, 10).length - 1" class="activity-line"></div>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                                  :class="getActivityColor(activity.type)">
                                                <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path :d="getActivityIcon(activity.type)"></path>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500" x-text="activity.description"></p>
                                                <div class="mt-2 text-sm text-gray-700">
                                                    <span x-show="activity.ip_address" class="text-xs bg-gray-100 px-2 py-1 rounded">
                                                        IP: <span x-text="activity.ip_address"></span>
                                                    </span>
                                                    <span x-show="activity.user_agent" class="text-xs bg-gray-100 px-2 py-1 rounded ml-1">
                                                        <span x-text="getBrowserInfo(activity.user_agent)"></span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time x-text="formatActivityDate(activity.created_at)"></time>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                        <div x-show="recentActivity.length === 0" class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Sin actividad reciente</h3>
                            <p class="mt-1 text-sm text-gray-500">Tu actividad aparecerá aquí cuando realices acciones.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Información de Seguridad -->
            <div class="profile-section">
                <div class="section-header">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Seguridad</h3>
                </div>
                <div class="section-content space-y-4">
                    <!-- Estado 2FA -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Autenticación 2FA</p>
                            <p class="text-sm text-gray-500">Verificación en dos pasos</p>
                        </div>
                        <div class="badge {{ auth()->user()->two_factor_secret ? 'badge-green' : 'badge-red' }}">
                            {{ auth()->user()->two_factor_secret ? 'Activa' : 'Inactiva' }}
                        </div>
                    </div>

                    <!-- Última verificación de email -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Email verificado</p>
                            <p class="text-sm text-gray-500">Estado de verificación</p>
                        </div>
                        <div class="badge {{ auth()->user()->email_verified_at ? 'badge-green' : 'badge-red' }}">
                            {{ auth()->user()->email_verified_at ? 'Verificado' : 'No verificado' }}
                        </div>
                    </div>

                    <!-- Sesiones activas -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Sesiones activas</p>
                            <p class="text-sm text-gray-500">Dispositivos conectados</p>
                        </div>
                        <span class="text-sm font-medium text-gray-900" x-text="stats.active_sessions || 1"></span>
                    </div>

                    <!-- Botón de configuración de seguridad -->
                    <div class="pt-2">
                        <button @click="openSecuritySettings()" 
                                class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Configurar seguridad
                        </button>
                    </div>
                </div>
            </div>

            <!-- Permisos del Usuario -->
            <div class="profile-section">
                <div class="section-header">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Permisos</h3>
                </div>
                <div class="section-content">
                    <div class="space-y-3 max-h-60 overflow-y-auto">
                        @forelse(auth()->user()->getAllPermissions()->groupBy('category') as $category => $permissions)
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                    {{ $category ?: 'General' }}
                                </h4>
                                <ul class="space-y-1">
                                    @foreach($permissions as $permission)
                                        <li class="flex items-center text-sm">
                                            <svg class="mr-2 h-3 w-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-gray-700">{{ $permission->display_name ?? $permission->name }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No tienes permisos específicos asignados.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Preferencias -->
            <div class="profile-section">
                <div class="section-header">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Preferencias</h3>
                </div>
                <div class="section-content space-y-4">
                    @php
                        $preferences = auth()->user()->preferences ?? [];
                    @endphp
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-900">Notificaciones por email</span>
                        <div class="badge {{ ($preferences['email_notifications'] ?? true) ? 'badge-green' : 'badge-red' }}">
                            {{ ($preferences['email_notifications'] ?? true) ? 'Activas' : 'Desactivadas' }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-900">Alertas de seguridad</span>
                        <div class="badge {{ ($preferences['security_alerts'] ?? true) ? 'badge-green' : 'badge-red' }}">
                            {{ ($preferences['security_alerts'] ?? true) ? 'Activas' : 'Desactivadas' }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-900">Perfil público</span>
                        <div class="badge {{ ($preferences['profile_visible'] ?? true) ? 'badge-green' : 'badge-red' }}">
                            {{ ($preferences['profile_visible'] ?? true) ? 'Visible' : 'Oculto' }}
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('profile.edit') }}#preferencias" 
                           class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Modificar preferencias
                        </a>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="profile-section">
                <div class="section-header">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Acciones</h3>
                </div>
                <div class="section-content space-y-3">
                    <button @click="changePassword()" 
                            class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Cambiar contraseña
                    </button>

                    <button @click="logoutAllDevices()" 
                            class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Cerrar todas las sesiones
                    </button>

                    @can('auditoria.ver')
                    <a href="{{ route('admin.activity.user', auth()->user()) }}" 
                       class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Ver logs completos
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function profileShow() {
    return {
        loadingStats: false,
        stats: {
            total_sessions: 0,
            total_time_online: 0,
            total_actions: 0,
            devices_used: 0,
            active_sessions: 1
        },
        recentActivity: [],

        async initProfile() {
            await this.loadStats();
            await this.loadRecentActivity();
        },

        async loadStats() {
            try {
                const response = await fetch('/api/v1/profile/stats', {
                    headers: {
                        'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]').content}`,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    this.stats = await response.json();
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async loadRecentActivity() {
            try {
                const response = await fetch('/api/v1/profile/activity', {
                    headers: {
                        'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]').content}`,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    this.recentActivity = await response.json();
                }
            } catch (error) {
                console.error('Error loading recent activity:', error);
            }
        },

        async refreshStats() {
            this.loadingStats = true;
            await this.loadStats();
            this.loadingStats = false;
        },

        formatHours(minutes) {
            if (!minutes) return '0';
            const hours = Math.floor(minutes / 60);
            return hours.toString();
        },

        formatActivityDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffMinutes = Math.ceil(diffTime / (1000 * 60));
            const diffHours = Math.ceil(diffTime / (1000 * 60 * 60));
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffMinutes < 60) {
                return `Hace ${diffMinutes} min`;
            } else if (diffHours < 24) {
                return `Hace ${diffHours}h`;
            } else if (diffDays < 7) {
                return `Hace ${diffDays}d`;
            } else {
                return date.toLocaleDateString('es-PE', {
                    month: 'short',
                    day: 'numeric'
                });
            }
        },

        getActivityColor(type) {
            const colors = {
                'login': 'bg-green-500',
                'logout': 'bg-gray-500',
                'create': 'bg-blue-500',
                'update': 'bg-yellow-500',
                'delete': 'bg-red-500',
                'view': 'bg-purple-500',
                'security': 'bg-red-600',
                'default': 'bg-gray-400'
            };
            return colors[type] || colors.default;
        },

        getActivityIcon(type) {
            const icons = {
                'login': 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
                'logout': 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
                'create': 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                'update': 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                'delete': 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
                'view': 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
                'security': 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                'default': 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
            };
            return icons[type] || icons.default;
        },

        getBrowserInfo(userAgent) {
            if (!userAgent) return 'Desconocido';
            
            // Detectar navegador
            if (userAgent.includes('Chrome')) return 'Chrome';
            if (userAgent.includes('Firefox')) return 'Firefox';
            if (userAgent.includes('Safari')) return 'Safari';
            if (userAgent.includes('Edge')) return 'Edge';
            if (userAgent.includes('Opera')) return 'Opera';
            
            // Detectar móvil
            if (userAgent.includes('Mobile')) return 'Móvil';
            if (userAgent.includes('Android')) return 'Android';
            if (userAgent.includes('iPhone')) return 'iPhone';
            
            return 'Desconocido';
        },

        async downloadProfileData() {
            try {
                const response = await fetch('/api/v1/profile/export', {
                    headers: {
                        'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]').content}`,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = `perfil-${new Date().toISOString().split('T')[0]}.json`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                } else {
                    alert('Error al exportar los datos');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión');
            }
        },

        openSecuritySettings() {
            // Redirigir a configuración de seguridad o abrir modal
            window.location.href = '{{ route("profile.edit") }}#seguridad';
        },

        changePassword() {
            // Redirigir a cambio de contraseña
            window.location.href = '{{ route("profile.edit") }}#password';
        },

        async logoutAllDevices() {
            if (!confirm('¿Estás seguro de que quieres cerrar sesión en todos los dispositivos?')) {
                return;
            }

            try {
                const response = await fetch('/api/v1/profile/logout-all-devices', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]').content}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    alert('Se han cerrado todas las sesiones. Serás redirigido al login.');
                    window.location.href = '/login';
                } else {
                    alert('Error al cerrar las sesiones');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión');
            }
        }
    }
}
</script>
@endpush
@endsection