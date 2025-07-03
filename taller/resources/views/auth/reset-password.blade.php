@extends('layouts.auth')

@section('title', 'Restablecer Contraseña')

@section('auth-subtitle', 'Crea una nueva contraseña segura para tu cuenta')

@section('content')
<div x-data="resetPasswordForm()">
    <!-- Instructions -->
    <div class="mb-6 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 class="mt-2 text-lg font-medium text-gray-900">Restablecer Contraseña</h3>
        <p class="mt-1 text-sm text-gray-600">
            Ingresa tu nueva contraseña. Asegúrate de que sea segura y fácil de recordar.
        </p>
    </div>

    <!-- Reset Form -->
    <form method="POST" action="{{ route('password.store') }}" @submit.prevent="submitResetPassword" class="space-y-6">
        @csrf
        
        <!-- Hidden Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        
        <!-- Email Field (Read-only) -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
                Correo Electrónico
            </label>
            <div class="mt-1 relative">
                <input id="email" 
                       name="email" 
                       type="email" 
                       readonly
                       x-model="formData.email"
                       class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 bg-gray-50 text-gray-500 cursor-not-allowed focus:outline-none sm:text-sm"
                       value="{{ $request->email ?? old('email') }}">
                
                <!-- Lock Icon -->
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-1a2 2 0 00-2-2H6a2 2 0 00-2 2v1a2 2 0 002 2zM11 5C11 4.448 11.448 4 12 4s1 .448 1 1v6c0 .552-.448 1-1 1s-1-.448-1-1V5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                Nueva Contraseña
            </label>
            <div class="mt-1 relative">
                <input id="password" 
                       name="password" 
                       :type="showPassword ? 'text' : 'password'" 
                       required 
                       x-model="formData.password"
                       x-ref="passwordInput"
                       @input="validatePassword"
                       :class="errors.password ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                       class="appearance-none block w-full px-3 py-2 pr-10 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm transition-colors"
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
            
            <!-- Password Requirements -->
            <div class="mt-2 space-y-1">
                <div class="flex items-center text-xs" :class="requirements.length ? 'text-green-600' : 'text-gray-500'">
                    <svg class="h-3 w-3 mr-1" :class="requirements.length ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" x-show="requirements.length"></path>
                        <circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="2" fill="none" x-show="!requirements.length"></circle>
                    </svg>
                    Al menos 8 caracteres
                </div>
                <div class="flex items-center text-xs" :class="requirements.uppercase ? 'text-green-600' : 'text-gray-500'">
                    <svg class="h-3 w-3 mr-1" :class="requirements.uppercase ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" x-show="requirements.uppercase"></path>
                        <circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="2" fill="none" x-show="!requirements.uppercase"></circle>
                    </svg>
                    Una letra mayúscula
                </div>
                <div class="flex items-center text-xs" :class="requirements.lowercase ? 'text-green-600' : 'text-gray-500'">
                    <svg class="h-3 w-3 mr-1" :class="requirements.lowercase ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" x-show="requirements.lowercase"></path>
                        <circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="2" fill="none" x-show="!requirements.lowercase"></circle>
                    </svg>
                    Una letra minúscula
                </div>
                <div class="flex items-center text-xs" :class="requirements.number ? 'text-green-600' : 'text-gray-500'">
                    <svg class="h-3 w-3 mr-1" :class="requirements.number ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" x-show="requirements.number"></path>
                        <circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="2" fill="none" x-show="!requirements.number"></circle>
                    </svg>
                    Un número
                </div>
                <div class="flex items-center text-xs" :class="requirements.special ? 'text-green-600' : 'text-gray-500'">
                    <svg class="h-3 w-3 mr-1" :class="requirements.special ? 'text-green-500' : 'text-gray-400'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" x-show="requirements.special"></path>
                        <circle cx="10" cy="10" r="9" stroke="currentColor" stroke-width="2" fill="none" x-show="!requirements.special"></circle>
                    </svg>
                    Un carácter especial (@$!%*?&)
                </div>
            </div>
            
            <!-- Password Error -->
            <div x-show="errors.password" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="mt-1">
                <p class="text-sm text-red-600" x-text="errors.password"></p>
            </div>
        </div>

        <!-- Confirm Password Field -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                Confirmar Nueva Contraseña
            </label>
            <div class="mt-1 relative">
                <input id="password_confirmation" 
                       name="password_confirmation" 
                       :type="showPasswordConfirm ? 'text' : 'password'" 
                       required 
                       x-model="formData.password_confirmation"
                       @input="validatePasswordConfirmation"
                       :class="errors.password_confirmation ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                       class="appearance-none block w-full px-3 py-2 pr-10 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm transition-colors"
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
            
            <!-- Password Match Indicator -->
            <div x-show="formData.password_confirmation" class="mt-1 flex items-center text-xs">
                <svg x-show="passwordsMatch" class="h-3 w-3 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <svg x-show="!passwordsMatch" class="h-3 w-3 mr-1 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
                <span :class="passwordsMatch ? 'text-green-600' : 'text-red-600'">
                    <span x-show="passwordsMatch">Las contraseñas coinciden</span>
                    <span x-show="!passwordsMatch">Las contraseñas no coinciden</span>
                </span>
            </div>
            
            <!-- Password Confirmation Error -->
            <div x-show="errors.password_confirmation" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="mt-1">
                <p class="text-sm text-red-600" x-text="errors.password_confirmation"></p>
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

                <!-- Key Icon -->
                <span x-show="!loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </span>

                <span x-text="loading ? 'Restableciendo contraseña...' : 'Restablecer Contraseña'"></span>
            </button>
        </div>

        <!-- Security Notice -->
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Recomendaciones de Seguridad
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>Usa una combinación única de letras, números y símbolos</li>
                            <li>Evita usar información personal como nombres o fechas</li>
                            <li>No reutilices esta contraseña en otros sitios web</li>
                            <li>Considera usar un administrador de contraseñas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('auth-links')
<div class="space-y-2">
    <p class="text-sm text-gray-300">
        ¿Recordaste tu contraseña? 
        <a href="{{ route('login') }}" class="font-medium text-white hover:text-gray-200 transition-colors">
            Volver al login
        </a>
    </p>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('resetPasswordForm', () => ({
        formData: {
            email: '{{ $request->email ?? old("email") }}',
            password: '',
            password_confirmation: ''
        },
        errors: {},
        loading: false,
        showPassword: false,
        showPasswordConfirm: false,
        
        // Password validation
        passwordStrength: 0,
        passwordStrengthText: '',
        requirements: {
            length: false,
            uppercase: false,
            lowercase: false,
            number: false,
            special: false
        },
        
        get isFormValid() {
            return this.formData.password && 
                   this.formData.password_confirmation &&
                   this.passwordsMatch &&
                   this.passwordStrength >= 3 &&
                   Object.keys(this.errors).length === 0;
        },
        
        get passwordsMatch() {
            return this.formData.password === this.formData.password_confirmation;
        },
        
        init() {
            // Auto-focus password field
            this.$nextTick(() => {
                this.$refs.passwordInput.focus();
            });
        },
        
        validatePassword() {
            const password = this.formData.password;
            
            // Reset requirements
            this.requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[@$!%*?&]/.test(password)
            };
            
            // Calculate strength
            const metRequirements = Object.values(this.requirements).filter(Boolean).length;
            this.passwordStrength = Math.min(3, Math.max(0, metRequirements - 1));
            
            // Set strength text
            const strengthTexts = ['', 'Débil', 'Media', 'Fuerte'];
            this.passwordStrengthText = strengthTexts[this.passwordStrength];
            
            // Validate password
            if (!password) {
                this.errors.password = 'La contraseña es requerida';
            } else if (!this.requirements.length) {
                this.errors.password = 'La contraseña debe tener al menos 8 caracteres';
            } else if (this.passwordStrength < 3) {
                this.errors.password = 'La contraseña debe cumplir todos los requisitos de seguridad';
            } else {
                delete this.errors.password;
            }
            
            // Revalidate confirmation if it exists
            if (this.formData.password_confirmation) {
                this.validatePasswordConfirmation();
            }
        },
        
        validatePasswordConfirmation() {
            if (!this.formData.password_confirmation) {
                this.errors.password_confirmation = 'Confirma tu contraseña';
            } else if (!this.passwordsMatch) {
                this.errors.password_confirmation = 'Las contraseñas no coinciden';
            } else {
                delete this.errors.password_confirmation;
            }
        },
        
        async submitResetPassword() {
            // Validate all fields
            this.validatePassword();
            this.validatePasswordConfirmation();
            
            if (Object.keys(this.errors).length > 0) {
                this.showToast('Por favor corrige los errores en el formulario', 'error');
                return;
            }
            
            this.loading = true;
            
            try {
                const formData = new FormData();
                formData.append('token', '{{ $request->route("token") }}');
                formData.append('email', this.formData.email);
                formData.append('password', this.formData.password);
                formData.append('password_confirmation', this.formData.password_confirmation);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                const response = await fetch('{{ route("password.store") }}', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.showToast('Contraseña restablecida exitosamente', 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("login") }}';
                    }, 2000);
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        this.errors = data.errors;
                    }
                    
                    if (data.message) {
                        this.showToast(data.message, 'error');
                    }
                }
            } catch (error) {
                console.error('Reset password error:', error);
                this.showToast('Error de conexión. Por favor intenta de nuevo.', 'error');
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