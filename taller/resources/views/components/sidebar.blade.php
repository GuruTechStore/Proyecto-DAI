{{-- resources/views/components/sidebar.blade.php --}}
@props(['collapsed' => false])

<div class="flex flex-col h-full bg-gestion-800 dark:bg-gray-900 w-64 transition-all duration-300">
    
    <!-- Logo/Header -->
    <div class="flex items-center justify-between h-16 px-4 bg-gestion-900 dark:bg-gray-950">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
            <div class="flex-shrink-0">
                <img class="h-8 w-8 transition-transform group-hover:scale-110" 
                     src="{{ asset('images/logo.png') }}" 
                     alt="{{ config('app.name') }}" 
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <div style="display:none" class="h-8 w-8 bg-white rounded-lg flex items-center justify-center transition-transform group-hover:scale-110">
                    <span class="text-gestion-800 font-bold text-lg">G</span>
                </div>
            </div>
            <span class="text-white text-lg font-semibold truncate">
                {{ config('app.name', 'Gestión') }}
            </span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
        
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" 
           class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-gestion-900 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            Dashboard
        </a>

        <!-- Clientes -->
        @can('clientes.ver')
        <div x-data="{ open: {{ request()->is('clientes*') ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="open = !open" 
                    class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->is('clientes*') ? 'bg-gestion-900 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="flex-1 text-left">Clientes</span>
                <svg class="ml-3 h-5 w-5 transform transition-transform" :class="open ? 'rotate-90' : ''" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </button>
            
            <div x-show="open" x-transition class="space-y-1">
                @can('clientes.ver')
                <a href="{{ route('clientes.index') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('clientes.index') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Lista de Clientes
                </a>
                @endcan
                
                @can('clientes.crear')
                <a href="{{ route('clientes.create') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('clientes.create') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Nuevo Cliente
                </a>
                @endcan
            </div>
        </div>
        @endcan

        <!-- Productos -->
        @can('productos.ver')
        <div x-data="{ open: {{ request()->is('productos*') ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="open = !open" 
                    class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->is('productos*') ? 'bg-gestion-900 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <span class="flex-1 text-left">Productos</span>
                <svg class="ml-3 h-5 w-5 transform transition-transform" :class="open ? 'rotate-90' : ''" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </button>
            
            <div x-show="open" x-transition class="space-y-1">
                @can('productos.ver')
                <a href="{{ route('productos.index') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('productos.index') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Inventario
                </a>
                @endcan
                
                @can('productos.crear')
                <a href="{{ route('productos.create') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('productos.create') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Nuevo Producto
                </a>
                @endcan
                
                @can('productos.ver')
                <a href="{{ route('productos.categorias.index') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('productos.categorias.*') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Categorías
                </a>
                @endcan
                
                @can('inventario.ver')
                <a href="{{ route('productos.stock-alerts') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('productos.stock-alerts') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Alertas de Stock
                </a>
                @endcan
            </div>
        </div>
        @endcan

        <!-- Reparaciones -->
        @can('reparaciones.ver')
        <div x-data="{ open: {{ request()->is('reparaciones*') ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="open = !open" 
                    class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->is('reparaciones*') ? 'bg-gestion-900 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="flex-1 text-left">Reparaciones</span>
                <svg class="ml-3 h-5 w-5 transform transition-transform" :class="open ? 'rotate-90' : ''" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </button>
            
            <div x-show="open" x-transition class="space-y-1">
                @can('reparaciones.ver')
                <a href="{{ route('reparaciones.index') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('reparaciones.index') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Lista de Reparaciones
                </a>
                @endcan
                
                @can('reparaciones.crear')
                <a href="{{ route('reparaciones.create') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('reparaciones.create') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Nueva Reparación
                </a>
                @endcan
                
                @can('reparaciones.ver')
                <a href="{{ route('reparaciones.reports.pending') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('reparaciones.reports.pending') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Pendientes
                </a>
                @endcan
                
                @can('reparaciones.ver')
                <a href="{{ route('reparaciones.reports.completed') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('reparaciones.reports.completed') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Completadas
                </a>
                @endcan
            </div>
        </div>
        @endcan

        <!-- Ventas -->
        @can('ventas.ver')
        <div x-data="{ open: {{ request()->is('ventas*') ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="open = !open" 
                    class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->is('ventas*') ? 'bg-gestion-900 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="flex-1 text-left">Ventas</span>
                <svg class="ml-3 h-5 w-5 transform transition-transform" :class="open ? 'rotate-90' : ''" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </button>
            
            <div x-show="open" x-transition class="space-y-1">
                @can('ventas.ver')
                <a href="{{ route('ventas.index') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('ventas.index') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Lista de Ventas
                </a>
                @endcan
                
                @can('ventas.crear')
                <a href="{{ route('ventas.create') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('ventas.create') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Nueva Venta
                </a>
                @endcan
            </div>
        </div>
        @endcan

        <!-- Reportes -->
        @can('reportes.ver')
        <div x-data="{ open: {{ request()->is('reportes*') ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="open = !open" 
                    class="group w-full flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->is('reportes*') ? 'bg-gestion-900 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="flex-1 text-left">Reportes</span>
                <svg class="ml-3 h-5 w-5 transform transition-transform" :class="open ? 'rotate-90' : ''" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </button>
            
            <div x-show="open" x-transition class="space-y-1">
                @can('reportes.ventas')
                <a href="{{ route('reportes.ventas.index') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('reportes.ventas.*') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Reporte de Ventas
                </a>
                @endcan
                
                @can('reportes.inventario')
                <a href="{{ route('reportes.inventario.index') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('reportes.inventario.*') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Reporte de Inventario
                </a>
                @endcan
                
                @can('reportes.reparaciones')
                <a href="{{ route('reportes.reparaciones.index') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('reportes.reparaciones.*') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Reporte de Reparaciones
                </a>
                @endcan
                
                @can('reportes.financieros')
                <a href="{{ route('reportes.financieros.index') }}" 
                   class="group flex items-center pl-11 pr-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('reportes.financieros.*') ? 'bg-gestion-700 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                    Reporte Financiero
                </a>
                @endcan
            </div>
        </div>
        @endcan

        <!-- Administración (Solo para roles con permisos) -->
        @if(auth()->user()->can('usuarios.ver') || auth()->user()->can('configuracion.ver') || auth()->user()->can('auditoria.ver'))
        <div class="pt-6">
            <p class="px-3 text-xs font-semibold text-gestion-300 uppercase tracking-wider">
                Administración
            </p>
            
            <!-- Usuarios -->
            @can('usuarios.ver')
            <a href="{{ route('admin.users.index') }}" 
               class="mt-2 group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->is('admin/users*') ? 'bg-gestion-900 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Usuarios
            </a>
            @endcan
            
            <!-- Actividad -->
            @can('auditoria.ver')
            <a href="{{ route('admin.activity.index') }}" 
               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->is('admin/activity*') ? 'bg-gestion-900 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Actividad
            </a>
            @endcan
            
            <!-- Seguridad -->
            @can('seguridad.ver')
            <a href="{{ route('admin.security.index') }}" 
               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->is('admin/security*') ? 'bg-gestion-900 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                Seguridad
            </a>
            @endcan
            
            <!-- Configuración -->
            @can('configuracion.ver')
            <a href="{{ route('admin.settings.index') }}" 
               class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors {{ request()->is('admin/settings*') ? 'bg-gestion-900 text-white' : 'text-gestion-100 hover:bg-gestion-700 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Configuración
            </a>
            @endcan
        </div>
        @endif
    </nav>

    <!-- Footer del Sidebar -->
    <div class="flex-shrink-0 flex border-t border-gestion-700 p-4">
        <div class="flex items-center">
            <div>
                <img class="inline-block h-9 w-9 rounded-full" 
                     src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->nombres ?? 'U') . '&background=3b82f6&color=fff' }}" 
                     alt="{{ auth()->user()->nombres ?? 'Usuario' }}">
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-white">
                    {{ auth()->user()->nombres ?? 'Usuario' }}
                </p>
                <p class="text-xs font-medium text-gestion-300">
                    @if(auth()->user()->roles()->exists())
                        {{ auth()->user()->roles()->first()->name }}
                    @else
                        Usuario
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>