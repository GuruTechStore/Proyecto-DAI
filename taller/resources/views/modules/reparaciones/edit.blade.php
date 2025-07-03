{{-- resources/views/modules/reparaciones/edit.blade.php - PARTE 1/3 --}}
@extends('layouts.app')

@section('title', 'Editar Reparación #' . $reparacion->codigo_ticket)

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
        <a href="{{ route('reparaciones.show', $reparacion) }}" class="ml-2 text-sm font-medium text-gray-700 hover:text-gray-900">#{{ $reparacion->codigo_ticket }}</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="ml-2 text-sm font-medium text-gray-700">Editar</span>
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
    .status-badge { @apply px-3 py-1 text-sm font-semibold rounded-full; }
    .status-recibido { @apply bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200; }
    .status-diagnosticando { @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200; }
    .status-diagnosticado { @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200; }
    .status-reparando { @apply bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200; }
    .status-completado { @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200; }
    .status-entregado { @apply bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200; }
    .status-cancelado { @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200; }
</style>
@endpush

@section('content')
<div x-data="editReparacionManager()" x-init="init()" class="max-w-6xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Estado actual -->
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-gestion-100 dark:bg-gestion-800 rounded-full flex items-center justify-center">
                            <svg class="h-6 w-6 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            </svg>
                        </div>
                    </div>
                    
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Editar Reparación #{{ $reparacion->codigo_ticket }}
                        </h1>
                        <div class="flex items-center space-x-4 mt-1">
                            <span class="status-badge status-{{ $reparacion->estado }}">
                                {{ ucfirst($reparacion->estado) }}
                            </span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $reparacion->cliente->nombre }} {{ $reparacion->cliente->apellido }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <a href="{{ route('reparaciones.show', $reparacion) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Ver Detalles
                    </a>
                    
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
    </div>

    <!-- Formulario -->
    <form action="{{ route('reparaciones.update', $reparacion) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        
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
                    Actualiza los detalles del dispositivo
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
                            <option value="celular" {{ old('tipo_equipo', $reparacion->tipo_equipo) == 'celular' ? 'selected' : '' }}>Celular</option>
                            <option value="tablet" {{ old('tipo_equipo', $reparacion->tipo_equipo) == 'tablet' ? 'selected' : '' }}>Tablet</option>
                            <option value="laptop" {{ old('tipo_equipo', $reparacion->tipo_equipo) == 'laptop' ? 'selected' : '' }}>Laptop</option>
                            <option value="computadora" {{ old('tipo_equipo', $reparacion->tipo_equipo) == 'computadora' ? 'selected' : '' }}>Computadora</option>
                            <option value="consola" {{ old('tipo_equipo', $reparacion->tipo_equipo) == 'consola' ? 'selected' : '' }}>Consola de Videojuegos</option>
                            <option value="otro" {{ old('tipo_equipo', $reparacion->tipo_equipo) == 'otro' ? 'selected' : '' }}>Otro</option>
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
                               value="{{ old('marca', $reparacion->marca) }}"
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
                               value="{{ old('modelo', $reparacion->modelo) }}"
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
                               value="{{ old('numero_serie', $reparacion->numero_serie) }}"
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
                               value="{{ old('color', $reparacion->color) }}"
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
                               value="{{ old('password_equipo', $reparacion->password_equipo) }}"
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
                              placeholder="Ej: Cargador, funda, protector de pantalla, capacidad de almacenamiento, etc.">{{ old('caracteristicas', $reparacion->caracteristicas) }}</textarea>
                    @error('caracteristicas')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
{{-- resources/views/modules/reparaciones/edit.blade.php - PARTE 2/3 --}}

        <!-- Problema y Configuración -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    Detalles de la Reparación
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Actualiza el problema y configuración de la reparación
                </p>
            </div>
            
            <div class="p-6 space-y-6">
                
                <!-- Problema reportado -->
                <div>
                    <label for="problema_reportado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Problema Reportado <span class="text-red-500">*</span>
                    </label>
                    <textarea id="problema_reportado" 
                              name="problema_reportado" 
                              rows="4"
                              required
                              class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('problema_reportado') border-red-500 @enderror"
                              placeholder="Describe detalladamente el problema reportado por el cliente...">{{ old('problema_reportado', $reparacion->problema_reportado) }}</textarea>
                    @error('problema_reportado')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Observaciones iniciales -->
                <div>
                    <label for="observaciones_iniciales" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Observaciones del Técnico
                    </label>
                    <textarea id="observaciones_iniciales" 
                              name="observaciones_iniciales" 
                              rows="3"
                              class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('observaciones_iniciales') border-red-500 @enderror"
                              placeholder="Observaciones adicionales del técnico...">{{ old('observaciones_iniciales', $reparacion->observaciones_iniciales) }}</textarea>
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
                                   {{ old('prioridad', $reparacion->prioridad) == 'baja' ? 'checked' : '' }}
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
                                   {{ old('prioridad', $reparacion->prioridad) == 'media' ? 'checked' : '' }}
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
                                   {{ old('prioridad', $reparacion->prioridad) == 'alta' ? 'checked' : '' }}
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
                                   {{ old('prioridad', $reparacion->prioridad) == 'urgente' ? 'checked' : '' }}
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
                        <option value="">Sin asignar</option>
                        @foreach($tecnicos as $tecnico)
                            <option value="{{ $tecnico->id }}" {{ old('tecnico_id', $reparacion->tecnico_id) == $tecnico->id ? 'selected' : '' }}>
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
                                   value="{{ old('costo_estimado', $reparacion->costo_estimado) }}"
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
                                   value="{{ old('fecha_estimada', $reparacion->fecha_estimada) }}"
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

        <!-- Estado de Componentes -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                    Estado de Componentes
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Actualiza el estado de componentes específicos
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
                            <option value="funcionando" {{ old('pantalla', $reparacion->pantalla) == 'funcionando' ? 'selected' : '' }}>Funcionando correctamente</option>
                            <option value="dañada" {{ old('pantalla', $reparacion->pantalla) == 'dañada' ? 'selected' : '' }}>Dañada</option>
                            <option value="quebrada" {{ old('pantalla', $reparacion->pantalla) == 'quebrada' ? 'selected' : '' }}>Quebrada</option>
                            <option value="no_funciona" {{ old('pantalla', $reparacion->pantalla) == 'no_funciona' ? 'selected' : '' }}>No funciona</option>
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
                            <option value="funcionando" {{ old('altavoz', $reparacion->altavoz) == 'funcionando' ? 'selected' : '' }}>Funcionando correctamente</option>
                            <option value="dañado" {{ old('altavoz', $reparacion->altavoz) == 'dañado' ? 'selected' : '' }}>Dañado</option>
                            <option value="no_funciona" {{ old('altavoz', $reparacion->altavoz) == 'no_funciona' ? 'selected' : '' }}>No funciona</option>
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
                            <option value="funcionando" {{ old('microfono', $reparacion->microfono) == 'funcionando' ? 'selected' : '' }}>Funcionando correctamente</option>
                            <option value="dañado" {{ old('microfono', $reparacion->microfono) == 'dañado' ? 'selected' : '' }}>Dañado</option>
                            <option value="no_funciona" {{ old('microfono', $reparacion->microfono) == 'no_funciona' ? 'selected' : '' }}>No funciona</option>
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
                            <option value="funcionando" {{ old('zocalo', $reparacion->zocalo) == 'funcionando' ? 'selected' : '' }}>Funcionando correctamente</option>
                            <option value="dañado" {{ old('zocalo', $reparacion->zocalo) == 'dañado' ? 'selected' : '' }}>Dañado</option>
                            <option value="no_funciona" {{ old('zocalo', $reparacion->zocalo) == 'no_funciona' ? 'selected' : '' }}>No funciona</option>
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
                            <option value="funcionando" {{ old('camara', $reparacion->camara) == 'funcionando' ? 'selected' : '' }}>Funcionando correctamente</option>
                            <option value="dañada" {{ old('camara', $reparacion->camara) == 'dañada' ? 'selected' : '' }}>Dañada</option>
                            <option value="no_funciona" {{ old('camara', $reparacion->camara) == 'no_funciona' ? 'selected' : '' }}>No funciona</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>
{{-- resources/views/modules/reparaciones/edit.blade.php - PARTE 3/3 (FINAL) --}}

        <!-- Notas Adicionales y Configuración -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Notas y Configuración Adicional
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Información adicional y configuraciones especiales
                </p>
            </div>
            
            <div class="p-6 space-y-6">
                
                <!-- Notas internas -->
                <div>
                    <label for="notas_internas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Notas Internas del Técnico
                    </label>
                    <textarea id="notas_internas" 
                              name="notas_internas" 
                              rows="4"
                              class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500 @error('notas_internas') border-red-500 @enderror"
                              placeholder="Notas privadas para el equipo técnico...">{{ old('notas_internas', $reparacion->notas_internas) }}</textarea>
                    @error('notas_internas')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Estas notas son privadas y no se mostrarán al cliente
                    </p>
                </div>

                <!-- Configuraciones especiales -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Configuraciones Especiales
                    </label>
                    <div class="space-y-3">
                        
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   id="requiere_diagnostico" 
                                   name="requiere_diagnostico" 
                                   value="1"
                                   {{ old('requiere_diagnostico', $reparacion->requiere_diagnostico) ? 'checked' : '' }}
                                   class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
                            <label for="requiere_diagnostico" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Requiere diagnóstico detallado antes de proceder
                            </label>
                        </div>

                        <div class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   id="backup_datos" 
                                   name="backup_datos" 
                                   value="1"
                                   {{ old('backup_datos', $reparacion->backup_datos) ? 'checked' : '' }}
                                   class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
                            <label for="backup_datos" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Cliente solicita respaldo de datos
                            </label>
                        </div>

                        <div class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   id="notificar_cliente" 
                                   name="notificar_cliente" 
                                   value="1"
                                   {{ old('notificar_cliente', $reparacion->notificar_cliente) ? 'checked' : '' }}
                                   class="h-4 w-4 text-gestion-600 focus:ring-gestion-500 border-gray-300 dark:border-gray-600 rounded">
                            <label for="notificar_cliente" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notificar automáticamente al cliente sobre cambios de estado
                            </label>
                        </div>

                        <div class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   id="urgente" 
                                   name="urgente" 
                                   value="1"
                                   {{ old('urgente', $reparacion->urgente) ? 'checked' : '' }}
                                   class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600 rounded">
                            <label for="urgente" class="text-sm font-medium text-red-700 dark:text-red-300">
                                Marcar como reparación urgente (prioridad máxima)
                            </label>
                        </div>

                        <div class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   id="garantia" 
                                   name="garantia" 
                                   value="1"
                                   {{ old('garantia', $reparacion->garantia) ? 'checked' : '' }}
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 dark:border-gray-600 rounded">
                            <label for="garantia" class="text-sm font-medium text-green-700 dark:text-green-300">
                                Reparación con garantía extendida
                            </label>
                        </div>

                    </div>
                </div>

                <!-- Información de contacto alternativo -->
                <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Contacto Alternativo</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div>
                            <label for="contacto_alternativo_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nombre de Contacto Alternativo
                            </label>
                            <input type="text" 
                                   id="contacto_alternativo_nombre" 
                                   name="contacto_alternativo_nombre" 
                                   value="{{ old('contacto_alternativo_nombre', $reparacion->contacto_alternativo_nombre) }}"
                                   class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500"
                                   placeholder="Ej: Familiar, asistente, etc.">
                        </div>

                        <div>
                            <label for="contacto_alternativo_telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Teléfono Alternativo
                            </label>
                            <input type="tel" 
                                   id="contacto_alternativo_telefono" 
                                   name="contacto_alternativo_telefono" 
                                   value="{{ old('contacto_alternativo_telefono', $reparacion->contacto_alternativo_telefono) }}"
                                   class="block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gestion-500 focus:border-gestion-500"
                                   placeholder="Número de contacto adicional">
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <!-- Resumen de Cambios -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <h4 class="text-md font-medium text-amber-800 dark:text-amber-200 mb-2">
                        Información Importante
                    </h4>
                    <ul class="text-sm text-amber-700 dark:text-amber-300 space-y-1">
                        <li>• Los cambios se guardarán en el historial de la reparación</li>
                        <li>• El cliente será notificado automáticamente si está habilitado</li>
                        <li>• Algunos cambios pueden requerir aprobación adicional</li>
                        <li>• Los costos actualizados afectarán el total de la reparación</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex justify-between items-center">
            <div class="flex space-x-4">
                <a href="{{ route('reparaciones.show', $reparacion) }}" 
                   class="inline-flex items-center px-6 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancelar
                </a>
                
                @if($reparacion->estado === 'recibido' || $reparacion->estado === 'diagnosticando')
                <button type="button" 
                        @click="confirmReset()"
                        class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-600 text-sm font-medium rounded-lg text-red-700 dark:text-red-300 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Restaurar Original
                </button>
                @endif
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" 
                        name="action" 
                        value="save"
                        class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Guardar Cambios
                </button>
                
                @can('reparaciones.editar')
                <div class="relative" x-data="{ showDropdown: false }">
                    <button type="button" 
                            @click="showDropdown = !showDropdown"
                            @click.away="showDropdown = false"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Guardar y...
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <div x-show="showDropdown" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-700 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            <button type="submit" 
                                    name="action" 
                                    value="save_and_continue"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Ver Detalles
                            </button>
                            
                            <button type="submit" 
                                    name="action" 
                                    value="save_and_new"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Nueva Reparación
                            </button>
                            
                            <button type="submit" 
                                    name="action" 
                                    value="save_and_list"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                Lista de Reparaciones
                            </button>
                        </div>
                    </div>
                </div>
                @endcan
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
function editReparacionManager() {
    return {
        init() {
            console.log('Edit Reparacion Manager initialized');
        },
        
        confirmReset() {
            if (confirm('¿Estás seguro de que deseas restaurar los valores originales? Se perderán todos los cambios realizados.')) {
                // Recargar la página para restaurar valores originales
                window.location.reload();
            }
        }
    }
}
</script>
@endpush
@endsection