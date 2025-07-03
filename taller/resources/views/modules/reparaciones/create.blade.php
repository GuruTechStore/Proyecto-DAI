{{-- resources/views/modules/reparaciones/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Nueva Reparación')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        </svg>
        <a href="{{ route('reparaciones.index') }}" class="ml-2 text-sm font-medium text-gray-700 hover:text-gray-900">Reparaciones</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="ml-2 text-sm font-medium text-gray-700">Nueva Reparación</span>
    </div>
</li>
@endsection

@push('styles')
<style>
    .fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .input-group { @apply relative; }
    .input-icon { @apply absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400; }
    .input-with-icon { @apply pl-10; }
    .priority-badge { @apply px-3 py-1 text-sm font-medium rounded-full cursor-pointer transition-all; }
    .priority-baja { @apply bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700; }
    .priority-media { @apply bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800; }
    .priority-alta { @apply bg-orange-100 text-orange-800 hover:bg-orange-200 dark:bg-orange-900 dark:text-orange-200 dark:hover:bg-orange-800; }
    .priority-urgente { @apply bg-red-100 text-red-800 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800; }
    .priority-selected { @apply ring-2 ring-gestion-500 ring-offset-2; }
</style>
@endpush

@section('content')
<div x-data="createReparacionManager()" x-init="init()" class="max-w-6xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Nueva Orden de Reparación
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Registra una nueva reparación en el sistema
                    </p>
                </div>
                
                <a href="{{ route('reparaciones.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <form action="{{ route('reparaciones.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <!-- Información del Cliente -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Información del Cliente
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Selecciona o registra un nuevo cliente
                </p>
            </div>
            
            <div class="p-6 space-y-6">
                
                <!-- Selector de cliente -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Cliente <span class="text-red-500">*</span>
                    </label>
                    
                    <div class="flex space-x-4">
                        <!-- Cliente existente -->
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" 
                                   name="cliente_tipo" 
                                   value="existente" 
                                   x-model="clienteTipo"
                                   class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Cliente existente</span>
                        </label>
                        
                        <!-- Nuevo cliente -->
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" 
                                   name="cliente_tipo" 
                                   value="nuevo" 
                                   x-model="clienteTipo"
                                   class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Nuevo cliente</span>
                        </label>
                    </div>
                </div>

                <!-- Cliente existente -->
                <div x-show="clienteTipo === 'existente'" x-transition class="space-y-4">
                    <div>
                        <label for="cliente_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Buscar Cliente
                        </label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" 
                                   x-model="clienteSearch"
                                   @input="searchClientes()"
                                   placeholder="Buscar por nombre, teléfono o documento..."
                                   class="pl-10 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                            
                            <!-- Resultados de búsqueda -->
                            <div x-show="clienteResults.length > 0" 
                                 x-transition
                                 class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto">
                                <template x-for="cliente in clienteResults" :key="cliente.id">
                                    <div @click="selectCliente(cliente)" 
                                         class="cursor-pointer select-none relative py-2 px-3 hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <div class="flex items-center">
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white" 
                                                     x-text="cliente.nombre + ' ' + cliente.apellido"></div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400" 
                                                     x-text="cliente.telefono"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Cliente seleccionado -->
                        <div x-show="selectedCliente" 
                             x-transition
                             class="mt-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white" 
                                         x-text="selectedCliente?.nombre + ' ' + selectedCliente?.apellido"></div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400" 
                                         x-text="selectedCliente?.telefono"></div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400" 
                                         x-text="selectedCliente?.email"></div>
                                </div>
                                <button type="button" 
                                        @click="clearSelectedCliente()"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <input type="hidden" name="cliente_id" :value="selectedCliente?.id">
                    </div>
                </div>

                <!-- Nuevo cliente -->
                <div x-show="clienteTipo === 'nuevo'" x-transition class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div>
                            <label for="cliente_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nombre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="cliente_nombre" 
                                   name="cliente_nombre" 
                                   value="{{ old('cliente_nombre') }}"
                                   class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('cliente_nombre') border-red-500 @enderror"
                                   placeholder="Nombre del cliente">
                            @error('cliente_nombre')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cliente_apellido" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Apellido <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="cliente_apellido" 
                                   name="cliente_apellido" 
                                   value="{{ old('cliente_apellido') }}"
                                   class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('cliente_apellido') border-red-500 @enderror"
                                   placeholder="Apellido del cliente">
                            @error('cliente_apellido')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cliente_telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Teléfono <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" 
                                   id="cliente_telefono" 
                                   name="cliente_telefono" 
                                   value="{{ old('cliente_telefono') }}"
                                   class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('cliente_telefono') border-red-500 @enderror"
                                   placeholder="Número de teléfono">
                            @error('cliente_telefono')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cliente_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email
                            </label>
                            <input type="email" 
                                   id="cliente_email" 
                                   name="cliente_email" 
                                   value="{{ old('cliente_email') }}"
                                   class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('cliente_email') border-red-500 @enderror"
                                   placeholder="correo@ejemplo.com">
                            @error('cliente_email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Información del Equipo -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Información del Equipo
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Detalles del dispositivo a reparar
                </p>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Tipo de equipo -->
                    <div>
                        <label for="tipo_equipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tipo de Equipo <span class="text-red-500">*</span>
                        </label>
                        <select id="tipo_equipo" 
                                name="tipo_equipo" 
                                required
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('tipo_equipo') border-red-500 @enderror">
                            <option value="">Seleccionar tipo</option>
                            <option value="celular" {{ old('tipo_equipo') == 'celular' ? 'selected' : '' }}>Celular</option>
                            <option value="tablet" {{ old('tipo_equipo') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                            <option value="laptop" {{ old('tipo_equipo') == 'laptop' ? 'selected' : '' }}>Laptop</option>
                            <option value="computadora" {{ old('tipo_equipo') == 'computadora' ? 'selected' : '' }}>Computadora</option>
                            <option value="consola" {{ old('tipo_equipo') == 'consola' ? 'selected' : '' }}>Consola de Videojuegos</option>
                            <option value="otro" {{ old('tipo_equipo') == 'otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('tipo_equipo')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Marca -->
                    <div>
                        <label for="marca" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Marca <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="marca" 
                               name="marca" 
                               value="{{ old('marca') }}"
                               required
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('marca') border-red-500 @enderror"
                               placeholder="Ej: Samsung, Apple, HP">
                        @error('marca')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Modelo -->
                    <div>
                        <label for="modelo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Modelo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="modelo" 
                               name="modelo" 
                               value="{{ old('modelo') }}"
                               required
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('modelo') border-red-500 @enderror"
                               placeholder="Ej: Galaxy S23, iPhone 14">
                        @error('modelo')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- IMEI/Serie -->
                    <div>
                        <label for="numero_serie" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            IMEI/Número de Serie
                        </label>
                        <input type="text" 
                               id="numero_serie" 
                               name="numero_serie" 
                               value="{{ old('numero_serie') }}"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('numero_serie') border-red-500 @enderror"
                               placeholder="IMEI o número de serie">
                        @error('numero_serie')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Color -->
                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Color
                        </label>
                        <input type="text" 
                               id="color" 
                               name="color" 
                               value="{{ old('color') }}"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('color') border-red-500 @enderror"
                               placeholder="Color del equipo">
                        @error('color')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contraseña/PIN -->
                    <div>
                        <label for="password_equipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Contraseña/PIN del Equipo
                        </label>
                        <input type="password" 
                               id="password_equipo" 
                               name="password_equipo" 
                               value="{{ old('password_equipo') }}"
                               class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('password_equipo') border-red-500 @enderror"
                               placeholder="Contraseña de desbloqueo">
                        @error('password_equipo')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Características/Accesorios -->
                <div>
                    <label for="caracteristicas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Características y Accesorios Incluidos
                    </label>
                    <textarea id="caracteristicas" 
                              name="caracteristicas" 
                              rows="3"
                              class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('caracteristicas') border-red-500 @enderror"
                              placeholder="Ej: Cargador, funda, protector de pantalla, capacidad de almacenamiento, etc.">{{ old('caracteristicas') }}</textarea>
                    @error('caracteristicas')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Problema y Diagnóstico -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    Problema Reportado
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Descripción del problema y configuración de la reparación
                </p>
            </div>
            
            <div class="p-6 space-y-6">
                
                <!-- Problema reportado -->
                <div>
                    <label for="problema_reportado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Problema Reportado por el Cliente <span class="text-red-500">*</span>
                    </label>
                    <textarea id="problema_reportado" 
                              name="problema_reportado" 
                              rows="4"
                              required
                              class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('problema_reportado') border-red-500 @enderror"
                              placeholder="Describe detalladamente el problema reportado por el cliente...">{{ old('problema_reportado') }}</textarea>
                    @error('problema_reportado')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Observaciones iniciales -->
                <div>
                    <label for="observaciones_iniciales" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Observaciones Iniciales del Técnico
                    </label>
                    <textarea id="observaciones_iniciales" 
                              name="observaciones_iniciales" 
                              rows="3"
                              class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('observaciones_iniciales') border-red-500 @enderror"
                              placeholder="Observaciones adicionales del técnico al recibir el equipo...">{{ old('observaciones_iniciales') }}</textarea>
                    @error('observaciones_iniciales')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prioridad -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Prioridad de la Reparación <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" 
                                   name="prioridad" 
                                   value="baja" 
                                   {{ old('prioridad') == 'baja' ? 'checked' : '' }}
                                   class="sr-only peer"
                                   required>
                            <div class="priority-badge priority-baja peer-checked:priority-selected w-full text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m0 0l4-4m3 4l4-4" />
                                    </svg>
                                    <span>Baja</span>
                                </div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" 
                                   name="prioridad" 
                                   value="media" 
                                   {{ old('prioridad', 'media') == 'media' ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="priority-badge priority-media peer-checked:priority-selected w-full text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Media</span>
                                </div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" 
                                   name="prioridad" 
                                   value="alta" 
                                   {{ old('prioridad') == 'alta' ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="priority-badge priority-alta peer-checked:priority-selected w-full text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m0 0v11a1 1 0 01-1 1H6a1 1 0 01-1-1V10z" />
                                    </svg>
                                    <span>Alta</span>
                                </div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" 
                                   name="prioridad" 
                                   value="urgente" 
                                   {{ old('prioridad') == 'urgente' ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="priority-badge priority-urgente peer-checked:priority-selected w-full text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Urgente</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('prioridad')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Técnico asignado -->
                <div>
                    <label for="tecnico_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Técnico Asignado
                    </label>
                    <select id="tecnico_id" 
                            name="tecnico_id" 
                            class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('tecnico_id') border-red-500 @enderror">
                        <option value="">Asignar más tarde</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}" {{ old('tecnico_id') == $tecnico->id ? 'selected' : '' }}>
                                {{ $tecnico->nombre }} {{ $tecnico->apellido }}
                            </option>
                        @endforeach
                    </select>
                    @error('tecnico_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Costo estimado y fecha -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <label for="costo_estimado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Costo Estimado
                        </label>
                        <div class="input-group">
                            <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                            <input type="number" 
                                   id="costo_estimado" 
                                   name="costo_estimado" 
                                   value="{{ old('costo_estimado') }}"
                                   step="0.01"
                                   min="0"
                                   class="input-with-icon block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('costo_estimado') border-red-500 @enderror"
                                   placeholder="0.00">
                        </div>
                        @error('costo_estimado')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fecha_estimada" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fecha Estimada de Entrega
                        </label>
                        <div class="input-group">
                            <svg class="input-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <input type="date" 
                                   id="fecha_estimada" 
                                   name="fecha_estimada" 
                                   value="{{ old('fecha_estimada') }}"
                                   min="{{ date('Y-m-d') }}"
                                   class="input-with-icon block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('fecha_estimada') border-red-500 @enderror">
                        </div>
                        @error('fecha_estimada')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <!-- Componentes Específicos (opcional) -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                    Estado de Componentes
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Especifica el estado de componentes específicos (opcional)
                </p>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <label for="pantalla" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Estado de la Pantalla
                        </label>
                        <select id="pantalla" 
                                name="pantalla" 
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="">No especificado</option>
                            <option value="funcionando" {{ old('pantalla') == 'funcionando' ? 'selected' : '' }}>Funcionando correctamente</option>
                            <option value="dañada" {{ old('pantalla') == 'dañada' ? 'selected' : '' }}>Dañada</option>
                            <option value="quebrada" {{ old('pantalla') == 'quebrada' ? 'selected' : '' }}>Quebrada</option>
                            <option value="no_funciona" {{ old('pantalla') == 'no_funciona' ? 'selected' : '' }}>No funciona</option>
                        </select>
                    </div>

                    <div>
                        <label for="altavoz" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Estado del Altavoz
                        </label>
                        <select id="altavoz" 
                                name="altavoz" 
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="">No especificado</option>
                            <option value="funcionando" {{ old('altavoz') == 'funcionando' ? 'selected' : '' }}>Funcionando correctamente</option>
                            <option value="dañado" {{ old('altavoz') == 'dañado' ? 'selected' : '' }}>Dañado</option>
                            <option value="no_funciona" {{ old('altavoz') == 'no_funciona' ? 'selected' : '' }}>No funciona</option>
                        </select>
                    </div>

                    <div>
                        <label for="microfono" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Estado del Micrófono
                        </label>
                        <select id="microfono" 
                                name="microfono" 
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="">No especificado</option>
                            <option value="funcionando" {{ old('microfono') == 'funcionando' ? 'selected' : '' }}>Funcionando correctamente</option>
                            <option value="dañado" {{ old('microfono') == 'dañado' ? 'selected' : '' }}>Dañado</option>
                            <option value="no_funciona" {{ old('microfono') == 'no_funciona' ? 'selected' : '' }}>No funciona</option>
                        </select>
                    </div>

                    <div>
                        <label for="zocalo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Estado del Zócalo de Carga
                        </label>
                        <select id="zocalo" 
                                name="zocalo" 
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="">No especificado</option>
                            <option value="funcionando" {{ old('zocalo') == 'funcionando' ? 'selected' : '' }}>Funcionando correctamente</option>
                            <option value="dañado" {{ old('zocalo') == 'dañado' ? 'selected' : '' }}>Dañado</option>
                            <option value="no_funciona" {{ old('zocalo') == 'no_funciona' ? 'selected' : '' }}>No funciona</option>
                        </select>
                    </div>

                    <div>
                        <label for="camara" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Estado de la Cámara
                        </label>
                        <select id="camara" 
                                name="camara" 
                                class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500">
                            <option value="">No especificado</option>
                            <option value="funcionando" {{ old('camara') == 'funcionando' ? 'selected' : '' }}>Funcionando correctamente</option>
                            <option value="dañada" {{ old('camara') == 'dañada' ? 'selected' : '' }}>Dañada</option>
                            <option value="no_funciona" {{ old('camara') == 'no_funciona' ? 'selected' : '' }}>No funciona</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        <!-- Configuraciones Adicionales -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    </svg>
                    Configuraciones Adicionales
                </h3>
            </div>
            
            <div class="p-6 space-y-4">
                
                <!-- Opciones de notificación -->
                <div class="space-y-3">
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" 
                               id="notificar_cliente" 
                               name="notificar_cliente" 
                               value="1"
                               {{ old('notificar_cliente', '1') ? 'checked' : '' }}
                               class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
                        <label for="notificar_cliente" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Notificar al cliente sobre el progreso de la reparación
                        </label>
                    </div>

                    <div class="flex items-center space-x-3">
                        <input type="checkbox" 
                               id="requiere_diagnostico" 
                               name="requiere_diagnostico" 
                               value="1"
                               {{ old('requiere_diagnostico', '1') ? 'checked' : '' }}
                               class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
                        <label for="requiere_diagnostico" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Requiere diagnóstico previo antes de la reparación
                        </label>
                    </div>

                    <div class="flex items-center space-x-3">
                        <input type="checkbox" 
                               id="backup_datos" 
                               name="backup_datos" 
                               value="1"
                               {{ old('backup_datos') ? 'checked' : '' }}
                               class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
                        <label for="backup_datos" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            El cliente solicita respaldo de datos
                        </label>
                    </div>
                </div>

            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('reparaciones.index') }}" 
               class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                Cancelar
            </a>
            
            <button type="submit" 
                    class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Crear Reparación
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
function createReparacionManager() {
    return {
        // Estados
        clienteTipo: 'existente',
        clienteSearch: '',
        clienteResults: [],
        selectedCliente: null,
        searchTimeout: null,
        
        async init() {
            console.log('Create Reparacion Manager initialized');
        },
        
        async searchClientes() {
            if (this.clienteSearch.length < 2) {
                this.clienteResults = [];
                return;
            }
            
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/clientes/api/search?q=${encodeURIComponent(this.clienteSearch)}`);
                    const data = await response.json();
                    this.clienteResults = data.slice(0, 10); // Máximo 10 resultados
                } catch (error) {
                    console.error('Error searching clientes:', error);
                    this.clienteResults = [];
                }
            }, 300);
        },
        
        selectCliente(cliente) {
            this.selectedCliente = cliente;
            this.clienteSearch = cliente.nombre + ' ' + cliente.apellido;
            this.clienteResults = [];
        },
        
        clearSelectedCliente() {
            this.selectedCliente = null;
            this.clienteSearch = '';
            this.clienteResults = [];
        }
    }
}
</script>
@endpush
@endsection