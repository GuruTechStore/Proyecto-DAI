{{-- resources/views/modules/proveedores/partials/stats.blade.php --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    
    <!-- Total Proveedores -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0a2 2 0 002-2v-4m-2 2a2 2 0 00-2-2h-4a2 2 0 00-2 2m8 0V9a2 2 0 00-2-2M9 21V9a2 2 0 012-2h4a2 2 0 012 2v12" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Proveedores</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total || '0'">
                    {{ $stats['total'] ?? 0 }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <span class="text-green-600 dark:text-green-400" x-text="stats.crecimiento_mensual ? '+' + stats.crecimiento_mensual + '%' : ''">
                        @if(isset($stats['crecimiento_mensual']) && $stats['crecimiento_mensual'] > 0)
                            +{{ $stats['crecimiento_mensual'] }}%
                        @endif
                    </span>
                    <span class="ml-1">este mes</span>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Proveedores Activos -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Proveedores Activos</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.activos || '0'">
                    {{ $stats['activos'] ?? 0 }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <span x-text="stats.total ? Math.round((stats.activos / stats.total) * 100) + '%' : '0%'">
                        @if(isset($stats['total']) && $stats['total'] > 0)
                            {{ round(($stats['activos'] / $stats['total']) * 100) }}%
                        @else
                            0%
                        @endif
                    </span>
                    <span class="ml-1">del total</span>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Proveedores con RUC -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Con RUC</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.con_ruc || '0'">
                    {{ $stats['con_ruc'] ?? 0 }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <span x-text="stats.total ? Math.round((stats.con_ruc / stats.total) * 100) + '%' : '0%'">
                        @if(isset($stats['total']) && $stats['total'] > 0)
                            {{ round(($stats['con_ruc'] / $stats['total']) * 100) }}%
                        @else
                            0%
                        @endif
                    </span>
                    <span class="ml-1">con RUC</span>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Agregados Este Mes -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Este Mes</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.este_mes || '0'">
                    {{ $stats['este_mes'] ?? 0 }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    <span class="capitalize">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                </p>
            </div>
        </div>
    </div>
    
</div>

<!-- Gráfico de Tendencias (Opcional) -->
@if(isset($stats['tendencias']) && count($stats['tendencias']) > 0)
<div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Tendencia de Registro de Proveedores
        </h3>
        <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
            <div class="flex items-center">
                <div class="w-3 h-3 bg-gestion-500 rounded-full mr-2"></div>
                <span>Nuevos proveedores</span>
            </div>
        </div>
    </div>
    
    <div class="h-64">
        <canvas id="tendenciasChart" class="w-full h-full"></canvas>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos de tendencias
    const tendenciasData = @json($stats['tendencias'] ?? []);
    
    if (tendenciasData.length > 0) {
        const ctx = document.getElementById('tendenciasChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: tendenciasData.map(item => item.mes),
                datasets: [{
                    label: 'Nuevos Proveedores',
                    data: tendenciasData.map(item => item.total),
                    borderColor: 'rgb(124, 58, 237)',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endif

<!-- Estadísticas Detalladas -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    
    <!-- Top Proveedores por Productos -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Top Proveedores por Productos
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Proveedores con más productos registrados
            </p>
        </div>
        
        <div class="p-6">
            @if(isset($stats['top_productos']) && count($stats['top_productos']) > 0)
                <div class="space-y-4">
                    @foreach($stats['top_productos'] as $index => $proveedor)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 bg-gestion-100 dark:bg-gestion-800 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-gestion-600 dark:text-gestion-400">
                                    {{ $index + 1 }}
                                </span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $proveedor['nombre'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $proveedor['productos_count'] }} productos
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-gestion-500 h-2 rounded-full" 
                                     style="width: {{ ($proveedor['productos_count'] / ($stats['max_productos'] ?? 1)) * 100 }}%"></div>
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $proveedor['productos_count'] }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No hay datos de productos disponibles
                    </p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Distribución por Estado -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Distribución por Estado
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Estado actual de los proveedores
            </p>
        </div>
        
        <div class="p-6">
            <div class="space-y-4">
                <!-- Activos -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Activos</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                            <div class="bg-green-500 h-2 rounded-full" 
                                 style="width: {{ isset($stats['total']) && $stats['total'] > 0 ? (($stats['activos'] ?? 0) / $stats['total']) * 100 : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-8">
                            {{ $stats['activos'] ?? 0 }}
                        </span>
                    </div>
                </div>
                
                <!-- Inactivos -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-red-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Inactivos</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                            <div class="bg-red-500 h-2 rounded-full" 
                                 style="width: {{ isset($stats['total']) && $stats['total'] > 0 ? (($stats['inactivos'] ?? 0) / $stats['total']) * 100 : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-8">
                            {{ $stats['inactivos'] ?? 0 }}
                        </span>
                    </div>
                </div>
                
                <!-- Con RUC -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-yellow-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Con RUC</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                            <div class="bg-yellow-500 h-2 rounded-full" 
                                 style="width: {{ isset($stats['total']) && $stats['total'] > 0 ? (($stats['con_ruc'] ?? 0) / $stats['total']) * 100 : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-8">
                            {{ $stats['con_ruc'] ?? 0 }}
                        </span>
                    </div>
                </div>
                
                <!-- Sin RUC -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-4 h-4 bg-gray-500 rounded-full mr-3"></div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Sin RUC</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                            <div class="bg-gray-500 h-2 rounded-full" 
                                 style="width: {{ isset($stats['total']) && $stats['total'] > 0 ? (($stats['sin_ruc'] ?? 0) / $stats['total']) * 100 : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-8">
                            {{ $stats['sin_ruc'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>