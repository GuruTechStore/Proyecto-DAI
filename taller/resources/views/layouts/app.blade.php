{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Taller Pro') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div x-data="appLayout()" x-init="init()" class="min-h-screen">
        
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 flex-shrink-0 bg-white dark:bg-gray-800 shadow-lg border-r border-gray-200 dark:border-gray-700 transition-all duration-300"
             :class="sidebarOpen ? 'w-64' : 'w-16'">
            
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tools text-white text-sm"></i>
                    </div>
                    <h1 x-show="sidebarOpen" x-transition class="text-xl font-bold text-gray-900 dark:text-white">
                        Taller oño
                    </h1>
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-bars text-gray-600 dark:text-gray-300"></i>
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
                
                <!-- Dashboard -->
                <div>
                    <h3 x-show="sidebarOpen" class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        Principal
                    </h3>
                    <a href="{{ route('dashboard') }}" 
                       class="group flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                        <span x-show="sidebarOpen" x-transition>Dashboard</span>
                    </a>
                </div>
                
                <!-- Gestión Principal -->
                <div>
                    <h3 x-show="sidebarOpen" class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 mt-6">
                        Gestión Principal
                    </h3>
                    
                    <!-- Clientes -->
                    @if(Route::has('clientes.index'))
                        @can('clientes.ver')
                        <a href="{{ route('clientes.index') }}" 
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('clientes.*') ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="fas fa-users w-5 h-5 mr-3"></i>
                            <span x-show="sidebarOpen" x-transition>Clientes</span>
                        </a>
                        @endcan
                    @endif
                    
                    <!-- Productos -->
                    @if(Route::has('productos.index'))
                        @can('productos.ver')
                        <a href="{{ route('productos.index') }}" 
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('productos.*') ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="fas fa-box w-5 h-5 mr-3"></i>
                            <span x-show="sidebarOpen" x-transition>Productos</span>
                        </a>
                        @endcan
                    @endif
                    
                    <!-- Proveedores -->
                    @if(Route::has('proveedores.index'))
                        @can('proveedores.ver')
                        <a href="{{ route('proveedores.index') }}" 
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('proveedores.*') ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="fas fa-truck w-5 h-5 mr-3"></i>
                            <span x-show="sidebarOpen" x-transition>Proveedores</span>
                        </a>
                        @endcan
                    @endif
                </div>
                
                <!-- Operaciones -->
                <div>
                    <h3 x-show="sidebarOpen" class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 mt-6">
                        Operaciones
                    </h3>
                    
                    <!-- Reparaciones -->
                    @if(Route::has('reparaciones.index'))
                        @can('reparaciones.ver')
                        <a href="{{ route('reparaciones.index') }}" 
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('reparaciones.*') ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="fas fa-tools w-5 h-5 mr-3"></i>
                            <span x-show="sidebarOpen" x-transition>Reparaciones</span>
                        </a>
                        @endcan
                    @endif
                    
                    <!-- Ventas -->
                    @if(Route::has('ventas.index'))
                        @can('ventas.ver')
                        <a href="{{ route('ventas.index') }}" 
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('ventas.*') ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="fas fa-shopping-cart w-5 h-5 mr-3"></i>
                            <span x-show="sidebarOpen" x-transition>Ventas</span>
                        </a>
                        @endcan
                    @endif
                </div>
                
                <!-- Administración -->
                <div>
                    <h3 x-show="sidebarOpen" class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2 mt-6">
                        Administración
                    </h3>
                    
                    <!-- Empleados -->
                    @if(Route::has('empleados.index'))
                        @can('empleados.ver')
                        <a href="{{ route('empleados.index') }}" 
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('empleados.*') ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="fas fa-user-tie w-5 h-5 mr-3"></i>
                            <span x-show="sidebarOpen" x-transition>Empleados</span>
                        </a>
                        @endcan
                    @endif
                    
                    <!-- Usuarios -->
                    @if(Route::has('usuarios.index'))
                        @can('usuarios.ver')
                        <a href="{{ route('usuarios.index') }}" 
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('usuarios.*') ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="fas fa-user-cog w-5 h-5 mr-3"></i>
                            <span x-show="sidebarOpen" x-transition>Usuarios</span>
                        </a>
                        @endcan
                    @endif
                    
                    <!-- Reportes -->
                    @if(Route::has('reportes.index'))
                        @can('reportes.ver')
                        <a href="{{ route('reportes.index') }}" 
                           class="group flex items-center px-2 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('reportes.*') ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="fas fa-chart-bar w-5 h-5 mr-3"></i>
                            <span x-show="sidebarOpen" x-transition>Reportes</span>
                        </a>
                        @endcan
                    @endif
                </div>
            </nav>
            
            <!-- User Menu -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-gray-600 dark:text-gray-300 text-sm"></i>
                    </div>
                    <div x-show="sidebarOpen" x-transition class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ Auth::user()->name ?? 'Usuario' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ Auth::user()->email ?? 'email@example.com' }}
                        </p>
                    </div>
                </div>
                
                <div x-show="sidebarOpen" x-transition class="mt-3 space-y-1">
                    @if(Route::has('profile.show'))
                    <a href="{{ route('profile.show') }}" 
                       class="group flex items-center px-2 py-2 text-xs font-medium rounded-md text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fas fa-user-circle w-4 h-4 mr-2"></i>
                        Mi Perfil
                    </a>
                    @endif
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="group flex items-center w-full px-2 py-2 text-xs font-medium rounded-md text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700">
                            <i class="fas fa-sign-out-alt w-4 h-4 mr-2"></i>
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main content area - ESTA ES LA PARTE CLAVE QUE FALTABA -->
        <div class="transition-all duration-300" :class="sidebarOpen ? 'ml-64' : 'ml-16'">
            
            <!-- Top Header -->
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        
                        <!-- Breadcrumb -->
                        @hasSection('breadcrumb')
                            <nav class="flex" aria-label="Breadcrumb">
                                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                    <li class="inline-flex items-center">
                                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                                            <i class="fas fa-home w-4 h-4 mr-2"></i>
                                            Dashboard
                                        </a>
                                    </li>
                                    @yield('breadcrumb')
                                </ol>
                            </nav>
                        @endif
                        
                        <!-- Right side actions -->
                        <div class="flex items-center space-x-4">
                            <!-- Dark mode toggle -->
                            <button @click="darkMode = !darkMode" 
                                    class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg">
                                <i class="fas fa-moon dark:hidden w-4 h-4"></i>
                                <i class="fas fa-sun hidden dark:block w-4 h-4"></i>
                            </button>
                            
                            <!-- Notifications -->
                            <button class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 rounded-lg relative">
                                <i class="fas fa-bell w-4 h-4"></i>
                                <!-- Notification badge -->
                                <span class="absolute top-1 right-1 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-white dark:ring-gray-800"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 relative z-0 overflow-y-auto focus:outline-none">
                <div class="py-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts at bottom -->
    @stack('scripts')
    
    <!-- Global JavaScript Functions -->
    <script>
        function appLayout() {
            return {
                sidebarOpen: localStorage.getItem('sidebarOpen') === 'true' || true,
                
                init() {
                    this.$watch('sidebarOpen', value => {
                        localStorage.setItem('sidebarOpen', value);
                    });
                }
            }
        }

        // Global notification functions
        function showSuccessNotification(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Éxito!',
                    text: message,
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        }

        function showErrorNotification(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error',
                    text: message,
                    icon: 'error',
                    confirmButtonColor: '#EF4444'
                });
            }
        }

        function showConfirmDialog(title, text, confirmText = 'Confirmar') {
            if (typeof Swal !== 'undefined') {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3B82F6',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: confirmText,
                    cancelButtonText: 'Cancelar'
                });
            } else {
                return { isConfirmed: confirm(text) };
            }
        }
    </script>
</body>
</html>