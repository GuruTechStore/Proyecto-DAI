{{-- resources/views/modules/proveedores/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Proveedor')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12" />
        </svg>
        <a href="{{ route('proveedores.index') }}" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gestion-600 dark:hover:text-gestion-400">
            Proveedores
        </a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('proveedores.show', $proveedor) }}" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gestion-600 dark:hover:text-gestion-400">
            {{ $proveedor->nombre }}
        </a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Editar</span>
    </div>
</li>
@endsection

@push('styles')
<style>
    .form-group { margin-bottom: 1.5rem; }
    .form-floating { position: relative; }
    .form-floating input, .form-floating textarea, .form-floating select {
        width: 100%;
        padding: 1rem 0.75rem 0.25rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: transparent;
        font-size: 1rem;
        transition: all 0.15s ease-in-out;
    }
    .dark .form-floating input, .dark .form-floating textarea, .dark .form-floating select {
        border-color: #4b5563;
        background: transparent;
        color: #f9fafb;
    }
    .form-floating label {
        position: absolute;
        top: 1rem;
        left: 0.75rem;
        font-size: 1rem;
        color: #6b7280;
        transition: all 0.15s ease-in-out;
        pointer-events: none;
        transform-origin: 0 0;
    }
    .form-floating input:focus ~ label,
    .form-floating input:not(:placeholder-shown) ~ label,
    .form-floating textarea:focus ~ label,
    .form-floating textarea:not(:placeholder-shown) ~ label,
    .form-floating select:focus ~ label,
    .form-floating select:not([value=""]) ~ label {
        transform: scale(0.85) translateY(-0.5rem);
        color: #7c3aed;
    }
    .form-floating input:focus,
    .form-floating textarea:focus,
    .form-floating select:focus {
        outline: none;
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
    }
    .dark .form-floating input:focus ~ label,
    .dark .form-floating textarea:focus ~ label,
    .dark .form-floating select:focus ~ label {
        color: #a78bfa;
    }
    .error-message {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }
    .dark .error-message {
        color: #fca5a5;
    }
    .changes-badge {
        background: linear-gradient(45deg, #f59e0b, #f97316);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        animation: pulse 2s infinite;
    }
</style>
@endpush

@section('content')
<div x-data="editProveedorForm()" class="space-y-6">
    
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Editar Proveedor
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Modifica la información del proveedor {{ $proveedor->nombre }}
                    </p>
                </div>
                
                <div class="flex items-center space-x-3">
                    <span x-show="hasChanges" class="changes-badge">
                        Cambios pendientes
                    </span>
                    
                    <a href="{{ route('proveedores.show', $proveedor) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form -->
    <form @submit.prevent="submitForm()" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Información Básica -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12" />
                    </svg>
                    Información Básica
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Datos principales del proveedor
                </p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Nombre -->
                    <div class="form-group">
                        <div class="form-floating">
                            <input type="text" 
                                   id="nombre" 
                                   name="nombre" 
                                   x-model="form.nombre"
                                   placeholder=" "
                                   class="form-control"
                                   required>
                            <label for="nombre">Nombre del Proveedor *</label>
                        </div>
                        <span class="error-message" x-show="errors.nombre" x-text="errors.nombre"></span>
                    </div>
                    
                    <!-- RUC -->
                    <div class="form-group">
                        <div class="form-floating">
                            <input type="text" 
                                   id="ruc" 
                                   name="ruc" 
                                   x-model="form.ruc"
                                   placeholder=" "
                                   class="form-control"
                                   maxlength="11"
                                   pattern="[0-9]{11}">
                            <label for="ruc">RUC (11 dígitos)</label>
                        </div>
                        <span class="error-message" x-show="errors.ruc" x-text="errors.ruc"></span>
                    </div>
                    
                    <!-- Contacto -->
                    <div class="form-group">
                        <div class="form-floating">
                            <input type="text" 
                                   id="contacto" 
                                   name="contacto" 
                                   x-model="form.contacto"
                                   placeholder=" "
                                   class="form-control">
                            <label for="contacto">Persona de Contacto</label>
                        </div>
                        <span class="error-message" x-show="errors.contacto" x-text="errors.contacto"></span>
                    </div>
                    
                    <!-- Teléfono -->
                    <div class="form-group">
                        <div class="form-floating">
                            <input type="tel" 
                                   id="telefono" 
                                   name="telefono" 
                                   x-model="form.telefono"
                                   placeholder=" "
                                   class="form-control">
                            <label for="telefono">Teléfono</label>
                        </div>
                        <span class="error-message" x-show="errors.telefono" x-text="errors.telefono"></span>
                    </div>
                    
                    <!-- Email -->
                    <div class="form-group md:col-span-2">
                        <div class="form-floating">
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   x-model="form.email"
                                   placeholder=" "
                                   class="form-control">
                            <label for="email">Correo Electrónico</label>
                        </div>
                        <span class="error-message" x-show="errors.email" x-text="errors.email"></span>
                    </div>
                    
                    <!-- Dirección -->
                    <div class="form-group md:col-span-2">
                        <div class="form-floating">
                            <textarea id="direccion" 
                                      name="direccion" 
                                      x-model="form.direccion"
                                      placeholder=" "
                                      rows="3"
                                      class="form-control resize-none"></textarea>
                            <label for="direccion">Dirección</label>
                        </div>
                        <span class="error-message" x-show="errors.direccion" x-text="errors.direccion"></span>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <!-- Información Comercial -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                    Información Comercial
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Datos bancarios y comerciales (opcional)
                </p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Banco -->
                    <div class="form-group">
                        <div class="form-floating">
                            <input type="text" 
                                   id="banco" 
                                   name="banco" 
                                   x-model="form.banco"
                                   placeholder=" "
                                   class="form-control">
                            <label for="banco">Banco</label>
                        </div>
                        <span class="error-message" x-show="errors.banco" x-text="errors.banco"></span>
                    </div>
                    
                    <!-- Tipo de Cuenta -->
                    <div class="form-group">
                        <div class="form-floating">
                            <select id="tipo_cuenta" 
                                    name="tipo_cuenta" 
                                    x-model="form.tipo_cuenta"
                                    class="form-control">
                                <option value="">Seleccionar tipo</option>
                                <option value="corriente">Cuenta Corriente</option>
                                <option value="ahorros">Cuenta de Ahorros</option>
                            </select>
                            <label for="tipo_cuenta">Tipo de Cuenta</label>
                        </div>
                        <span class="error-message" x-show="errors.tipo_cuenta" x-text="errors.tipo_cuenta"></span>
                    </div>
                    
                    <!-- Número de Cuenta -->
                    <div class="form-group md:col-span-2">
                        <div class="form-floating">
                            <input type="text" 
                                   id="numero_cuenta" 
                                   name="numero_cuenta" 
                                   x-model="form.numero_cuenta"
                                   placeholder=" "
                                   class="form-control">
                            <label for="numero_cuenta">Número de Cuenta</label>
                        </div>
                        <span class="error-message" x-show="errors.numero_cuenta" x-text="errors.numero_cuenta"></span>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <!-- Estado -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Configuración
                </h2>
            </div>
            
            <div class="p-6">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="activo" 
                           name="activo" 
                           x-model="form.activo"
                           class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
                    <label for="activo" class="ml-2 block text-sm text-gray-900 dark:text-white">
                        Proveedor activo
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Los proveedores inactivos no aparecerán en las listas de selección
                </p>
            </div>
        </div>
        
        <!-- Resumen de Cambios -->
        <div x-show="hasChanges" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Hay cambios sin guardar
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>Los siguientes campos han sido modificados:</p>
                        <ul class="list-disc list-inside mt-1">
                            <template x-for="field in changedFields" :key="field">
                                <li x-text="getFieldLabel(field)"></li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Buttons -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4">
                <div class="flex justify-between">
                    <div class="flex space-x-3">
                        <button type="button" 
                                @click="resetForm()"
                                :disabled="!hasChanges"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Restablecer
                        </button>
                    </div>
                    
                    <div class="flex space-x-3">
                        <a href="{{ route('proveedores.show', $proveedor) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Cancelar
                        </a>
                        
                        <button type="submit" 
                                :disabled="loading || !hasChanges"
                                class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors">
                            <span x-show="!loading">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Guardar Cambios
                            </span>
                            <span x-show="loading" class="flex items-center">
                                <div class="w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
    </form>
    
</div>

@push('scripts')
<script>
function editProveedorForm() {
    return {
        loading: false,
        
        // Datos originales del proveedor
        originalData: {
            nombre: @json($proveedor->nombre ?? ''),
            ruc: @json($proveedor->ruc ?? ''),
            contacto: @json($proveedor->contacto ?? ''),
            telefono: @json($proveedor->telefono ?? ''),
            email: @json($proveedor->email ?? ''),
            direccion: @json($proveedor->direccion ?? ''),
            banco: @json($proveedor->banco ?? ''),
            numero_cuenta: @json($proveedor->numero_cuenta ?? ''),
            tipo_cuenta: @json($proveedor->tipo_cuenta ?? ''),
            activo: @json($proveedor->activo ?? true)
        },
        
        // Formulario actual
        form: {
            nombre: @json($proveedor->nombre ?? ''),
            ruc: @json($proveedor->ruc ?? ''),
            contacto: @json($proveedor->contacto ?? ''),
            telefono: @json($proveedor->telefono ?? ''),
            email: @json($proveedor->email ?? ''),
            direccion: @json($proveedor->direccion ?? ''),
            banco: @json($proveedor->banco ?? ''),
            numero_cuenta: @json($proveedor->numero_cuenta ?? ''),
            tipo_cuenta: @json($proveedor->tipo_cuenta ?? ''),
            activo: @json($proveedor->activo ?? true)
        },
        
        errors: {},
        
        // Computed properties
        get hasChanges() {
            return Object.keys(this.form).some(key => {
                const original = this.originalData[key] || '';
                const current = this.form[key] || '';
                return original !== current;
            });
        },
        
        get changedFields() {
            return Object.keys(this.form).filter(key => {
                const original = this.originalData[key] || '';
                const current = this.form[key] || '';
                return original !== current;
            });
        },
        
        init() {
            // Validaciones en tiempo real
            this.$watch('form.ruc', () => this.validateRuc());
            this.$watch('form.email', () => this.validateEmail());
        },
        
        async submitForm() {
            this.loading = true;
            this.errors = {};
            
            try {
                const formData = new FormData();
                
                // Solo enviar campos que han cambiado
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== undefined) {
                        if (key === 'activo') {
                            formData.append(key, this.form[key] ? '1' : '0');
                        } else {
                            formData.append(key, this.form[key] || '');
                        }
                    }
                });
                
                // Agregar método y token CSRF
                formData.append('_method', 'PUT');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                const response = await fetch('{{ route("proveedores.update", $proveedor) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.showSuccess('Proveedor actualizado correctamente');
                    
                    // Actualizar datos originales
                    this.originalData = { ...this.form };
                    
                    // Redirigir después de un breve delay
                    setTimeout(() => {
                        window.location.href = '{{ route("proveedores.show", $proveedor) }}';
                    }, 1500);
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.showError(data.message || 'Error al actualizar el proveedor');
                    }
                }
                
            } catch (error) {
                console.error('Error:', error);
                this.showError('Error de conexión. Por favor, intenta nuevamente.');
            } finally {
                this.loading = false;
            }
        },
        
        resetForm() {
            this.form = { ...this.originalData };
            this.errors = {};
        },
        
        validateRuc() {
            if (this.form.ruc && this.form.ruc.length === 11) {
                if (!/^[0-9]{11}$/.test(this.form.ruc)) {
                    this.errors.ruc = 'El RUC debe contener exactamente 11 dígitos';
                } else {
                    delete this.errors.ruc;
                }
            }
        },
        
        validateEmail() {
            if (this.form.email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.form.email)) {
                    this.errors.email = 'Ingresa un correo electrónico válido';
                } else {
                    delete this.errors.email;
                }
            }
        },
        
        getFieldLabel(field) {
            const labels = {
                nombre: 'Nombre del proveedor',
                ruc: 'RUC',
                contacto: 'Persona de contacto',
                telefono: 'Teléfono',
                email: 'Correo electrónico',
                direccion: 'Dirección',
                banco: 'Banco',
                numero_cuenta: 'Número de cuenta',
                tipo_cuenta: 'Tipo de cuenta',
                activo: 'Estado'
            };
            return labels[field] || field;
        },
        
        showSuccess(message) {
            if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
                Alpine.store('notifications').add({
                    type: 'success',
                    message: message
                });
            } else {
                alert(message);
            }
        },
        
        showError(message) {
            if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
                Alpine.store('notifications').add({
                    type: 'error',
                    message: message
                });
            } else {
                alert(message);
            }
        }
    };
}
</script>
@endpush

@endsection