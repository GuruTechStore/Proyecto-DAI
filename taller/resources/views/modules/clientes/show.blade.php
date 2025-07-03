{{-- resources/views/modules/clientes/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Cliente: ' . $cliente->nombre)

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m0 0V9a3 3 0 00-6 0v4.294z" />
        </svg>
        <a href="{{ route('clientes.index') }}" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Clientes</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
        </svg>
        <span class="ml-2 text-sm font-medium text-gray-700">{{ $cliente->nombre }}</span>
    </div>
</li>
@endsection

@push('styles')
<style>
    .info-card {
        transition: all 0.3s ease;
    }
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .tab-content {
        min-height: 400px;
    }
    .status-badge {
        animation: pulse 2s infinite;
    }
    .activity-item {
        position: relative;
        padding-left: 1.5rem;
    }
    .activity-item::before {
        content: '';
        position: absolute;
        left: 0.375rem;
        top: 0.375rem;
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 50%;
        background-color: #e5e7eb;
    }
    .activity-item.success::before {
        background-color: #10b981;
    }
    .activity-item.warning::before {
        background-color: #f59e0b;
    }
    .activity-item.info::before {
        background-color: #3b82f6;
    }
</style>
@endpush

@section('content')
<div x-data="clienteShow()" x-init="init()" class="space-y-6">
    
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="h-16 w-16 rounded-full bg-gestion-100 dark:bg-gestion-900 flex items-center justify-center">
                            <span class="text-xl font-bold text-gestion-700 dark:text-gestion-300">
                                {{ strtoupper(substr($cliente->nombre, 0, 1) . substr($cliente->apellido ?? '', 0, 1)) }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Info -->
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $cliente->nombre }} {{ $cliente->apellido }}
                        </h1>
                        <div class="flex items-center space-x-4 mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cliente->activo ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                            @if($cliente->numero_documento)
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ strtoupper($cliente->tipo_documento ?? 'DOC') }}: {{ $cliente->numero_documento }}
                            </span>
                            @endif
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Cliente desde {{ $cliente->created_at->format('M Y') }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row gap-3">
                    @can('clientes.editar')
                    <a href="{{ route('clientes.edit', $cliente) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Editar Cliente
                    </a>
                    @endcan
                    
                    @can('reparaciones.crear')
                    <a href="{{ route('reparaciones.create', ['cliente_id' => $cliente->id]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Nueva Reparación
                    </a>
                    @endcan
                    
                    <a href="{{ route('clientes.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Volver al Listado
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="info-card bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Equipos Registrados</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $cliente->equipos->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="info-card bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Reparaciones</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $cliente->reparaciones->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="info-card bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Ventas</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">{{ $cliente->ventas->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="info-card bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Gastado</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white">
                                S/ {{ number_format($cliente->ventas->sum('total') + $cliente->reparaciones->sum('costo_total'), 2) }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div x-data="{ activeTab: 'info' }" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button @click="activeTab = 'info'" 
                        :class="activeTab === 'info' ? 'border-gestion-500 text-gestion-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Información
                </button>
                
                <button @click="activeTab = 'equipos'" 
                        :class="activeTab === 'equipos' ? 'border-gestion-500 text-gestion-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Equipos ({{ $cliente->equipos->count() }})
                </button>
                
                <button @click="activeTab = 'reparaciones'" 
                        :class="activeTab === 'reparaciones' ? 'border-gestion-500 text-gestion-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Reparaciones ({{ $cliente->reparaciones->count() }})
                </button>
                
                <button @click="activeTab = 'ventas'" 
                        :class="activeTab === 'ventas' ? 'border-gestion-500 text-gestion-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
                    </svg>
                    Ventas ({{ $cliente->ventas->count() }})
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            
            <!-- Información Tab -->
            <div x-show="activeTab === 'info'" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Datos Personales -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Datos Personales
                            </h3>
                            
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre Completo</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $cliente->nombre }} {{ $cliente->apellido }}</dd>
                                </div>
                                
                                @if($cliente->tipo_documento && $cliente->numero_documento)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ strtoupper($cliente->tipo_documento) }}</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $cliente->numero_documento }}</dd>
                                </div>
                                @endif
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                                    <dd class="text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cliente->activo ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ $cliente->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Registro</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $cliente->created_at->format('d/m/Y H:i') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- Datos de Contacto -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                Información de Contacto
                            </h3>
                            
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">
                                        <a href="tel:{{ $cliente->telefono }}" class="hover:text-gestion-600 transition-colors">
                                            {{ $cliente->telefono }}
                                        </a>
                                    </dd>
                                </div>
                                
                                @if($cliente->email)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">
                                        <a href="mailto:{{ $cliente->email }}" class="hover:text-gestion-600 transition-colors">
                                            {{ $cliente->email }}
                                        </a>
                                    </dd>
                                </div>
                                @endif
                                
                                @if($cliente->direccion)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dirección</dt>
                                    <dd class="text-sm text-gray-900 dark:text-white">{{ $cliente->direccion }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipos Tab -->
            <div x-show="activeTab === 'equipos'" class="tab-content">
                @if($cliente->equipos->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($cliente->equipos as $equipo)
                        <div class="info-card bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $equipo->tipo }}</h4>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $equipo->reparaciones->count() }} reparaciones
                                </span>
                            </div>
                            
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Marca:</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $equipo->marca }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">Modelo:</dt>
                                    <dd class="text-gray-900 dark:text-white">{{ $equipo->modelo }}</dd>
                                </div>
                                @if($equipo->imei)
                                <div class="flex justify-between">
                                    <dt class="text-gray-500 dark:text-gray-400">IMEI:</dt>
                                    <dd class="text-gray-900 dark:text-white font-mono text-xs">{{ $equipo->imei }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay equipos registrados</h3>
                        <p class="text-gray-500 dark:text-gray-400">Los equipos aparecerán aquí cuando se registren reparaciones</p>
                    </div>
                @endif
            </div>

            <!-- Reparaciones Tab -->
            <div x-show="activeTab === 'reparaciones'" class="tab-content">
                @if($cliente->reparaciones->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ticket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Equipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Técnico</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Costo</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($cliente->reparaciones->take(10) as $reparacion)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $reparacion->codigo_ticket }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $reparacion->problema_reportado }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ $reparacion->tipo_equipo }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $reparacion->marca }} {{ $reparacion->modelo }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($reparacion->estado === 'completado') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($reparacion->estado === 'en_proceso') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @elseif($reparacion->estado === 'pendiente') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $reparacion->estado)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $reparacion->tecnico ? $reparacion->tecnico->nombres : 'Sin asignar' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $reparacion->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        S/ {{ number_format($reparacion->costo_total ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @can('reparaciones.ver')
                                        <a href="{{ route('reparaciones.show', $reparacion) }}" 
                                           class="text-gestion-600 hover:text-gestion-900 dark:text-gestion-400 dark:hover:text-gestion-200">
                                            Ver
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($cliente->reparaciones->count() > 10)
                    <div class="mt-4 text-center">
                        <a href="{{ route('reparaciones.index', ['cliente_id' => $cliente->id]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Ver todas las reparaciones ({{ $cliente->reparaciones->count() }})
                        </a>
                    </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay reparaciones registradas</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">Crea la primera reparación para este cliente</p>
                        @can('reparaciones.crear')
                        <a href="{{ route('reparaciones.create', ['cliente_id' => $cliente->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nueva Reparación
                        </a>
                        @endcan
                    </div>
                @endif
            </div>

            <!-- Ventas Tab -->
            <div x-show="activeTab === 'ventas'" class="tab-content">
                @if($cliente->ventas->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Número</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Comprobante</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Productos</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($cliente->ventas->take(10) as $venta)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $venta->numero_venta }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ ucfirst($venta->tipo_comprobante) }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($venta->metodo_pago) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ $venta->detalleVentas->count() }} productos</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $venta->detalleVentas->sum('cantidad') }} unidades</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($venta->estado === 'completada') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($venta->estado === 'procesando') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @elseif($venta->estado === 'pendiente') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                            @endif">
                                            {{ ucfirst($venta->estado) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $venta->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        S/ {{ number_format($venta->total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @can('ventas.ver')
                                        <a href="{{ route('ventas.show', $venta) }}" 
                                           class="text-gestion-600 hover:text-gestion-900 dark:text-gestion-400 dark:hover:text-gestion-200">
                                            Ver
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($cliente->ventas->count() > 10)
                    <div class="mt-4 text-center">
                        <a href="{{ route('ventas.index', ['cliente_id' => $cliente->id]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Ver todas las ventas ({{ $cliente->ventas->count() }})
                        </a>
                    </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay ventas registradas</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">Las ventas de este cliente aparecerán aquí</p>
                        @can('ventas.crear')
                        <a href="{{ route('ventas.create', ['cliente_id' => $cliente->id]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gestion-600 hover:bg-gestion-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nueva Venta
                        </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Activity Timeline -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Actividad Reciente
            </h3>
        </div>
        
        <div class="px-6 py-4">
            <div class="flow-root">
                <ul class="-mb-8">
                    @php
                        $activities = collect();
                        
                        // Agregar reparaciones
                        foreach($cliente->reparaciones->take(5) as $reparacion) {
                            $activities->push([
                                'type' => 'reparation',
                                'title' => 'Reparación registrada',
                                'description' => $reparacion->problema_reportado,
                                'date' => $reparacion->created_at,
                                'icon' => 'repair',
                                'color' => 'yellow'
                            ]);
                        }
                        
                        // Agregar ventas
                        foreach($cliente->ventas->take(5) as $venta) {
                            $activities->push([
                                'type' => 'sale',
                                'title' => 'Venta realizada',
                                'description' => 'S/ ' . number_format($venta->total, 2),
                                'date' => $venta->created_at,
                                'icon' => 'shopping',
                                'color' => 'green'
                            ]);
                        }
                        
                        // Agregar registro del cliente
                        $activities->push([
                            'type' => 'register',
                            'title' => 'Cliente registrado',
                            'description' => 'Se registró en el sistema',
                            'date' => $cliente->created_at,
                            'icon' => 'user',
                            'color' => 'blue'
                        ]);
                        
                        $activities = $activities->sortByDesc('date')->take(8);
                    @endphp
                    
                    @if($activities->count() > 0)
                        @foreach($activities as $index => $activity)
                        <li>
                            <div class="relative pb-8 {{ $index === $activities->count() - 1 ? '' : 'border-l-2 border-gray-200 dark:border-gray-600 ml-4' }}">
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800
                                            @if($activity['color'] === 'green') bg-green-500
                                            @elseif($activity['color'] === 'yellow') bg-yellow-500
                                            @elseif($activity['color'] === 'blue') bg-blue-500
                                            @else bg-gray-500
                                            @endif">
                                            @if($activity['icon'] === 'repair')
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                </svg>
                                            @elseif($activity['icon'] === 'shopping')
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 pb-8">
                                        <div>
                                            <p class="text-sm text-gray-900 dark:text-white font-medium">{{ $activity['title'] }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $activity['description'] }}</p>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            <time datetime="{{ $activity['date']->format('Y-m-d') }}">{{ $activity['date']->diffForHumans() }}</time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    @else
                        <li class="text-center py-8">
                            <div class="text-gray-500 dark:text-gray-400">
                                No hay actividad reciente para mostrar
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function clienteShow() {
    return {
        loading: false,

        init() {
            console.log('Cliente show initialized');
        }
    }
}
</script>
@endpush

@endsection