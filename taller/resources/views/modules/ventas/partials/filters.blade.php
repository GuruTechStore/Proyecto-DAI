{{-- resources/views/modules/ventas/partials/filters.blade.php --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-data="ventasFilters()">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <svg class="w-5 h-5 mr-2 text-gestion-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filtros de Búsqueda
        </h3>
        <button @click="toggleFilters" 
                class="text-sm text-gestion-600 hover:text-gestion-700 font-medium">
            <span x-text="showFilters ? 'Ocultar' : 'Mostrar'"></span>
        </button>
    </div>

    <div x-show="showFilters" x-transition class="space-y-6">
        <!-- Search Bar -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="col-span-1 lg:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Búsqueda General
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" 
                           id="search" 
                           x-model="filters.search"
                           @input.debounce.300ms="applyFilters"
                           class="block w-full pl-10 pr-3 py-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500"
                           placeholder="Buscar por número, cliente, producto...">
                </div>
            </div>

            <div class="col-span-1">
                <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mostrar
                </label>
                <select id="per_page" 
                        x-model="filters.per_page"
                        @change="applyFilters"
                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="10">10 registros</option>
                    <option value="25">25 registros</option>
                    <option value="50">50 registros</option>
                    <option value="100">100 registros</option>
                </select>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <!-- Date Range -->
            <div class="col-span-1">
                <label for="fecha_desde" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Fecha Desde
                </label>
                <input type="date" 
                       id="fecha_desde" 
                       x-model="filters.fecha_desde"
                       @change="applyFilters"
                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
            </div>

            <div class="col-span-1">
                <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Fecha Hasta
                </label>
                <input type="date" 
                       id="fecha_hasta" 
                       x-model="filters.fecha_hasta"
                       @change="applyFilters"
                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
            </div>

            <!-- Status Filter -->
            <div class="col-span-1">
                <label for="estado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Estado
                </label>
                <select id="estado" 
                        x-model="filters.estado"
                        @change="applyFilters"
                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos los estados</option>
                    <option value="completado">Completado</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="cancelado">Cancelado</option>
                    <option value="devuelto">Devuelto</option>
                </select>
            </div>

            <!-- Payment Method Filter -->
            <div class="col-span-1">
                <label for="metodo_pago" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Método de Pago
                </label>
                <select id="metodo_pago" 
                        x-model="filters.metodo_pago"
                        @change="applyFilters"
                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos los métodos</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="credito">Crédito</option>
                </select>
            </div>
        </div>

        <!-- Additional Filters -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Customer Filter -->
            <div class="col-span-1">
                <label for="cliente" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Cliente
                </label>
                <select id="cliente" 
                        x-model="filters.cliente_id"
                        @change="applyFilters"
                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos los clientes</option>
                    @foreach($clientes ?? [] as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Employee Filter -->
            <div class="col-span-1">
                <label for="empleado" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Vendedor
                </label>
                <select id="empleado" 
                        x-model="filters.empleado_id"
                        @change="applyFilters"
                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                    <option value="">Todos los vendedores</option>
                    @foreach($empleados ?? [] as $empleado)
                        <option value="{{ $empleado->id }}">{{ $empleado->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Amount Range -->
            <div class="col-span-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Rango de Monto
                </label>
                <div class="flex space-x-2">
                    <input type="number" 
                           x-model="filters.monto_min"
                           @input.debounce.500ms="applyFilters"
                           placeholder="Mín"
                           class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 text-sm">
                    <input type="number" 
                           x-model="filters.monto_max"
                           @input.debounce.500ms="applyFilters"
                           placeholder="Máx"
                           class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 text-sm">
                </div>
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Filtros Rápidos</h4>
            <div class="flex flex-wrap gap-2">
                <button @click="setQuickFilter('today')"
                        :class="activeQuickFilter === 'today' ? 'bg-gestion-100 text-gestion-800 border-gestion-200' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                        class="inline-flex items-center px-3 py-1.5 border rounded-full text-xs font-medium transition-colors dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Hoy
                </button>
                <button @click="setQuickFilter('yesterday')"
                        :class="activeQuickFilter === 'yesterday' ? 'bg-gestion-100 text-gestion-800 border-gestion-200' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                        class="inline-flex items-center px-3 py-1.5 border rounded-full text-xs font-medium transition-colors dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Ayer
                </button>
                <button @click="setQuickFilter('week')"
                        :class="activeQuickFilter === 'week' ? 'bg-gestion-100 text-gestion-800 border-gestion-200' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                        class="inline-flex items-center px-3 py-1.5 border rounded-full text-xs font-medium transition-colors dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Esta semana
                </button>
                <button @click="setQuickFilter('month')"
                        :class="activeQuickFilter === 'month' ? 'bg-gestion-100 text-gestion-800 border-gestion-200' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                        class="inline-flex items-center px-3 py-1.5 border rounded-full text-xs font-medium transition-colors dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Este mes
                </button>
                <button @click="setQuickFilter('pending')"
                        :class="activeQuickFilter === 'pending' ? 'bg-gestion-100 text-gestion-800 border-gestion-200' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                        class="inline-flex items-center px-3 py-1.5 border rounded-full text-xs font-medium transition-colors dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Pendientes
                </button>
                <button @click="setQuickFilter('credit')"
                        :class="activeQuickFilter === 'credit' ? 'bg-gestion-100 text-gestion-800 border-gestion-200' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                        class="inline-flex items-center px-3 py-1.5 border rounded-full text-xs font-medium transition-colors dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    A crédito
                </button>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
            <button @click="clearFilters"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Limpiar Filtros
            </button>

            <div class="flex space-x-2">
                <button @click="exportResults('excel')"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-green-700 bg-green-100 border border-green-200 rounded-lg hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors dark:bg-green-900 dark:text-green-300 dark:border-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Exportar Excel
                </button>

                <button @click="exportResults('pdf')"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-100 border border-red-200 rounded-lg hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors dark:bg-red-900 dark:text-red-300 dark:border-red-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707L13.293 3.293A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Exportar PDF
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function ventasFilters() {
    return {
        showFilters: true,
        activeQuickFilter: null,
        
        filters: {
            search: '',
            per_page: 25,
            fecha_desde: '',
            fecha_hasta: '',
            estado: '',
            metodo_pago: '',
            cliente_id: '',
            empleado_id: '',
            monto_min: '',
            monto_max: ''
        },

        toggleFilters() {
            this.showFilters = !this.showFilters;
        },

        setQuickFilter(filter) {
            this.activeQuickFilter = filter;
            const today = new Date();
            
            switch(filter) {
                case 'today':
                    this.filters.fecha_desde = today.toISOString().split('T')[0];
                    this.filters.fecha_hasta = today.toISOString().split('T')[0];
                    this.filters.estado = '';
                    break;
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    this.filters.fecha_desde = yesterday.toISOString().split('T')[0];
                    this.filters.fecha_hasta = yesterday.toISOString().split('T')[0];
                    this.filters.estado = '';
                    break;
                case 'week':
                    const startOfWeek = new Date(today);
                    startOfWeek.setDate(today.getDate() - today.getDay());
                    this.filters.fecha_desde = startOfWeek.toISOString().split('T')[0];
                    this.filters.fecha_hasta = today.toISOString().split('T')[0];
                    this.filters.estado = '';
                    break;
                case 'month':
                    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                    this.filters.fecha_desde = startOfMonth.toISOString().split('T')[0];
                    this.filters.fecha_hasta = today.toISOString().split('T')[0];
                    this.filters.estado = '';
                    break;
                case 'pending':
                    this.filters.estado = 'pendiente';
                    this.filters.fecha_desde = '';
                    this.filters.fecha_hasta = '';
                    break;
                case 'credit':
                    this.filters.metodo_pago = 'credito';
                    this.filters.fecha_desde = '';
                    this.filters.fecha_hasta = '';
                    break;
            }
            
            this.applyFilters();
        },

        clearFilters() {
            this.filters = {
                search: '',
                per_page: 25,
                fecha_desde: '',
                fecha_hasta: '',
                estado: '',
                metodo_pago: '',
                cliente_id: '',
                empleado_id: '',
                monto_min: '',
                monto_max: ''
            };
            this.activeQuickFilter = null;
            this.applyFilters();
        },

        applyFilters() {
            // Build query string
            const params = new URLSearchParams();
            
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    params.append(key, this.filters[key]);
                }
            });
            
            // Update URL and reload
            const url = new URL(window.location);
            url.search = params.toString();
            window.location.href = url.toString();
        },

        exportResults(format) {
            const params = new URLSearchParams();
            
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    params.append(key, this.filters[key]);
                }
            });
            
            params.append('export', format);
            
            const exportUrl = `{{ route('ventas.index') }}?${params.toString()}`;
            window.open(exportUrl, '_blank');
        }
    };
}
</script>