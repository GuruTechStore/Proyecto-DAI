@extends('layouts.app')

@section('title', 'Nuevo Proveedor')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-500">Dashboard</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <a href="{{ route('proveedores.index') }}" class="text-gray-400 hover:text-gray-500">Proveedores</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <span class="text-gray-500 font-medium">Nuevo Proveedor</span>
    </div>
</li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="proveedorForm()">
    
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Nuevo Proveedor</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Registra un nuevo proveedor en el sistema
        </p>
    </div>

    <form @submit.prevent="submitForm" class="space-y-8">
        @csrf
        
        <!-- Información Básica -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12"/>
                    </svg>
                    Información Básica
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Datos principales del proveedor
                </p>
            </div>

            <div class="px-6 py-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Nombre -->
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nombre de la Empresa <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               x-model="form.nombre"
                               required
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Ingrese el nombre de la empresa">
                        <div x-show="errors.nombre" class="mt-1 text-sm text-red-600" x-text="errors.nombre"></div>
                    </div>

                    <!-- RUC -->
                    <div>
                        <label for="ruc" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            RUC
                        </label>
                        <input type="text" 
                               id="ruc" 
                               name="ruc" 
                               x-model="form.ruc"
                               maxlength="11"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="20123456789">
                        <div x-show="errors.ruc" class="mt-1 text-sm text-red-600" x-text="errors.ruc"></div>
                    </div>

                    <!-- Contacto -->
                    <div>
                        <label for="contacto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Persona de Contacto
                        </label>
                        <input type="text" 
                               id="contacto" 
                               name="contacto" 
                               x-model="form.contacto"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Nombre del contacto">
                        <div x-show="errors.contacto" class="mt-1 text-sm text-red-600" x-text="errors.contacto"></div>
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Teléfono
                        </label>
                        <input type="tel" 
                               id="telefono" 
                               name="telefono" 
                               x-model="form.telefono"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="999 123 456">
                        <div x-show="errors.telefono" class="mt-1 text-sm text-red-600" x-text="errors.telefono"></div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Correo Electrónico
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               x-model="form.email"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="contacto@empresa.com">
                        <div x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email"></div>
                    </div>

                    <!-- Dirección -->
                    <div class="md:col-span-2">
                        <label for="direccion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Dirección
                        </label>
                        <textarea id="direccion" 
                                  name="direccion" 
                                  x-model="form.direccion"
                                  rows="3"
                                  class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 resize-none"
                                  placeholder="Ingrese la dirección completa del proveedor"></textarea>
                        <div x-show="errors.direccion" class="mt-1 text-sm text-red-600" x-text="errors.direccion"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Bancaria -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Información Bancaria
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Datos bancarios para pagos (opcional)
                </p>
            </div>

            <div class="px-6 py-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Banco -->
                    <div>
                        <label for="banco" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Banco
                        </label>
                        <input type="text" 
                               id="banco" 
                               name="banco" 
                               x-model="form.banco"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Nombre del banco">
                        <div x-show="errors.banco" class="mt-1 text-sm text-red-600" x-text="errors.banco"></div>
                    </div>

                    <!-- Tipo de Cuenta -->
                    <div>
                        <label for="tipo_cuenta" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tipo de Cuenta
                        </label>
                        <select id="tipo_cuenta" 
                                name="tipo_cuenta" 
                                x-model="form.tipo_cuenta"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="">Seleccionar tipo</option>
                            <option value="corriente">Cuenta Corriente</option>
                            <option value="ahorros">Cuenta de Ahorros</option>
                            <option value="detraccion">Cuenta de Detracción</option>
                        </select>
                        <div x-show="errors.tipo_cuenta" class="mt-1 text-sm text-red-600" x-text="errors.tipo_cuenta"></div>
                    </div>

                    <!-- Número de Cuenta -->
                    <div class="md:col-span-2">
                        <label for="numero_cuenta" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Número de Cuenta
                        </label>
                        <input type="text" 
                               id="numero_cuenta" 
                               name="numero_cuenta" 
                               x-model="form.numero_cuenta"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                               placeholder="Número de cuenta bancaria">
                        <div x-show="errors.numero_cuenta" class="mt-1 text-sm text-red-600" x-text="errors.numero_cuenta"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Configuración
                </h3>
            </div>

            <div class="px-6 py-6">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="activo" 
                           name="activo" 
                           x-model="form.activo"
                           class="h-4 w-4 text-gestion-600 border-gray-300 rounded focus:ring-gestion-500">
                    <label for="activo" class="ml-2 block text-sm text-gray-900 dark:text-white">
                        Proveedor activo
                    </label>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Los proveedores inactivos no aparecerán en las listas de selección
                </p>
            </div>
        </div>

        <!-- Botones -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('proveedores.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancelar
                    </a>
                    
                    <button type="submit" 
                            :disabled="loading || !form.nombre"
                            class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors">
                        <span x-show="!loading" class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Crear Proveedor
                        </span>
                        <span x-show="loading" class="flex items-center">
                            <div class="w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            Creando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
        
    </form>
    
</div>

@push('scripts')
<script>
function proveedorForm() {
    return {
        loading: false,
        form: {
            nombre: '',
            ruc: '',
            contacto: '',
            telefono: '',
            email: '',
            direccion: '',
            banco: '',
            numero_cuenta: '',
            tipo_cuenta: '',
            activo: true
        },
        errors: {},
        
        async submitForm() {
            this.loading = true;
            this.errors = {};
            
            try {
                const formData = new FormData();
                
                // Agregar todos los campos del formulario
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== undefined) {
                        if (key === 'activo') {
                            formData.append(key, this.form[key] ? '1' : '0');
                        } else {
                            formData.append(key, this.form[key]);
                        }
                    }
                });

                const response = await fetch('{{ route("proveedores.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Éxito
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'Proveedor creado exitosamente',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route("proveedores.index") }}';
                        });
                    } else {
                        window.location.href = '{{ route("proveedores.index") }}';
                    }
                } else {
                    // Errores de validación
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.showError(data.message || 'Error al crear el proveedor');
                    }
                }

            } catch (error) {
                console.error('Error:', error);
                this.showError('Error de conexión. Inténtalo de nuevo.');
            } finally {
                this.loading = false;
            }
        },

        showError(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error',
                    text: message,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            } else {
                alert(message);
            }
        }
    }
}
</script>
@endpush
@endsection