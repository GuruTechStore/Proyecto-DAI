@extends('layouts.auth')

@section('title', 'Autenticación de Dos Factores')

@section('auth-subtitle', 'Ingresa el código de verificación para completar el acceso')

@section('content')
<div x-data="twoFactorForm()" x-init="startCodeTimer()">
    <!-- Security Notice -->
    <div class="mb-6 text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100">
            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
        </div>
        <h3 class="mt-4 text-xl font-medium text-gray-900">Verificación de Seguridad</h3>
        <p class="mt-2 text-sm text-gray-600 max-w-md mx-auto">
            Para proteger tu cuenta, necesitamos verificar tu identidad con un código de seguridad adicional.
        </p>
    </div>

    <!-- Method Selection Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeMethod = 'app'" 
                        :class="activeMethod === 'app' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        App Autenticación
                    </div>
                </button>
                <button @click="activeMethod = 'sms'" 
                        :class="activeMethod === 'sms' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        SMS
                    </div>
                </button>
                <button @click="activeMethod = 'recovery'" 
                        :class="activeMethod === 'recovery' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Código de Recuperación
                    </div>
                </button>
            </nav>
        </div>
    </div>

    <!-- Authenticator App Method -->
    <div x-show="activeMethod === 'app'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <form method="POST" action="{{ route('two-factor.login') }}" @submit.prevent="submitTwoFactor('app')" class="space-y-6">
            @csrf
            
            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Abre tu aplicación de autenticación (Google Authenticator, Authy, etc.) y ingresa el código de 6 dígitos.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Code Input -->
            <div>
                <label for="app_code" class="block text-sm font-medium text-gray-700 mb-2">
                    Código de Autenticación
                </label>
                <div class="flex justify-center">
                    <div class="flex space-x-2">
                        <template x-for="i in 6" :key="i">
                            <input :id="'app_digit_' + i"
                                   type="text" 
                                   maxlength="1"
                                   x-model="codes.app[i-1]"
                                   @input="handleDigitInput($event, i-1, 'app')"
                                   @keydown="handleKeyDown($event, i-1, 'app')"
                                   @paste="handlePaste($event, 'app')"
                                   :class="errors.app_code ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                                   class="w-12 h-12 text-center text-lg font-mono border rounded-md shadow-sm focus:outline-none focus:ring-1 transition-colors">
                        </template>
                    </div>
                </div>
                <input type="hidden" name="code" :value="codes.app.join('')">
                
                <!-- Error Message -->
                <div x-show="errors.app_code" class="mt-2 text-center">
                    <p class="text-sm text-red-600" x-text="errors.app_code"></p>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        :disabled="loadingStates.app || !isCodeComplete('app')"
                        :class="loadingStates.app || !isCodeComplete('app') ? 'bg-gray-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700'"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    
                    <span x-show="loadingStates.app" class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>

                    <span x-show="!loadingStates.app" class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>

                    <span x-text="loadingStates.app ? 'Verificando...' : 'Verificar Código'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- SMS Method -->
    <div x-show="activeMethod === 'sms'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <form method="POST" action="{{ route('two-factor.login') }}" @submit.prevent="submitTwoFactor('sms')" class="space-y-6">
            @csrf
            
            <!-- Phone Display & Send Button -->
            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex items-center justify-between">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                Enviaremos un código SMS a: <strong>***-***-{{ substr(auth()->user()->telefono ?? '1234', -4) }}</strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="sendSMSCode"
                            :disabled="loadingStates.sms_send || smsTimer > 0"
                            class="ml-4 inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loadingStates.sms_send && smsTimer === 0">Enviar SMS</span>
                        <span x-show="loadingStates.sms_send">Enviando...</span>
                        <span x-show="smsTimer > 0" x-text="'Reenviar en ' + smsTimer + 's'"></span>
                    </button>
                </div>
            </div>

            <!-- SMS sent confirmation -->
            <div x-show="smsSent" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Código SMS enviado. Revisa tu teléfono e ingresa el código de 6 dígitos.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Code Input -->
            <div>
                <label for="sms_code" class="block text-sm font-medium text-gray-700 mb-2">
                    Código SMS
                </label>
                <div class="flex justify-center">
                    <div class="flex space-x-2">
                        <template x-for="i in 6" :key="i">
                            <input :id="'sms_digit_' + i"
                                   type="text" 
                                   maxlength="1"
                                   x-model="codes.sms[i-1]"
                                   @input="handleDigitInput($event, i-1, 'sms')"
                                   @keydown="handleKeyDown($event, i-1, 'sms')"
                                   @paste="handlePaste($event, 'sms')"
                                   :class="errors.sms_code ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                                   class="w-12 h-12 text-center text-lg font-mono border rounded-md shadow-sm focus:outline-none focus:ring-1 transition-colors">
                        </template>
                    </div>
                </div>
                <input type="hidden" name="code" :value="codes.sms.join('')">
                <input type="hidden" name="method" value="sms">
                
                <!-- Error Message -->
                <div x-show="errors.sms_code" class="mt-2 text-center">
                    <p class="text-sm text-red-600" x-text="errors.sms_code"></p>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        :disabled="loadingStates.sms || !isCodeComplete('sms')"
                        :class="loadingStates.sms || !isCodeComplete('sms') ? 'bg-gray-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700'"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    
                    <span x-show="loadingStates.sms" class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>

                    <span x-show="!loadingStates.sms" class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>

                    <span x-text="loadingStates.sms ? 'Verificando...' : 'Verificar Código SMS'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Recovery Code Method -->
    <div x-show="activeMethod === 'recovery'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <form method="POST" action="{{ route('two-factor.login') }}" @submit.prevent="submitTwoFactor('recovery')" class="space-y-6">
            @csrf
            
            <!-- Warning -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Código de Recuperación
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>
                                Usa este método solo si no puedes acceder a tu aplicación de autenticación o recibir SMS. 
                                Cada código de recuperación solo se puede usar una vez.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recovery Code Input -->
            <div>
                <label for="recovery_code" class="block text-sm font-medium text-gray-700">
                    Código de Recuperación
                </label>
                <div class="mt-1">
                    <input id="recovery_code" 
                           name="recovery_code" 
                           type="text" 
                           required 
                           x-model="codes.recovery"
                           @input="formatRecoveryCode"
                           :class="errors.recovery_code ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'"
                           class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 sm:text-sm font-mono text-center tracking-wider"
                           placeholder="xxxx-xxxx-xxxx">
                </div>
                
                <!-- Error Message -->
                <div x-show="errors.recovery_code" class="mt-1">
                    <p class="text-sm text-red-600" x-text="errors.recovery_code"></p>
                </div>
                
                <!-- Help Text -->
                <p class="mt-2 text-sm text-gray-500">
                    Ingresa uno de los códigos de recuperación de 8 caracteres que guardaste cuando configuraste la autenticación de dos factores.
                </p>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        :disabled="loadingStates.recovery || !codes.recovery"
                        :class="loadingStates.recovery || !codes.recovery ? 'bg-gray-400 cursor-not-allowed' : 'bg-yellow-600 hover:bg-yellow-700'"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200">
                    
                    <span x-show="loadingStates.recovery" class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>

                    <span x-show="!loadingStates.recovery" class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-yellow-500 group-hover:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </span>

                    <span x-text="loadingStates.recovery ? 'Verificando...' : 'Usar Código de Recuperación'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Session Info -->
    <div class="mt-8 p-4 bg-gray-50 rounded-md">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-gray-600">
                <p><strong>Usuario:</strong> {{ auth()->user()->email ?? 'usuario@example.com' }}</p>
                <p><strong>IP:</strong> {{ request()->ip() }}</p>
                <p><strong>Navegador:</strong> <span x-text="navigator.userAgent.split(' ')[0]"></span></p>
            </div>
        </div>
    </div>

    <!-- Help Section -->
    <div class="mt-6 text-center">
        <div class="space-y-2">
            <p class="text-sm text-gray-600">¿Problemas para acceder?</p>
            <div class="space-x-4">
                <a href="{{ route('help.two-factor') }}" 
                   class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Centro de Ayuda
                </a>
                <span class="text-gray-400">•</span>
                <a href="mailto:soporte@empresa.com" 
                   class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Contactar Soporte
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('auth-links')
<div class="space-y-2">
    <form method="POST" action="{{ route('logout') }}" class="inline">
        @csrf
        <button type="submit" class="text-sm font-medium text-white hover:text-gray-200 transition-colors">
            Cancelar y cerrar sesión
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('twoFactorForm', () => ({
        activeMethod: 'app',
        codes: {
            app: ['', '', '', '', '', ''],
            sms: ['', '', '', '', '', ''],
            recovery: ''
        },
        errors: {},
        loadingStates: {
            app: false,
            sms: false,
            sms_send: false,
            recovery: false
        },
        smsSent: false,
        smsTimer: 0,
        smsInterval: null,
        
        init() {
            // Auto-focus first input of default method
            this.$nextTick(() => {
                const firstInput = document.getElementById('app_digit_1');
                if (firstInput) firstInput.focus();
            });
        },
        
        isCodeComplete(method) {
            if (method === 'recovery') {
                return this.codes.recovery.length >= 8;
            }
            return this.codes[method].every(digit => digit !== '');
        },
        
        handleDigitInput(event, index, method) {
            const value = event.target.value.replace(/[^0-9]/g, '');
            
            if (value) {
                this.codes[method][index] = value;
                
                // Auto-focus next input
                if (index < 5) {
                    const nextInput = document.getElementById(`${method}_digit_${index + 2}`);
                    if (nextInput) nextInput.focus();
                }
                
                // Auto-submit if complete
                if (this.isCodeComplete(method)) {
                    setTimeout(() => this.submitTwoFactor(method), 100);
                }
            }
        },
        
        handleKeyDown(event, index, method) {
            // Handle backspace
            if (event.key === 'Backspace' && !this.codes[method][index] && index > 0) {
                const prevInput = document.getElementById(`${method}_digit_${index}`);
                if (prevInput) {
                    prevInput.focus();
                    this.codes[method][index - 1] = '';
                }
            }
            
            // Handle arrow keys
            if (event.key === 'ArrowLeft' && index > 0) {
                const prevInput = document.getElementById(`${method}_digit_${index}`);
                if (prevInput) prevInput.focus();
            } else if (event.key === 'ArrowRight' && index < 5) {
                const nextInput = document.getElementById(`${method}_digit_${index + 2}`);
                if (nextInput) nextInput.focus();
            }
        },
        
        handlePaste(event, method) {
            event.preventDefault();
            const pastedData = event.clipboardData.getData('text').replace(/[^0-9]/g, '');
            
            if (pastedData.length === 6) {
                for (let i = 0; i < 6; i++) {
                    this.codes[method][i] = pastedData[i] || '';
                }
                
                // Focus last input
                const lastInput = document.getElementById(`${method}_digit_6`);
                if (lastInput) lastInput.focus();
                
                // Auto-submit
                setTimeout(() => this.submitTwoFactor(method), 100);
            }
        },
        
        formatRecoveryCode() {
            // Remove non-alphanumeric characters and convert to uppercase
            let code = this.codes.recovery.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            
            // Add dashes every 4 characters
            if (code.length > 4) {
                code = code.substring(0, 4) + '-' + code.substring(4);
            }
            if (code.length > 9) {
                code = code.substring(0, 9) + '-' + code.substring(9);
            }
            
            // Limit to 12 characters (including dashes)
            this.codes.recovery = code.substring(0, 12);
        },
        
        async sendSMSCode() {
            this.loadingStates.sms_send = true;
            
            try {
                const response = await fetch('{{ route("two-factor.send-sms") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    this.smsSent = true;
                    this.startSMSTimer();
                    this.showToast('Código SMS enviado exitosamente', 'success');
                    
                    // Focus first SMS input
                    this.$nextTick(() => {
                        const firstInput = document.getElementById('sms_digit_1');
                        if (firstInput) firstInput.focus();
                    });
                } else {
                    const data = await response.json();
                    this.showToast(data.message || 'Error al enviar SMS', 'error');
                }
            } catch (error) {
                console.error('SMS send error:', error);
                this.showToast('Error de conexión', 'error');
            } finally {
                this.loadingStates.sms_send = false;
            }
        },
        
        startSMSTimer() {
            this.smsTimer = 60; // 60 seconds
            
            if (this.smsInterval) {
                clearInterval(this.smsInterval);
            }
            
            this.smsInterval = setInterval(() => {
                this.smsTimer--;
                
                if (this.smsTimer <= 0) {
                    clearInterval(this.smsInterval);
                    this.smsInterval = null;
                }
            }, 1000);
        },
        
        startCodeTimer() {
            // Refresh codes every 30 seconds for security
            setInterval(() => {
                // Clear any partial codes for security
                if (this.activeMethod === 'app' && !this.isCodeComplete('app')) {
                    this.codes.app = ['', '', '', '', '', ''];
                }
                if (this.activeMethod === 'sms' && !this.isCodeComplete('sms')) {
                    this.codes.sms = ['', '', '', '', '', ''];
                }
            }, 30000);
        },
        
        async submitTwoFactor(method) {
            this.loadingStates[method] = true;
            this.errors = {};
            
            try {
                const formData = new FormData();
                
                if (method === 'recovery') {
                    formData.append('recovery_code', this.codes.recovery.replace(/-/g, ''));
                } else {
                    formData.append('code', this.codes[method].join(''));
                    if (method === 'sms') {
                        formData.append('method', 'sms');
                    }
                }
                
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                const response = await fetch('{{ route("two-factor.login") }}', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.showToast('Verificación exitosa', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route("dashboard") }}';
                    }, 1000);
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        this.errors = data.errors;
                    }
                    
                    if (data.message) {
                        this.showToast(data.message, 'error');
                    }
                    
                    // Clear codes on error
                    if (method !== 'recovery') {
                        this.codes[method] = ['', '', '', '', '', ''];
                        // Focus first input
                        this.$nextTick(() => {
                            const firstInput = document.getElementById(`${method}_digit_1`);
                            if (firstInput) firstInput.focus();
                        });
                    } else {
                        this.codes.recovery = '';
                    }
                }
            } catch (error) {
                console.error('Two-factor verification error:', error);
                this.showToast('Error de conexión. Por favor intenta de nuevo.', 'error');
            } finally {
                this.loadingStates[method] = false;
            }
        },
        
        showToast(message, type = 'info') {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: { message, type }
            }));
        }
    }));
});

// Cleanup timers on page unload
window.addEventListener('beforeunload', function() {
    const component = Alpine.$data(document.querySelector('[x-data="twoFactorForm()"]'));
    if (component && component.smsInterval) {
        clearInterval(component.smsInterval);
    }
});

// Handle browser back button
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>
@endpush