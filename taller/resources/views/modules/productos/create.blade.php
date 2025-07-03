@extends('layouts.app')

@section('title', 'Nuevo Producto')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-500">Dashboard</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <a href="{{ route('productos.index') }}" class="text-gray-400 hover:text-gray-500">Productos</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <span class="text-gray-500 font-medium">Nuevo</span>
    </div>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl sm:truncate">
                Nuevo Producto
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Complete la información del nuevo producto
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="{{ route('productos.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver a la lista
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 p-6">
            @csrf
            
            <!-- Información Básica -->
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Información Básica
                </h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Código -->
                    <div>
                        <label for="codigo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Código <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="codigo" id="codigo" value="{{ old('codigo') }}" required
                               class="mt-1 focus:ring-gestion-500 focus:border-gestion-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('codigo')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nombre -->
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nombre <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required
                               class="mt-1 focus:ring-gestion-500 focus:border-gestion-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('nombre')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label for="categoria_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Categoría
                        </label>
                        <select name="categoria_id" id="categoria_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-gestion-500 focus:border-gestion-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Seleccionar categoría</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('categoria_id')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Proveedor -->
                    <div>
                        <label for="proveedor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Proveedor
                        </label>
                        <select name="proveedor_id" id="proveedor_id"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-gestion-500 focus:border-gestion-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Seleccionar proveedor</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                    {{ $proveedor->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('proveedor_id')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Descripción -->
                <div class="mt-6">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Descripción
                    </label>
                    <textarea name="descripcion" id="descripcion" rows="3"
                              class="mt-1 shadow-sm focus:ring-gestion-500 focus:border-gestion-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                              placeholder="Describe las características del producto...">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Precios -->
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Precios
                </h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Precio de Compra -->
                    <div>
                        <label for="precio_compra" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Precio de Compra <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">S/</span>
                            </div>
                            <input type="number" name="precio_compra" id="precio_compra" step="0.01" min="0" 
                                   value="{{ old('precio_compra') }}" required
                                   class="focus:ring-gestion-500 focus:border-gestion-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   placeholder="0.00">
                        </div>
                        @error('precio_compra')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Precio de Venta -->
                    <div>
                        <label for="precio_venta" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Precio de Venta <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">S/</span>
                            </div>
                            <input type="number" name="precio_venta" id="precio_venta" step="0.01" min="0" 
                                   value="{{ old('precio_venta') }}" required
                                   class="focus:ring-gestion-500 focus:border-gestion-500 block w-full pl-10 pr-12 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   placeholder="0.00">
                        </div>
                        @error('precio_venta')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Margen de Ganancia (calculado automáticamente) -->
                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Margen de Ganancia</h3>
                            <div class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                <span id="margen-amount">S/ 0.00</span> 
                                (<span id="margen-percentage">0%</span>)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventario -->
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Control de Inventario
                </h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Stock Inicial -->
                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Stock Inicial
                        </label>
                        <input type="number" name="stock" id="stock" min="0" value="{{ old('stock', 0) }}"
                               class="mt-1 focus:ring-gestion-500 focus:border-gestion-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('stock')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stock Mínimo -->
                    <div>
                        <label for="stock_minimo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Stock Mínimo <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="stock_minimo" id="stock_minimo" min="0" 
                               value="{{ old('stock_minimo', 5) }}" required
                               class="mt-1 focus:ring-gestion-500 focus:border-gestion-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('stock_minimo')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Garantía e Imagen -->
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Información Adicional
                </h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Garantía -->
                    <div>
                        <label for="garantia_dias" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Garantía (días)
                        </label>
                        <select name="garantia_dias" id="garantia_dias"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-gestion-500 focus:border-gestion-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Sin garantía</option>
                            <option value="30" {{ old('garantia_dias') == '30' ? 'selected' : '' }}>30 días</option>
                            <option value="60" {{ old('garantia_dias') == '60' ? 'selected' : '' }}>60 días</option>
                            <option value="90" {{ old('garantia_dias') == '90' ? 'selected' : '' }}>90 días</option>
                            <option value="180" {{ old('garantia_dias') == '180' ? 'selected' : '' }}>6 meses</option>
                            <option value="365" {{ old('garantia_dias') == '365' ? 'selected' : '' }}>1 año</option>
                            <option value="730" {{ old('garantia_dias') == '730' ? 'selected' : '' }}>2 años</option>
                        </select>
                        @error('garantia_dias')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estado -->
                    <div>
                        <label for="activo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Estado
                        </label>
                        <div class="mt-1">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="activo" id="activo" value="1" 
                                       {{ old('activo', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-gestion-600 shadow-sm focus:border-gestion-300 focus:ring focus:ring-gestion-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Producto activo</span>
                            </label>
                        </div>
                        @error('activo')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Imagen -->
                <div class="mt-6">
                    <label for="imagen" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Imagen del Producto
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                <label for="imagen" class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-gestion-600 hover:text-gestion-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-gestion-500">
                                    <span>Subir archivo</span>
                                    <input id="imagen" name="imagen" type="file" accept="image/*" class="sr-only">
                                </label>
                                <p class="pl-1">o arrastra y suelta</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                PNG, JPG, GIF hasta 10MB
                            </p>
                        </div>
                    </div>
                    @error('imagen')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('productos.index') }}" 
                   class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
                    Cancelar
                </a>
                <button type="submit" 
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Guardar Producto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Cálculo automático del margen de ganancia
    function calculateMargin() {
        const precioCompra = parseFloat(document.getElementById('precio_compra').value) || 0;
        const precioVenta = parseFloat(document.getElementById('precio_venta').value) || 0;
        
        if (precioCompra > 0 && precioVenta > 0) {
            const margenAmount = precioVenta - precioCompra;
            const margenPercentage = ((margenAmount / precioCompra) * 100).toFixed(1);
            
            document.getElementById('margen-amount').textContent = `S/ ${margenAmount.toFixed(2)}`;
            document.getElementById('margen-percentage').textContent = `${margenPercentage}%`;
        } else {
            document.getElementById('margen-amount').textContent = 'S/ 0.00';
            document.getElementById('margen-percentage').textContent = '0%';
        }
    }

    // Auto-rellenar precio de venta basado en precio de compra con margen del 30%
    document.getElementById('precio_compra').addEventListener('input', function() {
        const precioCompra = parseFloat(this.value) || 0;
        const precioVentaField = document.getElementById('precio_venta');
        
        if (precioCompra > 0 && !precioVentaField.value) {
            const precioVentaSugerido = (precioCompra * 1.3).toFixed(2);
            precioVentaField.value = precioVentaSugerido;
        }
        
        calculateMargin();
    });

    document.getElementById('precio_venta').addEventListener('input', calculateMargin);

    // Generar código automático basado en el nombre
    document.getElementById('nombre').addEventListener('input', function() {
        const nombre = this.value.toUpperCase();
        const codigoField = document.getElementById('codigo');
        
        if (nombre && !codigoField.value) {
            // Generar código: primeras 3 letras + números aleatorios
            const prefijo = nombre.replace(/[^A-Z]/g, '').substring(0, 3);
            const sufijo = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            codigoField.value = prefijo + sufijo;
        }
    });

    // Preview de imagen
    document.getElementById('imagen').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Crear preview de imagen (si quieres implementarlo)
                console.log('Imagen seleccionada:', file.name);
            };
            reader.readAsDataURL(file);
        }
    });

    // Validación de stock mínimo
    document.getElementById('stock').addEventListener('input', function() {
        const stock = parseInt(this.value) || 0;
        const stockMinimo = parseInt(document.getElementById('stock_minimo').value) || 0;
        
        if (stock > 0 && stock <= stockMinimo) {
            this.classList.add('border-yellow-300', 'focus:border-yellow-500');
            this.classList.remove('border-gray-300');
        } else {
            this.classList.remove('border-yellow-300', 'focus:border-yellow-500');
            this.classList.add('border-gray-300');
        }
    });
</script>
@endpush