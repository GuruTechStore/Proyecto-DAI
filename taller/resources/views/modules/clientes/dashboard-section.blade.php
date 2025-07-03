{{-- resources/views/modules/clientes/dashboard-section.blade.php --}}
<div class="space-y-6">
    <!-- Estadísticas de Clientes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Clientes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Total Clientes</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalClientes ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Nuevos este Mes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Nuevos este Mes</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($nuevosClientesMes ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Clientes Activos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Clientes Activos</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($clientesActivos ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Top Cliente del Mes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Top Cliente</p>
                    @if(isset($topClientesMes) && $topClientesMes->count() > 0)
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                            {{ $topClientesMes->first()->nombres }} {{ $topClientesMes->first()->apellidos }}
                        </p>
                        <p class="text-xs text-gray-500">${{ number_format($topClientesMes->first()->total_compras, 2) }}</p>
                    @else
                        <p class="text-sm text-gray-500">Sin datos</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Clientes Recientes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Clientes Recientes</h3>
                    @can('clientes.crear')
                    <a href="{{ route('clientes.create') }}" 
                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuevo Cliente
                    </a>
                    @endcan
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($recentClientes ?? collect() as $cliente)
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">
                                            {{ strtoupper(substr($cliente->nombres, 0, 1)) }}{{ strtoupper(substr($cliente->apellidos, 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $cliente->nombres }} {{ $cliente->apellidos }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">{{ $cliente->email }}</p>
                                    <p class="text-xs text-gray-400">{{ $cliente->telefono }}</p>
                                </div>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <p class="text-xs text-gray-400">
                                    {{ $cliente->created_at->diffForHumans() }}
                                </p>
                                @can('clientes.ver')
                                <a href="{{ route('clientes.show', $cliente) }}" 
                                   class="text-xs text-gestion-600 hover:text-gestion-800">
                                    Ver perfil
                                </a>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay clientes recientes</h3>
                            <p class="mt-1 text-sm text-gray-500">Comienza agregando tu primer cliente.</p>
                            @can('clientes.crear')
                            <div class="mt-6">
                                <a href="{{ route('clientes.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Agregar Cliente
                                </a>
                            </div>
                            @endcan
                        </div>
                    @endforelse
                </div>
                
                @if(($recentClientes ?? collect())->count() > 0)
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('clientes.index') }}" 
                       class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Ver todos los clientes
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Top Clientes del Mes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Clientes del Mes</h3>
                <p class="text-sm text-gray-500">Clientes con mayor volumen de compras</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($topClientesMes ?? collect() as $index => $cliente)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                                        {{ $index === 0 ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $index === 1 ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $index === 2 ? 'bg-orange-100 text-orange-800' : '' }}
                                        {{ $index > 2 ? 'bg-blue-100 text-blue-800' : '' }}">
                                        <span class="text-xs font-bold">{{ $index + 1 }}</span>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $cliente->nombres }} {{ $cliente->apellidos }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        ${{ number_format($cliente->total_compras, 2) }} en compras
                                    </p>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                @if($index === 0)
                                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin datos de compras</h3>
                            <p class="mt-1 text-sm text-gray-500">No hay compras registradas este mes.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Acciones Rápidas</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @can('clientes.crear')
                <a href="{{ route('clientes.create') }}" 
                   class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-gestion-500 hover:bg-gestion-50 dark:hover:bg-gray-700 transition-colors group">
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-gestion-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-gestion-600">Nuevo Cliente</span>
                </a>
                @endcan

                @can('clientes.ver')
                <a href="{{ route('clientes.index') }}" 
                   class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-gestion-500 hover:bg-gestion-50 dark:hover:bg-gray-700 transition-colors group">
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-gestion-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-gestion-600">Ver Todos</span>
                </a>
                @endcan

                @can('reportes.clientes')
                <a href="{{ route('reportes.clientes.index') }}" 
                   class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-gestion-500 hover:bg-gestion-50 dark:hover:bg-gray-700 transition-colors group">
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-gestion-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-gestion-600">Reportes</span>
                </a>
                @endcan

                <a href="#" onclick="window.location.reload()" 
                   class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-gestion-500 hover:bg-gestion-50 dark:hover:bg-gray-700 transition-colors group">
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-gestion-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-gestion-600">Actualizar</span>
                </a>
            </div>
        </div>
    </div>
</div>