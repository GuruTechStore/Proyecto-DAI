{{-- resources/views/modules/reparaciones/show.blade.php - PARTE 1/3 --}}
@extends('layouts.app')

@section('title', 'Reparación #' . $reparacion->codigo_ticket)

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
        <span class="ml-2 text-sm font-medium text-gray-700">#{{ $reparacion->codigo_ticket }}</span>
    </div>
</li>
@endsection

@push('styles')
<style>
    .fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .status-badge { @apply px-3 py-1 text-sm font-semibold rounded-full; }
    .status-recibido { @apply bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200; }
    .status-diagnosticando { @apply bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200; }
    .status-diagnosticado { @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200; }
    .status-reparando { @apply bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200; }
    .status-completado { @apply bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200; }
    .status-entregado { @apply bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200; }
    .status-cancelado { @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200; }
    .priority-badge { @apply px-2 py-1 text-xs font-medium rounded; }
    .priority-baja { @apply bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200; }
    .priority-media { @apply bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200; }
    .priority-alta { @apply bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200; }
    .priority-urgente { @apply bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200; }
    .tab-content { @apply hidden; }
    .tab-content.active { @apply block; }
    .timeline-item { @apply relative pb-8; }
    .timeline-item:last-child { @apply pb-0; }
    .timeline-line { @apply absolute left-5 top-8 -bottom-6 w-0.5 bg-gray-300 dark:bg-gray-600; }
    .timeline-item:last-child .timeline-line { @apply hidden; }
</style>
@endpush

@section('content')
<div x-data="showReparacionManager()" x-init="init()" class="max-w-7xl mx-auto space-y-6">
    
    <!-- Header principal -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Icono del estado -->
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-gestion-100 dark:bg-gestion-800 rounded-full flex items-center justify-center">
                            <svg class="h-6 w-6 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            </svg>
                        </div>
                    </div>
                    
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            Reparación #{{ $reparacion->codigo_ticket }}
                        </h1>
                        <div class="flex items-center space-x-4 mt-1">
                            <span class="status-badge status-{{ $reparacion->estado }}">
                                {{ ucfirst($reparacion->estado) }}
                            </span>
                            <span class="priority-badge priority-{{ $reparacion->prioridad }}">
                                Prioridad {{ ucfirst($reparacion->prioridad) }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Creado {{ $reparacion->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones principales -->
                <div class="mt-4 lg:mt-0 flex flex-wrap gap-2">
                    
                    @can('reparaciones.editar')
                    <a href="{{ route('reparaciones.edit', $reparacion) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Editar
                    </a>
                    @endcan

                    @can('reparaciones.editar')
                    <button @click="showChangeStatusModal = true" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Cambiar Estado
                    </button>
                    @endcan

                    <a href="{{ route('reparaciones.print', $reparacion) }}" 
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gestion-600 hover:bg-gestion-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimir
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
        
        <!-- Progreso visual -->
        <div class="px-6 py-4">
            <div class="flex items-center space-x-4">
                <div class="flex-1">
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Progreso de la reparación</span>
                        <span class="font-medium text-gray-900 dark:text-white" x-text="getProgressPercentage() + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-300"
                             :class="getProgressColor()"
                             :style="`width: ${getProgressPercentage()}%`"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación por pestañas -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button @click="activeTab = 'resumen'"
                        :class="activeTab === 'resumen' ? 
                            'border-gestion-500 text-gestion-600 dark:text-gestion-400' : 
                            'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Resumen
                </button>
                
                <button @click="activeTab = 'timeline'"
                        :class="activeTab === 'timeline' ? 
                            'border-gestion-500 text-gestion-600 dark:text-gestion-400' : 
                            'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Timeline
                </button>
                
                <button @click="activeTab = 'diagnosticos'"
                        :class="activeTab === 'diagnosticos' ? 
                            'border-gestion-500 text-gestion-600 dark:text-gestion-400' : 
                            'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    Diagnósticos
                </button>
                
                <button @click="activeTab = 'costos'"
                        :class="activeTab === 'costos' ? 
                            'border-gestion-500 text-gestion-600 dark:text-gestion-400' : 
                            'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                    Costos
                </button>
                
                <button @click="activeTab = 'archivos'"
                        :class="activeTab === 'archivos' ? 
                            'border-gestion-500 text-gestion-600 dark:text-gestion-400' : 
                            'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    Archivos
                </button>
            </nav>
        </div>

        <!-- Contenido de las pestañas -->
        <div class="p-6">
            
            <!-- Tab Resumen -->
            <div x-show="activeTab === 'resumen'" class="tab-content active">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Información del Cliente -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Información del Cliente
                            </h3>
                            
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $reparacion->cliente->nombre }} {{ $reparacion->cliente->apellido }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $reparacion->cliente->telefono }}
                                        </p>
                                        @if($reparacion->cliente->email)
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $reparacion->cliente->email }}
                                        </p>
                                        @endif
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="tel:{{ $reparacion->cliente->telefono }}" 
                                           class="text-gestion-600 hover:text-gestion-800 dark:hover:text-gestion-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                        </a>
                                        @if($reparacion->cliente->email)
                                        <a href="mailto:{{ $reparacion->cliente->email }}" 
                                           class="text-gestion-600 hover:text-gestion-800 dark:hover:text-gestion-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Equipo -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Equipo a Reparar
                            </h3>
                            
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-3">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-600 dark:text-gray-400">Tipo</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst($reparacion->tipo_equipo) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 dark:text-gray-400">Marca</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $reparacion->marca }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600 dark:text-gray-400">Modelo</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $reparacion->modelo }}</p>
                                    </div>
                                    @if($reparacion->numero_serie)
                                    <div>
                                        <p class="text-gray-600 dark:text-gray-400">Serie/IMEI</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $reparacion->numero_serie }}</p>
                                    </div>
                                    @endif
                                </div>
                                
                                @if($reparacion->caracteristicas)
                                <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Características</p>
                                    <p class="text-gray-900 dark:text-white text-sm mt-1">{{ $reparacion->caracteristicas }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Reparación -->
                    <div class="space-y-6">
                        
                        <!-- Problema reportado -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Problema Reportado
                            </h3>
                            
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <p class="text-red-800 dark:text-red-200">{{ $reparacion->problema_reportado }}</p>
                            </div>
                        </div>

                        <!-- Detalles técnicos -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Detalles de la Reparación
                            </h3>
                            
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-4">
                                
                                <!-- Estado y Prioridad -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Estado Actual</p>
                                        {{-- resources/views/modules/reparaciones/show.blade.php - PARTE 2/3 --}}

                                        <span class="status-badge status-{{ $reparacion->estado }} mt-1">
                                            {{ ucfirst($reparacion->estado) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Prioridad</p>
                                        <span class="priority-badge priority-{{ $reparacion->prioridad }} mt-1">
                                            {{ ucfirst($reparacion->prioridad) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Técnico asignado -->
                                @if($reparacion->tecnico)
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Técnico Asignado</p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <div class="h-6 w-6 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                                {{ substr($reparacion->tecnico->nombre, 0, 1) }}{{ substr($reparacion->tecnico->apellido, 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $reparacion->tecnico->nombre }} {{ $reparacion->tecnico->apellido }}
                                        </span>
                                    </div>
                                </div>
                                @endif

                                <!-- Fechas -->
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-600 dark:text-gray-400">Fecha de Recepción</p>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $reparacion->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                    @if($reparacion->fecha_estimada)
                                    <div>
                                        <p class="text-gray-600 dark:text-gray-400">Entrega Estimada</p>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($reparacion->fecha_estimada)->format('d/m/Y') }}
                                        </p>
                                    </div>
                                    @endif
                                </div>

                                <!-- Costo estimado -->
                                @if($reparacion->costo_estimado)
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Costo Estimado</p>
                                    <p class="text-lg font-bold text-gestion-600 dark:text-gestion-400">
                                        ${{ number_format($reparacion->costo_estimado, 2) }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Timeline -->
            <div x-show="activeTab === 'timeline'" class="tab-content">
                <div class="max-w-3xl">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @forelse($reparacion->estadosReparacion as $estado)
                            <li class="timeline-item">
                                <div class="timeline-line"></div>
                                <div class="relative flex items-start space-x-3">
                                    <div class="relative">
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800
                                                    {{ $estado->estado === 'completado' ? 'bg-green-500' : 
                                                       ($estado->estado === 'cancelado' ? 'bg-red-500' : 'bg-gestion-500') }}">
                                            @if($estado->estado === 'recibido')
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                            @elseif($estado->estado === 'diagnosticando')
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            @elseif($estado->estado === 'reparando')
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                </svg>
                                            @elseif($estado->estado === 'completado')
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div>
                                            <div class="text-sm">
                                                <span class="font-medium text-gray-900 dark:text-white">
                                                    {{ ucfirst($estado->estado) }}
                                                </span>
                                                <span class="text-gray-500 dark:text-gray-400">
                                                    por {{ $estado->usuario->nombre }} {{ $estado->usuario->apellido }}
                                                </span>
                                            </div>
                                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $estado->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                        @if($estado->observaciones)
                                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                            <p>{{ $estado->observaciones }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin historial</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No hay cambios de estado registrados.</p>
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tab Diagnósticos -->
            <div x-show="activeTab === 'diagnosticos'" class="tab-content">
                <div class="space-y-6">
                    @can('reparaciones.editar')
                    <div class="flex justify-end">
                        <button @click="showAddDiagnosticModal = true" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gestion-600 hover:bg-gestion-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Agregar Diagnóstico
                        </button>
                    </div>
                    @endcan

                    @forelse($reparacion->diagnosticos as $diagnostico)
                    <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-6 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                        Diagnóstico #{{ $loop->iteration }}
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gestion-100 text-gestion-800 dark:bg-gestion-900 dark:text-gestion-200">
                                        {{ $diagnostico->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                
                                <div class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                    Por: {{ $diagnostico->usuario->nombre }} {{ $diagnostico->usuario->apellido }}
                                </div>
                                
                                <div class="prose dark:prose-invert max-w-none">
                                    <p class="text-gray-900 dark:text-white">{{ $diagnostico->descripcion }}</p>
                                </div>
                                
                                @if($diagnostico->costo_estimado)
                                <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                        <span class="text-sm font-medium text-green-800 dark:text-green-200">
                                            Costo estimado: ${{ number_format($diagnostico->costo_estimado, 2) }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin diagnósticos</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No se han registrado diagnósticos para esta reparación.</p>
                        @can('reparaciones.editar')
                        <div class="mt-6">
                            <button @click="showAddDiagnosticModal = true" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Agregar Diagnóstico
                            </button>
                        </div>
                        @endcan
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Tab Costos -->
            <div x-show="activeTab === 'costos'" class="tab-content">
                <div class="space-y-6">
                    @can('reparaciones.editar')
                    <div class="flex justify-end">
                        <button @click="showAddCostModal = true" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gestion-600 hover:bg-gestion-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Agregar Costo
                        </button>
                    </div>
                    @endcan

                    <!-- Resumen de costos -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Costo Estimado</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    ${{ number_format($reparacion->costo_estimado ?? 0, 2) }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Acumulado</p>
                                <p class="text-2xl font-bold text-gestion-600 dark:text-gestion-400">
                                    ${{ number_format($reparacion->costosReparacion->sum('monto'), 2) }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Diferencia</p>
                                <p class="text-2xl font-bold {{ ($reparacion->costosReparacion->sum('monto') - ($reparacion->costo_estimado ?? 0)) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    ${{ number_format($reparacion->costosReparacion->sum('monto') - ($reparacion->costo_estimado ?? 0), 2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de costos -->
                    @forelse($reparacion->costosReparacion as $costo)
                    <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-6 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ $costo->concepto }}
                                    </h4>
                                    <span class="text-xl font-bold text-gestion-600 dark:text-gestion-400">
                                        ${{ number_format($costo->monto, 2) }}
                                    </span>
                                </div>
                                
                                @if($costo->descripcion)
                                <p class="text-gray-600 dark:text-gray-400 mb-3">{{ $costo->descripcion }}</p>
                                @endif
                                
                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ $costo->usuario->nombre }} {{ $costo->usuario->apellido }}
                                    <span class="ml-2">•</span>
                                    <span class="ml-2">{{ $costo->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin costos registrados</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No se han registrado costos adicionales para esta reparación.</p>
                        @can('reparaciones.editar')
                        <div class="mt-6">
                            <button @click="showAddCostModal = true" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Agregar Costo
                            </button>
                        </div>
                        @endcan
                    </div>
                    @endforelse
                </div>
            </div>
        {{-- resources/views/modules/reparaciones/show.blade.php - PARTE 3/3 (FINAL) --}}

            <!-- Tab Archivos -->
            <div x-show="activeTab === 'archivos'" class="tab-content">
                <div class="space-y-6">
                    @can('reparaciones.editar')
                    <div class="flex justify-end">
                        <button @click="showUploadModal = true" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-gestion-600 hover:bg-gestion-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Subir Archivo
                        </button>
                    </div>
                    @endcan

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Aquí irían los archivos subidos -->
                        <div class="text-center py-12 col-span-full">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin archivos</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No se han subido archivos para esta reparación.</p>
                            @can('reparaciones.editar')
                            <div class="mt-6">
                                <button @click="showUploadModal = true" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    Subir Archivo
                                </button>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Incluir modales -->
@include('modules.reparaciones.partials.status-change-modal')
@include('modules.reparaciones.partials.diagnostic-modal')
@include('modules.reparaciones.partials.cost-modal')

@push('scripts')
<script>
function showReparacionManager() {
    return {
        activeTab: 'resumen',
        showChangeStatusModal: false,
        showAddDiagnosticModal: false,
        showAddCostModal: false,
        showUploadModal: false,
        
        init() {
            console.log('Show Reparacion Manager initialized');
        },
        
        getProgressPercentage() {
            const estado = '{{ $reparacion->estado }}';
            const progressMap = {
                'recibido': 10,
                'diagnosticando': 25,
                'diagnosticado': 40,
                'reparando': 70,
                'completado': 90,
                'entregado': 100,
                'cancelado': 0
            };
            return progressMap[estado] || 0;
        },
        
        getProgressColor() {
            const estado = '{{ $reparacion->estado }}';
            const colorMap = {
                'recibido': 'bg-blue-500',
                'diagnosticando': 'bg-yellow-500',
                'diagnosticado': 'bg-orange-500',
                'reparando': 'bg-purple-500',
                'completado': 'bg-green-500',
                'entregado': 'bg-emerald-500',
                'cancelado': 'bg-red-500'
            };
            return colorMap[estado] || 'bg-gray-500';
        }
    }
}

// Función para imprimir la reparación
function printReparation() {
    window.print();
}

// Función para exportar como PDF
function exportToPDF() {
    // Esta función requeriría una implementación específica del backend
    // o una librería de PDF del lado del cliente
    alert('Función de exportar PDF en desarrollo');
}

// Función para cambiar estado rápido
async function quickStatusChange(newStatus) {
    if (confirm(`¿Cambiar estado a "${newStatus}"?`)) {
        try {
            const response = await fetch(`/reparaciones/{{ $reparacion->id }}/change-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    estado: newStatus,
                    observaciones: '',
                    notify_client: true
                })
            });
            
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Error al cambiar el estado');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de conexión');
        }
    }
}

// Función para notificar al cliente
async function notifyClient() {
    if (confirm('¿Enviar notificación al cliente sobre el estado actual?')) {
        try {
            const response = await fetch(`/reparaciones/{{ $reparacion->id }}/notify-client`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                alert('Notificación enviada correctamente');
            } else {
                alert('Error al enviar la notificación');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de conexión');
        }
    }
}

// Auto-refresh cada 30 segundos para actualizaciones en tiempo real
setInterval(() => {
    // Solo actualizar si no hay modales abiertos
    const hasOpenModals = document.querySelector('[x-show="showChangeStatusModal"]') ||
                         document.querySelector('[x-show="showAddDiagnosticModal"]') ||
                         document.querySelector('[x-show="showAddCostModal"]') ||
                         document.querySelector('[x-show="showUploadModal"]');
    
    if (!hasOpenModals) {
        // Aquí podrías hacer una petición AJAX para verificar si hay cambios
        // y actualizar solo las partes necesarias sin recargar toda la página
    }
}, 30000);

// Atajos de teclado
document.addEventListener('keydown', function(e) {
    // Solo activar si no estamos en un input/textarea
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    
    switch(e.key) {
        case '1':
            Alpine.store('activeTab', 'resumen');
            break;
        case '2':
            Alpine.store('activeTab', 'timeline');
            break;
        case '3':
            Alpine.store('activeTab', 'diagnosticos');
            break;
        case '4':
            Alpine.store('activeTab', 'costos');
            break;
        case '5':
            Alpine.store('activeTab', 'archivos');
            break;
        case 'p':
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                printReparation();
            }
            break;
        case 'e':
            if (e.ctrlKey || e.metaKey) {
                e.preventDefault();
                window.location.href = '{{ route("reparaciones.edit", $reparacion) }}';
            }
            break;
    }
});

// Función para compartir información de la reparación
function shareReparation() {
    if (navigator.share) {
        navigator.share({
            title: 'Reparación #{{ $reparacion->codigo_ticket }}',
            text: 'Estado: {{ ucfirst($reparacion->estado) }} - {{ $reparacion->cliente->nombre }}',
            url: window.location.href
        });
    } else {
        // Fallback: copiar URL al clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('URL copiada al portapapeles');
        });
    }
}
</script>
@endpush
@endsection

{{-- Estilos adicionales para impresión --}}
@push('styles')
<style media="print">
    .no-print {
        display: none !important;
    }
    
    .print-only {
        display: block !important;
    }
    
    body {
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    
    .bg-white {
        background: white !important;
    }
    
    .text-gray-900 {
        color: #111827 !important;
    }
    
    .border {
        border: 1px solid #d1d5db !important;
    }
    
    .shadow-sm,
    .shadow-lg {
        box-shadow: none !important;
    }
    
    .rounded-lg {
        border-radius: 0 !important;
    }
    
    @page {
        margin: 1in;
        size: letter;
    }
    
    /* Ocultar elementos innecesarios en impresión */
    .border-b-2,
    nav,
    button,
    .hover\:bg-gray-50 {
        display: none !important;
    }
    
    /* Mostrar todos los tabs en impresión */
    .tab-content {
        display: block !important;
        page-break-inside: avoid;
        margin-bottom: 2rem;
    }
    
    h3 {
        page-break-after: avoid;
    }
</style>
@endpush

{{-- Meta tags para SEO y compartir --}}
@push('meta')
<meta name="description" content="Reparación #{{ $reparacion->codigo_ticket }} - {{ $reparacion->cliente->nombre }} {{ $reparacion->cliente->apellido }}">
<meta property="og:title" content="Reparación #{{ $reparacion->codigo_ticket }}">
<meta property="og:description" content="Estado: {{ ucfirst($reparacion->estado) }} - {{ $reparacion->tipo_equipo }} {{ $reparacion->marca }} {{ $reparacion->modelo }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ request()->url() }}">
@endpush