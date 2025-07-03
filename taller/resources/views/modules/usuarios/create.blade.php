{{-- resources/views/modules/usuarios/create.blade.php - PARTE 1 DE 3 --}}
@extends('layouts.app')

@section('title', 'Crear Usuario')

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
        <span class="ml-2 text-sm font-medium text-gray-500">Crear Usuario</span>
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
    .step-indicator { 
        transition: all 0.3s ease; 
    }
    .step-active { 
        @apply bg-gestion-600 text-white; 
    }
    .step-completed { 
        @apply bg-green-600 text-white; 
    }
    .step-pending { 
        @apply bg-gray-200 text-gray-500; 
    }
    .form-section { 
        min-height: 400px; 
    }
    .password-strength-bar { 
        transition: all 0.3s ease; 
    }
    .availability-check {
        transition: all 0.2s ease;
    }
    .role-card {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .role-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .role-card.selected {
        border-color: #3B82F6;
        background-color: #EFF6FF;
    }
</style>
@endpush

@section('content')
<div x-data="userCreateForm()" x-init="init()" class="space-y-6">
    
    <!-- Header con información del formulario -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Crear Nuevo Usuario
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Complete la información para crear un nuevo usuario del sistema
                    </p>
                </div>
                
                <div class="flex space-x-3">
                    <a href="{{ route('admin.users.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Indicador de progreso por pasos -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    
                    <!-- Paso 1: Información Básica -->
                    <div class="flex items-center">
                        <div class="step-indicator w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                             :class="currentStep >= 1 ? (currentStep > 1 ? 'step-completed' : 'step-active') : 'step-pending'">
                            <span x-show="currentStep > 1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span x-show="currentStep <= 1">1</span>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Información Básica</span>
                    </div>
                    
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    
                    <!-- Paso 2: Credenciales -->
                    <div class="flex items-center">
                        <div class="step-indicator w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                             :class="currentStep >= 2 ? (currentStep > 2 ? 'step-completed' : 'step-active') : 'step-pending'">
                            <span x-show="currentStep > 2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span x-show="currentStep <= 2">2</span>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Credenciales</span>
                    </div>
                    
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    
                    <!-- Paso 3: Roles y Permisos -->
                    <div class="flex items-center">
                        <div class="step-indicator w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                             :class="currentStep >= 3 ? (currentStep > 3 ? 'step-completed' : 'step-active') : 'step-pending'">
                            <span x-show="currentStep > 3">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span x-show="currentStep <= 3">3</span>
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Roles y Permisos</span>
                    </div>
                    
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    
                    <!-- Paso 4: Confirmación -->
                    <div class="flex items-center">
                        <div class="step-indicator w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium"
                             :class="currentStep >= 4 ? 'step-active' : 'step-pending'">
                            4
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Confirmación</span>
                    </div>
                </div>
            </div>
            
            <!-- Barra de progreso -->
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gestion-600 h-2 rounded-full transition-all duration-300"
                         :style="`width: ${(currentStep / 4) * 100}%`"></div>
                </div>
                <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Paso <span x-text="currentStep"></span> de 4 - <span x-text="getStepDescription()"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario principal -->
    <form @submit.prevent="submitForm()" class="space-y-6">
        @csrf
        
        <!-- Paso 1: Información Básica -->
        <div x-show="currentStep === 1" x-transition:enter="fade-in" class="form-section">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Información Personal del Usuario
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Ingrese los datos personales básicos del nuevo usuario
                    </p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Empleado asociado (opcional) -->
                        <div class="col-span-1 lg:col-span-2">
                            <label for="empleado_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Empleado Asociado (Opcional)
                            </label>
                            <div class="mt-1">
                                <select id="empleado_id" 
                                        name="empleado_id" 
                                        x-model="form.empleado_id"
                                        @change="onEmpleadoChange()"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                        :class="errors.empleado_id ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''">
                                    <option value="">Seleccionar empleado existente (opcional)</option>
                                    <template x-for="empleado in empleados" :key="empleado.id">
                                        <option :value="empleado.id" 
                                                x-text="`${empleado.nombres} ${empleado.apellidos} - ${empleado.especialidad || 'Sin especialidad'}`">
                                        </option>
                                    </template>
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Si selecciona un empleado, algunos campos se completarán automáticamente
                            </p>
                            <div x-show="errors.empleado_id" class="mt-1 text-sm text-red-600" x-text="errors.empleado_id"></div>
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
                                       x-model="form.nombres"
                                       @input="validateField('nombres')"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       :class="errors.nombres ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                       placeholder="Ingrese los nombres del usuario"
                                       autocomplete="given-name">
                            </div>
                            <div x-show="errors.nombres" class="mt-1 text-sm text-red-600" x-text="errors.nombres"></div>
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
                                       x-model="form.apellidos"
                                       @input="validateField('apellidos')"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       :class="errors.apellidos ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                       placeholder="Ingrese los apellidos del usuario"
                                       autocomplete="family-name">
                            </div>
                            <div x-show="errors.apellidos" class="mt-1 text-sm text-red-600" x-text="errors.apellidos"></div>
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
                                       x-model="form.email"
                                       @input="validateField('email')"
                                       @blur="checkEmailAvailability()"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 pr-10"
                                       :class="errors.email ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : (emailAvailable === false ? 'border-red-300' : (emailAvailable === true ? 'border-green-300' : ''))"
                                       placeholder="usuario@ejemplo.com"
                                       autocomplete="email">
                                
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
                            <div x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email"></div>
                            <div x-show="emailAvailable === false && !errors.email" class="mt-1 text-sm text-red-600">
                                Este correo electrónico ya está en uso por otro usuario
                            </div>
                            <div x-show="emailAvailable === true && !errors.email" class="mt-1 text-sm text-green-600">
                                Email disponible
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
                                       x-model="form.telefono"
                                       @input="validateField('telefono')"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                       :class="errors.telefono ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                       placeholder="+51 987 654 321"
                                       autocomplete="tel">
                            </div>
                            <div x-show="errors.telefono" class="mt-1 text-sm text-red-600" x-text="errors.telefono"></div>
                        </div>
                        
                        <!-- Tipo de usuario -->
                        <div class="col-span-1">
                            <label for="tipo_usuario" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tipo de Usuario <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1">
                                <select id="tipo_usuario" 
                                        name="tipo_usuario" 
                                        x-model="form.tipo_usuario"
                                        @change="validateField('tipo_usuario')"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                                        :class="errors.tipo_usuario ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''">
                                    <option value="">Seleccionar tipo de usuario</option>
                                    <option value="administrador">Administrador</option>
                                    <option value="gerente">Gerente</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="vendedor">Vendedor</option>
                                    <option value="tecnico">Técnico</option>
                                    <option value="empleado">Empleado</option>
                                </select>
                            </div>
                            <div x-show="errors.tipo_usuario" class="mt-1 text-sm text-red-600" x-text="errors.tipo_usuario"></div>
                        </div>
                        
                        <!-- Estado inicial -->
                        <div class="col-span-1">
                            <label for="activo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Estado Inicial del Usuario
                            </label>
                            <div class="mt-1">
                                <select id="activo" 
                                        name="activo" 
                                        x-model="form.activo"
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                                    <option value="1">Activo (puede acceder al sistema)</option>
                                    <option value="0">Inactivo (no puede acceder)</option>
                                </select>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Solo los usuarios activos pueden iniciar sesión en el sistema
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- Paso 2: Credenciales -->
        <div x-show="currentStep === 2" x-transition:enter="fade-in" class="form-section">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z" />
                        </svg>
                        Credenciales de Acceso
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Configure las credenciales de acceso y opciones de seguridad
                    </p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Nombre de usuario -->
                        <div class="col-span-1 lg:col-span-2">
                            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nombre de Usuario <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       x-model="form.username"
                                       @input="validateField('username')"
                                       @blur="checkUsernameAvailability()"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 pr-10"
                                       :class="errors.username ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : (usernameAvailable === false ? 'border-red-300' : (usernameAvailable === true ? 'border-green-300' : ''))"
                                       placeholder="usuario123"
                                       autocomplete="username">
                                
                                <!-- Indicador de verificación de username -->
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
                            <div class="mt-1 flex justify-between">
                                <div>
                                    <div x-show="errors.username" class="text-sm text-red-600" x-text="errors.username"></div>
                                    <div x-show="usernameAvailable === false && !errors.username" class="text-sm text-red-600">
                                        Este nombre de usuario ya está en uso
                                    </div>
                                    <div x-show="usernameAvailable === true && !errors.username" class="text-sm text-green-600">
                                        Nombre de usuario disponible
                                    </div>
                                </div>
                                <button type="button" 
                                        @click="generateUsername()"
                                        class="text-xs text-gestion-600 hover:text-gestion-700 font-medium transition-colors">
                                    Generar automáticamente
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Mínimo 3 caracteres, solo letras, números y guiones bajos. Será usado para iniciar sesión.
                            </p>
                        </div>
                        
                        <!-- Contraseña -->
                        <div class="col-span-1">
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Contraseña <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <input :type="showPassword ? 'text' : 'password'" 
                                       id="password" 
                                       name="password" 
                                       x-model="form.password"
                                       @input="validateField('password'); checkPasswordStrength()"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 pr-10"
                                       :class="errors.password ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                       placeholder="Ingrese una contraseña segura"
                                       autocomplete="new-password">
                                
                                <!-- Toggle mostrar/ocultar contraseña -->
                                <button type="button" 
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg x-show="!showPassword" class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showPassword" class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Indicador de fortaleza de contraseña -->
                            <div x-show="form.password" class="mt-2">
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
                                    <span class="text-xs font-medium w-16"
                                          :class="{
                                              'text-red-600': passwordStrength === 'weak',
                                              'text-yellow-600': passwordStrength === 'medium',
                                              'text-green-600': passwordStrength === 'strong',
                                              'text-green-700': passwordStrength === 'very-strong'
                                          }"
                                          x-text="passwordStrengthText"></span>
                                </div>
                                
                                <!-- Criterios de contraseña -->
                                <div class="mt-2 text-xs space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3" :class="form.password.length >= 8 ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span :class="form.password.length >= 8 ? 'text-gray-700 dark:text-gray-300' : 'text-gray-500'">
                                            Al menos 8 caracteres
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3" :class="/[A-Z]/.test(form.password) ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span :class="/[A-Z]/.test(form.password) ? 'text-gray-700 dark:text-gray-300' : 'text-gray-500'">
                                            Una letra mayúscula
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3" :class="/[0-9]/.test(form.password) ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span :class="/[0-9]/.test(form.password) ? 'text-gray-700 dark:text-gray-300' : 'text-gray-500'">
                                            Al menos un número
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-1 flex justify-between">
                                <div x-show="errors.password" class="text-sm text-red-600" x-text="errors.password"></div>
                                <button type="button" 
                                        @click="generatePassword()"
                                        class="text-xs text-gestion-600 hover:text-gestion-700 font-medium transition-colors">
                                    Generar automáticamente
                                </button>
                            </div>
                        </div>
                        
                        <!-- Confirmar contraseña -->
                        <div class="col-span-1">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Confirmar Contraseña <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <input :type="showPasswordConfirmation ? 'text' : 'password'" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       x-model="form.password_confirmation"
                                       @input="validateField('password_confirmation')"
                                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 pr-10"
                                       :class="errors.password_confirmation ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : (form.password && form.password_confirmation && form.password === form.password_confirmation ? 'border-green-300' : '')"
                                       placeholder="Confirme la contraseña"
                                       autocomplete="new-password">
                                
                                <!-- Toggle mostrar/ocultar contraseña de confirmación -->
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
                            <div x-show="errors.password_confirmation" class="mt-1 text-sm text-red-600" x-text="errors.password_confirmation"></div>
                            <div x-show="form.password && form.password_confirmation && form.password === form.password_confirmation && !errors.password_confirmation" 
                                 class="mt-1 text-sm text-green-600 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Las contraseñas coinciden
                            </div>
                        </div>
                        
                        <!-- Opciones de seguridad -->
                        <div class="col-span-1 lg:col-span-2">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Opciones de Seguridad Inicial
                                </h4>
                                
                                <div class="space-y-3">
                                    <!-- Forzar cambio de contraseña -->
                                    <label class="flex items-start">
                                        <input type="checkbox" 
                                               x-model="form.force_password_change"
                                               class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500 mt-0.5">
                                        <div class="ml-3">
                                            <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                                Forzar cambio de contraseña en el primer login
                                            </span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                El usuario deberá cambiar su contraseña cuando inicie sesión por primera vez
                                            </p>
                                        </div>
                                    </label>
                                    
                                    <!-- Enviar credenciales por email -->
                                    <label class="flex items-start">
                                        <input type="checkbox" 
                                               x-model="form.send_credentials"
                                               class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500 mt-0.5">
                                        <div class="ml-3">
                                            <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                                Enviar credenciales de acceso por email
                                            </span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Se enviará un email con las credenciales de acceso al usuario
                                            </p>
                                        </div>
                                    </label>
                                    
                                    <!-- Verificación de email requerida -->
                                    <label class="flex items-start">
                                        <input type="checkbox" 
                                               x-model="form.email_verification_required"
                                               class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500 mt-0.5">
                                        <div class="ml-3">
                                            <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                                Requerir verificación de email antes del primer acceso
                                            </span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                El usuario debe verificar su email antes de poder iniciar sesión
                                            </p>
                                        </div>
                                    </label>
                                    
                                    <!-- Habilitar 2FA desde el inicio -->
                                    <label class="flex items-start">
                                        <input type="checkbox" 
                                               x-model="form.enable_2fa"
                                               class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500 mt-0.5">
                                        <div class="ml-3">
                                            <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">
                                                Configurar autenticación de dos factores (2FA)
                                            </span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Se le pedirá al usuario configurar 2FA en su primer login (recomendado para administradores)
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información adicional de seguridad -->
                        <div class="col-span-1 lg:col-span-2">
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                            Recomendaciones de Seguridad
                                        </h3>
                                        <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                            <ul class="list-disc pl-5 space-y-1">
                                                <li>Use contraseñas fuertes con al menos 8 caracteres</li>
                                                <li>Incluya mayúsculas, minúsculas, números y símbolos</li>
                                                <li>Active la verificación de email para mayor seguridad</li>
                                                <li>Considere habilitar 2FA para usuarios con privilegios administrativos</li>
                                                <li>Force el cambio de contraseña en el primer login para mayor seguridad</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paso 3: Roles y Permisos -->
        <div x-show="currentStep === 3" x-transition:enter="fade-in" class="form-section">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Roles y Permisos del Sistema
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Asigne roles y permisos específicos para definir las capacidades del usuario
                    </p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="space-y-6">
                        
                        <!-- Selección de roles principales -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Roles del Sistema <span class="text-red-500">*</span>
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                Seleccione uno o más roles. Los permisos se acumularán automáticamente.
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <template x-for="role in availableRoles" :key="role.id">
                                    <div class="role-card relative">
                                        <label class="block cursor-pointer">
                                            <input type="checkbox" 
                                                   :value="role.id"
                                                   x-model="form.roles"
                                                   @change="onRoleChange(role)"
                                                   class="sr-only">
                                            <div class="p-4 border-2 rounded-lg transition-all duration-200"
                                                 :class="form.roles.includes(role.id) ? 'border-gestion-500 bg-gestion-50 dark:bg-gestion-900/20 selected' : 'border-gray-300 dark:border-gray-600 hover:border-gestion-300'">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <div class="flex items-center">
                                                            <div class="font-medium text-gray-900 dark:text-white" x-text="role.display_name || role.name"></div>
                                                            <svg x-show="form.roles.includes(role.id)" class="ml-2 h-4 w-4 text-gestion-600" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="role.description || 'Sin descripción'"></div>
                                                        <div class="mt-2 flex items-center text-xs text-gray-400">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                            </svg>
                                                            <span x-text="`${role.permissions_count || 0} permisos`"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Preview de permisos principales -->
                                                <div x-show="form.roles.includes(role.id) && role.permissions && role.permissions.length > 0" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Permisos incluidos:</div>
                                                    <div class="flex flex-wrap gap-1">
                                                        <template x-for="(permission, index) in role.permissions.slice(0, 3)" :key="permission.id">
                                                            <span class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 rounded">
                                                                <span x-text="permission.display_name || permission.name"></span>
                                                            </span>
                                                        </template>
                                                        <span x-show="role.permissions.length > 3" class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded">
                                                            +<span x-text="role.permissions.length - 3"></span> más
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </template>
                            </div>
                            <div x-show="errors.roles" class="mt-2 text-sm text-red-600" x-text="errors.roles"></div>
                        </div>
                        
                        <!-- Resumen de permisos por roles seleccionados -->
                        <div x-show="selectedRolePermissions.length > 0" class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-3">
                                Permisos Otorgados por Roles Seleccionados
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <template x-for="permission in selectedRolePermissions.slice(0, 12)" :key="permission.id">
                                    <div class="flex items-center text-sm text-blue-800 dark:text-blue-300">
                                        <svg class="w-3 h-3 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span x-text="permission.display_name || permission.name"></span>
                                    </div>
                                </template>
                            </div>
                            <div x-show="selectedRolePermissions.length > 12" class="mt-3 text-sm text-blue-700 dark:text-blue-300">
                                Y <span x-text="selectedRolePermissions.length - 12"></span> permisos adicionales...
                            </div>
                            <div class="mt-3 text-sm font-medium text-blue-900 dark:text-blue-200">
                                Total: <span x-text="selectedRolePermissions.length"></span> permisos únicos
                            </div>
                        </div>
                        
                        <!-- Permisos específicos adicionales -->
                        <div x-show="availableAdditionalPermissions.length > 0">
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Permisos Específicos Adicionales
                                </label>
                                <button type="button" 
                                        @click="showAllPermissions = !showAllPermissions"
                                        class="text-xs text-gestion-600 hover:text-gestion-700 font-medium">
                                    <span x-show="!showAllPermissions">Ver todos los permisos</span>
                                    <span x-show="showAllPermissions">Mostrar menos</span>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                Agregue permisos específicos que no están incluidos en los roles seleccionados.
                            </p>
                            
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4"
                                 :class="showAllPermissions ? 'max-h-96 overflow-y-auto' : 'max-h-48 overflow-y-auto'">
                                
                                <!-- Agrupación por categorías -->
                                <template x-for="(permissions, category) in groupedAdditionalPermissions" :key="category">
                                    <div class="mb-4 last:mb-0">
                                        <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 capitalize">
                                            <span x-text="category.replace('_', ' ')"></span>
                                            <span class="ml-1 text-gray-400">(<span x-text="permissions.length"></span>)</span>
                                        </h5>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            <template x-for="permission in permissions" :key="permission.id">
                                                <label class="flex items-start p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer">
                                                    <input type="checkbox" 
                                                           :value="permission.id"
                                                           x-model="form.additional_permissions"
                                                           class="rounded border-gray-300 text-gestion-600 focus:ring-gestion-500 mt-0.5">
                                                    <div class="ml-2">
                                                        <div class="text-sm text-gray-700 dark:text-gray-300" x-text="permission.display_name || permission.name"></div>
                                                        <div x-show="permission.description" class="text-xs text-gray-500 dark:text-gray-400" x-text="permission.description"></div>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                
                                <div x-show="availableAdditionalPermissions.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    Todos los permisos disponibles están incluidos en los roles seleccionados
                                </div>
                            </div>
                        </div>
                        
                        <!-- Resumen final de permisos -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                Resumen de Permisos Totales
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="text-center p-3 bg-white dark:bg-gray-700 rounded-lg">
                                    <div class="text-lg font-semibold text-blue-600 dark:text-blue-400" x-text="selectedRolePermissions.length"></div>
                                    <div class="text-gray-600 dark:text-gray-400">Por Roles</div>
                                </div>
                                <div class="text-center p-3 bg-white dark:bg-gray-700 rounded-lg">
                                    <div class="text-lg font-semibold text-green-600 dark:text-green-400" x-text="form.additional_permissions.length"></div>
                                    <div class="text-gray-600 dark:text-gray-400">Específicos</div>
                                </div>
                                <div class="text-center p-3 bg-white dark:bg-gray-700 rounded-lg border-2 border-gestion-200 dark:border-gestion-700">
                                    <div class="text-lg font-semibold text-gestion-600 dark:text-gestion-400" x-text="totalPermissions"></div>
                                    <div class="text-gray-600 dark:text-gray-400">Total Únicos</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Advertencias sobre permisos críticos -->
                        <div x-show="hasCriticalPermissions" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                        Permisos Críticos Detectados
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p>Este usuario tendrá permisos administrativos críticos. Asegúrese de que:</p>
                                        <ul class="list-disc pl-5 mt-1 space-y-1">
                                            <li>La persona es de confianza y tiene la formación necesaria</li>
                                            <li>Se ha habilitado la autenticación de dos factores</li>
                                            <li>Se requiere verificación de email antes del primer acceso</li>
                                            <li>Se fuerza el cambio de contraseña en el primer login</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- Paso 4: Confirmación -->
        <div x-show="currentStep === 4" x-transition:enter="fade-in" class="form-section">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Confirmación y Resumen
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Revise toda la información antes de crear el usuario
                    </p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <!-- Información Personal -->
                        <div class="space-y-6">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Información Personal
                                </h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre Completo</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium" x-text="`${form.nombres} ${form.apellidos}`"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Correo Electrónico</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white" x-text="form.email"></dd>
                                    </div>
                                    <div x-show="form.telefono">
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teléfono</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white" x-text="form.telefono"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo de Usuario</dt>
                                        <dd class="mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 capitalize" x-text="form.tipo_usuario"></span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado Inicial</dt>
                                        <dd class="mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                  :class="form.activo === '1' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'"
                                                  x-text="form.activo === '1' ? 'Activo' : 'Inactivo'"></span>
                                        </dd>
                                    </div>
                                    <div x-show="form.empleado_id">
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Empleado Asociado</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white" x-text="getEmpleadoName(form.empleado_id)"></dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <!-- Credenciales y Seguridad -->
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z" />
                                    </svg>
                                    Credenciales y Seguridad
                                </h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre de Usuario</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono" x-text="form.username"></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contraseña</dt>
                                        <dd class="mt-1 flex items-center space-x-2">
                                            <span class="text-sm text-gray-900 dark:text-white font-mono" x-text="'●'.repeat(form.password.length)"></span>
                                            <span class="text-xs px-2 py-1 rounded-full"
                                                  :class="{
                                                      'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': passwordStrength === 'weak',
                                                      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': passwordStrength === 'medium',
                                                      'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': passwordStrength === 'strong',
                                                      'bg-green-200 text-green-900 dark:bg-green-800 dark:text-green-100': passwordStrength === 'very-strong'
                                                  }"
                                                  x-text="passwordStrengthText"></span>
                                        </dd>
                                    </div>
                                    
                                    <!-- Opciones de seguridad seleccionadas -->
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Configuraciones de Seguridad</dt>
                                        <dd class="space-y-2">
                                            <div x-show="form.force_password_change" class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Forzar cambio de contraseña en primer login
                                            </div>
                                            <div x-show="form.send_credentials" class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Enviar credenciales por email
                                            </div>
                                            <div x-show="form.email_verification_required" class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Verificación de email requerida
                                            </div>
                                            <div x-show="form.enable_2fa" class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Configurar autenticación 2FA
                                            </div>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                        
                        <!-- Roles y Permisos -->
                        <div class="space-y-6">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Roles y Permisos
                                </h4>
                                
                                <!-- Roles asignados -->
                                <div class="mb-6">
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Roles Asignados</dt>
                                    <dd>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="roleId in form.roles" :key="roleId">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gestion-100 text-gestion-800 dark:bg-gestion-900 dark:text-gestion-200">
                                                    <span x-text="getRoleName(roleId)"></span>
                                                </span>
                                            </template>
                                            <span x-show="form.roles.length === 0" class="text-sm text-gray-500 dark:text-gray-400 italic">
                                                Ningún rol asignado
                                            </span>
                                        </div>
                                    </dd>
                                </div>
                                
                                <!-- Estadísticas de permisos -->
                                <div class="bg-gradient-to-r from-gestion-50 to-blue-50 dark:from-gestion-900/20 dark:to-blue-900/20 rounded-lg p-4 mb-6">
                                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Resumen de Permisos</h5>
                                    <div class="grid grid-cols-3 gap-4 text-center">
                                        <div>
                                            <div class="text-lg font-bold text-blue-600 dark:text-blue-400" x-text="selectedRolePermissions.length"></div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Por Roles</div>
                                        </div>
                                        <div>
                                            <div class="text-lg font-bold text-green-600 dark:text-green-400" x-text="form.additional_permissions.length"></div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Específicos</div>
                                        </div>
                                        <div>
                                            <div class="text-lg font-bold text-gestion-600 dark:text-gestion-400" x-text="totalPermissions"></div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Total</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Lista de permisos principales -->
                                <div x-show="totalPermissions > 0">
                                    <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                        Permisos Principales 
                                        <span class="text-gray-400">(<span x-text="Math.min(10, totalPermissions)"></span> de <span x-text="totalPermissions"></span>)</span>
                                    </dt>
                                    <dd>
                                        <div class="space-y-1 max-h-32 overflow-y-auto">
                                            <template x-for="(permission, index) in getAllPermissions().slice(0, 10)" :key="permission.id">
                                                <div class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                                                    <svg class="w-3 h-3 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span x-text="permission.display_name || permission.name"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <div x-show="totalPermissions > 10" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            Y <span x-text="totalPermissions - 10"></span> permisos adicionales...
                                        </div>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Advertencia final si es necesaria -->
                    <div x-show="hasCriticalPermissions || form.tipo_usuario === 'administrador'" class="mt-8 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    Usuario con Permisos Críticos
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p>Este usuario tendrá acceso a funciones críticas del sistema. Verifique que todas las configuraciones de seguridad sean apropiadas antes de continuar.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botones de navegación -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4">
                <div class="flex justify-between items-center">
                    
                    <!-- Botón anterior -->
                    <button type="button" 
                            @click="previousStep()"
                            x-show="currentStep > 1"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Anterior
                    </button>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Indicador de paso actual -->
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            Paso <span x-text="currentStep"></span> de 4
                        </span>
                        
                        <div class="flex space-x-3">
                            <!-- Botón siguiente -->
                            <button type="button" 
                                    @click="nextStep()"
                                    x-show="currentStep < 4"
                                    :disabled="!canProceedToNextStep()"
                                    class="inline-flex items-center px-6 py-2 bg-gestion-600 text-white text-sm font-medium rounded-lg hover:bg-gestion-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-text="getNextButtonText()"></span>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                            
                            <!-- Botón crear usuario -->
                            <button type="submit" 
                                    x-show="currentStep === 4"
                                    :disabled="submitting || !isFormValid()"
                                    class="inline-flex items-center px-8 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg x-show="!submitting" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <svg x-show="submitting" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-show="!submitting">Crear Usuario</span>
                                <span x-show="submitting">Creando Usuario...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function userCreateForm() {
    return {
        // Estado del formulario
        currentStep: 1,
        submitting: false,
        
        // Datos del formulario
        form: {
            empleado_id: '',
            nombres: '',
            apellidos: '',
            email: '',
            telefono: '',
            tipo_usuario: '',
            activo: '1',
            username: '',
            password: '',
            password_confirmation: '',
            force_password_change: true,
            send_credentials: true,
            email_verification_required: false,
            enable_2fa: false,
            roles: [],
            additional_permissions: []
        },
        
        // Errores de validación
        errors: {},
        
        // Estados de verificación
        emailAvailable: null,
        checkingEmail: false,
        usernameAvailable: null,
        checkingUsername: false,
        
        // Contraseña
        showPassword: false,
        showPasswordConfirmation: false,
        passwordStrength: '',
        passwordStrengthText: '',
        
        // Datos externos
        empleados: [],
        availableRoles: [],
        availablePermissions: [],
        selectedRolePermissions: [],
        showAllPermissions: false,
        
        // Inicialización
        async init() {
            await this.loadEmpleados();
            await this.loadRoles();
            await this.loadPermissions();
        },
        
        // Cargar datos externos
        async loadEmpleados() {
            try {
                const response = await fetch('{{ route("api.empleados.index") }}?sin_usuario=1', {
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
                const response = await fetch('{{ route("api.roles.index") }}', {
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
                const response = await fetch('{{ route("api.permissions.index") }}', {
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
        
        // Navegación entre pasos
        nextStep() {
            if (this.canProceedToNextStep()) {
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        
        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        
        canProceedToNextStep() {
            switch (this.currentStep) {
                case 1:
                    return this.validateStep1();
                case 2:
                    return this.validateStep2();
                case 3:
                    return this.validateStep3();
                default:
                    return false;
            }
        },
        
        // Textos dinámicos
        getStepDescription() {
            const descriptions = {
                1: 'Información personal del usuario',
                2: 'Credenciales de acceso y seguridad',
                3: 'Roles y permisos del sistema',
                4: 'Revisión y confirmación'
            };
            return descriptions[this.currentStep] || '';
        },
        
        getNextButtonText() {
            return this.currentStep === 3 ? 'Revisar y Crear' : 'Siguiente';
        },
        
        // Validación por pasos
        validateStep1() {
            const required = ['nombres', 'apellidos', 'email', 'tipo_usuario'];
            return required.every(field => this.form[field].trim() !== '') &&
                   this.emailAvailable !== false &&
                   Object.keys(this.errors).filter(key => required.includes(key)).length === 0;
        },
        
        validateStep2() {
            return this.form.username.trim() !== '' &&
                   this.form.password.trim() !== '' &&
                   this.form.password === this.form.password_confirmation &&
                   this.usernameAvailable !== false &&
                   this.passwordStrength !== 'weak' &&
                   Object.keys(this.errors).filter(key => ['username', 'password', 'password_confirmation'].includes(key)).length === 0;
        },
        
        validateStep3() {
            return this.form.roles.length > 0;
        },
        
        isFormValid() {
            return this.validateStep1() && this.validateStep2() && this.validateStep3();
        },
        
        // Validación de campos
        validateField(field) {
            this.errors[field] = '';
            
            switch (field) {
                case 'nombres':
                    if (!this.form.nombres.trim()) {
                        this.errors.nombres = 'Los nombres son requeridos';
                    } else if (this.form.nombres.length < 2) {
                        this.errors.nombres = 'Los nombres deben tener al menos 2 caracteres';
                    }
                    break;
                    
                case 'apellidos':
                    if (!this.form.apellidos.trim()) {
                        this.errors.apellidos = 'Los apellidos son requeridos';
                    } else if (this.form.apellidos.length < 2) {
                        this.errors.apellidos = 'Los apellidos deben tener al menos 2 caracteres';
                    }
                    break;
                    
                case 'email':
                    if (!this.form.email.trim()) {
                        this.errors.email = 'El formato del email no es válido';
                    }
                    break;
                    
                case 'username':
                    if (!this.form.username.trim()) {
                        this.errors.username = 'El nombre de usuario es requerido';
                    } else if (this.form.username.length < 3) {
                        this.errors.username = 'El nombre de usuario debe tener al menos 3 caracteres';
                    } else if (!/^[a-zA-Z0-9_]+$/.test(this.form.username)) {
                        this.errors.username = 'Solo se permiten letras, números y guiones bajos';
                    }
                    break;
                    
                case 'password':
                    if (!this.form.password.trim()) {
                        this.errors.password = 'La contraseña es requerida';
                    } else if (this.form.password.length < 8) {
                        this.errors.password = 'La contraseña debe tener al menos 8 caracteres';
                    }
                    break;
                    
                case 'password_confirmation':
                    if (this.form.password !== this.form.password_confirmation) {
                        this.errors.password_confirmation = 'Las contraseñas no coinciden';
                    }
                    break;
                    
                case 'tipo_usuario':
                    if (!this.form.tipo_usuario) {
                        this.errors.tipo_usuario = 'El tipo de usuario es requerido';
                    }
                    break;
            }
        },
        
        // Utilidades de validación
        isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        // Verificación de disponibilidad
        async checkEmailAvailability() {
            if (!this.form.email || !this.isValidEmail(this.form.email)) {
                this.emailAvailable = null;
                return;
            }
            
            this.checkingEmail = true;
            try {
                const response = await fetch('{{ route("api.users.check-email") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: this.form.email })
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
            if (!this.form.username || this.form.username.length < 3) {
                this.usernameAvailable = null;
                return;
            }
            
            this.checkingUsername = true;
            try {
                const response = await fetch('{{ route("api.users.check-username") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ username: this.form.username })
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
        
        // Generadores automáticos
        generateUsername() {
            if (this.form.nombres && this.form.apellidos) {
                const base = (this.form.nombres.split(' ')[0] + this.form.apellidos.split(' ')[0]).toLowerCase();
                const clean = base.replace(/[^a-zA-Z0-9]/g, '');
                this.form.username = clean + Math.floor(Math.random() * 100);
                this.validateField('username');
                this.checkUsernameAvailability();
            }
        },
        
        generatePassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            this.form.password = password;
            this.form.password_confirmation = password;
            this.checkPasswordStrength();
            this.validateField('password');
            this.validateField('password_confirmation');
        },
        
        // Análisis de fortaleza de contraseña
        checkPasswordStrength() {
            const password = this.form.password;
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
            if (this.form.empleado_id) {
                const empleado = this.empleados.find(e => e.id == this.form.empleado_id);
                if (empleado) {
                    this.form.nombres = empleado.nombres;
                    this.form.apellidos = empleado.apellidos;
                    this.form.email = empleado.email || '';
                    this.form.telefono = empleado.telefono || '';
                    
                    // Auto-generar username
                    this.generateUsername();
                    
                    // Validar campos completados
                    this.validateField('nombres');
                    this.validateField('apellidos');
                    if (this.form.email) {
                        this.validateField('email');
                        this.checkEmailAvailability();
                    }
                }
            }
        },
        
        // Gestión de roles y permisos
        onRoleChange(role) {
            this.updateSelectedRolePermissions();
        },
        
        updateSelectedRolePermissions() {
            this.selectedRolePermissions = [];
            
            for (const roleId of this.form.roles) {
                const role = this.availableRoles.find(r => r.id == roleId);
                if (role && role.permissions) {
                    this.selectedRolePermissions = [
                        ...this.selectedRolePermissions,
                        ...role.permissions.filter(p => !this.selectedRolePermissions.find(sp => sp.id === p.id))
                    ];
                }
            }
        },
        
        get availableAdditionalPermissions() {
            return this.availablePermissions.filter(permission => 
                !this.selectedRolePermissions.find(rp => rp.id === permission.id)
            );
        },
        
        get groupedAdditionalPermissions() {
            const groups = {};
            this.availableAdditionalPermissions.forEach(permission => {
                const category = permission.name.split('.')[0] || 'general';
                if (!groups[category]) {
                    groups[category] = [];
                }
                groups[category].push(permission);
            });
            return groups;
        },
        
        get totalPermissions() {
            const rolePermissionIds = this.selectedRolePermissions.map(p => p.id);
            const additionalPermissionIds = this.form.additional_permissions;
            const uniquePermissions = new Set([...rolePermissionIds, ...additionalPermissionIds]);
            return uniquePermissions.size;
        },
        
        get hasCriticalPermissions() {
            const criticalPermissions = ['usuarios.eliminar', 'configuracion.editar', 'sistema.administrar'];
            const allPermissionNames = this.getAllPermissions().map(p => p.name);
            return criticalPermissions.some(cp => allPermissionNames.includes(cp));
        },
        
        getAllPermissions() {
            const rolePermissions = this.selectedRolePermissions;
            const additionalPermissions = this.availablePermissions.filter(p => 
                this.form.additional_permissions.includes(p.id)
            );
            
            const allPermissions = [...rolePermissions, ...additionalPermissions];
            const uniquePermissions = allPermissions.filter((permission, index, self) => 
                index === self.findIndex(p => p.id === permission.id)
            );
            
            return uniquePermissions;
        },
        
        getRoleName(roleId) {
            const role = this.availableRoles.find(r => r.id == roleId);
            return role ? (role.display_name || role.name) : 'Rol desconocido';
        },
        
        getEmpleadoName(empleadoId) {
            if (!empleadoId) return '';
            const empleado = this.empleados.find(e => e.id == empleadoId);
            return empleado ? `${empleado.nombres} ${empleado.apellidos}` : '';
        },
        
        // Envío del formulario
        async submitForm() {
            if (!this.isFormValid()) {
                this.showNotification('Por favor complete todos los campos requeridos correctamente', 'error');
                return;
            }
            
            this.submitting = true;
            try {
                const response = await fetch('{{ route("admin.users.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.showNotification('Usuario creado exitosamente', 'success');
                    
                    // Mostrar información adicional si se enviaron credenciales
                    if (this.form.send_credentials) {
                        this.showNotification('Se han enviado las credenciales por email al usuario', 'info');
                    }
                    
                    // Redirigir después de un breve delay
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route("admin.users.index") }}';
                    }, 1500);
                    
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                        // Ir al primer paso con errores
                        if (Object.keys(data.errors).some(key => ['nombres', 'apellidos', 'email', 'tipo_usuario'].includes(key))) {
                            this.currentStep = 1;
                        } else if (Object.keys(data.errors).some(key => ['username', 'password'].includes(key))) {
                            this.currentStep = 2;
                        } else if (Object.keys(data.errors).some(key => ['roles'].includes(key))) {
                            this.currentStep = 3;
                        }
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                    this.showNotification(data.message || 'Error al crear el usuario', 'error');
                }
            } catch (error) {
                console.error('Error creating user:', error);
                this.showNotification('Error de conexión al crear el usuario', 'error');
            } finally {
                this.submitting = false;
            }
        },
        
        // Utilidades
        showNotification(message, type = 'info') {
            // Crear notificación temporal en la parte superior
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm ${
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
                    notification.remove();
                }
            }, 5000);
        }
    }
}
// Script completo para resources/views/modules/usuarios/create.blade.php
// Agregar este script al final de la vista, dentro de @push('scripts')

function userCreateForm() {
    return {
        // Estado del formulario
        currentStep: 1,
        submitting: false,
        
        // Datos del formulario
        form: {
            empleado_id: '',
            nombres: '',
            apellidos: '',
            email: '',
            telefono: '',
            tipo_usuario: '',
            activo: '1',
            username: '',
            password: '',
            password_confirmation: '',
            force_password_change: true,
            send_credentials: true,
            email_verification_required: false,
            enable_2fa: false,
            roles: [],
            additional_permissions: []
        },
        
        // Errores de validación
        errors: {},
        
        // Estados de verificación
        emailAvailable: null,
        checkingEmail: false,
        usernameAvailable: null,
        checkingUsername: false,
        
        // Contraseña
        showPassword: false,
        showPasswordConfirmation: false,
        passwordStrength: '',
        passwordStrengthText: '',
        
        // Datos externos
        empleados: [],
        availableRoles: [],
        availablePermissions: [],
        selectedRolePermissions: [],
        showAllPermissions: false,
        
        // Inicialización
        async init() {
            await this.loadEmpleados();
            await this.loadRoles();
            await this.loadPermissions();
        },
        
        // Cargar datos externos
        async loadEmpleados() {
            try {
                const response = await fetch('/api/empleados?sin_usuario=1', {
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
        
        // Navegación entre pasos
        nextStep() {
            if (this.canProceedToNextStep()) {
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        
        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        
        canProceedToNextStep() {
            switch (this.currentStep) {
                case 1:
                    return this.validateStep1();
                case 2:
                    return this.validateStep2();
                case 3:
                    return this.validateStep3();
                default:
                    return false;
            }
        },
        
        // Textos dinámicos
        getStepDescription() {
            const descriptions = {
                1: 'Información personal del usuario',
                2: 'Credenciales de acceso y seguridad',
                3: 'Roles y permisos del sistema',
                4: 'Revisión y confirmación'
            };
            return descriptions[this.currentStep] || '';
        },
        
        getNextButtonText() {
            return this.currentStep === 3 ? 'Revisar y Crear' : 'Siguiente';
        },
        
        // Validación por pasos
        validateStep1() {
            const required = ['nombres', 'apellidos', 'email', 'tipo_usuario'];
            return required.every(field => this.form[field].trim() !== '') &&
                   this.emailAvailable !== false &&
                   Object.keys(this.errors).filter(key => required.includes(key)).length === 0;
        },
        
        validateStep2() {
            return this.form.username.trim() !== '' &&
                   this.form.password.trim() !== '' &&
                   this.form.password === this.form.password_confirmation &&
                   this.usernameAvailable !== false &&
                   this.passwordStrength !== 'weak' &&
                   Object.keys(this.errors).filter(key => ['username', 'password', 'password_confirmation'].includes(key)).length === 0;
        },
        
        validateStep3() {
            return this.form.roles.length > 0;
        },
        
        isFormValid() {
            return this.validateStep1() && this.validateStep2() && this.validateStep3();
        },
        
        // Validación de campos
        validateField(field) {
            this.errors[field] = '';
            
            switch (field) {
                case 'nombres':
                    if (!this.form.nombres.trim()) {
                        this.errors.nombres = 'Los nombres son requeridos';
                    } else if (this.form.nombres.length < 2) {
                        this.errors.nombres = 'Los nombres deben tener al menos 2 caracteres';
                    }
                    break;
                    
                case 'apellidos':
                    if (!this.form.apellidos.trim()) {
                        this.errors.apellidos = 'Los apellidos son requeridos';
                    } else if (this.form.apellidos.length < 2) {
                        this.errors.apellidos = 'Los apellidos deben tener al menos 2 caracteres';
                    }
                    break;
                    
                case 'email':
                    if (!this.form.email.trim()) {
                        this.errors.email = 'El email es requerido';
                    } else if (!this.isValidEmail(this.form.email)) {
                        this.errors.email = 'El formato del email no es válido';
                    }
                    break;
                    
                case 'username':
                    if (!this.form.username.trim()) {
                        this.errors.username = 'El nombre de usuario es requerido';
                    } else if (this.form.username.length < 3) {
                        this.errors.username = 'El nombre de usuario debe tener al menos 3 caracteres';
                    } else if (!/^[a-zA-Z0-9_]+$/.test(this.form.username)) {
                        this.errors.username = 'Solo se permiten letras, números y guiones bajos';
                    }
                    break;
                    
                case 'password':
                    if (!this.form.password.trim()) {
                        this.errors.password = 'La contraseña es requerida';
                    } else if (this.form.password.length < 8) {
                        this.errors.password = 'La contraseña debe tener al menos 8 caracteres';
                    }
                    break;
                    
                case 'password_confirmation':
                    if (this.form.password !== this.form.password_confirmation) {
                        this.errors.password_confirmation = 'Las contraseñas no coinciden';
                    }
                    break;
                    
                case 'tipo_usuario':
                    if (!this.form.tipo_usuario) {
                        this.errors.tipo_usuario = 'El tipo de usuario es requerido';
                    }
                    break;
            }
        },
        
        // Utilidades de validación
        isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        // Verificación de disponibilidad
        async checkEmailAvailability() {
            if (!this.form.email || !this.isValidEmail(this.form.email)) {
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
                    body: JSON.stringify({ email: this.form.email })
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
            if (!this.form.username || this.form.username.length < 3) {
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
                    body: JSON.stringify({ username: this.form.username })
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
        
        // Generadores automáticos
        generateUsername() {
            if (this.form.nombres && this.form.apellidos) {
                const base = (this.form.nombres.split(' ')[0] + this.form.apellidos.split(' ')[0]).toLowerCase();
                const clean = base.replace(/[^a-zA-Z0-9]/g, '');
                this.form.username = clean + Math.floor(Math.random() * 100);
                this.validateField('username');
                this.checkUsernameAvailability();
            }
        },
        
        generatePassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0; i < length; i++) {
                password += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            this.form.password = password;
            this.form.password_confirmation = password;
            this.checkPasswordStrength();
            this.validateField('password');
            this.validateField('password_confirmation');
        },
        
        // Análisis de fortaleza de contraseña
        checkPasswordStrength() {
            const password = this.form.password;
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
            if (this.form.empleado_id) {
                const empleado = this.empleados.find(e => e.id == this.form.empleado_id);
                if (empleado) {
                    this.form.nombres = empleado.nombres;
                    this.form.apellidos = empleado.apellidos;
                    this.form.email = empleado.email || '';
                    this.form.telefono = empleado.telefono || '';
                    
                    // Auto-generar username
                    this.generateUsername();
                    
                    // Validar campos completados
                    this.validateField('nombres');
                    this.validateField('apellidos');
                    if (this.form.email) {
                        this.validateField('email');
                        this.checkEmailAvailability();
                    }
                }
            }
        },
        
        // Gestión de roles y permisos
        onRoleChange(role) {
            this.updateSelectedRolePermissions();
        },
        
        updateSelectedRolePermissions() {
            this.selectedRolePermissions = [];
            
            for (const roleId of this.form.roles) {
                const role = this.availableRoles.find(r => r.id == roleId);
                if (role && role.permissions) {
                    this.selectedRolePermissions = [
                        ...this.selectedRolePermissions,
                        ...role.permissions.filter(p => !this.selectedRolePermissions.find(sp => sp.id === p.id))
                    ];
                }
            }
        },
        
        get availableAdditionalPermissions() {
            return this.availablePermissions.filter(permission => 
                !this.selectedRolePermissions.find(rp => rp.id === permission.id)
            );
        },
        
        get groupedAdditionalPermissions() {
            const groups = {};
            this.availableAdditionalPermissions.forEach(permission => {
                const category = permission.name.split('.')[0] || 'general';
                if (!groups[category]) {
                    groups[category] = [];
                }
                groups[category].push(permission);
            });
            return groups;
        },
        
        get totalPermissions() {
            const rolePermissionIds = this.selectedRolePermissions.map(p => p.id);
            const additionalPermissionIds = this.form.additional_permissions;
            const uniquePermissions = new Set([...rolePermissionIds, ...additionalPermissionIds]);
            return uniquePermissions.size;
        },
        
        get hasCriticalPermissions() {
            const criticalPermissions = ['usuarios.eliminar', 'configuracion.editar', 'sistema.administrar'];
            const allPermissionNames = this.getAllPermissions().map(p => p.name);
            return criticalPermissions.some(cp => allPermissionNames.includes(cp));
        },
        
        getAllPermissions() {
            const rolePermissions = this.selectedRolePermissions;
            const additionalPermissions = this.availablePermissions.filter(p => 
                this.form.additional_permissions.includes(p.id)
            );
            
            const allPermissions = [...rolePermissions, ...additionalPermissions];
            const uniquePermissions = allPermissions.filter((permission, index, self) => 
                index === self.findIndex(p => p.id === permission.id)
            );
            
            return uniquePermissions;
        },
        
        getRoleName(roleId) {
            const role = this.availableRoles.find(r => r.id == roleId);
            return role ? (role.display_name || role.name) : 'Rol desconocido';
        },
        
        getEmpleadoName(empleadoId) {
            if (!empleadoId) return '';
            const empleado = this.empleados.find(e => e.id == empleadoId);
            return empleado ? `${empleado.nombres} ${empleado.apellidos}` : '';
        },
        
        // Envío del formulario
        async submitForm() {
            if (!this.isFormValid()) {
                this.showNotification('Por favor complete todos los campos requeridos correctamente', 'error');
                return;
            }
            
            this.submitting = true;
            try {
                const response = await fetch('/admin/users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.showNotification('Usuario creado exitosamente', 'success');
                    
                    // Mostrar información adicional si se enviaron credenciales
                    if (this.form.send_credentials) {
                        this.showNotification('Se han enviado las credenciales por email al usuario', 'info');
                    }
                    
                    // Redirigir después de un breve delay
                    setTimeout(() => {
                        window.location.href = data.redirect || '/admin/users';
                    }, 1500);
                    
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                        // Ir al primer paso con errores
                        if (Object.keys(data.errors).some(key => ['nombres', 'apellidos', 'email', 'tipo_usuario'].includes(key))) {
                            this.currentStep = 1;
                        } else if (Object.keys(data.errors).some(key => ['username', 'password'].includes(key))) {
                            this.currentStep = 2;
                        } else if (Object.keys(data.errors).some(key => ['roles'].includes(key))) {
                            this.currentStep = 3;
                        }
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                    this.showNotification(data.message || 'Error al crear el usuario', 'error');
                }
            } catch (error) {
                console.error('Error creating user:', error);
                this.showNotification('Error de conexión al crear el usuario', 'error');
            } finally {
                this.submitting = false;
            }
        },
        
        // Utilidades
        showNotification(message, type = 'info') {
            // Crear notificación temporal en la parte superior
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm ${
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
                    notification.remove();
                }
            }, 5000);
        }
    }
}