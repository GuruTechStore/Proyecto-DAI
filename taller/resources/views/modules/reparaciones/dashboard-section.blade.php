{{-- resources/views/modules/reparaciones/dashboard-section.blade.php - PARTE 1 --}}
<div class="space-y-6">
    <!-- Estadísticas de Reparaciones -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Reparaciones Activas -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Reparaciones Activas</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reparacionesActivas ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Pendientes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Pendientes</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reparacionesPendientes ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Completadas -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Completadas</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($reparacionesCompletadas ?? 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Ingresos del Mes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">Ingresos del Mes</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($ingresoReparacionesMes ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Estado de Reparaciones -->
    @if(isset($reparacionesPorEstado) && count($reparacionesPorEstado) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Distribución por Estado</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @php
                    $total = array_sum($reparacionesPorEstado);
                    $colores = [
                        'pendiente' => ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-700'],
                        'en_proceso' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-700'],
                        'completada' => ['bg' => 'bg-green-500', 'text' => 'text-green-700'],
                        'cancelada' => ['bg' => 'bg-red-500', 'text' => 'text-red-700'],
                        'entregada' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-700'],
                    ];
                @endphp
                @foreach($reparacionesPorEstado as $estado => $cantidad)
                    @php
                        $porcentaje = $total > 0 ? ($cantidad / $total) * 100 : 0;
                        $color = $colores[$estado] ?? ['bg' => 'bg-gray-500', 'text' => 'text-gray-700'];
                    @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 rounded-full {{ $color['bg'] }}"></div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white capitalize">
                                    {{ str_replace('_', ' ', $estado) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-sm text-gray-500">{{ $cantidad }}</span>
                            <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $color['bg'] }}" style="width: {{ $porcentaje }}%"></div>
                            </div>
                            <span class="text-sm {{ $color['text'] }} w-12 text-right">{{ number_format($porcentaje, 1) }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Contenido Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Reparaciones Recientes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reparaciones Recientes</h3>
                    @can('reparaciones.crear')
                    <a href="{{ route('reparaciones.create') }}" 
                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nueva Reparación
                    </a>
                    @endcan
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($recentReparaciones ?? collect() as $reparacion)
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        Orden #{{ $reparacion->numero_orden ?? $reparacion->codigo_ticket ?? $reparacion->id }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        Cliente: {{ $reparacion->cliente->nombre ?? $reparacion->cliente->nombres ?? 'Sin cliente' }} {{ $reparacion->cliente->apellido ?? $reparacion->cliente->apellidos ?? '' }}
                                    </p>
                                    <p class="text-xs text-gray-400 truncate">
                                        {{ $reparacion->equipo->tipo ?? $reparacion->tipo_equipo ?? 'Equipo' }}: {{ $reparacion->equipo->marca ?? $reparacion->marca ?? '' }} {{ $reparacion->equipo->modelo ?? $reparacion->modelo ?? '' }}
                                    </p>
                                    <div class="flex items-center space-x-4 text-xs mt-1">
                                        <span class="text-green-600 font-medium">${{ number_format($reparacion->costo_total ?? $reparacion->costo ?? 0, 2) }}</span>
                                        <span class="text-gray-500">{{ \Carbon\Carbon::parse($reparacion->fecha_ingreso ?? $reparacion->created_at)->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            {{-- resources/views/modules/reparaciones/dashboard-section.blade.php - PARTE 2 (continuación) --}}
                            <div class="flex-shrink-0 text-right">
                                <div class="flex items-center space-x-2">
                                    @switch($reparacion->estado)
                                        @case('pendiente')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pendiente
                                            </span>
                                            @break
                                        @case('en_proceso')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                En Proceso
                                            </span>
                                            @break
                                        @case('completada')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Completada
                                            </span>
                                            @break
                                        @case('entregada')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Entregada
                                            </span>
                                            @break
                                        @case('cancelada')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Cancelada
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ ucfirst($reparacion->estado) }}
                                            </span>
                                    @endswitch
                                </div>
                                @can('reparaciones.ver')
                                <a href="{{ route('reparaciones.show', $reparacion) }}" 
                                   class="text-xs text-gestion-600 hover:text-gestion-800 block mt-1">
                                    Ver detalles
                                </a>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay reparaciones recientes</h3>
                            <p class="mt-1 text-sm text-gray-500">Comienza registrando tu primera reparación.</p>
                            @can('reparaciones.crear')
                            <div class="mt-6">
                                <a href="{{ route('reparaciones.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Nueva Reparación
                                </a>
                            </div>
                            @endcan
                        </div>
                    @endforelse
                </div>
                
                @if(($recentReparaciones ?? collect())->count() > 0)
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('reparaciones.index') }}" 
                       class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Ver todas las reparaciones
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Reparaciones por Prioridad -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reparaciones Urgentes</h3>
                <p class="text-sm text-gray-500">Que requieren atención inmediata</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    {{-- Reparaciones urgentes basadas en fechas de entrega próximas --}}
                    @php
                        $reparacionesUrgentes = ($recentReparaciones ?? collect())->filter(function($rep) {
                            return $rep->estado === 'pendiente' && 
                                   isset($rep->fecha_entrega_estimada) && 
                                   \Carbon\Carbon::parse($rep->fecha_entrega_estimada)->isPast();
                        })->take(5);
                    @endphp
                    
                    @forelse($reparacionesUrgentes as $reparacion)
                        <div class="flex items-center justify-between py-3 bg-red-50 dark:bg-red-900/20 rounded-lg px-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L4.349 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        Orden #{{ $reparacion->numero_orden ?? $reparacion->codigo_ticket ?? $reparacion->id }}
                                    </p>
                                    <p class="text-xs text-red-600 dark:text-red-400">
                                        Fecha límite vencida
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Urgente
                            </span>
                        </div>
                    @empty
                        {{-- Mostrar reparaciones en proceso como alternativa --}}
                        @php
                            $reparacionesEnProceso = ($recentReparaciones ?? collect())->filter(function($rep) {
                                return $rep->estado === 'en_proceso';
                            })->take(5);
                        @endphp
                        
                        @forelse($reparacionesEnProceso as $reparacion)
                            <div class="flex items-center justify-between py-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-bold text-blue-600">{{ $loop->iteration }}</span>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            Orden #{{ $reparacion->numero_orden ?? $reparacion->codigo_ticket ?? $reparacion->id }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $reparacion->cliente->nombre ?? $reparacion->cliente->nombres ?? 'Sin cliente' }} {{ $reparacion->cliente->apellido ?? $reparacion->cliente->apellidos ?? '' }}
                                        </p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    En Proceso
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin reparaciones urgentes</h3>
                                <p class="mt-1 text-sm text-gray-500">Todas las reparaciones están al día.</p>
                            </div>
                        @endforelse
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    {{-- resources/views/modules/reparaciones/dashboard-section.blade.php - PARTE 3 FINAL --}}

    <!-- Acciones Rápidas -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Acciones Rápidas</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @can('reparaciones.crear')
                <a href="{{ route('reparaciones.create') }}" 
                   class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-gestion-500 hover:bg-gestion-50 dark:hover:bg-gray-700 transition-colors group">
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-gestion-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-gestion-600">Nueva Reparación</span>
                </a>
                @endcan

                @can('reparaciones.ver')
                <a href="{{ route('reparaciones.index') }}" 
                   class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-gestion-500 hover:bg-gestion-50 dark:hover:bg-gray-700 transition-colors group">
                    <svg class="w-8 h-8 text-gray-400 group-hover:text-gestion-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-gestion-600">Ver Todas</span>
                </a>
                @endcan

                @can('reportes.reparaciones')
                <a href="{{ route('reportes.reparaciones.index') }}" 
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

    <!-- Resumen de Tiempos de Reparación -->
    @if(($recentReparaciones ?? collect())->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Métricas de Rendimiento</h3>
            <p class="text-sm text-gray-500">Tiempos promedio y eficiencia del servicio</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Tiempo Promedio -->
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        @php
                            $reparacionesCompletadasConTiempo = ($recentReparaciones ?? collect())->filter(function($rep) {
                                return $rep->estado === 'completada' && 
                                       isset($rep->fecha_entrega) && 
                                       isset($rep->fecha_ingreso);
                            });
                            
                            $tiempoPromedio = 0;
                            if ($reparacionesCompletadasConTiempo->count() > 0) {
                                $totalDias = $reparacionesCompletadasConTiempo->sum(function($rep) {
                                    return \Carbon\Carbon::parse($rep->fecha_entrega)->diffInDays(\Carbon\Carbon::parse($rep->fecha_ingreso));
                                });
                                $tiempoPromedio = round($totalDias / $reparacionesCompletadasConTiempo->count(), 1);
                            }
                        @endphp
                        {{ $tiempoPromedio }} días
                    </div>
                    <div class="text-sm text-gray-500">Tiempo Promedio</div>
                </div>

                <!-- Eficiencia -->
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        @php
                            $totalReparaciones = ($recentReparaciones ?? collect())->count();
                            $reparacionesCompletadas = ($recentReparaciones ?? collect())->where('estado', 'completada')->count();
                            $eficiencia = $totalReparaciones > 0 ? round(($reparacionesCompletadas / $totalReparaciones) * 100, 1) : 0;
                        @endphp
                        {{ $eficiencia }}%
                    </div>
                    <div class="text-sm text-gray-500">Tasa de Éxito</div>
                </div>

                <!-- Satisfacción -->
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        @php
                            // Simulamos una calificación promedio basada en reparaciones completadas
                            $satisfaccion = $reparacionesCompletadas > 0 ? 4.5 : 0;
                        @endphp
                        {{ $satisfaccion }}/5
                    </div>
                    <div class="text-sm text-gray-500">Satisfacción</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Próximas Entregas -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Próximas Entregas</h3>
            <p class="text-sm text-gray-500">Reparaciones listas para entregar</p>
        </div>
        <div class="p-6">
            @php
                $reparacionesListas = ($recentReparaciones ?? collect())->filter(function($rep) {
                    return $rep->estado === 'completada';
                })->take(3);
            @endphp
            
            @forelse($reparacionesListas as $reparacion)
                <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                Orden #{{ $reparacion->numero_orden ?? $reparacion->codigo_ticket ?? $reparacion->id }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $reparacion->cliente->nombre ?? $reparacion->cliente->nombres ?? 'Cliente' }} {{ $reparacion->cliente->apellido ?? $reparacion->cliente->apellidos ?? '' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Lista
                        </span>
                        @can('reparaciones.editar')
                        <div class="mt-1">
                            <button onclick="marcarComoEntregada({{ $reparacion->id }})" 
                                    class="text-xs text-gestion-600 hover:text-gestion-800">
                                Marcar entregada
                            </button>
                        </div>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin entregas pendientes</h3>
                    <p class="mt-1 text-sm text-gray-500">No hay reparaciones completadas pendientes de entrega.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    function marcarComoEntregada(reparacionId) {
        if (confirm('¿Está seguro de marcar esta reparación como entregada?')) {
            fetch(`/reparaciones/${reparacionId}/entregar`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error al actualizar la reparación: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        }
    }
</script>
@endpush