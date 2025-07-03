@extends('layouts.auth')

@section('title', 'Iniciar Sesión')

@section('auth-subtitle', 'Accede a tu cuenta de gestión empresarial')

@section('content')
<div x-data="loginForm()" @submit.prevent="submitLogin">
    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-6">
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
                       placeholder="usuario@empresa.com"
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

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                Contraseña
            </label>
            <div class="mt-1 relative">
                <input id="password" 
                       name="password" 
                       :type="showPassword ? 'text' : 'password'" 
                       autocomplete="current-password" 
                       required 
                       x-model="formData.password"
                       @blur="validatePassword"
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
            
            <!-- Password Error -->
            <div x-show="errors.password" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="mt-1">
                <p class="text-sm text-red-600" x-text="errors.password"></p>
            </div>
        </div>

        <!-- Login Attempts Warning -->
        <div x-show="showLoginAttempts" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Atención: Intentos de acceso
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Te quedan <span x-text="attemptsRemaining"></span> intentos antes de que tu cuenta sea bloqueada temporalmente.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Captcha (after 3 failed attempts) -->
        <div x-show="showCaptcha" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-3">
            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                <label for="captcha" class="block text-sm font-medium text-gray-700 mb-2">
                    Verificación de Seguridad
                </label>
                <div class="flex items-center space-x-3">
                    <div class="bg-white border border-gray-300 rounded px-3 py-2 font-mono text-lg tracking-widest select-none" x-text="captchaText"></div>
                    <button type="button" @click="generateCaptcha" class="text-indigo-600 hover:text-indigo-800 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
                <input id="captcha" 
                       name="captcha" 
                       type="text" 
                       x-model="formData.captcha"
                       :class="errors.captcha ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                       class="mt-2 appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm"
                       placeholder="Ingresa el código mostrado arriba">
                <div x-show="errors.captcha" class="mt-1">
                    <p class="text-sm text-red-600" x-text="errors.captcha"></p>
                </div>
            </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember_me" 
                       name="remember" 
                       type="checkbox" 
                       x-model="formData.remember"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                    Recordarme
                </label>
            </div>

            @if(Route::has('password.request'))
                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            @endif
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

                <!-- Lock Icon -->
                <span x-show="!loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                    </svg>
                </span>

                <span x-text="loading ? 'Iniciando sesión...' : 'Iniciar Sesión'"></span>
            </button>
        </div>

        <!-- Social Login (Optional) -->
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">O continúa con</span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <!-- Google Login -->
                <button type="button" 
                        @click="socialLogin('google')"
                        class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                    <svg class="h-5 w-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="ml-2">Google</span>
                </button>

                <!-- Microsoft Login -->
                <button type="button" 
                        @click="socialLogin('microsoft')"
                        class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                    <svg class="h-5 w-5" viewBox="0 0 24 24">
                        <path fill="#F25022" d="M1 1h10v10H1z"/>
                        <path fill="#00A4EF" d="M13 1h10v10H13z"/>
                        <path fill="#7FBA00" d="M1 13h10v10H1z"/>
                        <path fill="#FFB900" d="M13 13h10v10H13z"/>
                    </svg>
                    <span class="ml-2">Microsoft</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('auth-links')
<div class="space-y-2">
    @if(Route::has('register'))
        <p class="text-sm text-gray-300">
            ¿No tienes cuenta? 
            <a href="{{ route('register') }}" class="font-medium text-white hover:text-gray-200 transition-colors">
                Contacta al administrador
            </a>
        </p>
    @endif
    
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
    Alpine.data('loginForm', () => ({
        formData: {
            email: '',
            password: '',
            remember: false,
            captcha: ''
        },
        errors: {},
        loading: false,
        showPassword: false,
        showCaptcha: false,
        showLoginAttempts: false,
        attemptsRemaining: 5,
        captchaText: '',
        
        get isFormValid() {
            return this.formData.email && 
                   this.formData.password && 
                   (!this.showCaptcha || this.formData.captcha);
        },
        
        init() {
            // Check login attempts from session
            const attempts = parseInt(sessionStorage.getItem('loginAttempts') || '0');
            if (attempts > 0) {
                this.attemptsRemaining = 5 - attempts;
                this.showLoginAttempts = attempts >= 2;
                this.showCaptcha = attempts >= 3;
                if (this.showCaptcha) {
                    this.generateCaptcha();
                }
            }
            
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
        
        validatePassword() {
            if (!this.formData.password) {
                this.errors.password = 'La contraseña es requerida';
            } else if (this.formData.password.length < 6) {
                this.errors.password = 'La contraseña debe tener al menos 6 caracteres';
            } else {
                delete this.errors.password;
            }
        },
        
        validateCaptcha() {
            if (this.showCaptcha && this.formData.captcha !== this.captchaText) {
                this.errors.captcha = 'El código de verificación es incorrecto';
                return false;
            }
            delete this.errors.captcha;
            return true;
        },
        
        generateCaptcha() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
            this.captchaText = Array.from({length: 6}, () => 
                chars.charAt(Math.floor(Math.random() * chars.length))
            ).join('');
            this.formData.captcha = '';
        },
        
        async submitLogin() {
            // Validate all fields
            this.validateEmail();
            this.validatePassword();
            
            if (this.showCaptcha && !this.validateCaptcha()) {
                this.generateCaptcha();
                return;
            }
            
            if (Object.keys(this.errors).length > 0) {
                return;
            }
            
            this.loading = true;
            
            try {
                const formData = new FormData();
                formData.append('email', this.formData.email);
                formData.append('password', this.formData.password);
                formData.append('remember', this.formData.remember ? '1' : '0');
                if (this.showCaptcha) {
                    formData.append('captcha', this.formData.captcha);
                }
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                const response = await fetch('{{ route("login") }}', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    // Clear login attempts on success
                    sessionStorage.removeItem('loginAttempts');
                    // Redirect will happen automatically
                    window.location.href = response.url || '{{ route("dashboard") }}';
                } else {
                    const data = await response.json();
                    
                    // Handle validation errors
                    if (data.errors) {
                        this.errors = data.errors;
                    }
                    
                    // Increment login attempts
                    const attempts = parseInt(sessionStorage.getItem('loginAttempts') || '0') + 1;
                    sessionStorage.setItem('loginAttempts', attempts);
                    
                    this.attemptsRemaining = 5 - attempts;
                    this.showLoginAttempts = attempts >= 2;
                    this.showCaptcha = attempts >= 3;
                    
                    if (this.showCaptcha) {
                        this.generateCaptcha();
                    }
                    
                    // Show generic error message
                    if (data.message) {
                        this.showToast(data.message, 'error');
                    }
                }
            } catch (error) {
                console.error('Login error:', error);
                this.showToast('Error de conexión. Por favor intenta de nuevo.', 'error');
            } finally {
                this.loading = false;
            }
        },
        
        async socialLogin(provider) {
            try {
                window.location.href = `/auth/${provider}/redirect`;
            } catch (error) {
                console.error(`${provider} login error:`, error);
                this.showToast(`Error al iniciar sesión con ${provider}`, 'error');
            }
        },
        
        showToast(message, type = 'info') {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    }));
});

// Handle browser back button and prevent form resubmission
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>
@endpush