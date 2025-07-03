{{-- resources/views/modules/proveedores/partials/form-fields.blade.php --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    
    <!-- Nombre -->
    <div class="form-group">
        <div class="form-floating">
            <input type="text" 
                   id="nombre" 
                   name="nombre" 
                   value="{{ old('nombre', $proveedor->nombre ?? '') }}"
                   placeholder=" "
                   class="form-control @error('nombre') border-red-500 @enderror"
                   required>
            <label for="nombre">Nombre del Proveedor *</label>
        </div>
        @error('nombre')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
    
    <!-- RUC -->
    <div class="form-group">
        <div class="form-floating">
            <input type="text" 
                   id="ruc" 
                   name="ruc" 
                   value="{{ old('ruc', $proveedor->ruc ?? '') }}"
                   placeholder=" "
                   class="form-control @error('ruc') border-red-500 @enderror"
                   maxlength="11"
                   pattern="[0-9]{11}">
            <label for="ruc">RUC (11 dígitos)</label>
        </div>
        @error('ruc')
            <span class="error-message">{{ $message }}</span>
        @enderror
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ejemplo: 20123456789</p>
    </div>
    
    <!-- Contacto -->
    <div class="form-group">
        <div class="form-floating">
            <input type="text" 
                   id="contacto" 
                   name="contacto" 
                   value="{{ old('contacto', $proveedor->contacto ?? '') }}"
                   placeholder=" "
                   class="form-control @error('contacto') border-red-500 @enderror">
            <label for="contacto">Persona de Contacto</label>
        </div>
        @error('contacto')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
    
    <!-- Teléfono -->
    <div class="form-group">
        <div class="form-floating">
            <input type="tel" 
                   id="telefono" 
                   name="telefono" 
                   value="{{ old('telefono', $proveedor->telefono ?? '') }}"
                   placeholder=" "
                   class="form-control @error('telefono') border-red-500 @enderror">
            <label for="telefono">Teléfono</label>
        </div>
        @error('telefono')
            <span class="error-message">{{ $message }}</span>
        @enderror
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Ejemplo: +51 999 123 456</p>
    </div>
    
    <!-- Email -->
    <div class="form-group md:col-span-2">
        <div class="form-floating">
            <input type="email" 
                   id="email" 
                   name="email" 
                   value="{{ old('email', $proveedor->email ?? '') }}"
                   placeholder=" "
                   class="form-control @error('email') border-red-500 @enderror">
            <label for="email">Correo Electrónico</label>
        </div>
        @error('email')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
    
    <!-- Dirección -->
    <div class="form-group md:col-span-2">
        <div class="form-floating">
            <textarea id="direccion" 
                      name="direccion" 
                      placeholder=" "
                      rows="3"
                      class="form-control resize-none @error('direccion') border-red-500 @enderror">{{ old('direccion', $proveedor->direccion ?? '') }}</textarea>
            <label for="direccion">Dirección</label>
        </div>
        @error('direccion')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
    
</div>

<!-- Información Comercial -->
<div class="mt-8">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
        Información Bancaria (Opcional)
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Banco -->
        <div class="form-group">
            <div class="form-floating">
                <input type="text" 
                       id="banco" 
                       name="banco" 
                       value="{{ old('banco', $proveedor->banco ?? '') }}"
                       placeholder=" "
                       class="form-control @error('banco') border-red-500 @enderror">
                <label for="banco">Banco</label>
            </div>
            @error('banco')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        
        <!-- Tipo de Cuenta -->
        <div class="form-group">
            <div class="form-floating">
                <select id="tipo_cuenta" 
                        name="tipo_cuenta" 
                        class="form-control @error('tipo_cuenta') border-red-500 @enderror">
                    <option value="">Seleccionar tipo</option>
                    <option value="corriente" {{ old('tipo_cuenta', $proveedor->tipo_cuenta ?? '') === 'corriente' ? 'selected' : '' }}>
                        Cuenta Corriente
                    </option>
                    <option value="ahorros" {{ old('tipo_cuenta', $proveedor->tipo_cuenta ?? '') === 'ahorros' ? 'selected' : '' }}>
                        Cuenta de Ahorros
                    </option>
                </select>
                <label for="tipo_cuenta">Tipo de Cuenta</label>
            </div>
            @error('tipo_cuenta')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        
        <!-- Número de Cuenta -->
        <div class="form-group md:col-span-2">
            <div class="form-floating">
                <input type="text" 
                       id="numero_cuenta" 
                       name="numero_cuenta" 
                       value="{{ old('numero_cuenta', $proveedor->numero_cuenta ?? '') }}"
                       placeholder=" "
                       class="form-control @error('numero_cuenta') border-red-500 @enderror">
                <label for="numero_cuenta">Número de Cuenta</label>
            </div>
            @error('numero_cuenta')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>
        
    </div>
</div>

<!-- Estado -->
<div class="mt-8">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Estado</h3>
    <div class="flex items-center">
        <input type="checkbox" 
               id="activo" 
               name="activo" 
               value="1"
               {{ old('activo', $proveedor->activo ?? true) ? 'checked' : '' }}
               class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
        <label for="activo" class="ml-2 block text-sm text-gray-900 dark:text-white">
            Proveedor activo
        </label>
    </div>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Los proveedores inactivos no aparecerán en las listas de selección
    </p>
</div>

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
</style>
@endpush