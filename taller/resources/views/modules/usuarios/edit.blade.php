{{-- resources/views/modules/usuarios/edit.blade.php - PARTE 1 DE 3 --}}
@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
        </svg>
        <a href="{{ route('admin.users.index') }}" class="ml-2 text-sm font-medium text-gray-700 hover:text-gray-900">Usuarios</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('admin.users.show', $usuario->id) }}" class="ml-2 text-sm font-medium text-gray-700 hover:text-gray-900">{{ $usuario->username }}</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="ml-2 text-sm font-medium text-gray-500">Editar</span>
    </div>
</li>
@endsection

@push('styles')
<style>
    .fade-in { 
        animation: fadeIn 0.3s ease-in-out; 
    }
    @keyframes fadeIn { 
        from { opacity: 0; transform: translateY(10px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    .tab-active { 
        @apply border-gestion-500 text-gestion-600 bg-gestion-50 dark:bg-gestion-900/20; 
    }
    .tab-inactive { 
        @apply border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300; 
    }
    .password-strength-bar { 
        transition: all 0.3s ease; 
    }
    .role-badge { 
        @apply px-2 py-1 text-xs font-semibold rounded-full; 
    }
    .role-super-admin { 
        @apply bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200; 
    }
    .role-gerente { 
        @apply bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200; 
    }
    .role-supervisor { 
        @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200; 
    }
    .role-vendedor { 
        @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200; 
    }
    .role-tecnico { 
        @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200; 
    }
    .role-default { 
        @apply bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200; 
    }
    .availability-check {
        transition: all 0.2s ease;
    }
    .security-action {
        transition: all 0.2s ease;
    }
    .security-action:hover {
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
<div x-data="userEditForm(@json($usuario))" x-init="init()" class="space-y-6">
    
    <!-- Header con información del usuario -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Avatar del usuario -->
                    <div class="flex-shrink-0">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-r from-gestion-500 to-gestion-600 flex items-center justify-center shadow-lg">
                            <span class="text-xl font-bold text-white">
                                {{ strtoupper(substr($usuario->username, 0, 2)) }}
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Editar Usuario: {{ $usuario->username }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $usuario->nombres }} {{ $usuario->apellidos }}
                        </p>
                        
                        <!-- Estado y badges del usuario -->
                        <div class="mt-3 flex items-center space-x-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="user.activo ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                                <div class="w-1.5 h-1.5 rounded-full mr-1.5"
                                     :class="user.activo ? 'bg-green-400' : 'bg-red-400'"></div>
                                <span x-text="user.activo ? 'Activo' : 'Inactivo'"></span>
                            </span>
                            
                            @if($usuario->bloqueado)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                Bloqueado
                            </span>
                            @endif
                            
                            @if($usuario->email_verified_at)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Email Verificado
                            </span>
                            @endif
                            
                            @if($usuario->two_factor_secret)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1-1-.257-.257A6 6 0 0118 8zm-2 0a4 4 0 11-8 0 4 4 0 018 0zm-4-2a2 2 0 100 4 2 2 0 000-4z" clip-rule="evenodd" />
                                </svg>
                                2FA Habilitado
                            </span>
                            @endif
                            
                            <!-- Último login -->
                            @if($usuario->ultimo_login)
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Último acceso: {{ $usuario->ultimo_login->diffForHumans() }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <a href="{{ route('admin.users.show', $usuario->id) }}" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Ver Perfil
                    </a>
                    
                    <a href="{{ route('admin.users.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver a Lista
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas rápidas del usuario -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $usuario->roles->count() }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Roles Asignados</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $usuario->getAllPermissions()->count() }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Permisos Totales</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $usuario->intentos_fallidos }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Intentos Fallidos</p>
                </div>
                <div class="text-center">
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" x-text="formatDate('{{ $usuario->created_at }}', 'short')"></p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Miembro desde</p>
                </div>
            </div>
        </div>
        
        <!-- Navegación por tabs -->
        <div class="px-6">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'profile'"
                        class="py-3 px-1 border-b-2 font-medium text-sm transition-colors"
                        :class="activeTab === 'profile' ? 'tab-active' : 'tab-inactive'">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Información Personal
                </button>
                
                <button @click="activeTab = 'credentials'"
                        class="py-3 px-1 border-b-2 font-medium text-sm transition-colors"
                        :class="activeTab === 'credentials' ? 'tab-active' : 'tab-inactive'">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z" />
                    </svg>
                    Credenciales
                </button>
                
                <button @click="activeTab = 'roles'"
                        class="py-3 px-1 border-b-2 font-medium text-sm transition-colors"
                        :class="activeTab === 'roles' ? 'tab-active' : 'tab-inactive'">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Roles y Permisos
                </button>
                
                <button @click="activeTab = 'security'"
                        class="py-3 px-1 border-b-2 font-medium text-sm transition-colors"
                        :class="activeTab === 'security' ? 'tab-active' : 'tab-inactive'">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Seguridad
                </button>
                
                <button @click="activeTab = 'activity'"
                        class="py-3 px-1 border-b-2 font-medium text-sm transition-colors"
                        :class="activeTab === 'activity' ? 'tab-active' : 'tab-inactive'">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Actividad
                </button>
            </nav>
        </div>
    </div>

    <!-- Contenido de los tabs -->
    
    <!-- Tab: Información Personal -->
    <div x-show="activeTab === 'profile'" x-transition:enter="fade-in" class="space-y-6">
        <form @submit.prevent="updateProfile()" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Información Personal
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Actualice los datos personales del usuario
                    </p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Empleado asociado -->
                        <div class="col-span-1 lg:col-span-2">
                            <label for="empleado_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Empleado Asociado
                            </label>
                            <div class="mt-1">
                                <select id="tipo_usuario" 
                                        name="tipo_usuario" 
                                        x-model="form.profile.tipo_usuario"
                                        @change="validateProfileField('tipo_usuario')"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                        :class="profileErrors.tipo_usuario ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''">
                                    <option value="">Seleccionar tipo</option>
                                    <option value="administrador">Administrador</option>
                                    <option value="gerente">Gerente</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="vendedor">Vendedor</option>
                                    <option value="tecnico">Técnico</option>
                                    <option value="empleado">Empleado</option>
                                </select>
                            </div>
                            <div x-show="profileErrors.tipo_usuario" class="mt-1 text-sm text-red-600" x-text="profileErrors.tipo_usuario"></div>
                        </div>
                        
                        <!-- Estado del usuario -->
                        <div class="col-span-1">
                            <label for="activo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Estado del Usuario
                            </label>
                            <div class="mt-1">
                                <select id="activo" 
                                        name="activo" 
                                        x-model="form.profile.activo"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                                    <option value="1">Activo (puede acceder al sistema)</option>
                                    <option value="0">Inactivo (no puede acceder)</option>
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Los usuarios inactivos no pueden iniciar sesión en el sistema
                            </p>
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-600">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Información Adicional</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">ID del Usuario:</span>
                                <span class="ml-2 font-mono text-gray-900 dark:text-white">{{ $usuario->id }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Creado:</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="formatDate('{{ $usuario->created_at }}')"></span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Última actualización:</span>
                                <span class="ml-2 text-gray-900 dark:text-white" x-text="formatDate(user.updated_at)"></span>
                            </div>
                            @if($usuario->created_by)
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Creado por:</span>
                                <span class="ml-2 text-gray-900 dark:text-white">{{ $usuario->createdBy->username ?? 'Sistema' }}</span>
                            </div>
                            @endif
                            @if($usuario->ultimo_login)
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Último login:</span>
                                <span class="ml-2 text-gray-900 dark:text-white">{{ $usuario->ultimo_login->diffForHumans() }}</span>
                            </div>
                            @endif
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Intentos fallidos:</span>
                                <span class="ml-2 text-gray-900 dark:text-white">{{ $usuario->intentos_fallidos }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botones de acción del perfil -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 rounded-b-lg">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <span x-show="hasProfileChanges()" class="text-amber-600 dark:text-amber-400">
                                ⚠️ Hay cambios sin guardar
                            </span>
                            <span x-show="!hasProfileChanges()" class="text-green-600 dark:text-green-400">
                                ✓ Información actualizada
                            </span>
                        </div>
                        <div class="flex space-x-3">
                            <button type="button" 
                                    @click="resetProfileForm()"
                                    :disabled="!hasProfileChanges()"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                Descartar Cambios
                            </button>
                            <button type="submit" 
                                    :disabled="updatingProfile || !hasProfileChanges()"
                                    class="px-6 py-2 text-sm font-medium text-white bg-gestion-600 border border-transparent rounded-lg hover:bg-gestion-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <svg x-show="!updatingProfile" class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <svg x-show="updatingProfile" class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-show="!updatingProfile">Guardar Cambios</span>
                                <span x-show="updatingProfile">Guardando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Tab: Credenciales -->
    <div x-show="activeTab === 'credentials'" x-transition:enter="fade-in" class="space-y-6">
        
        <!-- Cambio de nombre de usuario -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Cambiar Nombre de Usuario
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Modifique el nombre de usuario para el acceso al sistema
                </p>
            </div>
            
            <form @submit.prevent="updateUsername()" class="px-6 py-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Usuario actual -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Usuario Actual
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   :value="user.username"
                                   disabled
                                   class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800 cursor-not-allowed">
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Username actual del usuario
                        </p>
                    </div>
                    
                    <!-- Nuevo usuario -->
                    <div>
                        <label for="new_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nuevo Usuario <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative">
                            <input type="text" 
                                   id="new_username" 
                                   name="new_username" 
                                   x-model="form.credentials.username"
                                   @input="validateCredentialField('username')"
                                   @blur="checkUsernameAvailability()"
                                   class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 pr-10"
                                   :class="credentialErrors.username ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : (usernameAvailable === false ? 'border-red-300' : (usernameAvailable === true ? 'border-green-300' : ''))"
                                   placeholder="nuevo_usuario">
                            
                            <!-- Indicador de verificación -->
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center availability-check">
                                <svg x-show="checkingUsername" class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg x-show="usernameAvailable === true" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <svg x-show="usernameAvailable === false" class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <div x-show="credentialErrors.username" class="mt-1 text-sm text-red-600" x-text="credentialErrors.username"></div>
                        <div x-show="usernameAvailable === false && !credentialErrors.username" class="mt-1 text-sm text-red-600">
                            Este nombre de usuario ya está en uso
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Solo letras, números y guiones bajos. Mínimo 3 caracteres.
                        </p>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" 
                            @click="form.credentials.username = user.username; credentialErrors.username = ''; usernameAvailable = null;"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="updatingUsername || !form.credentials.username || form.credentials.username === user.username || usernameAvailable === false"
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!updatingUsername">Cambiar Usuario</span>
                        <span x-show="updatingUsername">Cambiando...</span>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Cambio de contraseña -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z" />
                    </svg>
                    Cambiar Contraseña
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Establezca una nueva contraseña para el usuario
                </p>
            </div>
            
            <form @submit.prevent="updatePassword()" class="px-6 py-6">
                <div class="space-y-6">
                    
                    <!-- Nueva contraseña -->
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nueva Contraseña <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative">
                            <input :type="showNewPassword ? 'text' : 'password'" 
                                   id="new_password" 
                                   name="new_password" 
                                   x-model="form.credentials.password"
                                   @input="validateCredentialField('password'); checkPasswordStrength()"
                                   class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 pr-10"
                                   :class="credentialErrors.password ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                   placeholder="Nueva contraseña">
                            
                            <!-- Toggle mostrar/ocultar contraseña -->
                            <button type="button" 
                                    @click="showNewPassword = !showNewPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg x-show="!showNewPassword" class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showNewPassword" class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Indicador de fortaleza de contraseña -->
                        <div x-show="form.credentials.password" class="mt-3">
                            <div class="flex items-center space-x-2">
                                <div class="flex-1 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                    <div class="password-strength-bar h-2 rounded-full"
                                         :class="{
                                             'bg-red-500 w-1/4': passwordStrength === 'weak',
                                             'bg-yellow-500 w-2/4': passwordStrength === 'medium',
                                             'bg-green-500 w-3/4': passwordStrength === 'strong',
                                             'bg-green-600 w-full': passwordStrength === 'very-strong'
                                         }"></div>
                                </div>
                                <span class="text-xs font-medium w-20"
                                      :class="{
                                          'text-red-600': passwordStrength === 'weak',
                                          'text-yellow-600': passwordStrength === 'medium',
                                          'text-green-600': passwordStrength === 'strong',
                                          'text-green-700': passwordStrength === 'very-strong'
                                      }"
                                      x-text="passwordStrengthText"></span>
                            </div>
                        </div>
                        
                        <div class="mt-2 flex justify-between">
                            <div x-show="credentialErrors.password" class="text-sm text-red-600" x-text="credentialErrors.password"></div>
                            <button type="button" 
                                    @click="generatePassword()"
                                    class="text-xs text-gestion-600 hover:text-gestion-700 font-medium transition-colors">
                                Generar automáticamente
                            </button>
                        </div>
                    </div>
                                <select id="empleado_id" 
                                        name="empleado_id" 
                                        x-model="form.profile.empleado_id"
                                        @change="onEmpleadoChange()"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                                    <option value="">Sin empleado asociado</option>
                                    <template x-for="empleado in empleados" :key="empleado.id">
                                        <option :value="empleado.id" 
                                                x-text="`${empleado.nombres} ${empleado.apellidos} - ${empleado.especialidad || 'Sin especialidad'}`">
                                        </option>
                                    </template>
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Asocie este usuario con un empleado del sistema para sincronizar información
                            </p>
                        </div>
                        
                        <!-- Nombres -->
                        <div class="col-span-1">
                            <label for="nombres" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nombres <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="nombres" 
                                       name="nombres" 
                                       x-model="form.profile.nombres"
                                       @input="validateProfileField('nombres')"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       :class="profileErrors.nombres ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                       placeholder="Ingrese los nombres">
                            </div>
                            <div x-show="profileErrors.nombres" class="mt-1 text-sm text-red-600" x-text="profileErrors.nombres"></div>
                        </div>
                        
                        <!-- Apellidos -->
                        <div class="col-span-1">
                            <label for="apellidos" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Apellidos <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="apellidos" 
                                       name="apellidos" 
                                       x-model="form.profile.apellidos"
                                       @input="validateProfileField('apellidos')"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       :class="profileErrors.apellidos ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                       placeholder="Ingrese los apellidos">
                            </div>
                            <div x-show="profileErrors.apellidos" class="mt-1 text-sm text-red-600" x-text="profileErrors.apellidos"></div>
                        </div>
                        
                        <!-- Email -->
                        <div class="col-span-1">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Correo Electrónico <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       x-model="form.profile.email"
                                       @input="validateProfileField('email')"
                                       @blur="checkEmailAvailability()"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 pr-10"
                                       :class="profileErrors.email ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : (emailAvailable === false ? 'border-red-300' : (emailAvailable === true ? 'border-green-300' : ''))"
                                       placeholder="usuario@ejemplo.com">
                                
                                <!-- Indicador de verificación de email -->
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center availability-check">
                                    <svg x-show="checkingEmail" class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg x-show="emailAvailable === true" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <svg x-show="emailAvailable === false" class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div x-show="profileErrors.email" class="mt-1 text-sm text-red-600" x-text="profileErrors.email"></div>
                            <div x-show="emailAvailable === false && !profileErrors.email" class="mt-1 text-sm text-red-600">
                                Este correo electrónico ya está en uso por otro usuario
                            </div>
                        </div>
                        
                        <!-- Teléfono -->
                        <div class="col-span-1">
                            <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Teléfono
                            </label>
                            <div class="mt-1">
                                <input type="tel" 
                                       id="telefono" 
                                       name="telefono" 
                                       x-model="form.profile.telefono"
                                       @input="validateProfileField('telefono')"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       :class="profileErrors.telefono ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                       placeholder="+51 987 654 321">
                            </div>
                            <div x-show="profileErrors.telefono" class="mt-1 text-sm text-red-600" x-text="profileErrors.telefono"></div>
                        </div>
                        
                        <!-- Tipo de usuario -->
                        <div class="col-span-1">
                            <label for="tipo_usuario" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tipo de Usuario <span class="text-red-500">*</span>
                            </label>
<!-- Confirmar contraseña -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Confirmar Nueva Contraseña <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative">
                            <input :type="showPasswordConfirmation ? 'text' : 'password'" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   x-model="form.credentials.password_confirmation"
                                   @input="validateCredentialField('password_confirmation')"
                                   class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 pr-10"
                                   :class="credentialErrors.password_confirmation ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : (form.credentials.password && form.credentials.password_confirmation && form.credentials.password === form.credentials.password_confirmation ? 'border-green-300' : '')"
                                   placeholder="Confirme la nueva contraseña">
                            
                            <!-- Toggle mostrar/ocultar contraseña -->
                            <button type="button" 
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg x-show="!showPasswordConfirmation" class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPasswordConfirmation" class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                </svg>
                            </button>
                        </div>
                        <div x-show="credentialErrors.password_confirmation" class="mt-1 text-sm text-red-600" x-text="credentialErrors.password_confirmation"></div>
                        <div x-show="form.credentials.password && form.credentials.password_confirmation && form.credentials.password === form.credentials.password_confirmation && !credentialErrors.password_confirmation" 
                             class="mt-1 text-sm text-green-600 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Las contraseñas coinciden
                        </div>
                    </div>
                    
                    <!-- Opciones de seguridad -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Opciones de Seguridad
                        </h4>
                        
                        <div class="space-y-3">
                            <!-- Forzar cambio de contraseña -->
                            <label class="flex items-start">
                                <input type="checkbox" 
                                       x-model="form.credentials.force_password_change"
                                       class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500 mt-0.5">
                                <div class="ml-3">
                                    <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                        Forzar cambio de contraseña en el próximo login
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        El usuario deberá cambiar su contraseña cuando inicie sesión nuevamente
                                    </p>
                                </div>
                            </label>
                            
                            <!-- Notificar al usuario -->
                            <label class="flex items-start">
                                <input type="checkbox" 
                                       x-model="form.credentials.notify_user"
                                       class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500 mt-0.5">
                                <div class="ml-3">
                                    <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                        Notificar al usuario sobre el cambio de contraseña
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Se enviará un email informando sobre el cambio de contraseña
                                    </p>
                                </div>
                            </label>
                            
                            <!-- Logout de todas las sesiones -->
                            <label class="flex items-start">
                                <input type="checkbox" 
                                       x-model="form.credentials.logout_all_sessions"
                                       class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500 mt-0.5">
                                <div class="ml-3">
                                    <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                        Cerrar todas las sesiones activas
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Forzar logout en todos los dispositivos después del cambio
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" 
                            @click="resetPasswordForm()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="updatingPassword || !form.credentials.password || form.credentials.password !== form.credentials.password_confirmation"
                            class="px-6 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!updatingPassword">Cambiar Contraseña</span>
                        <span x-show="updatingPassword">Cambiando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab: Roles y Permisos -->
    <div x-show="activeTab === 'roles'" x-transition:enter="fade-in" class="space-y-6">
        
        <!-- Gestión de roles -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Roles Asignados
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Gestione los roles del usuario en el sistema
                </p>
            </div>
            
            <div class="px-6 py-6">
                
                <!-- Roles actuales -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Roles Actuales
                    </h4>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="role in user.roles" :key="role.id">
                            <div class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-gestion-100 text-gestion-800 dark:bg-gestion-900 dark:text-gestion-200 border border-gestion-200 dark:border-gestion-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span x-text="role.display_name || role.name"></span>
                                <button type="button" 
                                        @click="removeRole(role.id)"
                                        :disabled="removingRole === role.id"
                                        class="ml-3 text-gestion-600 hover:text-gestion-800 disabled:opacity-50">
                                    <svg x-show="removingRole !== role.id" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <svg x-show="removingRole === role.id" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>
                        <span x-show="user.roles.length === 0" class="text-sm text-gray-500 dark:text-gray-400 italic">
                            Sin roles asignados
                        </span>
                    </div>
                </div>
                
                <!-- Agregar nuevos roles -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Roles Disponibles
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="role in availableRolesToAdd" :key="role.id">
                            <div class="relative">
                                <button type="button"
                                        @click="addRole(role.id)"
                                        :disabled="addingRole === role.id"
                                        class="w-full flex items-start p-4 border-2 border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:border-gestion-500 hover:bg-gestion-50 dark:hover:bg-gestion-900/20 transition-all text-left disabled:opacity-50 disabled:cursor-not-allowed">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white flex items-center">
                                            <span x-text="role.display_name || role.name"></span>
                                            <svg x-show="addingRole !== role.id" class="w-5 h-5 text-gestion-600 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            <svg x-show="addingRole === role.id" class="animate-spin w-5 h-5 text-gestion-600 flex-shrink-0 ml-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="role.description || 'Sin descripción'"></div>
                                        <div class="mt-2 text-xs text-gray-400 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            <span x-text="`${role.permissions_count || 0} permisos`"></span>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </template>
                        <div x-show="availableRolesToAdd.length === 0" class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm font-medium">Todos los roles disponibles ya están asignados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Permisos específicos -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Permisos Específicos
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Asigne permisos individuales adicionales a los roles
                </p>
            </div>
            
            <div class="px-6 py-6">
                
                <!-- Resumen de permisos -->
                <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-gestion-50 dark:from-blue-900/20 dark:to-gestion-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Resumen de Permisos</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-3">
                            <div class="text-lg font-bold text-blue-600 dark:text-blue-400" x-text="rolePermissionsCount"></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Por Roles</div>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-3">
                            <div class="text-lg font-bold text-green-600 dark:text-green-400" x-text="directPermissionsCount"></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Específicos</div>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-3 border-2 border-gestion-200 dark:border-gestion-700">
                            <div class="text-lg font-bold text-gestion-600 dark:text-gestion-400" x-text="totalPermissionsCount"></div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Total Únicos</div>
                        </div>
                    </div>
                </div>
                
                <!-- Permisos por categoría -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Gestionar Permisos Individuales
                        </h4>
                        <button type="button" 
                                @click="showAllPermissions = !showAllPermissions"
                                class="text-xs text-gestion-600 hover:text-gestion-700 font-medium">
                            <span x-show="!showAllPermissions">Mostrar todos</span>
                            <span x-show="showAllPermissions">Mostrar menos</span>
                        </button>
                    </div>
                    
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg"
                         :class="showAllPermissions ? 'max-h-96 overflow-y-auto' : 'max-h-64 overflow-y-auto'">
                        
                        <template x-for="(permissions, category) in permissionsByCategory" :key="category">
                            <div class="border-b border-gray-200 dark:border-gray-600 last:border-b-0">
                                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                    <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider capitalize">
                                        <span x-text="category.replace('_', ' ')"></span>
                                        <span class="ml-2 text-gray-400">(<span x-text="permissions.length"></span> permisos)</span>
                                    </h5>
                                </div>
                                <div class="p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <template x-for="permission in permissions" :key="permission.id">
                                            <label class="flex items-start p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                                                   :class="userHasPermission(permission.id) ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-600' : ''">
                                                <input type="checkbox" 
                                                       :checked="userHasPermission(permission.id)"
                                                       @change="togglePermission(permission.id, $event.target.checked)"
                                                       :disabled="isPermissionFromRole(permission.id) || togglingPermission === permission.id"
                                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 disabled:opacity-50 mt-0.5">
                                                <div class="ml-3 flex-1">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="permission.display_name || permission.name"></div>
                                                    <div x-show="permission.description" class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="permission.description"></div>
                                                    <div x-show="isPermissionFromRole(permission.id)" class="text-xs text-blue-600 dark:text-blue-400 mt-1 flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        Incluido por rol
                                                    </div>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
<span x-show="activity.ultima_actividad">Última: <span x-text="formatDate(activity.ultima_actividad)"></span></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Paginación de actividad -->
                <div x-show="userActivity.length > 0" class="mt-6 flex justify-center">
                    <button type="button"
                            @click="loadMoreActivity()"
                            :disabled="loadingMoreActivity || !hasMoreActivity"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        <span x-show="!loadingMoreActivity">Cargar más actividad</span>
                        <span x-show="loadingMoreActivity">Cargando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de bloqueo temporal -->
    <div x-show="showTempBlockModal" 
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 dark:bg-orange-900">
                    <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-2 text-center">Bloqueo Temporal</h3>
                <div class="mt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-4">
                        Configurar bloqueo temporal para el usuario 
                        <span class="font-medium" x-text="user.username"></span>
                    </p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Duración del bloqueo
                            </label>
                            <select x-model="tempBlock.duration" 
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500">
                                <option value="30">30 minutos</option>
                                <option value="60">1 hora</option>
                                <option value="120">2 horas</option>
                                <option value="240">4 horas</option>
                                <option value="480">8 horas</option>
                                <option value="1440">24 horas</option>
                                <option value="custom">Personalizado</option>
                            </select>
                        </div>
                        
                        <div x-show="tempBlock.duration === 'custom'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Minutos personalizados
                            </label>
                            <input type="number" 
                                   x-model="tempBlock.customMinutes"
                                   min="1"
                                   max="10080"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="Ej: 90">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Máximo 7 días (10080 minutos)</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Razón del bloqueo
                            </label>
                            <textarea x-model="tempBlock.reason"
                                      rows="3"
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                                      placeholder="Motivo del bloqueo temporal..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex space-x-3">
                    <button @click="applyTempBlock()"
                            :disabled="tempBlocking"
                            class="flex-1 px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 disabled:opacity-50">
                        <span x-show="!tempBlocking">Aplicar Bloqueo</span>
                        <span x-show="tempBlocking">Aplicando...</span>
                    </button>
                    <button @click="showTempBlockModal = false; tempBlock = { duration: '60', customMinutes: '', reason: '' }"
                            class="flex-1 px-4 py-2 bg-gray-300 text-gray-800 text-sm font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function userEditForm(userData) {
    return {
        // Estado
        activeTab: 'profile',
        user: userData,
        
        // Formularios
        form: {
            profile: {
                empleado_id: userData.empleado_id || '',
                nombres: userData.nombres || '',
                apellidos: userData.apellidos || '',
                email: userData.email || '',
                telefono: userData.telefono || '',
                tipo_usuario: userData.tipo_usuario || '',
                activo: userData.activo ? '1' : '0'
            },
            credentials: {
                username: userData.username || '',
                password: '',
                password_confirmation: '',
                force_password_change: false,
                notify_user: true,
                logout_all_sessions: false
            }
        },
        
        // Estados de carga
        updatingProfile: false,
        updatingUsername: false,
        updatingPassword: false,
        loadingSessions: false,
        loadingActivity: false,
        loadingMoreActivity: false,
        
        // Estados de seguridad
        togglingBlock: false,
        resettingAttempts: false,
        toggling2FA: false,
        regeneratingCodes: false,
        revokingAllSessions: false,
        revokingSession: null,
        sendingVerification: false,
        
        // Estados de roles
        addingRole: null,
        removingRole: null,
        togglingPermission: null,
        
        // Errores
        profileErrors: {},
        credentialErrors: {},
        
        // Verificaciones
        emailAvailable: null,
        checkingEmail: false,
        usernameAvailable: null,
        checkingUsername: false,
        
        // Contraseña
        showNewPassword: false,
        showPasswordConfirmation: false,
        passwordStrength: '',
        passwordStrengthText: '',
        
        // Datos externos
        empleados: [],
        availableRoles: [],
        availablePermissions: [],
        
        // Actividad
        userActivity: [],
        activeSessions: [],
        activityFilters: {
            module: '',
            from_date: '',
            to_date: ''
        },
        hasMoreActivity: true,
        activityPage: 1,
        
        // Modal de bloqueo temporal
        showTempBlockModal: false,
        tempBlocking: false,
        tempBlock: {
            duration: '60',
            customMinutes: '',
            reason: ''
        },
        
        // Datos originales para detectar cambios
        originalProfile: {},
        
        // Inicialización
        async init() {
            // Guardar datos originales
            this.originalProfile = { ...this.form.profile };
            
            await this.loadEmpleados();
            await this.loadRoles();
            await this.loadPermissions();
            await this.loadUserActivity();
            await this.loadActiveSessions();
            
            // Establecer fechas por defecto para actividad
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
            this.activityFilters.from_date = thirtyDaysAgo.toISOString().split('T')[0];
            this.activityFilters.to_date = new Date().toISOString().split('T')[0];
        },
        
        // Detectar cambios en el perfil
        hasProfileChanges() {
            return JSON.stringify(this.form.profile) !== JSON.stringify(this.originalProfile);
        },
        
        // Cargar datos externos
        async loadEmpleados() {
            try {
                const response = await fetch('/api/empleados', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.empleados = data.data || [];
                }
            } catch (error) {
                console.error('Error loading empleados:', error);
            }
        },
        
        async loadRoles() {
            try {
                const response = await fetch('/api/roles', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.availableRoles = data.data || [];
                }
            } catch (error) {
                console.error('Error loading roles:', error);
            }
        },
        
        async loadPermissions() {
            try {
                const response = await fetch('/api/permissions', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.availablePermissions = data.data || [];
                }
            } catch (error) {
                console.error('Error loading permissions:', error);
            }
        },
        
        // Gestión de perfil
        async updateProfile() {
            if (!this.validateProfile()) return;
            
            this.updatingProfile = true;
            try {
                const response = await fetch(`/admin/users/${this.user.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form.profile)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.user = { ...this.user, ...data.data };
                    this.originalProfile = { ...this.form.profile };
                    this.showNotification('Perfil actualizado exitosamente', 'success');
                } else {
                    if (data.errors) {
                        this.profileErrors = data.errors;
                    }
                    this.showNotification(data.message || 'Error al actualizar perfil', 'error');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.updatingProfile = false;
            }
        },
        
        resetProfileForm() {
            this.form.profile = { ...this.originalProfile };
            this.profileErrors = {};
            this.emailAvailable = null;
        },
        
        // Gestión de credenciales
        async updateUsername() {
            if (!this.form.credentials.username || this.form.credentials.username === this.user.username || this.usernameAvailable === false) {
                return;
            }
            
            this.updatingUsername = true;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/update-username`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ username: this.form.credentials.username })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.user.username = data.data.username;
                    this.showNotification('Nombre de usuario actualizado exitosamente', 'success');
                } else {
                    this.showNotification(data.message || 'Error al actualizar nombre de usuario', 'error');
                }
            } catch (error) {
                console.error('Error updating username:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.updatingUsername = false;
            }
        },
        
        async updatePassword() {
            if (!this.form.credentials.password || this.form.credentials.password !== this.form.credentials.password_confirmation) {
                return;
            }
            
            this.updatingPassword = true;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/reset-password`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        password: this.form.credentials.password,
                        password_confirmation: this.form.credentials.password_confirmation,
                        force_password_change: this.form.credentials.force_password_change,
                        notify_user: this.form.credentials.notify_user,
                        logout_all_sessions: this.form.credentials.logout_all_sessions
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.resetPasswordForm();
                    this.showNotification('Contraseña actualizada exitosamente', 'success');
                    if (this.form.credentials.logout_all_sessions) {
                        this.loadActiveSessions();
                    }
                } else {
                    this.showNotification(data.message || 'Error al actualizar contraseña', 'error');
                }
            } catch (error) {
                console.error('Error updating password:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.updatingPassword = false;
            }
        },
        
        resetPasswordForm() {
            this.form.credentials.password = '';
            this.form.credentials.password_confirmation = '';
            this.form.credentials.force_password_change = false;
            this.form.credentials.notify_user = true;
            this.form.credentials.logout_all_sessions = false;
            this.credentialErrors = {};
            this.passwordStrength = '';
            this.passwordStrengthText = '';
        },
        
        // Validación
        validateProfile() {
            this.profileErrors = {};
            let isValid = true;
            
            if (!this.form.profile.nombres.trim()) {
                this.profileErrors.nombres = 'Los nombres son requeridos';
                isValid = false;
            }
            
            if (!this.form.profile.apellidos.trim()) {
                this.profileErrors.apellidos = 'Los apellidos son requeridos';
                isValid = false;
            }
            
            if (!this.form.profile.email.trim()) {
                this.profileErrors.email = 'El email es requerido';
                isValid = false;
            } else if (!this.isValidEmail(this.form.profile.email)) {
                this.profileErrors.email = 'El formato del email no es válido';
                isValid = false;
            }
            
            if (!this.form.profile.tipo_usuario) {
                this.profileErrors.tipo_usuario = 'El tipo de usuario es requerido';
                isValid = false;
            }
            
            return isValid && this.emailAvailable !== false;
        },
        
        validateProfileField(field) {
            this.profileErrors[field] = '';
            
            switch (field) {
                case 'nombres':
                    if (!this.form.profile.nombres.trim()) {
                        this.profileErrors.nombres = 'Los nombres son requeridos';
                    }
                    break;
                case 'apellidos':
                    if (!this.form.profile.apellidos.trim()) {
                        this.profileErrors.apellidos = 'Los apellidos son requeridos';
                    }
                    break;
                case 'email':
                    if (!this.form.profile.email.trim()) {
                        this.profileErrors.email = 'El email es requerido';
                    } else if (!this.isValidEmail(this.form.profile.email)) {
                        this.profileErrors.email = 'El formato del email no es válido';
                    }
                    break;
                case 'tipo_usuario':
                    if (!this.form.profile.tipo_usuario) {
                        this.profileErrors.tipo_usuario = 'El tipo de usuario es requerido';
                    }
                    break;
            }
        },
        
        validateCredentialField(field) {
            this.credentialErrors[field] = '';
            
            switch (field) {
                case 'username':
                    if (!this.form.credentials.username.trim()) {
                        this.credentialErrors.username = 'El nombre de usuario es requerido';
                    } else if (this.form.credentials.username.length < 3) {
                        this.credentialErrors.username = 'El nombre de usuario debe tener al menos 3 caracteres';
                    }
                    break;
                case 'password':
                    if (!this.form.credentials.password.trim()) {
                        this.credentialErrors.password = 'La contraseña es requerida';
                    } else if (this.form.credentials.password.length < 8) {
                        this.credentialErrors.password = 'La contraseña debe tener al menos 8 caracteres';
                    }
                    break;
                case 'password_confirmation':
                    if (this.form.credentials.password !== this.form.credentials.password_confirmation) {
                        this.credentialErrors.password_confirmation = 'Las contraseñas no coinciden';
                    }
                    break;
            }
        },
        
        // Verificación de disponibilidad
        async checkEmailAvailability() {
            if (!this.form.profile.email || !this.isValidEmail(this.form.profile.email) || this.form.profile.email === this.user.email) {
                this.emailAvailable = null;
                return;
            }
            
            this.checkingEmail = true;
            try {
                const response = await fetch('/api/users/check-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        email: this.form.profile.email,
                        exclude_id: this.user.id 
                    })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.emailAvailable = data.available;
                }
            } catch (error) {
                console.error('Error checking email:', error);
            } finally {
                this.checkingEmail = false;
            }
        },
        
        async checkUsernameAvailability() {
            if (!this.form.credentials.username || this.form.credentials.username.length < 3 || this.form.credentials.username === this.user.username) {
                this.usernameAvailable = null;
                return;
            }
            
            this.checkingUsername = true;
            try {
                const response = await fetch('/api/users/check-username', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        username: this.form.credentials.username,
                        exclude_id: this.user.id 
                    })
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.usernameAvailable = data.available;
                }
            } catch (error) {
                console.error('Error checking username:', error);
            } finally {
                this.checkingUsername = false;
            }
        },
        
        // Utilidades
        isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        formatDate(dateString, format = 'full') {
            if (!dateString) return '';
            const date = new Date(dateString);
            
            if (format === 'short') {
                return date.toLocaleDateString('es-ES', {
                    month: 'short',
                    year: 'numeric'
                });
            }
            
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        
        // Generadores
        generatePassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            this.form.credentials.password = password;
            this.form.credentials.password_confirmation = password;
            this.checkPasswordStrength();
            this.validateCredentialField('password');
            this.validateCredentialField('password_confirmation');
        },
        
        checkPasswordStrength() {
            const password = this.form.credentials.password;
            let score = 0;
            
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^a-zA-Z0-9]/.test(password)) score++;
            
            if (score <= 2) {
                this.passwordStrength = 'weak';
                this.passwordStrengthText = 'Débil';
            } else if (score <= 4) {
                this.passwordStrength = 'medium';
                this.passwordStrengthText = 'Media';
            } else if (score <= 5) {
                this.passwordStrength = 'strong';
                this.passwordStrengthText = 'Fuerte';
            } else {
                this.passwordStrength = 'very-strong';
                this.passwordStrengthText = 'Muy Fuerte';
            }
        },
        
        // Gestión de empleados
        onEmpleadoChange() {
            if (this.form.profile.empleado_id) {
                const empleado = this.empleados.find(e => e.id == this.form.profile.empleado_id);
                if (empleado) {
                    this.form.profile.nombres = empleado.nombres;
                    this.form.profile.apellidos = empleado.apellidos;
                    this.form.profile.email = empleado.email || this.form.profile.email;
                    this.form.profile.telefono = empleado.telefono || this.form.profile.telefono;
                    
                    // Validar campos completados
                    this.validateProfileField('nombres');
                    this.validateProfileField('apellidos');
                    if (this.form.profile.email) {
                        this.validateProfileField('email');
                        this.checkEmailAvailability();
                    }
                }
            }
        },
        
        // Gestión de roles
        get availableRolesToAdd() {
            const userRoleIds = this.user.roles.map(r => r.id);
            return this.availableRoles.filter(role => !userRoleIds.includes(role.id));
        },
        
        async addRole(roleId) {
            this.addingRole = roleId;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/assign-role`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ role_id: roleId })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.user = data.data;
                    this.showNotification('Rol asignado exitosamente', 'success');
                } else {
                    this.showNotification(data.message || 'Error al asignar rol', 'error');
                }
            } catch (error) {
                console.error('Error adding role:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.addingRole = null;
            }
        },
        
        async removeRole(roleId) {
            this.removingRole = roleId;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/remove-role`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ role_id: roleId })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.user = data.data;
                    this.showNotification('Rol removido exitosamente', 'success');
                } else {
                    this.showNotification(data.message || 'Error al remover rol', 'error');
                }
            } catch (error) {
                console.error('Error removing role:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.removingRole = null;
            }
        },
        
        // Gestión de permisos
        get permissionsByCategory() {
            const categories = {};
            this.availablePermissions.forEach(permission => {
                const category = permission.name.split('.')[0] || 'general';
                if (!categories[category]) {
                    categories[category] = [];
                }
                categories[category].push(permission);
            });
            return categories;
        },
        
        get rolePermissionsCount() {
            let count = 0;
            this.user.roles.forEach(role => {
                if (role.permissions) {
                    count += role.permissions.length;
                }
            });
            return count;
        },
        
        get directPermissionsCount() {
            return this.user.permissions ? this.user.permissions.length : 0;
        },
        
        get totalPermissionsCount() {
            return this.rolePermissionsCount + this.directPermissionsCount;
        },
        
        userHasPermission(permissionId) {
            // Verificar permisos directos
            if (this.user.permissions && this.user.permissions.find(p => p.id === permissionId)) {
                return true;
            }
            
            // Verificar permisos por roles
            for (const role of this.user.roles) {
                if (role.permissions && role.permissions.find(p => p.id === permissionId)) {
                    return true;
                }
            }
            
            return false;
        },
        
        isPermissionFromRole(permissionId) {
            for (const role of this.user.roles) {
                if (role.permissions && role.permissions.find(p => p.id === permissionId)) {
                    return true;
                }
            }
            return false;
        },
        
        async togglePermission(permissionId, checked) {
            if (this.isPermissionFromRole(permissionId)) {
                return; // No se puede cambiar permisos que vienen de roles
            }
            
            this.togglingPermission = permissionId;
            try {
                const action = checked ? 'assign' : 'remove';
                const response = await fetch(`/admin/users/${this.user.id}/${action}-permission`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ permission_id: permissionId })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.user = data.data;
                    this.showNotification(`Permiso ${checked ? 'asignado' : 'removido'} exitosamente`, 'success');
                } else {
                    this.showNotification(data.message || `Error al ${checked ? 'asignar' : 'remover'} permiso`, 'error');
                }
            } catch (error) {
                console.error('Error toggling permission:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.togglingPermission = null;
            }
        },
        
        // Gestión de seguridad
        async toggleUserBlock() {
            this.togglingBlock = true;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/toggle-block`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.user = data.data;
                    this.showNotification(data.message, 'success');
                } else {
                    this.showNotification(data.message || 'Error al cambiar estado de bloqueo', 'error');
                }
            } catch (error) {
                console.error('Error toggling block:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.togglingBlock = false;
            }
        },
        
        async resetFailedAttempts() {
            this.resettingAttempts = true;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/reset-failed-attempts`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.user.intentos_fallidos = 0;
                    this.showNotification('Intentos fallidos reseteados', 'success');
                } else {
                    this.showNotification(data.message || 'Error al resetear intentos', 'error');
                }
            } catch (error) {
                console.error('Error resetting attempts:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.resettingAttempts = false;
            }
        },
        
        async applyTempBlock() {
            this.tempBlocking = true;
            try {
                const minutes = this.tempBlock.duration === 'custom' ? 
                    parseInt(this.tempBlock.customMinutes) : 
                    parseInt(this.tempBlock.duration);
                
                const response = await fetch(`/admin/users/${this.user.id}/temp-block`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        minutes: minutes,
                        reason: this.tempBlock.reason
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.user = data.data;
                    this.showTempBlockModal = false;
                    this.tempBlock = { duration: '60', customMinutes: '', reason: '' };
                    this.showNotification('Bloqueo temporal aplicado exitosamente', 'success');
                } else {
                    this.showNotification(data.message || 'Error al aplicar bloqueo temporal', 'error');
                }
            } catch (error) {
                console.error('Error applying temp block:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.tempBlocking = false;
            }
        },
        
        async toggle2FA() {
            this.toggling2FA = true;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/toggle-2fa`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.user = data.data;
                    this.showNotification(data.message, 'success');
                } else {
                    this.showNotification(data.message || 'Error al cambiar 2FA', 'error');
                }
            } catch (error) {
                console.error('Error toggling 2FA:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.toggling2FA = false;
            }
        },
        
        async regenerateRecoveryCodes() {
            this.regeneratingCodes = true;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/regenerate-recovery-codes`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.showNotification('Códigos de recuperación regenerados', 'success');
                } else {
                    this.showNotification(data.message || 'Error al regenerar códigos', 'error');
                }
            } catch (error) {
                console.error('Error regenerating codes:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.regeneratingCodes = false;
            }
        },
        
        async sendEmailVerification() {
            this.sendingVerification = true;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/send-verification`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.showNotification('Email de verificación enviado', 'success');
                } else {
                    this.showNotification(data.message || 'Error al enviar verificación', 'error');
                }
            } catch (error) {
                console.error('Error sending verification:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.sendingVerification = false;
            }
        },
        
        // Gestión de sesiones
        async loadActiveSessions() {
            this.loadingSessions = true;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/sessions`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.activeSessions = data.data || [];
                }
            } catch (error) {
                console.error('Error loading sessions:', error);
            } finally {
                this.loadingSessions = false;
            }
        },
        
        async revokeSession(sessionId) {
            this.revokingSession = sessionId;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/revoke-session/${sessionId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.activeSessions = this.activeSessions.filter(s => s.id !== sessionId);
                    this.showNotification('Sesión revocada exitosamente', 'success');
                } else {
                    this.showNotification(data.message || 'Error al revocar sesión', 'error');
                }
            } catch (error) {
                console.error('Error revoking session:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.revokingSession = null;
            }
        },
        
        async revokeAllOtherSessions() {
            this.revokingAllSessions = true;
            try {
                const response = await fetch(`/admin/users/${this.user.id}/revoke-all-sessions`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.activeSessions = this.activeSessions.filter(s => s.is_current);
                    this.showNotification('Todas las otras sesiones han sido revocadas', 'success');
                } else {
                    this.showNotification(data.message || 'Error al revocar sesiones', 'error');
                }
            } catch (error) {
                console.error('Error revoking sessions:', error);
                this.showNotification('Error de conexión', 'error');
            } finally {
                this.revokingAllSessions = false;
            }
        },
        
        // Gestión de actividad
        async loadUserActivity() {
            this.loadingActivity = true;
            this.activityPage = 1;
            try {
                const params = new URLSearchParams({
                    page: this.activityPage,
                    per_page: 20,
                    ...this.activityFilters
                });
                
                const response = await fetch(`/admin/users/${this.user.id}/activity?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.userActivity = data.data || [];
                    this.hasMoreActivity = data.pagination ? data.pagination.current_page < data.pagination.last_page : false;
                }
            } catch (error) {
                console.error('Error loading activity:', error);
            } finally {
                this.loadingActivity = false;
            }
        },
        
        async loadMoreActivity() {
            if (!this.hasMoreActivity || this.loadingMoreActivity) return;
            
            this.loadingMoreActivity = true;
            this.activityPage++;
            try {
                const params = new URLSearchParams({
                    page: this.activityPage,
                    per_page: 20,
                    ...this.activityFilters
                });
                
                const response = await fetch(`/admin/users/${this.user.id}/activity?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.userActivity = [...this.userActivity, ...(data.data || [])];
                    this.hasMoreActivity = data.pagination ? data.pagination.current_page < data.pagination.last_page : false;
                }
            } catch (error) {
                console.error('Error loading more activity:', error);
            } finally {
                this.loadingMoreActivity = false;
            }
        },
        
        exportUserActivity() {
            const params = new URLSearchParams(this.activityFilters);
            window.open(`/admin/users/${this.user.id}/activity/export?${params}`, '_blank');
        },
        
        // Utilidades
        showNotification(message, type = 'info') {
            // Crear notificación temporal en la parte superior
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }
    }
}
</script>
@endpush
@endsection<!-- Tab: Seguridad -->
    <div x-show="activeTab === 'security'" x-transition:enter="fade-in" class="space-y-6">
        
        <!-- Estado de seguridad -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Configuración de Seguridad
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Gestione las opciones de seguridad y acceso del usuario
                </p>
            </div>
            
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Bloqueos y restricciones -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 12M6 6l12 12" />
                            </svg>
                            Bloqueos y Restricciones
                        </h4>
                        
                        <div class="space-y-4">
                            <!-- Estado de bloqueo -->
                            <div class="security-action flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Estado de Bloqueo</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <span x-show="user.bloqueado">Bloqueado desde: <span x-text="formatDate(user.blocked_at)"></span></span>
                                        <span x-show="!user.bloqueado">El usuario no está bloqueado</span>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="toggleUserBlock()"
                                        :disabled="togglingBlock"
                                        class="px-3 py-1 text-xs font-medium rounded-md transition-colors"
                                        :class="user.bloqueado ? 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200'">
                                    <span x-show="!togglingBlock" x-text="user.bloqueado ? 'Desbloquear' : 'Bloquear'"></span>
                                    <span x-show="togglingBlock">Procesando...</span>
                                </button>
                            </div>
                            
                            <!-- Intentos fallidos -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Intentos Fallidos</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <span x-text="user.intentos_fallidos"></span> intentos registrados
                                    </div>
                                </div>
                                <button type="button"
                                        @click="resetFailedAttempts()"
                                        :disabled="resettingAttempts || user.intentos_fallidos === 0"
                                        class="px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200 disabled:opacity-50 dark:bg-yellow-900 dark:text-yellow-200">
                                    <span x-show="!resettingAttempts">Resetear</span>
                                    <span x-show="resettingAttempts">Reseteando...</span>
                                </button>
                            </div>
                            
                            <!-- Bloqueo temporal -->
                            <div class="p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Bloqueo Temporal</div>
                                    <button type="button"
                                            @click="showTempBlockModal = true"
                                            class="px-3 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-md hover:bg-orange-200 dark:bg-orange-900 dark:text-orange-200">
                                        Configurar
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Bloquear usuario por un período específico
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Autenticación de dos factores -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Autenticación Dos Factores
                        </h4>
                        
                        <div class="space-y-4">
                            <!-- Estado 2FA -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">2FA Estado</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <span x-show="user.two_factor_secret">Configurado y activo</span>
                                        <span x-show="!user.two_factor_secret">No configurado</span>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="toggle2FA()"
                                        :disabled="toggling2FA"
                                        class="px-3 py-1 text-xs font-medium rounded-md transition-colors"
                                        :class="user.two_factor_secret ? 'bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-200'">
                                    <span x-show="!toggling2FA" x-text="user.two_factor_secret ? 'Deshabilitar' : 'Habilitar'"></span>
                                    <span x-show="toggling2FA">Procesando...</span>
                                </button>
                            </div>
                            
                            <!-- Códigos de recuperación -->
                            <div x-show="user.two_factor_secret" class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Códigos de Recuperación</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Generar nuevos códigos de respaldo
                                    </div>
                                </div>
                                <button type="button"
                                        @click="regenerateRecoveryCodes()"
                                        :disabled="regeneratingCodes"
                                        class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-md hover:bg-blue-200 disabled:opacity-50 dark:bg-blue-900 dark:text-blue-200">
                                    <span x-show="!regeneratingCodes">Regenerar</span>
                                    <span x-show="regeneratingCodes">Generando...</span>
                                </button>
                            </div>
                            
                            <!-- Verificación de email -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Verificación Email</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <span x-show="user.email_verified_at">Verificado el <span x-text="formatDate(user.email_verified_at)"></span></span>
                                        <span x-show="!user.email_verified_at">Email no verificado</span>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="sendEmailVerification()"
                                        :disabled="sendingVerification || user.email_verified_at"
                                        class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-md hover:bg-blue-200 disabled:opacity-50 dark:bg-blue-900 dark:text-blue-200">
                                    <span x-show="!sendingVerification">Enviar Verificación</span>
                                    <span x-show="sendingVerification">Enviando...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sesiones activas -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Sesiones Activas
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Gestione las sesiones activas del usuario
                        </p>
                    </div>
                    <button type="button"
                            @click="loadActiveSessions()"
                            :disabled="loadingSessions"
                            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        <svg x-show="!loadingSessions" class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <svg x-show="loadingSessions" class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Actualizar
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-6">
                <div x-show="activeSessions.length === 0 && !loadingSessions" class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <p>No hay sesiones activas</p>
                </div>
                
                <div class="space-y-4">
                    <template x-for="session in activeSessions" :key="session.id">
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        <span x-text="session.ip_address"></span>
                                        <span x-show="session.is_current" class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Sesión Actual
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <span x-text="session.user_agent"></span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Última actividad: <span x-text="formatDate(session.last_activity)"></span>
                                    </div>
                                </div>
                            </div>
                            <button type="button"
                                    @click="revokeSession(session.id)"
                                    :disabled="session.is_current || revokingSession === session.id"
                                    class="px-3 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-md hover:bg-red-200 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-red-900 dark:text-red-200">
                                <span x-show="session.is_current">Actual</span>
                                <span x-show="!session.is_current && revokingSession !== session.id">Revocar</span>
                                <span x-show="revokingSession === session.id">Revocando...</span>
                            </button>
                        </div>
                    </template>
                </div>
                
                <div x-show="activeSessions.length > 1" class="mt-6 text-center">
                    <button type="button"
                            @click="revokeAllOtherSessions()"
                            :disabled="revokingAllSessions"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 disabled:opacity-50">
                        <span x-show="!revokingAllSessions">Revocar Todas las Otras Sesiones</span>
                        <span x-show="revokingAllSessions">Revocando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Actividad -->
    <div x-show="activeTab === 'activity'" x-transition:enter="fade-in" class="space-y-6">
        
        <!-- Filtros de actividad -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Filtros de Actividad
                </h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="activity_module" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Módulo
                        </label>
                        <select id="activity_module" 
                                x-model="activityFilters.module"
                                @change="loadUserActivity()"
                                class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="">Todos los módulos</option>
                            <option value="Dashboard">Dashboard</option>
                            <option value="Usuarios">Usuarios</option>
                            <option value="Clientes">Clientes</option>
                            <option value="Productos">Productos</option>
                            <option value="Reparaciones">Reparaciones</option>
                            <option value="Ventas">Ventas</option>
                            <option value="Empleados">Empleados</option>
                            <option value="Reportes">Reportes</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="activity_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Desde
                        </label>
                        <input type="date" 
                               id="activity_from" 
                               x-model="activityFilters.from_date"
                               @change="loadUserActivity()"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-gestion-500 focus:border-gestion-500">
                    </div>
                    
                    <div>
                        <label for="activity_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Hasta
                        </label>
                        <input type="date" 
                               id="activity_to" 
                               x-model="activityFilters.to_date"
                               @change="loadUserActivity()"
                               class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-gestion-500 focus:border-gestion-500">
                    </div>
                    
                    <div class="flex items-end">
                        <button type="button"
                                @click="exportUserActivity()"
                                class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Exportar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Timeline de actividad -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Actividad Reciente
                    </h3>
                    <button type="button"
                            @click="loadUserActivity()"
                            :disabled="loadingActivity"
                            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        <svg x-show="!loadingActivity" class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <svg x-show="loadingActivity" class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Actualizar
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-6">
                <div x-show="userActivity.length === 0 && !loadingActivity" class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p>No hay actividad registrada para los filtros seleccionados</p>
                </div>
                
                <div class="space-y-6">
                    <template x-for="activity in userActivity" :key="activity.id">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gestion-100 dark:bg-gestion-900 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="activity.accion"></div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="formatDate(activity.created_at)"></div>
                                </div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mr-2">
                                        <span x-text="activity.modulo"></span>
                                    </span>
                                    <span x-text="activity.ruta"></span>
                                </div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex items-center space-x-4">
                                    <span>IP: <span x-text="activity.ip" class="font-mono"></span></span>
                                    <span>Accesos: <span x-text="activity.contador_accesos"></span></span>
                                    <span x-