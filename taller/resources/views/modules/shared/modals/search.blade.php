{{-- resources/views/shared/modals/search.blade.php --}}
@props([
    'id' => 'searchModal',
    'placeholder' => 'Buscar en todo el sistema...',
    'minLength' => 2,
    'maxResults' => 10,
    'searchUrl' => '/search',
    'showCategories' => true,
    'showRecent' => true,
    'showKeyboardShortcuts' => true
])

<!-- Search Modal -->
<div x-data="{ 
    show: false,
    query: '',
    results: [],
    loading: false,
    selectedIndex: -1,
    categories: [],
    recentSearches: JSON.parse(localStorage.getItem('recentSearches') || '[]'),
    
    open() {
        this.show = true;
        this.query = '';
        this.results = [];
        this.selectedIndex = -1;
        this.$nextTick(() => {
            this.$refs.searchInput.focus();
        });
    },
    
    close() {
        this.show = false;
        this.query = '';
        this.results = [];
        this.selectedIndex = -1;
    },
    
    async search() {
        if (this.query.length < {{ $minLength }}) {
            this.results = [];
            return;
        }
        
        this.loading = true;
        
        try {
            const response = await fetch('{{ $searchUrl }}?' + new URLSearchParams({
                q: this.query,
                limit: {{ $maxResults }}
            }));
            
            const data = await response.json();
            this.results = data.results || [];
            this.categories = data.categories || [];
            this.selectedIndex = -1;
        } catch (error) {
            console.error('Error en búsqueda:', error);
            this.results = [];
        } finally {
            this.loading = false;
        }
    },
    
    selectResult(index) {
        if (index >= 0 && index < this.results.length) {
            this.selectedIndex = index;
            this.goToResult(this.results[index]);
        }
    },
    
    goToResult(result) {
        if (result && result.url) {
            this.addToRecent(result);
            window.location.href = result.url;
        }
    },
    
    addToRecent(result) {
        const recent = this.recentSearches.filter(item => item.id !== result.id);
        recent.unshift(result);
        this.recentSearches = recent.slice(0, 5);
        localStorage.setItem('recentSearches', JSON.stringify(this.recentSearches));
    },
    
    clearRecent() {
        this.recentSearches = [];
        localStorage.removeItem('recentSearches');
    },
    
    handleKeydown(event) {
        switch(event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
                break;
            case 'ArrowUp':
                event.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                break;
            case 'Enter':
                event.preventDefault();
                if (this.selectedIndex >= 0) {
                    this.selectResult(this.selectedIndex);
                }
                break;
            case 'Escape':
                this.close();
                break;
        }
    },
    
    highlightText(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark class=\"bg-yellow-200 dark:bg-yellow-800 px-0.5 rounded\">$1</mark>');
    }
}" 
     x-show="show" 
     x-cloak
     id="{{ $id }}"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.window="handleKeydown($event)"
     aria-labelledby="search-title" 
     role="dialog" 
     aria-modal="true">
    
    <!-- Background backdrop -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="close()"></div>
    
    <!-- Modal panel -->
    <div class="flex min-h-full items-start justify-center p-4 text-center sm:p-6 md:p-20">
        <div x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative w-full max-w-2xl transform rounded-lg bg-white dark:bg-gray-800 shadow-2xl transition-all">
            
            <!-- Search input -->
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input x-ref="searchInput"
                       x-model="query"
                       @input.debounce.300ms="search()"
                       type="text" 
                       class="w-full bg-transparent border-0 border-b border-gray-200 dark:border-gray-700 pl-11 pr-4 py-4 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-0 focus:border-gestion-500 text-lg"
                       placeholder="{{ $placeholder }}"
                       autocomplete="off">
                
                <!-- Loading indicator -->
                <div x-show="loading" class="absolute inset-y-0 right-0 flex items-center pr-4">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-gestion-600 border-t-transparent"></div>
                </div>
            </div>
            
            <!-- Results -->
            <div class="max-h-96 overflow-y-auto">
                <!-- Search Results -->
                <div x-show="results.length > 0" class="py-2">
                    <template x-for="(result, index) in results" :key="result.id">
                        <button @click="selectResult(index)"
                                class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                :class="{ 'bg-gray-50 dark:bg-gray-700': selectedIndex === index }">
                            <div class="flex items-center">
                                <!-- Result icon -->
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                                     :class="`bg-${result.color || 'gray'}-100 text-${result.color || 'gray'}-600`">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path x-show="result.type === 'cliente'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        <path x-show="result.type === 'producto'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        <path x-show="result.type === 'venta'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
                                        <path x-show="result.type === 'reparacion'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    </svg>
                                </div>
                                
                                <!-- Result content -->
                                <div class="ml-3 flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white" 
                                       x-html="highlightText(result.title, query)"></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate"
                                       x-html="highlightText(result.description, query)"></p>
                                </div>
                                
                                <!-- Result badge -->
                                <div class="ml-3 flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="`bg-${result.color || 'gray'}-100 text-${result.color || 'gray'}-800`"
                                          x-text="result.type"></span>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
                
                <!-- No results -->
                <div x-show="query.length >= {{ $minLength }} && results.length === 0 && !loading" 
                     class="px-4 py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin resultados</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        No se encontraron resultados para "<span x-text="query"></span>"
                    </p>
                </div>
                
                <!-- Recent searches -->
                <div x-show="query === '' && recentSearches.length > 0 && {{ $showRecent ? 'true' : 'false' }}" 
                     class="py-2 border-t border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-2 flex items-center justify-between">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            Búsquedas recientes
                        </h4>
                        <button @click="clearRecent()" 
                                class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            Limpiar
                        </button>
                    </div>
                    
                    <template x-for="recent in recentSearches" :key="recent.id">
                        <button @click="goToResult(recent)"
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm text-gray-900 dark:text-white" x-text="recent.title"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
            
            <!-- Footer -->
            <div x-show="{{ $showKeyboardShortcuts ? 'true' : 'false' }}" 
                 class="border-t border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50 dark:bg-gray-700 rounded-b-lg">
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <kbd class="inline-flex items-center px-1.5 py-0.5 border border-gray-200 dark:border-gray-600 rounded text-xs font-mono bg-white dark:bg-gray-800">↵</kbd>
                            <span class="ml-1">para seleccionar</span>
                        </div>
                        <div class="flex items-center">
                            <kbd class="inline-flex items-center px-1.5 py-0.5 border border-gray-200 dark:border-gray-600 rounded text-xs font-mono bg-white dark:bg-gray-800">↑↓</kbd>
                            <span class="ml-1">para navegar</span>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <kbd class="inline-flex items-center px-1.5 py-0.5 border border-gray-200 dark:border-gray-600 rounded text-xs font-mono bg-white dark:bg-gray-800">ESC</kbd>
                        <span class="ml-1">para cerrar</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función global para mostrar el modal de búsqueda
window.showSearchModal = function() {
    const modal = document.getElementById('{{ $id }}');
    if (modal && modal.__x) {
        modal.__x.$data.open();
    }
};

// Keyboard shortcut para abrir búsqueda (Ctrl/Cmd + K)
document.addEventListener('keydown', function(event) {
    if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
        event.preventDefault();
        showSearchModal();
    }
});
</script>