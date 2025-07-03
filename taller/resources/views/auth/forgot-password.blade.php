@extends('layouts.auth')

@section('title', 'Recuperar Contraseña')

@section('auth-subtitle', 'Te enviaremos un enlace para restablecer tu contraseña')

@section('content')
<div x-data="forgotPasswordForm()">
    <!-- Instructions -->
    <div class="mb-6 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-1a2 2 0 00-2-2H6a2 2 0 00-2 2v1a2 2 0 002 2zM11 5C11 4.448 11.448 4 12 4s1 .448 1 1v6c0 .552-.448 1-1 1s-1-.448-1-1V5z"></path>
            </svg>
        </div>
        <h3 class="mt-2 text-lg font-medium text-gray-900">¿Olvidaste tu contraseña?</h3>
        <p class="mt-1 text-sm text-gray-600">
            No te preocupes. Ingresa tu correo electrónico y te enviaremos un enlace para crear una nueva contraseña.
        </p>
    </div>

    <!-- Reset Form -->
    <form method="POST" action="{{ route('password.email') }}" @submit.prevent="submitForgotPassword" class="space-y-6">
        @csrf
        
        <!-- Email Field -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
                Correo Electrónico
            </label>
            <div class="mt-1 relative">
                <input id="email" 
                       name="email" 
                       type="email" 
                       autocomplete="email" 
                       required 
                       x-model="formData.email"
                       x-ref="emailInput"
                       @blur="validateEmail"
                       :class="errors.email ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                       class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm transition-colors"
                       placeholder="tu-email@empresa.com"
                       value="{{ old('email') }}">
                
                <!-- Email Icon -->
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Email Error -->
            <div x-show="errors.email" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="mt-1">
                <p class="text-sm text-red-600" x-text="errors.email"></p>
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

                <!-- Email Icon -->
                <span x-show="!loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </span>

                <span x-text="loading ? 'Enviando enlace...' : 'Enviar Enlace de Recuperación'"></span>
            </button>
        </div>

        <!-- Success State -->
        <div x-show="emailSent" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">
                        ¡Enlace enviado exitosamente!
                    </h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>
                            Hemos enviado un enlace de recuperación a <strong x-text="formData.email"></strong>. 
                            Revisa tu bandeja de entrada y sigue las instrucciones para restablecer tu contraseña.
                        </p>
                        <p class="mt-2">
                            <strong>Nota:</strong> El enlace expirará en 60 minutos por seguridad.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resend Timer -->
        <div x-show="emailSent && resendTimer > 0" 
             class="text-center">
            <p class="text-sm text-gray-600">
                ¿No recibiste el correo? Podrás solicitar otro enlace en 
                <span class="font-medium text-indigo-600" x-text="resendTimer"></span> segundos.
            </p>
        </div>

        <!-- Resend Button -->
        <div x-show="emailSent && resendTimer === 0" 
             class="text-center">
            <button type="button" 
                    @click="resendEmail"
                    :disabled="resendLoading"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-500 disabled:text-gray-400 disabled:cursor-not-allowed">
                <span x-show="!resendLoading">¿No recibiste el correo? Enviar de nuevo</span>
                <span x-show="resendLoading">Reenviando...</span>
            </button>
        </div>
    </form>

    <!-- Help Section -->
    <div class="mt-8 p-4 bg-gray-50 rounded-md">
        <h4 class="text-sm font-medium text-gray-900 mb-2">¿Necesitas ayuda adicional?</h4>
        <div class="space-y-2 text-sm text-gray-600">
            <p>• Verifica tu carpeta de spam o correo no deseado</p>
            <p>• Asegúrate de que el correo esté correctamente escrito</p>
            <p>• Si el problema persiste, contacta al administrador del sistema</p>
        </div>
        <div class="mt-3">
            <a href="mailto:admin@empresa.com" 
               class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                Contactar Soporte Técnico
            </a>
        </div>
    </div>
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
    
    <p class="text-sm text-gray-400">
        ¿Necesitas una cuenta? 
        <a href="{{ route('help') }}" class="font-medium text-white hover:text-gray-200 transition-colors">
            Contacta al administrador
        </a>
    </p>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('forgotPasswordForm', () => ({
        formData: {
            email: ''
        },
        errors: {},
        loading: false,
        emailSent: false,
        resendTimer: 0,
        resendLoading: false,
        timerInterval: null,
        
        get isFormValid() {
            return this.formData.email && !this.errors.email;
        },
        
        init() {
            // Auto-focus email field
            this.$nextTick(() => {
                this.$refs.emailInput.focus();
            });
        },
        
        validateEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!this.formData.email) {
                this.errors.email = 'El correo electrónico es requerido';
            } else if (!emailRegex.test(this.formData.email)) {
                this.errors.email = 'Ingresa un correo electrónico válido';
            } else {
                delete this.errors.email;
            }
        },
        
        async submitForgotPassword() {
            // Validate email
            this.validateEmail();
            
            if (Object.keys(this.errors).length > 0) {
                return;
            }
            
            this.loading = true;
            
            try {
                const formData = new FormData();
                formData.append('email', this.formData.email);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                const response = await fetch('{{ route("password.email") }}', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.emailSent = true;
                    this.startResendTimer();
                    this.showToast('Enlace de recuperación enviado exitosamente', 'success');
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
                console.error('Forgot password error:', error);
                this.showToast('Error de conexión. Por favor intenta de nuevo.', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        async resendEmail() {
            this.resendLoading = true;
            
            try {
                const formData = new FormData();
                formData.append('email', this.formData.email);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                const response = await fetch('{{ route("password.email") }}', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    this.startResendTimer();
                    this.showToast('Enlace reenviado exitosamente', 'success');
                } else {
                    this.showToast('Error al reenviar el enlace', 'error');
                }
            } catch (error) {
                console.error('Resend email error:', error);
                this.showToast('Error de conexión. Por favor intenta de nuevo.', 'error');
            } finally {
                this.resendLoading = false;
            }
        },
        
        startResendTimer() {
            this.resendTimer = 60; // 60 seconds
            
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
            }
            
            this.timerInterval = setInterval(() => {
                this.resendTimer--;
                
                if (this.resendTimer <= 0) {
                    clearInterval(this.timerInterval);
                    this.timerInterval = null;
                }
            }, 1000);
        },
        
        showToast(message, type = 'info') {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    }));
});

// Cleanup timer on page unload
window.addEventListener('beforeunload', function() {
    const component = Alpine.$data(document.querySelector('[x-data="forgotPasswordForm()"]'));
    if (component && component.timerInterval) {
        clearInterval(component.timerInterval);
    }
});
</script>
@endpush