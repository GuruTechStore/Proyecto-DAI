{{-- resources/views/modules/proveedores/partials/actions.blade.php --}}
<div x-data="{ open: false }" class="relative inline-block text-left">
    
    <!-- Dropdown Button -->
    <button @click="open = !open" 
            @click.away="open = false"
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
        </svg>
        <span class="sr-only">Abrir menú de acciones</span>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50">
        
        <div class="py-1" role="menu" aria-orientation="vertical">
            
            @can('proveedores.ver')
            <a href="{{ route('proveedores.show', $proveedor) }}" 
               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
               role="menuitem">
                <svg class="w-4 h-4 mr-3 text-gestion-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Ver Detalles
            </a>
            @endcan
            
            @can('proveedores.editar')
            <a href="{{ route('proveedores.edit', $proveedor) }}" 
               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
               role="menuitem">
                <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
            </a>
            @endcan
            
            <!-- Separator -->
            <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
            
            <!-- Quick Actions -->
            <button type="button" 
                    @click="copyToClipboard('{{ $proveedor->email ?? '' }}')"
                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    role="menuitem"
                    {{ !$proveedor->email ? 'disabled' : '' }}>
                <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.5 7.5L21 4.5" />
                </svg>
                Copiar Email
            </button>
            
            @if($proveedor->telefono)
            <a href="tel:{{ $proveedor->telefono }}" 
               class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
               role="menuitem">
                <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                Llamar
            </a>
            @endif
            
            <!-- Separator -->
            <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
            
            <!-- Status Toggle -->
            @can('proveedores.editar')
            <form action="{{ route('proveedores.toggle-status', $proveedor) }}" method="POST" class="w-full">
                @csrf
                @method('PATCH')
                <button type="submit" 
                        class="flex items-center w-full px-4 py-2 text-sm {{ $proveedor->activo ? 'text-yellow-700 dark:text-yellow-400' : 'text-green-700 dark:text-green-400' }} hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        role="menuitem">
                    @if($proveedor->activo)
                        <svg class="w-4 h-4 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14L5 9m0 0l5-5m-5 5h14" />
                        </svg>
                        Desactivar
                    @else
                        <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Activar
                    @endif
                </button>
            </form>
            @endcan
            
            <!-- Export Individual -->
            <button type="button" 
                    @click="exportProveedor({{ $proveedor->id }})"
                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    role="menuitem">
                <svg class="w-4 h-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar Datos
            </button>
            
            <!-- Separator -->
            <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
            
            <!-- Delete Action -->
            @can('proveedores.eliminar')
            <button type="button" 
                    @click="confirmDelete({{ $proveedor->id }}, '{{ $proveedor->nombre }}')"
                    class="flex items-center w-full px-4 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    role="menuitem">
                <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Eliminar
            </button>
            @endcan
            
        </div>
    </div>
</div>

@push('scripts')
<script>
// Función para copiar al portapapeles
function copyToClipboard(text) {
    if (!text) {
        alert('No hay email para copiar');
        return;
    }
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            // Mostrar notificación de éxito
            if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
                Alpine.store('notifications').add({
                    type: 'success',
                    message: 'Email copiado al portapapeles'
                });
            } else {
                alert('Email copiado: ' + text);
            }
        }).catch(() => {
            // Fallback para navegadores que no soportan clipboard API
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        alert('Email copiado: ' + text);
    } catch (err) {
        console.error('Error al copiar: ', err);
        alert('Error al copiar el email');
    }
    
    document.body.removeChild(textArea);
}

// Función para exportar proveedor individual
async function exportProveedor(proveedorId) {
    try {
        const response = await fetch(`/api/proveedores/${proveedorId}/export`, {
            method: 'GET',
            headers: {
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `proveedor_${proveedorId}_${new Date().toISOString().split('T')[0]}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            // Mostrar notificación de éxito
            if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
                Alpine.store('notifications').add({
                    type: 'success',
                    message: 'Datos del proveedor exportados correctamente'
                });
            }
        } else {
            throw new Error('Error en la respuesta del servidor');
        }
    } catch (error) {
        console.error('Error exporting proveedor:', error);
        if (typeof Alpine !== 'undefined' && Alpine.store('notifications')) {
            Alpine.store('notifications').add({
                type: 'error',
                message: 'Error al exportar los datos del proveedor'
            });
        } else {
            alert('Error al exportar los datos del proveedor');
        }
    }
}

// Función para confirmar eliminación
function confirmDelete(proveedorId, proveedorNombre) {
    if (confirm(`¿Estás seguro de que deseas eliminar al proveedor "${proveedorNombre}"? Esta acción no se puede deshacer.`)) {
        // Crear formulario para enviar DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/proveedores/${proveedorId}`;
        
        // Agregar token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Agregar método DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Enviar formulario
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush