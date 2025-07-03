{{-- resources/views/modules/clientes/edit.blade.php - PARTE 1 --}}
@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7l3-3 3 3m0 8l-3 3-3-3" />
        </svg>
        <a href="{{ route('clientes.index') }}" class="ml-2 text-sm font-medium text-gray-700 hover:text-blue-600">Clientes</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7l3-3 3 3m0 8l-3 3-3-3" />
        </svg>
        <span class="ml-2 text-sm font-medium text-gray-500">Editar Cliente</span>
    </div>
</li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Editar Cliente
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Modifique la información del cliente: {{ $cliente->nombre }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    @if(Route::has('clientes.show'))
                    <a href="{{ route('clientes.show', $cliente) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Ver
                    </a>
                    @endif
                    <a href="{{ route('clientes.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <form x-data="clienteEditForm()" @submit.prevent="submitForm()" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Error Summary -->
            <div x-show="Object.keys(errors).length > 0" x-transition 
                 class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <h3 class="text-sm font-medium text-red-800">Hay errores en el formulario:</h3>
                </div>
                <ul class="text-sm text-red-700 space-y-1">
                    <template x-for="(error, field) in errors" :key="field">
                        <li x-text="error"></li>
                    </template>
                </ul>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Nombre -->
                <div class="md:col-span-2">
                    <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nombre Completo <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               x-model="form.nombre"
                               @blur="validateField('nombre')"
                               @input="clearError('nombre')"
                               :class="errors.nombre ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500'"
                               class="pl-10 block w-full dark:bg-gray-700 dark:text-white rounded-lg shadow-sm"
                               placeholder="Ingrese el nombre completo"
                               required>
                    </div>
                    <p x-show="errors.nombre" x-text="errors.nombre" class="mt-1 text-sm text-red-600"></p>
                </div>
                
                
                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Teléfono <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <input type="tel" 
                               id="telefono" 
                               name="telefono" 
                               x-model="form.telefono"
                               @blur="validateField('telefono')"
                               @input="clearError('telefono')"
                               :class="errors.telefono ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500'"
                               class="pl-10 block w-full dark:bg-gray-700 dark:text-white rounded-lg shadow-sm"
                               placeholder="Ej: 999-123-456"
                               required>
                    </div>
                    <p x-show="errors.telefono" x-text="errors.telefono" class="mt-1 text-sm text-red-600"></p>
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Correo Electrónico
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               x-model="form.email"
                               @blur="validateField('email')"
                               @input="clearError('email')"
                               :class="errors.email ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500'"
                               class="pl-10 block w-full dark:bg-gray-700 dark:text-white rounded-lg shadow-sm"
                               placeholder="cliente@ejemplo.com">
                    </div>
                    <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600"></p>
                </div>
            <!-- Form Actions -->
            <div class="mt-8 flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('clientes.index') }}" 
                   class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        :disabled="loading"
                        :class="loading ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? 'Actualizando...' : 'Actualizar Cliente'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function clienteEditForm() {
    return {
        loading: false,
        errors: {},
        form: {
            nombre: '{{ $cliente->nombre }}',
            apellido: '{{ $cliente->tipo_documento }}',
            telefono: '{{ $cliente->telefono }}',
            email: '{{ $cliente->email ?? "" }}',
        },

        validateField(field) {
            // Limpiar errores previos del campo
            delete this.errors[field];
            
            const value = this.form[field];
            
            switch(field) {
                case 'nombre':
                    if (!value || !value.trim()) {
                        this.errors[field] = 'El nombre es requerido';
                    } else if (value.trim().length < 2) {
                        this.errors[field] = 'El nombre debe tener al menos 2 caracteres';
                    } else if (value.trim().length > 100) {
                        this.errors[field] = 'El nombre no puede tener más de 100 caracteres';
                    }
                    break;
                    
                    
                case 'telefono':
                    if (!value || !value.trim()) {
                        this.errors[field] = 'El teléfono es requerido';
                    } else if (!/^[\d\-\+\(\)\s]{9,}$/.test(value)) {
                        this.errors[field] = 'Formato de teléfono inválido (mínimo 9 dígitos)';
                    }
                    break;
                    
                case 'email':
                    if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        this.errors[field] = 'Formato de email inválido';
                    }
                    break;
                    
            }
        },

        clearError(field) {
            delete this.errors[field];
        },

        validateForm() {
            // Validar todos los campos requeridos
            this.validateField('nombre');
            this.validateField('apellido');
            this.validateField('telefono');
            
            // Validar campos opcionales si tienen valor
            if (this.form.email) this.validateField('email');
            
            return Object.keys(this.errors).length === 0;
        },

        async submitForm() {
            if (!this.validateForm()) {
                showErrorNotification('Por favor corrija los errores en el formulario');
                return;
            }

            const result = await showConfirmDialog(
                '¿Confirmar actualización?',
                '¿Está seguro de que desea actualizar la información de este cliente?',
                'Sí, actualizar'
            );

            if (!result.isConfirmed) return;

            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route("clientes.update", $cliente) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    showSuccessNotification('Cliente actualizado exitosamente');
                    // Redirigir después de un breve delay
                    setTimeout(() => {
                        @if(Route::has('clientes.show'))
                            window.location.href = '{{ route("clientes.show", $cliente) }}';
                        @else
                            window.location.href = '{{ route("clientes.index") }}';
                        @endif
                    }, 1500);
                } else {
                    // Manejar errores de validación del servidor
                    if (data.errors) {
                        this.errors = data.errors;
                        showErrorNotification('Hay errores en el formulario');
                    } else {
                        showErrorNotification(data.message || 'Error al actualizar el cliente');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showErrorNotification('Error de conexión. Por favor, inténtelo nuevamente.');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush