{{-- resources/views/shared/modals/image.blade.php --}}
@props([
    'id' => 'imageModal',
    'title' => null,
    'description' => null,
    'showInfo' => true,
    'showDownload' => true,
    'showFullscreen' => true,
    'showNavigation' => true,
    'images' => [],
    'currentIndex' => 0
])

<!-- Modal -->
<div x-data="{ 
    show: false,
    images: {{ json_encode($images) }},
    currentIndex: {{ $currentIndex }},
    title: '{{ $title }}',
    description: '{{ $description }}',
    loading: false,
    fullscreen: false,
    open(imageData, index = 0) {
        if (Array.isArray(imageData)) {
            this.images = imageData;
            this.currentIndex = index;
        } else {
            this.images = [imageData];
            this.currentIndex = 0;
        }
        this.show = true;
        this.$nextTick(() => {
            this.preloadImages();
        });
    },
    close() {
        this.show = false;
        this.fullscreen = false;
        this.currentIndex = 0;
        this.images = [];
    },
    get currentImage() {
        return this.images[this.currentIndex] || null;
    },
    nextImage() {
        if (this.currentIndex < this.images.length - 1) {
            this.currentIndex++;
        } else {
            this.currentIndex = 0; // Loop back to first
        }
    },
    prevImage() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
        } else {
            this.currentIndex = this.images.length - 1; // Loop to last
        }
    },
    toggleFullscreen() {
        this.fullscreen = !this.fullscreen;
    },
    downloadImage() {
        if (this.currentImage && this.currentImage.url) {
            const link = document.createElement('a');
            link.href = this.currentImage.url;
            link.download = this.currentImage.name || 'image';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    },
    preloadImages() {
        this.images.forEach(image => {
            const img = new Image();
            img.src = image.url;
        });
    },
    handleKeydown(event) {
        if (!this.show) return;
        
        switch(event.key) {
            case 'ArrowLeft':
                event.preventDefault();
                this.prevImage();
                break;
            case 'ArrowRight':
                event.preventDefault();
                this.nextImage();
                break;
            case 'f':
            case 'F':
                event.preventDefault();
                this.toggleFullscreen();
                break;
            case 'Escape':
                if (this.fullscreen) {
                    this.fullscreen = false;
                } else {
                    this.close();
                }
                break;
        }
    }
}" 
     x-show="show" 
     x-cloak
     id="{{ $id }}"
     class="fixed inset-0 z-50 overflow-hidden" 
     :class="{ 'bg-black': fullscreen, 'bg-gray-900 bg-opacity-90': !fullscreen }"
     style="display: none;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.window="handleKeydown($event)"
     @click.self="close()"
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    <!-- Header (hidden in fullscreen) -->
    <div x-show="!fullscreen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="absolute top-0 left-0 right-0 z-10 bg-white dark:bg-gray-800 shadow-sm">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex-1 min-w-0">
                <h3 x-show="currentImage && currentImage.title" 
                    x-text="currentImage ? currentImage.title : ''" 
                    class="text-lg font-medium text-gray-900 dark:text-white truncate"></h3>
                <p x-show="images.length > 1" 
                   class="text-sm text-gray-500 dark:text-gray-400">
                    <span x-text="currentIndex + 1"></span> de <span x-text="images.length"></span>
                </p>
            </div>
            
            <div class="flex items-center space-x-2 ml-4">
                @if($showDownload)
                    <button @click="downloadImage()" 
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <span class="sr-only">Descargar imagen</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </button>
                @endif
                
                @if($showFullscreen)
                    <button @click="toggleFullscreen()" 
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <span class="sr-only">Pantalla completa</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>
                @endif
                
                <button @click="close()" 
                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <span class="sr-only">Cerrar</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Main content area -->
    <div class="flex items-center justify-center h-full" 
         :class="{ 'pt-20': !fullscreen, 'pt-0': fullscreen }">
        
        <!-- Navigation buttons -->
        <template x-if="images.length > 1 && {{ $showNavigation ? 'true' : 'false' }}">
            <div class="absolute inset-y-0 left-0 flex items-center z-10">
                <button @click="prevImage()" 
                        class="ml-4 p-2 rounded-full bg-black bg-opacity-50 text-white hover:bg-opacity-75 transition-colors">
                    <span class="sr-only">Imagen anterior</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
            </div>
        </template>
        
        <template x-if="images.length > 1 && {{ $showNavigation ? 'true' : 'false' }}">
            <div class="absolute inset-y-0 right-0 flex items-center z-10">
                <button @click="nextImage()" 
                        class="mr-4 p-2 rounded-full bg-black bg-opacity-50 text-white hover:bg-opacity-75 transition-colors">
                    <span class="sr-only">Siguiente imagen</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </template>
        
        <!-- Image container -->
        <div class="max-w-full max-h-full p-6 flex items-center justify-center">
            <img x-show="currentImage" 
                 :src="currentImage ? currentImage.url : ''" 
                 :alt="currentImage ? currentImage.alt || currentImage.title || 'Imagen' : ''"
                 class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
                 @load="loading = false"
                 @error="loading = false">
        </div>
        
        <!-- Loading indicator -->
        <div x-show="loading" 
             class="absolute inset-0 flex items-center justify-center">
            <div class="flex flex-col items-center space-y-3">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-white border-t-transparent"></div>
                <p class="text-white text-sm">Cargando imagen...</p>
            </div>
        </div>
    </div>
    
    <!-- Footer info (hidden in fullscreen) -->
    <div x-show="!fullscreen && showInfo && currentImage && currentImage.description" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-800 shadow-sm">
        <div class="px-6 py-4">
            <p x-text="currentImage ? currentImage.description : ''" 
               class="text-sm text-gray-600 dark:text-gray-400"></p>
        </div>
    </div>
    
    <!-- Fullscreen controls -->
    <div x-show="fullscreen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute top-4 right-4 z-20 flex items-center space-x-2">
        
        @if($showDownload)
            <button @click="downloadImage()" 
                    class="p-2 rounded-full bg-black bg-opacity-50 text-white hover:bg-opacity-75 transition-colors">
                <span class="sr-only">Descargar imagen</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </button>
        @endif
        
        <button @click="toggleFullscreen()" 
                class="p-2 rounded-full bg-black bg-opacity-50 text-white hover:bg-opacity-75 transition-colors">
            <span class="sr-only">Salir de pantalla completa</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        
        <button @click="close()" 
                class="p-2 rounded-full bg-black bg-opacity-50 text-white hover:bg-opacity-75 transition-colors">
            <span class="sr-only">Cerrar</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    
    <!-- Image counter for fullscreen -->
    <div x-show="fullscreen && images.length > 1" 
         class="absolute bottom-4 left-1/2 transform -translate-x-1/2 z-20">
        <div class="px-3 py-1 rounded-full bg-black bg-opacity-50 text-white text-sm">
            <span x-text="currentIndex + 1"></span> / <span x-text="images.length"></span>
        </div>
    </div>
</div>

<script>
// Función global para mostrar el modal de imagen
window.showImageModal = function(images, index = 0) {
    const modal = document.getElementById('{{ $id }}');
    if (modal && modal.__x) {
        modal.__x.$data.open(images, index);
    }
};

// Función para cerrar el modal de imagen
window.closeImageModal = function() {
    const modal = document.getElementById('{{ $id }}');
    if (modal && modal.__x) {
        modal.__x.$data.close();
    }
};
</script>