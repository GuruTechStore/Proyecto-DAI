{{-- resources/views/dashboard.blade.php - VERSIÓN LIMPIA --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-sm">
        <div class="px-6 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">
                        ¡Bienvenido, {{ Auth::user()->name ?? 'Usuario' }}!
                    </h1>
                    <p class="mt-2 text-blue-100">
                        {{ now()->format('l, j \d\e F \d\e Y') }} • {{ now()->format('H:i') }}
                    </p>
                </div>
                <div class="hidden md:block">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total Clientes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Clientes</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Registrados</p>
                </div>
            </div>
            @if(Route::has('clientes.index'))
            <div class="mt-4">
                <a href="{{ route('clientes.index') }}" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                    Ver todos →
                </a>
            </div>
            @endif
        </div>

        <!-- Total Productos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Productos</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                    <p class="text-sm text-green-600 dark:text-green-400">Stock normal</p>
                </div>
            </div>
            @if(Route::has('productos.index'))
            <div class="mt-4">
                <a href="{{ route('productos.index') }}" class="text-sm text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                    Ver todos →
                </a>
            </div>
            @endif
        </div>

        <!-- Reparaciones -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tools text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Reparaciones</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                    <p class="text-sm text-green-600 dark:text-green-400">Al día</p>
                </div>
            </div>
            @if(Route::has('reparaciones.index'))
            <div class="mt-4">
                <a href="{{ route('reparaciones.index') }}" class="text-sm text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-200">
                    Ver todas →
                </a>
            </div>
            @endif
        </div>

        <!-- Ventas Hoy -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ventas Hoy</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">S/ 0.00</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sin ventas hoy</p>
                </div>
            </div>
            @if(Route::has('ventas.index'))
            <div class="mt-4">
                <a href="{{ route('ventas.index') }}" class="text-sm text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-200">
                    Ver todas →
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Ventas de los Últimos 7 Días -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ventas de los Últimos 7 Días</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center h-64">
                    <div class="text-center">
                        <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400 mb-2">No hay datos de ventas</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Los datos aparecerán cuando tengas ventas registradas</p>
                        @if(Route::has('ventas.create'))
                        <a href="{{ route('ventas.create') }}" 
                           class="inline-flex items-center mt-4 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Registrar primera venta
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Reparaciones por Estado -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reparaciones por Estado</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center h-64">
                    <div class="text-center">
                        <i class="fas fa-tools text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400 mb-2">No hay reparaciones registradas</p>
                        @if(Route::has('reparaciones.create'))
                        <a href="{{ route('reparaciones.create') }}" 
                           class="inline-flex items-center mt-4 px-4 py-2 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Nueva reparación
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Actividad Reciente -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Actividad Reciente</h3>
                    <button class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 flex items-center">
                        <i class="fas fa-sync-alt mr-1"></i>
                        Actualizar
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center h-48">
                    <div class="text-center">
                        <i class="fas fa-clock text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">No hay actividad reciente</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                            La actividad aparecerá cuando realices acciones en el sistema
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="space-y-6">
            
            <!-- Productos Más Vendidos -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Productos Más Vendidos</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-center h-32">
                        <div class="text-center">
                            <i class="fas fa-shopping-bag text-gray-400 text-2xl mb-2"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Sin datos de ventas</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Rápidas -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Estadísticas Rápidas</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Empleados Activos</span>
                        <span class="font-semibold text-gray-900 dark:text-white">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Categorías</span>
                        <span class="font-semibold text-gray-900 dark:text-white">0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Proveedores</span>
                        <span class="font-semibold text-gray-900 dark:text-white">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Float Button -->
    @if(Route::has('clientes.create') || Route::has('productos.create') || Route::has('reparaciones.create') || Route::has('ventas.create'))
    <div class="fixed bottom-6 right-6 z-50">
        <div x-data="{ open: false }" class="relative">
            <!-- Main Button -->
            <button @click="open = !open" 
                    class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center">
                <i class="fas" :class="open ? 'fa-times' : 'fa-plus'" class="text-xl transition-transform"></i>
            </button>
            
            <!-- Action Buttons -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute bottom-16 right-0 space-y-2">
                
                @if(Route::has('clientes.create'))
                <a href="{{ route('clientes.create') }}" 
                   class="flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full shadow-lg transition-colors">
                    <i class="fas fa-user-plus"></i>
                    <span class="text-sm">Cliente</span>
                </a>
                @endif
                
                @if(Route::has('productos.create'))
                <a href="{{ route('productos.create') }}" 
                   class="flex items-center space-x-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-full shadow-lg transition-colors">
                    <i class="fas fa-plus"></i>
                    <span class="text-sm">Producto</span>
                </a>
                @endif
                
                @if(Route::has('reparaciones.create'))
                <a href="{{ route('reparaciones.create') }}" 
                   class="flex items-center space-x-2 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-full shadow-lg transition-colors">
                    <i class="fas fa-wrench"></i>
                    <span class="text-sm">Reparación</span>
                </a>
                @endif
                
                @if(Route::has('ventas.create'))
                <a href="{{ route('ventas.create') }}" 
                   class="flex items-center space-x-2 bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-full shadow-lg transition-colors">
                    <i class="fas fa-cash-register"></i>
                    <span class="text-sm">Venta</span>
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection