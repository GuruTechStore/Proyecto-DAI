{{-- resources/views/modules/ventas/partials/add-product-modal.blade.php --}}
<div x-show="showAddProductModal" 
     x-transition.opacity 
     class="fixed inset-0 z-50 overflow-y-auto" 
     x-cloak>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div x-show="showAddProductModal" 
             x-transition.opacity
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
             @click="showAddProductModal = false"></div>

        <!-- Modal panel -->
        <div x-show="showAddProductModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
            
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-gestion-100 dark:bg-gestion-900 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-gestion-600 dark:text-gestion-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Agregar Productos a la Venta
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Selecciona los productos que deseas agregar a esta venta
                    </p>
                </div>
            </div>

            <!-- Product Search and List -->
            <div class="mt-6" x-data="addProductModal()">
                <!-- Search Bar -->
                <div class="mb-6">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" 
                               x-model="searchQuery"
                               @input.debounce.300ms="searchProducts"
                               class="block w-full pl-10 pr-3 py-3 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500 text-lg"
                               placeholder="Buscar productos por nombre, código o categoría...">
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="searchLoading" class="flex justify-center py-8">
                    <svg class="animate-spin h-8 w-8 text-gestion-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Products Grid -->
                <div x-show="!searchLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-96 overflow-y-auto">
                    <template x-for="producto in productos" :key="producto.id">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:border-gestion-300 dark:hover:border-gestion-600 transition-colors cursor-pointer"
                             :class="selectedProduct?.id === producto.id ? 'border-gestion-500 bg-gestion-50 dark:bg-gestion-900/20' : ''"
                             @click="selectProduct(producto)">
                            
                            <div class="flex items-start space-x-3">
                                <!-- Product Icon -->
                                <div class="flex-shrink-0">
                                    <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-gestion-400 to-gestion-600 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                </div>
                                
                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="producto.nombre"></h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="producto.codigo"></p>
                                    
                                    <div class="mt-2 flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-semibold text-gestion-600 dark:text-gestion-400">
                                                S/ <span x-text="parseFloat(producto.precio_venta).toFixed(2)"></span>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Stock: <span x-text="producto.stock"></span>
                                            </p>
                                        </div>
                                        
                                        <!-- Stock Status -->
                                        <div>
                                            <span x-show="producto.stock > producto.stock_minimo" 
                                                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                Disponible
                                            </span>
                                            <span x-show="producto.stock <= producto.stock_minimo && producto.stock > 0" 
                                                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                Bajo stock
                                            </span>
                                            <span x-show="producto.stock <= 0" 
                                                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                Sin stock
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="!searchLoading && productos.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay productos</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        No se encontraron productos con tu búsqueda
                    </p>
                </div>

                <!-- Selected Product Details -->
                <div x-show="selectedProduct" class="mt-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Configurar Producto</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Product Info Summary -->
                        <div class="md:col-span-2">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-gestion-400 to-gestion-600 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900 dark:text-white" x-text="selectedProduct?.nombre"></h5>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedProduct?.codigo"></p>
                                    <p class="text-sm font-semibold text-gestion-600 dark:text-gestion-400">
                                        S/ <span x-text="selectedProduct ? parseFloat(selectedProduct.precio_venta).toFixed(2) : '0.00'"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Cantidad
                            </label>
                            <input type="number" 
                                   x-model="productQuantity"
                                   min="1" 
                                   :max="selectedProduct?.stock || 1"
                                   class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Máx: <span x-text="selectedProduct?.stock || 0"></span>
                            </p>
                        </div>

                        <!-- Discount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Descuento %
                            </label>
                            <input type="number" 
                                   x-model="productDiscount"
                                   min="0" 
                                   max="100"
                                   step="0.01"
                                   class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-gestion-500 focus:border-gestion-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                0 - 100%
                            </p>
                        </div>
                    </div>

                    <!-- Subtotal Preview -->
                    <div x-show="selectedProduct" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Subtotal:</span>
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                S/ <span x-text="calculatePreviewSubtotal().toFixed(2)"></span>
                            </span>
                        </div>
                        <div x-show="productDiscount > 0" class="flex justify-between items-center text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Descuento:</span>
                            <span class="text-red-600 dark:text-red-400">
                                -S/ <span x-text="calculatePreviewDiscount().toFixed(2)"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" 
                            @click="showAddProductModal = false; resetProductSelection()"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                        Cancelar
                    </button>
                    <button type="button" 
                            @click="addProductToSale()"
                            :disabled="!selectedProduct || productQuantity <= 0 || productQuantity > (selectedProduct?.stock || 0)"
                            :class="(!selectedProduct || productQuantity <= 0 || productQuantity > (selectedProduct?.stock || 0)) ? 'opacity-50 cursor-not-allowed' : ''"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-gestion-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Agregar a la Venta
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addProductModal() {
    return {
        searchLoading: false,
        searchQuery: '',
        productos: @json($productos ?? []),
        selectedProduct: null,
        productQuantity: 1,
        productDiscount: 0,

        init() {
            this.searchProducts();
        },

        async searchProducts() {
            if (!this.searchQuery) {
                // Load all products if no search query
                this.searchLoading = true;
                try {
                    const response = await fetch('{{ route("productos.api.search") }}');
                    const data = await response.json();
                    this.productos = data.productos || [];
                } catch (error) {
                    console.error('Error loading products:', error);
                } finally {
                    this.searchLoading = false;
                }
                return;
            }

            this.searchLoading = true;
            try {
                const response = await fetch(`{{ route("productos.api.search") }}?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                this.productos = data.productos || [];
            } catch (error) {
                console.error('Error searching products:', error);
                this.productos = [];
            } finally {
                this.searchLoading = false;
            }
        },

        selectProduct(producto) {
            this.selectedProduct = producto;
            this.productQuantity = 1;
            this.productDiscount = 0;
        },

        calculatePreviewSubtotal() {
            if (!this.selectedProduct || this.productQuantity <= 0) return 0;
            
            const subtotal = this.productQuantity * this.selectedProduct.precio_venta;
            const discount = subtotal * (this.productDiscount / 100);
            return subtotal - discount;
        },

        calculatePreviewDiscount() {
            if (!this.selectedProduct || this.productQuantity <= 0 || this.productDiscount <= 0) return 0;
            
            const subtotal = this.productQuantity * this.selectedProduct.precio_venta;
            return subtotal * (this.productDiscount / 100);
        },

        addProductToSale() {
            if (!this.selectedProduct || this.productQuantity <= 0) return;

            // Check if product already exists in the sale
            const existingIndex = this.$parent.form.items.findIndex(item => item.producto_id === this.selectedProduct.id);
            
            if (existingIndex !== -1) {
                // Update existing item
                const currentQty = this.$parent.form.items[existingIndex].cantidad;
                const newQty = currentQty + this.productQuantity;
                
                if (newQty <= this.selectedProduct.stock) {
                    this.$parent.form.items[existingIndex].cantidad = newQty;
                    this.$parent.form.items[existingIndex].descuento = this.productDiscount;
                } else {
                    alert(`No hay suficiente stock. Disponible: ${this.selectedProduct.stock}, solicitado: ${newQty}`);
                    return;
                }
            } else {
                // Add new item
                this.$parent.form.items.push({
                    producto_id: this.selectedProduct.id,
                    nombre: this.selectedProduct.nombre,
                    codigo: this.selectedProduct.codigo,
                    precio_unitario: this.selectedProduct.precio_venta,
                    cantidad: this.productQuantity,
                    descuento: this.productDiscount,
                    stock_disponible: this.selectedProduct.stock
                });
            }

            // Close modal and reset
            this.$parent.showAddProductModal = false;
            this.resetProductSelection();
        },

        resetProductSelection() {
            this.selectedProduct = null;
            this.productQuantity = 1;
            this.productDiscount = 0;
            this.searchQuery = '';
        }
    };
}
</script>