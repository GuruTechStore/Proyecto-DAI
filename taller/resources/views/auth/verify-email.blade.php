@extends('layouts.auth')

@section('title', 'Verificar Email')

@section('auth-subtitle', 'Confirma tu dirección de correo electrónico')

@section('content')
<div x-data="verifyEmailForm()">
    <!-- Verification Status -->
    <div class="mb-6 text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100">
            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>
        <h3 class="mt-4 text-xl font-medium text-gray-900">Verifica tu Correo Electrónico</h3>
        <p class="mt-2 text-sm text-gray-600 max-w-md mx-auto">
            Hemos enviado un enlace de verificación a <strong>{{ auth()->user()->email ?? 'tu correo electrónico' }}</strong>. 
            Revisa tu bandeja de entrada y haz clic en el enlace para activar tu cuenta.
        </p>
    </div>

    <!-- Verification Instructions -->
    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    ¿Qué hacer ahora?
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ol class="list-decimal list-inside space-y-1">
                        <li>Revisa tu bandeja de entrada de correo electrónico</li>
                        <li>Busca un email de {{ config('app.name') }} con el asunto "Verificar Email"</li>
                        <li>Haz clic en el enlace "Verificar Dirección de Email"</li>
                        <li>Serás redirigido automáticamente al sistema</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Resend Verification -->
    <div class="space-y-4">
        <!-- Resend Button -->
        <div x-show="!emailSent">
            <button type="button" 
                    @click="resendVerification"
                    :disabled="loading"
                    :class="loading ? 'bg-gray-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'"
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </span>

                <span x-text="loading ? 'Enviando...' : 'Reenviar Email de Verificación'"></span>
            </button>
        </div>

        <!-- Success Message -->
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
                        ¡Email enviado exitosamente!
                    </h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>
                            Hemos enviado un nuevo enlace de verificación a tu correo electrónico. 
                            Si no lo recibes en unos minutos, revisa tu carpeta de spam.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resend Timer -->
        <div x-show="emailSent && resendTimer > 0" 
             class="text-center">
            <p class="text-sm text-gray-600">
                Podrás solicitar otro enlace en 
                <span class="font-medium text-indigo-600" x-text="resendTimer"></span> segundos.
            </p>
        </div>

        <!-- Resend Again Button -->
        <div x-show="emailSent && resendTimer === 0" 
             class="text-center">
            <button type="button" 
                    @click="resendVerification"
                    :disabled="resendLoading"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-500 disabled:text-gray-400 disabled:cursor-not-allowed">
                <span x-show="!resendLoading">Enviar enlace nuevamente</span>
                <span x-show="resendLoading">Enviando...</span>
            </button>
        </div>
    </div>

    <!-- Manual Verification Option -->
    <div class="mt-8 border-t border-gray-200 pt-6">
        <div class="text-center">
            <h4 class="text-sm font-medium text-gray-900 mb-3">¿Problemas con la verificación?</h4>
            <div class="space-y-2">
                <button type="button" 
                        @click="checkVerificationStatus"
                        :disabled="checkingStatus"
                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500 disabled:text-gray-400 disabled:cursor-not-allowed">
                    <span x-show="!checkingStatus">Verificar estado actual</span>
                    <span x-show="checkingStatus">Verificando...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="mt-8 p-4 bg-gray-50 rounded-md">
        <h4 class="text-sm font-medium text-gray-900 mb-2">¿No recibes el email?</h4>
        <div class="space-y-2 text-sm text-gray-600">
            <p>• Verifica que el email no esté en tu carpeta de spam o correo no deseado</p>
            <p>• Asegúrate de que {{ auth()->user()->email ?? 'tu correo' }} sea correcto</p>
            <p>• Algunos proveedores de email pueden demorar hasta 15 minutos</p>
            <p>• Verifica que tu bandeja de entrada no esté llena</p>
        </div>
        <div class="mt-4 space-y-2">
            <a href="mailto:soporte@empresa.com" 
               class="block text-sm font-medium text-indigo-600 hover:text-indigo-500">
                Contactar Soporte Técnico
            </a>
            @auth
                <a href="{{ route('profile.edit') }}" 
                   class="block text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Cambiar dirección de email
                </a>
            @endauth
        </div>
    </div>

    <!-- Alternative Access -->
    <div class="mt-6 text-center border-t border-gray-200 pt-4">
        <p class="text-sm text-gray-600 mb-3">
            ¿Necesitas acceso inmediato? Contacta al administrador del sistema.
        </p>
        <div class="space-y-2">
            <a href="tel:+51999123456" 
               class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                +51 999 123 456
            </a>
            <span class="text-gray-400">•</span>
            <a href="mailto:admin@empresa.com" 
               class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                admin@empresa.com
            </a>
        </div>
    </div>
</div>
@endsection

@section('auth-links')
<div class="space-y-2">
    @auth
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm font-medium text-white hover:text-gray-200 transition-colors">
                Cerrar sesión
            </button>
        </form>
    @else
        <p class="text-sm text-gray-300">
            ¿Ya verificaste tu email? 
            <a href="{{ route('login') }}" class="font-medium text-white hover:text-gray-200 transition-colors">
                Iniciar sesión
            </a>
        </p>
    @endauth
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('verifyEmailForm', () => ({
        loading: false,
        emailSent: false,
        resendTimer: 0,
        resendLoading: false,
        checkingStatus: false,
        timerInterval: null,
        
        init() {
            // Check if we just sent an email (from session)
            @if(session('resent'))
                this.emailSent = true;
                this.startResendTimer();
            @endif
            
            // Auto-check verification status periodically
            this.startStatusCheck();
        },
        
        async resendVerification() {
            this.loading = true;
            
            try {
                const response = await fetch('{{ route("verification.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    this.emailSent = true;
                    this.startResendTimer();
                    this.showToast('Enlace de verificación enviado exitosamente', 'success');
                } else {
                    const data = await response.json();
                    this.showToast(data.message || 'Error al enviar el enlace', 'error');
                }
            } catch (error) {
                console.error('Resend verification error:', error);
                this.showToast('Error de conexión. Por favor intenta de nuevo.', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        async checkVerificationStatus() {
            this.checkingStatus = true;
            
            try {
                const response = await fetch('/api/user/verification-status', {
                    headers: {
                        'Authorization': 'Bearer ' + (localStorage.getItem('auth_token') || ''),
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.verified) {
                        this.showToast('¡Email verificado exitosamente!', 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route("dashboard") }}';
                        }, 2000);
                    } else {
                        this.showToast('Tu email aún no ha sido verificado', 'warning');
                    }
                } else {
                    this.showToast('No se pudo verificar el estado', 'error');
                }
            } catch (error) {
                console.error('Check verification status error:', error);
                this.showToast('Error al verificar estado', 'error');
            } finally {
                this.checkingStatus = false;
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
        
        startStatusCheck() {
            // Check verification status every 30 seconds
            setInterval(() => {
                this.checkVerificationStatusSilently();
            }, 30000);
        },
        
        async checkVerificationStatusSilently() {
            try {
                const response = await fetch('/api/user/verification-status', {
                    headers: {
                        'Authorization': 'Bearer ' + (localStorage.getItem('auth_token') || ''),
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.verified) {
                        // Automatically redirect if verified
                        window.location.href = '{{ route("dashboard") }}';
                    }
                }
            } catch (error) {
                // Silently fail - this is just a background check
                console.log('Background verification check failed:', error);
            }
        },
        
        showToast(message, type = 'info') {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    }));
});

// Listen for browser tab focus to check verification status
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        const component = Alpine.$data(document.querySelector('[x-data="verifyEmailForm()"]'));
        if (component) {
            component.checkVerificationStatusSilently();
        }
    }
});

// Cleanup timer on page unload
window.addEventListener('beforeunload', function() {
    const component = Alpine.$data(document.querySelector('[x-data="verifyEmailForm()"]'));
    if (component && component.timerInterval) {
        clearInterval(component.timerInterval);
    }
});
</script>
@endpush