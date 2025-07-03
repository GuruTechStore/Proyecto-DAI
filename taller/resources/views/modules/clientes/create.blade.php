{{-- resources/views/modules/clientes/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Crear Cliente')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7l3-3 3 3m0 8l-3 3-3-3" />
        </svg>
        <a href="{{ route('clientes.index') }}" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600">Clientes</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7l3-3 3 3m0 8l-3 3-3-3" />
        </svg>
        <span class="ml-2 text-sm font-medium text-gray-500 dark:text-gray-400">Crear Cliente</span>
    </div>
</li>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="clienteCreateForm()">
        
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Crear Nuevo Cliente
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Complete la información del cliente para registrarlo en el sistema
                        </p>
                    </div>
                    <a href="{{ route('clientes.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="space-y-6">
            @csrf
            
            <!-- Información Personal -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Información Personal
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Datos básicos de identificación del cliente
                    </p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Nombre Completo -->
                        <div class="col-span-1 lg:col-span-2">
                            <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nombre Completo <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="nombre" 
                                       name="nombre" 
                                       x-model="form.nombre"
                                       @blur="validateField('nombre')"
                                       :class="errors.nombre ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500'"
                                       class="pl-10 block w-full dark:bg-gray-700 dark:text-white rounded-lg shadow-sm"
                                       placeholder="Ingrese el nombre completo">
                            </div>
                            <p x-show="errors.nombre" x-text="errors.nombre" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Tipo de Documento -->
                        <div class="col-span-1">
                            <label for="tipo_documento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipo de Documento <span class="text-red-500">*</span>
                            </label>
                            <select id="tipo_documento" 
                                    name="tipo_documento" 
                                    x-model="form.tipo_documento"
                                    @change="validateField('tipo_documento'); resetDocumentValidation()"
                                    :class="errors.tipo_documento ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500'"
                                    class="block w-full dark:bg-gray-700 dark:text-white rounded-lg shadow-sm">
                                <option value="">Seleccione...</option>
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Carnet de Extranjería">Carnet de Extranjería</option>
                            </select>
                            <p x-show="errors.tipo_documento" x-text="errors.tipo_documento" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Número de Documento -->
                        <div class="col-span-1">
                            <label for="documento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Número de Documento <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="documento" 
                                       name="documento" 
                                       x-model="form.documento"
                                       @blur="validateDocument()"
                                       :class="errors.documento ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500'"
                                       class="pl-10 block w-full dark:bg-gray-700 dark:text-white rounded-lg shadow-sm"
                                       placeholder="Número de documento">
                            </div>
                            <div class="flex items-center mt-1">
                                <svg x-show="validatingDoc" class="animate-spin h-4 w-4 text-blue-500 mr-1" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p x-show="errors.documento" x-text="errors.documento" class="text-sm text-red-600"></p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        Información de Contacto
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Datos para comunicarse con el cliente
                    </p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Teléfono -->
                        <div class="col-span-1">
                            <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Teléfono <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <input type="tel" 
                                       id="telefono" 
                                       name="telefono" 
                                       x-model="form.telefono"
                                       @blur="validateField('telefono')"
                                       :class="errors.telefono ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500'"
                                       class="pl-10 block w-full dark:bg-gray-700 dark:text-white rounded-lg shadow-sm"
                                       placeholder="Ej: 999-123-456">
                            </div>
                            <p x-show="errors.telefono" x-text="errors.telefono" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Email -->
                        <div class="col-span-1">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Correo Electrónico
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       x-model="form.email"
                                       @blur="validateField('email')"
                                       :class="errors.email ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500'"
                                       class="pl-10 block w-full dark:bg-gray-700 dark:text-white rounded-lg shadow-sm"
                                       placeholder="cliente@ejemplo.com">
                            </div>
                            <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Dirección -->
                        <div class="col-span-1 lg:col-span-2">
                            <label for="direccion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Dirección
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <textarea id="direccion" 
                                          name="direccion" 
                                          x-model="form.direccion"
                                          @blur="validateField('direccion')"
                                          rows="3"
                                          :class="errors.direccion ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500'"
                                          class="pl-10 block w-full dark:bg-gray-700 dark:text-white rounded-lg shadow-sm resize-none"
                                          placeholder="Ingrese la dirección completa"></textarea>
                            </div>
                            <p x-show="errors.direccion" x-text="errors.direccion" class="mt-1 text-sm text-red-600"></p>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Los campos marcados con * son obligatorios
                        </div>
                        
                        <div class="flex space-x-3">
                            <a href="{{ route('clientes.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Cancelar
                            </a>
                            
                            <button type="submit" 
                                    :disabled="loading"
                                    :class="loading ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="loading ? 'Creando...' : 'Guardar Cliente'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
function clienteCreateForm() {
    return {
        loading: false,
        validatingDoc: false,
        errors: {},
        form: {
            nombre: '',
            apellido: '',
            telefono: '',
            email: '',
        },

        validateField(field) {
            // Limpiar error anterior
            delete this.errors[field];

            switch (field) {
                case 'nombre':
                    if (!this.form.nombre.trim()) {
                        this.errors.nombre = 'El nombre es obligatorio';
                    } else if (this.form.nombre.trim().length < 2) {
                        this.errors.nombre = 'El nombre debe tener al menos 2 caracteres';
                    }
                    break;
                case 'apellido':
                    // Sin validación especial (campo opcional)
                    break;
                case 'telefono':
                    if (!this.form.telefono.trim()) {
                        this.errors.telefono = 'El teléfono es obligatorio';
                    } else if (this.form.telefono.trim().length < 7) {
                        this.errors.telefono = 'El teléfono debe tener al menos 7 dígitos';
                    }
                    break;

                case 'email':
                    if (this.form.email && !this.validateEmail(this.form.email)) {
                        this.errors.email = 'El formato del email no es válido';
                    }
                    break;
            }
        },

        validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        validateForm() {
            // Validar todos los campos requeridos
            this.validateField('nombre');
            this.validateField('telefono');
            
            // Validar campos opcionales si tienen valor
            if (this.form.email) this.validateField('email');
            
            return Object.keys(this.errors).length === 0;
        },

        async submitForm() {
            if (!this.validateForm()) {
                this.showErrorNotification('Por favor corrija los errores en el formulario');
                return;
            }

            // Confirmación antes de crear
            const confirmed = await this.showConfirmDialog(
                '¿Confirmar creación?',
                '¿Está seguro de que desea crear este cliente?',
                'Sí, crear cliente'
            );

            if (!confirmed) return;

            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route("clientes.store") }}', {
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
                    this.showSuccessNotification('Cliente creado exitosamente');
                    // Redirigir después de un breve delay
                    setTimeout(() => {
                        window.location.href = '{{ route("clientes.index") }}';
                    }, 1500);
                } else {
                    // Manejar errores de validación del servidor
                    if (data.errors) {
                        this.errors = data.errors;
                        this.showErrorNotification('Hay errores en el formulario');
                    } else {
                        this.showErrorNotification(data.message || 'Error al crear el cliente');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                this.showErrorNotification('Error de conexión. Por favor, inténtelo nuevamente.');
            } finally {
                this.loading = false;
            }
        },

        async showConfirmDialog(title, text, confirmText) {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3B82F6',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: confirmText,
                    cancelButtonText: 'Cancelar'
                });
                return result.isConfirmed;
            } else {
                return confirm(text);
            }
        },

        showSuccessNotification(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                alert(message);
            }
        },

        showErrorNotification(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error',
                    text: message,
                    icon: 'error',
                    confirmButtonColor: '#EF4444'
                });
            } else {
                alert(message);
            }
        }
    }
}
</script>
@endpush