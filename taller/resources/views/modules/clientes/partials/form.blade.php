{{-- resources/views/modules/clientes/partials/form.blade.php --}}
@props([
    'cliente' => null,
    'action' => 'create',
    'method' => 'POST',
    'route' => '#'
])

@php
    $isEdit = $action === 'edit' && $cliente;
@endphp

<form method="POST" action="{{ $route }}" class="space-y-6" x-data="clienteFormPartial()" x-init="init()">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    
    <!-- Información Personal -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                
                <!-- Nombre -->
                <div class="col-span-1">
                    <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               value="{{ old('nombre', $cliente->nombre ?? '') }}"
                               @change="validateField('nombre')"
                               required
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 @error('nombre') border-red-500 @enderror"
                               placeholder="Ingrese el nombre del cliente">
                    </div>
                    @error('nombre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Apellido -->
                <div class="col-span-1">
                    <label for="apellido" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Apellido
                    </label>
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" 
                               id="apellido" 
                               name="apellido" 
                               value="{{ old('apellido', $cliente->apellido ?? '') }}"
                               @change="validateField('apellido')"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 @error('apellido') border-red-500 @enderror"
                               placeholder="Ingrese el apellido del cliente">
                    </div>
                    @error('apellido')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                            </div>
        </div>
    </div>

    <!-- Información de Contacto -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Teléfono <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <input type="tel" 
                               id="telefono" 
                               name="telefono" 
                               value="{{ old('telefono', $cliente->telefono ?? '') }}"
                               @change="validateField('telefono')"
                               required
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 @error('telefono') border-red-500 @enderror"
                               placeholder="Ej: 999-123-456">
                    </div>
                    @error('telefono')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Email -->
                <div class="col-span-1">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Correo Electrónico
                    </label>
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $cliente->email ?? '') }}"
                               @change="validateField('email')"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 @error('email') border-red-500 @enderror"
                               placeholder="cliente@ejemplo.com">
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Información Adicional
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Configuración y datos complementarios
            </p>
        </div>
        
        <div class="px-6 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Estado -->
                <div class="col-span-1">
                    <label for="activo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Estado del Cliente
                    </label>
                    <div class="mt-1">
                        <select id="activo" 
                                name="activo" 
                                @change="validateField('activo')"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 @error('activo') border-red-500 @enderror">
                            <option value="1" {{ old('activo', $cliente->activo ?? '1') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('activo', $cliente->activo ?? '1') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Los clientes inactivos no aparecerán en las búsquedas por defecto
                    </p>
                    @error('activo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Observaciones -->
                <div class="col-span-2 lg:col-span-1">
                    <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Observaciones
                    </label>
                    <div class="mt-1">
                        <textarea id="observaciones" 
                                  name="observaciones" 
                                  @change="validateField('observaciones')"
                                  rows="4"
                                  class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 @error('observaciones') border-red-500 @enderror"
                                  placeholder="Notas adicionales sobre el cliente...">{{ old('observaciones', $cliente->observaciones ?? '') }}</textarea>
                    </div>
                    @error('observaciones')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </a>
                    
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $isEdit ? 'Actualizar Cliente' : 'Crear Cliente' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function clienteFormPartial() {
    return {
        errors: {},
        
        init() {
            console.log('Cliente form partial initialized');
        },
        
        validateField(field) {
            // Limpiar errores previos
            delete this.errors[field];
            
            const value = document.getElementById(field).value;
            
            // Validaciones específicas
            switch(field) {
                case 'nombre':
                    if (!value.trim()) {
                        this.errors[field] = 'El nombre es requerido';
                    } else if (value.length < 2) {
                        this.errors[field] = 'El nombre debe tener al menos 2 caracteres';
                    }
                    break;
                    
                case 'telefono':
                    if (!value.trim()) {
                        this.errors[field] = 'El teléfono es requerido';
                    } else if (!/^[\d\-\+\(\)\s]{9,}$/.test(value)) {
                        this.errors[field] = 'Formato de teléfono inválido';
                    }
                    break;
                    
                case 'email':
                    if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        this.errors[field] = 'Formato de email inválido';
                    }
                    break;
                    
                case 'numero_documento':
                    const tipoDoc = document.getElementById('tipo_documento').value;
                    if (value && tipoDoc) {
                        if (tipoDoc === 'dni' && !/^\d{8}$/.test(value)) {
                            this.errors[field] = 'DNI debe tener 8 dígitos';
                        } else if (tipoDoc === 'ruc' && !/^\d{11}$/.test(value)) {
                            this.errors[field] = 'RUC debe tener 11 dígitos';
                        }
                    }
                    break;
            }
            
            // Mostrar/ocultar errores
            this.updateFieldError(field);
        },
        
        updateFieldError(field) {
            const input = document.getElementById(field);
            const errorElement = input.parentNode.parentNode.querySelector('.text-red-600');
            
            if (this.errors[field]) {
                input.classList.add('border-red-500');
                input.classList.remove('border-gray-300');
                
                if (errorElement) {
                    errorElement.textContent = this.errors[field];
                } else {
                    const error = document.createElement('p');
                    error.className = 'mt-1 text-sm text-red-600';
                    error.textContent = this.errors[field];
                    input.parentNode.parentNode.appendChild(error);
                }
            } else {
                input.classList.remove('border-red-500');
                input.classList.add('border-gray-300');
                
                if (errorElement) {
                    errorElement.remove();
                }
            }
        }
    }
}
</script>
@endpush