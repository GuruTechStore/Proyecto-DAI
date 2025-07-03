<!-- Navigation Component - Menú dinámico por permisos -->
<nav x-data="navigationData()" 
     x-init="initNavigation()"
     class="bg-gestion-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo y título -->
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <img class="h-8 w-8" src="{{ asset('images/logo.png') }}" alt="Logo" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="display:none" class="h-8 w-8 bg-white rounded-lg flex items-center justify-center">
                            <span class="text-gestion-800 font-bold text-lg">G</span>
                        </div>
                    </a>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-baseline space-x-4">
                        <span class="text-white text-xl font-semibold">
                            {{ config('app.name', 'Gestión Empresarial') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Menú principal desktop -->
            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       class="nav-item {{ Request::routeIs('dashboard') ? 'nav-item-active' : 'nav-item-inactive' }}">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2zm0 0V9a2 2 0 012-2h14a2 2 0 012 2v10M9 21V9a2 2 0 012-2h2a2 2 0 012 2v12"/>
                        </svg>
                        Dashboard
                    </a>

                    <!-- Módulo Clientes -->
                    @can('clientes.ver')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                @click.away="open = false"
                                class="nav-item {{ Request::is('clientes*') ? 'nav-item-active' : 'nav-item-inactive' }} flex items-center">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Clientes
                            <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                @can('clientes.ver')
                                <a href="{{ route('clientes.index') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Ver clientes
                                </a>
                                @endcan
                                @can('clientes.crear')
                                <a href="{{ route('clientes.create') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                    Nuevo cliente
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- Módulo Productos -->
                    @can('productos.ver')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                @click.away="open = false"
                                class="nav-item {{ Request::is('productos*') ? 'nav-item-active' : 'nav-item-inactive' }} flex items-center">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Productos
                            <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                @can('productos.ver')
                                <a href="{{ route('productos.index') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Inventario
                                </a>
                                @endcan
                                @can('productos.crear')
                                <a href="{{ route('productos.create') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Agregar producto
                                </a>
                                @endcan
                                @can('productos.categorias')
                                <a href="{{ route('productos.categorias.index') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    Categorías
                                </a>
                                @endcan
                                @can('productos.stock')
                                <a href="{{ route('productos.stock.alertas') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Alertas de stock
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- Módulo Reparaciones -->
                    @can('reparaciones.ver')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                @click.away="open = false"
                                class="nav-item {{ Request::is('reparaciones*') ? 'nav-item-active' : 'nav-item-inactive' }} flex items-center">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Reparaciones
                            <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                @can('reparaciones.ver')
                                <a href="{{ route('reparaciones.index') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Ver reparaciones
                                </a>
                                @endcan
                                @can('reparaciones.crear')
                                <a href="{{ route('reparaciones.create') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Nueva reparación
                                </a>
                                @endcan
                                @hasrole('Técnico')
                                <a href="{{ route('reparaciones.mis-asignadas') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mis asignaciones
                                </a>
                                @endhasrole
                                @can('reparaciones.diagnostico')
                                <a href="{{ route('reparaciones.diagnostico') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Diagnóstico
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- Módulo Ventas -->
                    @can('ventas.ver')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                @click.away="open = false"
                                class="nav-item {{ Request::is('ventas*') ? 'nav-item-active' : 'nav-item-inactive' }} flex items-center">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Ventas
                            <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                @can('ventas.ver')
                                <a href="{{ route('ventas.index') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Ver ventas
                                </a>
                                @endcan
                                @can('ventas.crear')
                                <a href="{{ route('ventas.create') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Nueva venta
                                </a>
                                @endcan
                                @hasrole('Vendedor')
                                <a href="{{ route('ventas.mis-ventas') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mis ventas
                                </a>
                                @endhasrole
                                @can('ventas.comisiones')
                                <a href="{{ route('ventas.comisiones') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3-3-3a3 3 0 110-6zm0 0V6a2 2 0 012-2h6a2 2 0 012 2v2M7 16l-4-4m0 0l4-4m-4 4h18"/>
                                    </svg>
                                    Comisiones
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endcan

                    <!-- Módulo Reportes -->
                    @can('reportes.ver')
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                @click.away="open = false"
                                class="nav-item {{ Request::is('reportes*') ? 'nav-item-active' : 'nav-item-inactive' }} flex items-center">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Reportes
                            <svg class="ml-1 h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="py-1">
                                @can('reportes.ventas')
                                <a href="{{ route('reportes.ventas') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                    Reporte de ventas
                                </a>
                                @endcan
                                @can('reportes.inventario')
                                <a href="{{ route('reportes.inventario') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    Inventario
                                </a>
                                @endcan
                                @can('reportes.reparaciones')
                                <a href="{{ route('reportes.reparaciones') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Reparaciones
                                </a>
                                @endcan
                                @can('reportes.clientes')
                                <a href="{{ route('reportes.clientes') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Clientes
                                </a>
                                @endcan
                                @can('reportes.financiero')
                                <a href="{{ route('reportes.financiero') }}" class="dropdown-item">
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    Financiero
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>

            <!-- Lado derecho - Notificaciones, Búsqueda y Usuario -->
            <div class="hidden md:block">
                <div class="ml-4 flex items-center md:ml-6">
                    <!-- Notificaciones -->
                    <div class="relative" x-data="{ open: false, notifications: [], unreadCount: 0 }" x-init="loadNotifications()">
                        <button @click="open = !open; markAsRead()" 
                                @click.away="open = false"
                                class="bg-gestion-800 p-1 rounded-full text-gestion-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gestion-800 focus:ring-white relative">
                            <span class="sr-only">Ver notificaciones</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5V9.09c0-2.05-1.64-3.73-3.68-3.87C10.04 5.06 9 6.03 9 7.21v4.68L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span x-show="unreadCount > 0" 
                                  x-text="unreadCount" 
                                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"></span>
                        </button>

                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 max-h-96 overflow-y-auto">
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Notificaciones</h3>
                                    <button @click="clearAll()" class="text-sm text-gestion-600 hover:text-gestion-500">
                                        Limpiar todas
                                    </button>
                                </div>
                                
                                <div x-show="notifications.length > 0" class="space-y-3">
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer"
                                             :class="{ 'bg-blue-50': !notification.read_at }"
                                             @click="openNotification(notification)">
                                            <div class="flex-shrink-0">
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center"
                                                     :class="getNotificationColor(notification.type)">
                                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path :d="getNotificationIcon(notification.type)"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                                                <p class="text-sm text-gray-500" x-text="notification.message"></p>
                                                <p class="text-xs text-gray-400" x-text="formatNotificationDate(notification.created_at)"></p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                
                                <div x-show="notifications.length === 0" class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2"/>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">No hay notificaciones</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Búsqueda rápida -->
                    <div class="relative ml-3" x-data="{ open: false, query: '', results: [] }">
                        <button @click="open = !open" 
                                @click.away="open = false"
                                class="bg-gestion-800 p-1 rounded-full text-gestion-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gestion-800 focus:ring-white">
                            <span class="sr-only">Búsqueda rápida</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>

                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <div class="p-4">
                                <div class="relative">
                                    <input type="text" 
                                           x-model="query"
                                           @input.debounce.300ms="search()"
                                           placeholder="Buscar clientes, productos, reparaciones..."
                                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-gestion-500 focus:border-gestion-500">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                
                                <div x-show="results.length > 0" class="mt-4 space-y-2 max-h-60 overflow-y-auto">
                                    <template x-for="result in results" :key="result.id">
                                        <a :href="result.url" class="block p-2 hover:bg-gray-50 rounded-md">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    <div class="h-6 w-6 rounded-full flex items-center justify-center"
                                                         :class="getSearchResultColor(result.type)">
                                                        <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path :d="getSearchResultIcon(result.type)"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium text-gray-900" x-text="result.title"></p>
                                                    <p class="text-xs text-gray-500" x-text="result.subtitle"></p>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                                
                                <div x-show="query && results.length === 0" class="mt-4 text-center py-4">
                                    <p class="text-sm text-gray-500">No se encontraron resultados</p>
                                </div>
                            </div>
                        </div>
                    </div>

            <!-- Menú móvil button -->
            <div class="-mr-2 flex md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="bg-gestion-800 inline-flex items-center justify-center p-2 rounded-md text-gestion-400 hover:text-white hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gestion-800 focus:ring-white">
                    <span class="sr-only">Abrir menú principal</span>
                    <svg x-show="!mobileMenuOpen" class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileMenuOpen" class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</nav>
<!-- Menú de usuario -->
                    <div class="ml-3 relative" x-data="{ open: false }">
                        <div>
                            <button @click="open = !open" 
                                    @click.away="open = false"
                                    class="max-w-xs bg-gestion-800 rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gestion-800 focus:ring-white">
                                <span class="sr-only">Abrir menú de usuario</span>
                                @if(auth()->user()->avatar)
                                    <img class="h-8 w-8 rounded-full object-cover" 
                                         src="{{ Storage::url(auth()->user()->avatar) }}" 
                                         alt="{{ auth()->user()->first_name }}">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-gestion-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">
                                            {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                                <span class="ml-3 text-gestion-300 text-sm font-medium hidden lg:block">
                                    {{ auth()->user()->first_name }}
                                </span>
                                <svg class="ml-2 h-4 w-4 text-gestion-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>

                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <!-- Información del usuario -->
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm text-gray-700 font-medium">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                                <div class="mt-1">
                                    @foreach(auth()->user()->roles as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gestion-100 text-gestion-800">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Enlaces del menú -->
                            <a href="{{ route('profile.show') }}" class="dropdown-item">
                                <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Mi perfil
                            </a>

                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Configuración
                            </a>

                            
                                <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Mi actividad
                            </a>

                            <!-- Administración (solo para roles administrativos) -->
                        @canany(['usuarios.ver', 'auditoria.ver', 'configuracion.ver'])
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            @can('usuarios.ver')
                            <a href="{{ route('admin.users.index') }}" class="dropdown-item">
                                <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                                Gestionar usuarios
                            </a>
                            @endcan

                            @can('auditoria.ver')
                            <a href="{{ route('admin.activity.index') }}" class="dropdown-item">
                                <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Logs de actividad
                            </a>
                            @endcan

                            @can('seguridad.ver')
                            <a href="{{ route('admin.security.dashboard') }}" class="dropdown-item">
                                <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Panel de seguridad
                            </a>
                            @endcan

                            @can('configuracion.ver')
                            <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
                                <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                                Configuración
                            </a>
                            @endcan

                            <!-- Enlace directo al dashboard administrativo -->
                            @role('Super Admin|Admin|Gerente')
                            <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                                <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Panel Admin
                            </a>
                            @endrole
                        </div>
                        @endcanany

                            <!-- Modo oscuro toggle -->
                            <div class="border-t border-gray-100 mt-1 pt-1">
                                <button @click="toggleDarkMode()" class="dropdown-item w-full text-left">
                                    <svg x-show="!darkMode" class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                    </svg>
                                    <svg x-show="darkMode" class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <span x-text="darkMode ? 'Modo claro' : 'Modo oscuro'"></span>
                                </button>
                            </div>

                            <!-- Cerrar sesión -->
                            <div class="border-t border-gray-100 mt-1 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item w-full text-left text-red-600 hover:bg-red-50">
                                        <svg class="mr-3 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menú móvil -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-gestion-700">
            <!-- Dashboard móvil -->
            <a href="{{ route('dashboard') }}" 
               class="mobile-nav-item {{ Request::routeIs('dashboard') ? 'mobile-nav-item-active' : 'mobile-nav-item-inactive' }}">
                Dashboard
            </a>

            <!-- Módulos móvil -->
            @can('clientes.ver')
            <div x-data="{ open: false }">
                <button @click="open = !open" class="mobile-nav-item mobile-nav-item-inactive w-full text-left flex items-center justify-between">
                    <span>Clientes</span>
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div x-show="open" class="ml-4 mt-2 space-y-1">
                    @can('clientes.ver')
                    <a href="{{ route('clientes.index') }}" class="mobile-sub-nav-item">Ver clientes</a>
                    @endcan
                    @can('clientes.crear')
                    <a href="{{ route('clientes.create') }}" class="mobile-sub-nav-item">Nuevo cliente</a>
                    @endcan
                </div>
            </div>
            @endcan

            @can('productos.ver')
            <div x-data="{ open: false }">
                <button @click="open = !open" class="mobile-nav-item mobile-nav-item-inactive w-full text-left flex items-center justify-between">
                    <span>Productos</span>
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div x-show="open" class="ml-4 mt-2 space-y-1">
                    @can('productos.ver')
                    <a href="{{ route('productos.index') }}" class="mobile-sub-nav-item">Inventario</a>
                    @endcan
                    @can('productos.crear')
                    <a href="{{ route('productos.create') }}" class="mobile-sub-nav-item">Agregar producto</a>
                    @endcan
                </div>
            </div>
            @endcan

            @can('reparaciones.ver')
            <div x-data="{ open: false }">
                <button @click="open = !open" class="mobile-nav-item mobile-nav-item-inactive w-full text-left flex items-center justify-between">
                    <span>Reparaciones</span>
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div x-show="open" class="ml-4 mt-2 space-y-1">
                    @can('reparaciones.ver')
                    <a href="{{ route('reparaciones.index') }}" class="mobile-sub-nav-item">Ver reparaciones</a>
                    @endcan
                    @can('reparaciones.crear')
                    <a href="{{ route('reparaciones.create') }}" class="mobile-sub-nav-item">Nueva reparación</a>
                    @endcan
                    @hasrole('Técnico')
                    <a href="{{ route('reparaciones.mis-asignadas') }}" class="mobile-sub-nav-item">Mis asignaciones</a>
                    @endhasrole
                </div>
            </div>
            @endcan

            @can('ventas.ver')
            <div x-data="{ open: false }">
                <button @click="open = !open" class="mobile-nav-item mobile-nav-item-inactive w-full text-left flex items-center justify-between">
                    <span>Ventas</span>
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div x-show="open" class="ml-4 mt-2 space-y-1">
                    @can('ventas.ver')
                    <a href="{{ route('ventas.index') }}" class="mobile-sub-nav-item">Ver ventas</a>
                    @endcan
                    @can('ventas.crear')
                    <a href="{{ route('ventas.create') }}" class="mobile-sub-nav-item">Nueva venta</a>
                    @endcan
                    @hasrole('Vendedor')
                    <a href="{{ route('ventas.mis-ventas') }}" class="mobile-sub-nav-item">Mis ventas</a>
                    @endhasrole
                </div>
            </div>
            @endcan

            @can('reportes.ver')
            <a href="{{ route('reportes.index') }}" 
               class="mobile-nav-item {{ Request::is('reportes*') ? 'mobile-nav-item-active' : 'mobile-nav-item-inactive' }}">
                Reportes
            </a>
            @endcan
        </div>

        <!-- Información de usuario móvil -->
        <div class="pt-4 pb-3 border-t border-gestion-700">
            <div class="flex items-center px-5">
                <div class="flex-shrink-0">
                    @if(auth()->user()->avatar)
                        <img class="h-10 w-10 rounded-full object-cover" 
                             src="{{ Storage::url(auth()->user()->avatar) }}" 
                             alt="{{ auth()->user()->first_name }}">
                    @else
                        <div class="h-10 w-10 rounded-full bg-gestion-600 flex items-center justify-center">
                            <span class="text-sm font-medium text-white">
                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="ml-3">
                    <div class="text-base font-medium leading-none text-white">
                        {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                    </div>
                    <div class="text-sm font-medium leading-none text-gestion-400 mt-1">
                        {{ auth()->user()->email }}
                    </div>
                </div>
            </div>
            <div class="mt-3 px-2 space-y-1">
                <a href="{{ route('profile.show') }}" class="mobile-nav-item mobile-nav-item-inactive">
                    Mi perfil
                </a>
                <a href="{{ route('profile.edit') }}" class="mobile-nav-item mobile-nav-item-inactive">
                    Configuración
                </a>
                @canany(['usuarios.ver', 'auditoria.ver'])
                <a href="{{ route('admin.users.index') }}" class="mobile-nav-item mobile-nav-item-inactive">
                    Administración
                </a>
                @endcanany
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mobile-nav-item mobile-nav-item-inactive w-full text-left text-red-300">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>

@push('styles')
<style>
    .nav-item {
        @apply px-3 py-2 rounded-md text-sm font-medium transition-colors duration-150 flex items-center;
    }
    .nav-item-active {
        @apply bg-gestion-900 text-white;
    }
    .nav-item-inactive {
        @apply text-gestion-300 hover:bg-gestion-700 hover:text-white;
    }
    .dropdown-item {
        @apply flex px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 items-center;
    }
    .mobile-nav-item {
        @apply block px-3 py-2 rounded-md text-base font-medium transition-colors duration-150;
    }
    .mobile-nav-item-active {
        @apply bg-gestion-900 text-white;
    }
    .mobile-nav-item-inactive {
        @apply text-gestion-300 hover:bg-gestion-700 hover:text-white;
    }
    .mobile-sub-nav-item {
        @apply block px-3 py-2 rounded-md text-sm font-medium text-gestion-400 hover:bg-gestion-700 hover:text-white;
    }
</style>
@endpush

@push('scripts')
<script>
function navigationData() {
    return {
        mobileMenuOpen: false,
        darkMode: localStorage.getItem('darkMode') === 'true' || false,
        
        initNavigation() {
            // Aplicar modo oscuro si está habilitado
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
            }
            
            // Cargar notificaciones al inicializar
            this.loadNotifications();
            
            // Cerrar menú móvil al cambiar de ruta
            this.$watch('mobileMenuOpen', (value) => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
        },

        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('darkMode', this.darkMode);
            
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        },

        async loadNotifications() {
            try {
                const response = await fetch('/api/v1/notifications', {
                    headers: {
                        'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]').content}`,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.notifications || [];
                    this.unreadCount = data.unread_count || 0;
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        },

        async markAsRead() {
            if (this.unreadCount === 0) return;
            
            try {
                await fetch('/api/v1/notifications/mark-read', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]').content}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                this.unreadCount = 0;
                this.notifications.forEach(n => n.read_at = new Date().toISOString());
            } catch (error) {
                console.error('Error marking notifications as read:', error);
            }
        },

        async clearAll() {
            try {
                await fetch('/api/v1/notifications/clear', {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]').content}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                this.notifications = [];
                this.unreadCount = 0;
            } catch (error) {
                console.error('Error clearing notifications:', error);
            }
        },

        openNotification(notification) {
            if (notification.action_url) {
                window.location.href = notification.action_url;
            }
        },

        async search() {
            if (this.query.length < 3) {
                this.results = [];
                return;
            }

            try {
                const response = await fetch(`/api/v1/search?q=${encodeURIComponent(this.query)}`, {
                    headers: {
                        'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]').content}`,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.results = data.results || [];
                }
            } catch (error) {
                console.error('Error searching:', error);
                this.results = [];
            }
        },

        getNotificationColor(type) {
            const colors = {
                'info': 'bg-blue-500',
                'success': 'bg-green-500',
                'warning': 'bg-yellow-500',
                'error': 'bg-red-500',
                'security': 'bg-red-600',
                'venta': 'bg-green-600',
                'reparacion': 'bg-yellow-600',
                'cliente': 'bg-blue-600'
            };
            return colors[type] || 'bg-gray-500';
        },

        getNotificationIcon(type) {
            const icons = {
                'info': 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'success': 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'warning': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'error': 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'security': 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                'venta': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
                'reparacion': 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                'cliente': 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'
            };
            return icons[type] || icons.info;
        },

        getSearchResultColor(type) {
            const colors = {
                'cliente': 'bg-blue-500',
                'producto': 'bg-green-500',
                'reparacion': 'bg-yellow-500',
                'venta': 'bg-purple-500'
            };
            return colors[type] || 'bg-gray-500';
        },

        getSearchResultIcon(type) {
            const icons = {
                'cliente': 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'producto': 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                'reparacion': 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                'venta': 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'
            };
            return icons[type] || icons.cliente;
        },

        formatNotificationDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMinutes = Math.floor((now - date) / 60000);
            
            if (diffMinutes < 1) return 'Ahora';
            if (diffMinutes < 60) return `${diffMinutes}m`;
            
            const diffHours = Math.floor(diffMinutes / 60);
            if (diffHours < 24) return `${diffHours}h`;
            
            const diffDays = Math.floor(diffHours / 24);
            if (diffDays < 7) return `${diffDays}d`;
            
            return date.toLocaleDateString('es-PE');
        }
    }
}

// Actualizar notificaciones cada 30 segundos
setInterval(() => {
    const navComponent = document.querySelector('[x-data*="navigationData"]');
    if (navComponent && navComponent._x_dataStack) {
        navComponent._x_dataStack[0].loadNotifications();
    }
}, 30000);

// Limpiar overflow del body al cerrar
window.addEventListener('beforeunload', () => {
    document.body.style.overflow = '';
});
</script>
@endpush