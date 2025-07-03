@extends('layouts.auth')

@section('title', 'Registro de Usuario')

@section('auth-subtitle', 'Crear nueva cuenta en el sistema')

@section('content')
@canany(['usuarios.crear', 'Super Admin', 'Gerente'])
<div x-data="registerForm()" @submit.prevent="submitRegister">
    <!-- Register Form -->
    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf
        
        <!-- Personal Information Section -->
        <div class="bg-gray-50 px-4 py-3 rounded-md">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Información Personal</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- DNI -->
                <div>
                    <label for="dni" class="block text-sm font-medium text-gray-700">
                        DNI <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative">
                        <input id="dni" 
                               name="dni" 
                               type="text" 
                               required 
                               maxlength="8"
                               x-model="formData.dni"
                               @input="validateDNI"
                               :class="errors.dni ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                               class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm"
                               placeholder="12345678"
                               value="{{ old('dni') }}">
                        
                        <!-- DNI Validation Icon -->
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg x-show="dniValidating" class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg x-show="dniValid && !dniValidating" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div x-show="errors.dni" class="mt-1">
                        <p class="text-sm text-red-600" x-text="errors.dni"></p>
                    </div>
                </div>

                <!-- Nombres -->
                <div>
                    <label for="nombres" class="block text-sm font-medium text-gray-700">
                        Nombres <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input id="nombres" 
                               name="nombres" 
                               type="text" 
                               required 
                               x-model="formData.nombres"
                               @blur="validateNombres"
                               :class="errors.nombres ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                               class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm"
                               placeholder="Juan Carlos"
                               value="{{ old('nombres') }}">
                    </div>
                    <div x-show="errors.nombres" class="mt-1">
                        <p class="text-sm text-red-600" x-text="errors.nombres"></p>
                    </div>
                </div>

                <!-- Apellido Paterno -->
                <div>
                    <label for="apellido_paterno" class="block text-sm font-medium text-gray-700">
                        Apellido Paterno <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input id="apellido_paterno" 
                               name="apellido_paterno" 
                               type="text" 
                               required 
                               x-model="formData.apellido_paterno"
                               @blur="validateApellidoPaterno"
                               :class="errors.apellido_paterno ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                               class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm"
                               placeholder="García"
                               value="{{ old('apellido_paterno') }}">
                    </div>
                    <div x-show="errors.apellido_paterno" class="mt-1">
                        <p class="text-sm text-red-600" x-text="errors.apellido_paterno"></p>
                    </div>
                </div>

                <!-- Apellido Materno -->
                <div>
                    <label for="apellido_materno" class="block text-sm font-medium text-gray-700">
                        Apellido Materno <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input id="apellido_materno" 
                               name="apellido_materno" 
                               type="text" 
                               required 
                               x-model="formData.apellido_materno"
                               @blur="validateApellidoMaterno"
                               :class="errors.apellido_materno ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                               class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm"
                               placeholder="López"
                               value="{{ old('apellido_materno') }}">
                    </div>
                    <div x-show="errors.apellido_materno" class="mt-1">
                        <p class="text-sm text-red-600" x-text="errors.apellido_materno"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="bg-gray-50 px-4 py-3 rounded-md">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Información de Contacto</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Email -->
                <div class="md:col-span-2">
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Correo Electrónico <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative">
                        <input id="email" 
                               name="email" 
                               type="email" 
                               autocomplete="email" 
                               required 
                               x-model="formData.email"
                               @blur="validateEmail"
                               :class="errors.email ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                               class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm"
                               placeholder="usuario@empresa.com"
                               value="{{ old('email') }}">
                        
                        <!-- Email Validation Icon -->
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg x-show="emailValidating" class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg x-show="emailValid && !emailValidating" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div x-show="errors.email" class="mt-1">
                        <p class="text-sm text-red-600" x-text="errors.email"></p>
                    </div>
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">
                        Nombre de Usuario <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative">
                        <input id="username" 
                               name="username" 
                               type="text" 
                               required 
                               x-model="formData.username"
                               @blur="validateUsername"
                               :class="errors.username ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                               class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm"
                               placeholder="jgarcia"
                               value="{{ old('username') }}">
                        
                        <!-- Username Validation Icon -->
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg x-show="usernameValidating" class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg x-show="usernameValid && !usernameValidating" class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div x-show="errors.username" class="mt-1">
                        <p class="text-sm text-red-600" x-text="errors.username"></p>
                    </div>
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700">
                        Teléfono <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input id="telefono" 
                               name="telefono" 
                               type="tel" 
                               required 
                               x-model="formData.telefono"
                               @input="formatTelefono"
                               @blur="validateTelefono"
                               :class="errors.telefono ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                               class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm"
                               placeholder="999 123 456"
                               value="{{ old('telefono') }}">
                    </div>
                    <div x-show="errors.telefono" class="mt-1">
                        <p class="text-sm text-red-600" x-text="errors.telefono"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Information Section -->
        <div class="bg-gray-50 px-4 py-3 rounded-md">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Información Profesional</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Especialidad/Área -->
                <div>
                    <label for="especialidad" class="block text-sm font-medium text-gray-700">
                        Especialidad/Área <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <select id="especialidad" 
                                name="especialidad" 
                                required 
                                x-model="formData.especialidad"
                                @change="validateEspecialidad"
                                :class="errors.especialidad ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                                class="block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                            <option value="">Seleccionar especialidad</option>
                            <option value="administracion">Administración</option>
                            <option value="ventas">Ventas</option>
                            <option value="tecnico">Técnico</option>
                            <option value="contabilidad">Contabilidad</option>
                            <option value="recursos_humanos">Recursos Humanos</option>
                            <option value="marketing">Marketing</option>
                            <option value="sistemas">Sistemas</option>
                            <option value="logistica">Logística</option>
                            <option value="atencion_cliente">Atención al Cliente</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div x-show="errors.especialidad" class="mt-1">
                        <p class="text-sm text-red-600" x-text="errors.especialidad"></p>
                    </div>
                </div>

                <!-- Rol -->
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700">
                        Rol en el Sistema <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <select id="role_id" 
                                name="role_id" 
                                required 
                                x-model="formData.role_id"
                                @change="validateRole"
                                :class="errors.role_id ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                                class="block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                            <option value="">Seleccionar rol</option>
                            @can('usuarios.asignar.super_admin')
                                <option value="1">Super Admin</option>
                            @endcan
                            @can('usuarios.asignar.gerente')
                                <option value="2">Gerente</option>
                            @endcan
                            @can('usuarios.asignar.supervisor')
                                <option value="3">Supervisor</option>
                            @endcan
                            @can('usuarios.asignar.vendedor')
                                <option value="4">Vendedor</option>
                            @endcan
                            @can('usuarios.asignar.tecnico')
                                <option value="5">Técnico</option>
                            @endcan
                            @can('usuarios.asignar.contador')
                                <option value="6">Contador</option>
                            @endcan
                            @can('usuarios.asignar.empleado')
                                <option value="7">Empleado</option>
                            @endcan
                            @can('usuarios.asignar.cliente')
                                <option value="8">Cliente</option>
                            @endcan
                        </select>
                    </div>
                    <div x-show="errors.role_id" class="mt-1">
                        <p class="text-sm text-red-600" x-text="errors.role_id"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Information Section -->
        <div class="bg-gray-50 px-4 py-3 rounded-md">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Información de Seguridad</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Contraseña <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative">
                        <input id="password" 
                               name="password" 
                               :type="showPassword ? 'text' : 'password'" 
                               required 
                               x-model="formData.password"
                               @input="validatePassword"
                               :class="errors.password ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                               class="appearance-none block w-full px-3 py-2 pr-10 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm"
                               placeholder="••••••••">
                        
                        <!-- Password Toggle -->
                        <button type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg x-show="!showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div class="mt-2">
                        <div class="flex space-x-1">
                            <div class="h-1 flex-1 rounded" :class="passwordStrength >= 1 ? 'bg-red-500' : 'bg-gray-200'"></div>
                            <div class="h-1 flex-1 rounded" :class="passwordStrength >= 2 ? 'bg-yellow-500' : 'bg-gray-200'"></div>
                            <div class="h-1 flex-1 rounded" :class="passwordStrength >= 3 ? 'bg-green-500' : 'bg-gray-200'"></div>
                        </div>
                        <p class="text-xs mt-1" :class="{
                            'text-red-600': passwordStrength === 1,
                            'text-yellow-600': passwordStrength === 2,
                            'text-green-600': passwordStrength === 3,
                            'text-gray-500': passwordStrength === 0
                        }" x-text="passwordStrengthText"></p>
                    </div>
                    
                    <div x-show="errors.password" class="mt-1">
                        <p class="text-sm text-red-600" x-text="errors.password"></p>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Confirmar Contraseña <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative">
                        <input id="password_confirmation" 
                               name="password_confirmation" 
                               :type="showPasswordConfirm ? 'text' : 'password'" 
                               required 
                               x-model="formData.password_confirmation"
                               @input="validatePasswordConfirmation"
                               :class="errors.password_confirmation ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                               class="appearance-none block w-full px-3 py-2 pr-10 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm"
                               placeholder="••••••••">
                        
                        <!-- Password Toggle -->
                        <button type="button" 
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg x-show="!showPasswordConfirm" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showPasswordConfirm" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    <div x-show="errors.password_confirmation" class="mt-1">
                        <p class="text-sm text-red-600" x-text="errors.password_confirmation"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="terms" 
                           name="terms" 
                           type="checkbox" 
                           required
                           x-model="formData.terms"
                           :class="errors.terms ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 rounded">
                </div>
                <div class="ml-3 text-sm">
                    <label for="terms" class="font-medium text-blue-800">
                        Acepto los términos y condiciones
                    </label>
                    <p class="text-blue-700">
                        Al crear esta cuenta, acepto las 
                        <a href="#" class="underline hover:text-blue-900">políticas de privacidad</a> y 
                        <a href="#" class="underline hover:text-blue-900">términos de uso</a> del sistema.
                    </p>
                </div>
            </div>
            <div x-show="errors.terms" class="mt-2">
                <p class="text-sm text-red-600" x-text="errors.terms"></p>
            </div>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" 
                    :disabled="loading || !isFormValid"
                    :class="loading || !isFormValid ? 'bg-gray-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200">
                
                <!-- Loading Spinner -->
                <span x-show="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>

                <!-- User Plus Icon -->
                <span x-show="!loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"></path>
                    </svg>
                </span>

                <span x-text="loading ? 'Creando usuario...' : 'Crear Usuario'"></span>
            </button>
        </div>

        <!-- Progress Indicator -->
        <div class="bg-gray-50 px-4 py-3 rounded-md">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Progreso del formulario</span>
                <span class="font-medium text-gray-900" x-text="Math.round(formProgress) + '%'"></span>
            </div>
            <div class="mt-2 bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" 
                     :style="'width: ' + formProgress + '%'"></div>
            </div>
        </div>
    </form>
</div>
@else
<div class="text-center py-8">
    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-2.694-.833-3.464 0L3.349 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
    </div>
    <h3 class="mt-4 text-lg font-medium text-gray-900">Acceso Denegado</h3>
    <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
        No tienes permisos para registrar nuevos usuarios en el sistema. 
        Solo los administradores y gerentes pueden crear nuevas cuentas.
    </p>
    <div class="mt-6 space-y-3">
        <a href="{{ route('login') }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
            </svg>
            Volver al Login
        </a>
        
        <div class="text-center">
            <p class="text-xs text-gray-400">¿Necesitas acceso?</p>
            <a href="mailto:admin@empresa.com" 
               class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                Contacta al administrador
            </a>
        </div>
    </div>
</div>
@endcanany
@endsection

@section('auth-links')
<div class="space-y-2">
    <p class="text-sm text-gray-300">
        ¿Ya tienes cuenta? 
        <a href="{{ route('login') }}" class="font-medium text-white hover:text-gray-200 transition-colors">
            Iniciar sesión
        </a>
    </p>
    
    <p class="text-sm text-gray-400">
        <a href="{{ route('help') }}" class="font-medium text-white hover:text-gray-200 transition-colors">
            ¿Necesitas ayuda?
        </a>
    </p>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('registerForm', () => ({
        formData: {
            dni: '',
            nombres: '',
            apellido_paterno: '',
            apellido_materno: '',
            email: '',
            username: '',
            telefono: '',
            especialidad: '',
            role_id: '',
            password: '',
            password_confirmation: '',
            terms: false
        },
        errors: {},
        loading: false,
        showPassword: false,
        showPasswordConfirm: false,
        
        // Validation states
        dniValid: false,
        dniValidating: false,
        emailValid: false,
        emailValidating: false,
        usernameValid: false,
        usernameValidating: false,
        
        // Password strength
        passwordStrength: 0,
        passwordStrengthText: '',
        
        get isFormValid() {
            const requiredFields = [
                'dni', 'nombres', 'apellido_paterno', 'apellido_materno', 
                'email', 'username', 'telefono', 'especialidad', 'role_id', 
                'password', 'password_confirmation'
            ];
            
            const fieldsValid = requiredFields.every(field => this.formData[field]);
            const passwordsMatch = this.formData.password === this.formData.password_confirmation;
            const noErrors = Object.keys(this.errors).length === 0;
            const termsAccepted = this.formData.terms;
            
            return fieldsValid && passwordsMatch && noErrors && termsAccepted && this.passwordStrength >= 2;
        },
        
        get formProgress() {
            const totalFields = 12; // Total fields including terms
            let completedFields = 0;
            
            // Count completed fields
            Object.keys(this.formData).forEach(key => {
                if (key === 'terms') {
                    if (this.formData[key]) completedFields++;
                } else if (this.formData[key]) {
                    completedFields++;
                }
            });
            
            // Add bonus for validation states
            if (this.dniValid) completedFields += 0.5;
            if (this.emailValid) completedFields += 0.5;
            if (this.usernameValid) completedFields += 0.5;
            if (this.passwordStrength >= 2) completedFields += 0.5;
            
            return Math.min(100, (completedFields / totalFields) * 100);
        },
        
        async validateDNI() {
            const dni = this.formData.dni.replace(/\D/g, '');
            
            if (!dni) {
                this.errors.dni = 'El DNI es requerido';
                this.dniValid = false;
                return;
            }
            
            if (dni.length !== 8) {
                this.errors.dni = 'El DNI debe tener 8 dígitos';
                this.dniValid = false;
                return;
            }
            
            // Validar formato de DNI peruano
            if (!/^[0-9]{8}$/.test(dni)) {
                this.errors.dni = 'El DNI debe contener solo números';
                this.dniValid = false;
                return;
            }
            
            this.dniValidating = true;
            
            try {
                const response = await fetch(`/api/validate/dni/${dni}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const result = await response.json();
                
                if (result.exists) {
                    this.errors.dni = 'Este DNI ya está registrado en el sistema';
                    this.dniValid = false;
                } else {
                    delete this.errors.dni;
                    this.dniValid = true;
                }
            } catch (error) {
                console.error('DNI validation error:', error);
                // En caso de error de conexión, asumir que es válido por ahora
                delete this.errors.dni;
                this.dniValid = true;
            } finally {
                this.dniValidating = false;
            }
        },
        
        validateNombres() {
            if (!this.formData.nombres.trim()) {
                this.errors.nombres = 'Los nombres son requeridos';
            } else if (this.formData.nombres.trim().length < 2) {
                this.errors.nombres = 'Los nombres deben tener al menos 2 caracteres';
            } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(this.formData.nombres)) {
                this.errors.nombres = 'Los nombres solo pueden contener letras y espacios';
            } else {
                delete this.errors.nombres;
            }
        },
        
        validateApellidoPaterno() {
            if (!this.formData.apellido_paterno.trim()) {
                this.errors.apellido_paterno = 'El apellido paterno es requerido';
            } else if (this.formData.apellido_paterno.trim().length < 2) {
                this.errors.apellido_paterno = 'El apellido paterno debe tener al menos 2 caracteres';
            } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(this.formData.apellido_paterno)) {
                this.errors.apellido_paterno = 'El apellido paterno solo puede contener letras y espacios';
            } else {
                delete this.errors.apellido_paterno;
            }
        },
        
        validateApellidoMaterno() {
            if (!this.formData.apellido_materno.trim()) {
                this.errors.apellido_materno = 'El apellido materno es requerido';
            } else if (this.formData.apellido_materno.trim().length < 2) {
                this.errors.apellido_materno = 'El apellido materno debe tener al menos 2 caracteres';
            } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(this.formData.apellido_materno)) {
                this.errors.apellido_materno = 'El apellido materno solo puede contener letras y espacios';
            } else {
                delete this.errors.apellido_materno;
            }
        },
        
        async validateEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!this.formData.email) {
                this.errors.email = 'El correo electrónico es requerido';
                this.emailValid = false;
                return;
            }
            
            if (!emailRegex.test(this.formData.email)) {
                this.errors.email = 'Ingresa un correo electrónico válido';
                this.emailValid = false;
                return;
            }
            
            this.emailValidating = true;
            
            try {
                const response = await fetch(`/api/validate/email`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email: this.formData.email })
                });
                
                const result = await response.json();
                
                if (result.exists) {
                    this.errors.email = 'Este correo electrónico ya está registrado';
                    this.emailValid = false;
                } else {
                    delete this.errors.email;
                    this.emailValid = true;
                }
            } catch (error) {
                console.error('Email validation error:', error);
                // En caso de error, asumir válido por ahora
                delete this.errors.email;
                this.emailValid = true;
            } finally {
                this.emailValidating = false;
            }
        },
        
        async validateUsername() {
            if (!this.formData.username) {
                this.errors.username = 'El nombre de usuario es requerido';
                this.usernameValid = false;
                return;
            }
            
            if (this.formData.username.length < 3) {
                this.errors.username = 'El nombre de usuario debe tener al menos 3 caracteres';
                this.usernameValid = false;
                return;
            }
            
            if (this.formData.username.length > 20) {
                this.errors.username = 'El nombre de usuario no puede tener más de 20 caracteres';
                this.usernameValid = false;
                return;
            }
            
            const usernameRegex = /^[a-zA-Z0-9_.-]+$/;
            if (!usernameRegex.test(this.formData.username)) {
                this.errors.username = 'El nombre de usuario solo puede contener letras, números, puntos, guiones y guiones bajos';
                this.usernameValid = false;
                return;
            }
            
            this.usernameValidating = true;
            
            try {
                const response = await fetch(`/api/validate/username`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ username: this.formData.username })
                });
                
                const result = await response.json();
                
                if (result.exists) {
                    this.errors.username = 'Este nombre de usuario ya está en uso';
                    this.usernameValid = false;
                } else {
                    delete this.errors.username;
                    this.usernameValid = true;
                }
            } catch (error) {
                console.error('Username validation error:', error);
                // En caso de error, asumir válido
                delete this.errors.username;
                this.usernameValid = true;
            } finally {
                this.usernameValidating = false;
            }
        },
        
        formatTelefono() {
            // Remover todo excepto números
            let phone = this.formData.telefono.replace(/\D/g, '');
            
            // Limitar a 9 dígitos
            if (phone.length > 9) phone = phone.substring(0, 9);
            
            // Formatear: 999 123 456
            if (phone.length >= 6) {
                phone = phone.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3');
            } else if (phone.length >= 3) {
                phone = phone.replace(/(\d{3})(\d{1,3})/, '$1 $2');
            }
            
            this.formData.telefono = phone;
        },
        
        validateTelefono() {
            const phone = this.formData.telefono.replace(/\D/g, '');
            
            if (!phone) {
                this.errors.telefono = 'El teléfono es requerido';
            } else if (phone.length !== 9) {
                this.errors.telefono = 'El teléfono debe tener 9 dígitos';
            } else if (!phone.startsWith('9')) {
                this.errors.telefono = 'El teléfono móvil debe comenzar con 9';
            } else {
                delete this.errors.telefono;
            }
        },
        
        validateEspecialidad() {
            if (!this.formData.especialidad) {
                this.errors.especialidad = 'La especialidad es requerida';
            } else {
                delete this.errors.especialidad;
            }
        },
        
        validateRole() {
            if (!this.formData.role_id) {
                this.errors.role_id = 'El rol es requerido';
            } else {
                delete this.errors.role_id;
            }
        },
        
        validatePassword() {
            const password = this.formData.password;
            
            if (!password) {
                this.errors.password = 'La contraseña es requerida';
                this.passwordStrength = 0;
                this.passwordStrengthText = '';
                return;
            }
            
            if (password.length < 8) {
                this.errors.password = 'La contraseña debe tener al menos 8 caracteres';
                this.passwordStrength = 0;
                this.passwordStrengthText = 'Muy débil';
                return;
            }
            
            // Calcular fortaleza de contraseña
            let strength = 0;
            let criteria = [];
            
            if (password.length >= 8) {
                strength++;
                criteria.push('longitud');
            }
            if (/[A-Z]/.test(password)) {
                strength++;
                criteria.push('mayúscula');
            }
            if (/[a-z]/.test(password)) {
                strength++;
                criteria.push('minúscula');
            }
            if (/[0-9]/.test(password)) {
                strength++;
                criteria.push('número');
            }
            if (/[^A-Za-z0-9]/.test(password)) {
                strength++;
                criteria.push('especial');
            }
            
            // Mapear fortaleza (0-5) a escala (0-3)
            this.passwordStrength = Math.min(3, Math.floor(strength * 3 / 5));
            
            const strengthTexts = ['', 'Débil', 'Media', 'Fuerte'];
            this.passwordStrengthText = strengthTexts[this.passwordStrength];
            
            if (strength < 3) {
                this.errors.password = 'La contraseña debe contener mayúsculas, minúsculas y números';
            } else {
                delete this.errors.password;
            }
            
            // Revalidar confirmación si existe
            if (this.formData.password_confirmation) {
                this.validatePasswordConfirmation();
            }
        },
        
        validatePasswordConfirmation() {
            if (!this.formData.password_confirmation) {
                this.errors.password_confirmation = 'Confirma tu contraseña';
            } else if (this.formData.password !== this.formData.password_confirmation) {
                this.errors.password_confirmation = 'Las contraseñas no coinciden';
            } else {
                delete this.errors.password_confirmation;
            }
        },
        
        validateTerms() {
            if (!this.formData.terms) {
                this.errors.terms = 'Debes aceptar los términos y condiciones';
            } else {
                delete this.errors.terms;
            }
        },
        
        async submitRegister() {
            // Validar todos los campos
            await this.validateDNI();
            this.validateNombres();
            this.validateApellidoPaterno();
            this.validateApellidoMaterno();
            await this.validateEmail();
            await this.validateUsername();
            this.validateTelefono();
            this.validateEspecialidad();
            this.validateRole();
            this.validatePassword();
            this.validatePasswordConfirmation();
            this.validateTerms();
            
            if (Object.keys(this.errors).length > 0) {
                this.showToast('Por favor corrige los errores en el formulario', 'error');
                return;
            }
            
            this.loading = true;
            
            try {
                const formData = new FormData();
                Object.keys(this.formData).forEach(key => {
                    if (key === 'terms') {
                        formData.append(key, this.formData[key] ? '1' : '0');
                    } else {
                        formData.append(key, this.formData[key]);
                    }
                });
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                const response = await fetch('{{ route("register") }}', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.showToast('Usuario creado exitosamente', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route("admin.users.index") }}';
                    }, 2000);
                } else {
                    // Manejar errores de validación
                    if (data.errors) {
                        this.errors = data.errors;
                    }
                    
                    if (data.message) {
                        this.showToast(data.message, 'error');
                    }
                }
            } catch (error) {
                console.error('Registration error:', error);
                this.showToast('Error al crear el usuario. Por favor intenta de nuevo.', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        showToast(message, type = 'info') {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    }));
});
</script>
@endpush