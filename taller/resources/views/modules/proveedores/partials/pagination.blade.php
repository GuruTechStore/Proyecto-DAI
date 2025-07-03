{{-- resources/views/modules/proveedores/partials/pagination.blade.php --}}
<div class="flex flex-col sm:flex-row items-center justify-between">
    
    <!-- Info -->
    <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
        <span>Mostrando</span>
        <span class="font-medium mx-1" x-text="pagination.from"></span>
        <span>a</span>
        <span class="font-medium mx-1" x-text="pagination.to"></span>
        <span>de</span>
        <span class="font-medium mx-1" x-text="pagination.total"></span>
        <span>resultados</span>
    </div>
    
    <!-- Navigation -->
    <div class="flex items-center space-x-2 mt-4 sm:mt-0">
        
        <!-- Previous Button -->
        <button @click="goToPage(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                :class="pagination.current_page <= 1 ? 
                    'opacity-50 cursor-not-allowed' : 
                    'hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'"
                class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-l-md transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="ml-1 hidden sm:inline">Anterior</span>
        </button>
        
        <!-- Page Numbers -->
        <div class="hidden sm:flex space-x-1">
            <template x-for="page in getVisiblePages()" :key="page">
                <button @click="goToPage(page)"
                        :class="page === pagination.current_page ? 
                            'bg-gestion-500 text-white border-gestion-500' : 
                            'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium border transition-colors"
                        x-text="page">
                </button>
            </template>
        </div>
        
        <!-- Current Page Info (Mobile) -->
        <div class="sm:hidden px-3 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600">
            <span x-text="pagination.current_page"></span>
            <span>/</span>
            <span x-text="pagination.last_page"></span>
        </div>
        
        <!-- Next Button -->
        <button @click="goToPage(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                :class="pagination.current_page >= pagination.last_page ? 
                    'opacity-50 cursor-not-allowed' : 
                    'hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white'"
                class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-r-md transition-colors">
            <span class="mr-1 hidden sm:inline">Siguiente</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
        
    </div>
    
</div>

<script>
// Agregar método para obtener páginas visibles
document.addEventListener('alpine:init', () => {
    Alpine.data('proveedoresManager', (existingData) => ({
        ...existingData(),
        
        getVisiblePages() {
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            const pages = [];
            
            if (last <= 7) {
                // Si hay 7 páginas o menos, mostrar todas
                for (let i = 1; i <= last; i++) {
                    pages.push(i);
                }
            } else {
                // Lógica para mostrar páginas con ellipsis
                if (current <= 4) {
                    // Mostrar primeras páginas
                    for (let i = 1; i <= 5; i++) {
                        pages.push(i);
                    }
                    pages.push('...');
                    pages.push(last);
                } else if (current >= last - 3) {
                    // Mostrar últimas páginas
                    pages.push(1);
                    pages.push('...');
                    for (let i = last - 4; i <= last; i++) {
                        pages.push(i);
                    }
                } else {
                    // Mostrar páginas del medio
                    pages.push(1);
                    pages.push('...');
                    for (let i = current - 1; i <= current + 1; i++) {
                        pages.push(i);
                    }
                    pages.push('...');
                    pages.push(last);
                }
            }
            
            return pages;
        }
    }));
});
</script>