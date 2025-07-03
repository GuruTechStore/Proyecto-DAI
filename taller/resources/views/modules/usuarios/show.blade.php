<!-- Reemplazar la sección "Usuario del Sistema" en show.blade.php -->
<div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 lg:col-span-2">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Usuario del Sistema</h3>
    
    @if($empleado->usuario)
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuario</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $empleado->usuario->username }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email del Sistema</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $empleado->usuario->email }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Roles</dt>
                <dd class="text-sm">
                    @if($empleado->usuario->roles && $empleado->usuario->roles->count() > 0)
                        @foreach($empleado->usuario->roles as $role)
                            <span class="status-badge user-status-active mr-2">{{ $role->name }}</span>
                        @endforeach
                    @else
                        <span class="text-gray-500 dark:text-gray-400">Sin roles asignados</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Último Acceso</dt>
                <dd class="text-sm text-gray-900 dark:text-white">
                    {{ $empleado->usuario->ultimo_login ? $empleado->usuario->ultimo_login->diffForHumans() : 'Nunca' }}
                </dd>
            </div>
        </dl>
    @else
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Este empleado no tiene un usuario del sistema asociado.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Para acceder al sistema, este empleado necesita un usuario.
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                    Se creará con username basado en su nombre y email actual.
                </p>
            </div>
            
            @can('usuarios.crear')
            <div class="ml-4">
                <button onclick="crearUsuarioParaEmpleado({{ $empleado->id }})" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Crear Usuario
                </button>
            </div>
            @else
            <div class="ml-4">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    No tienes permisos para crear usuarios
                </span>
            </div>
            @endcan
        </div>
    @endif
</div>

@push('scripts')
<script>
async function crearUsuarioParaEmpleado(empleadoId) {
    try {
        // Confirmación
        const result = await Swal.fire({
            title: '¿Crear usuario del sistema?',
            text: 'Se creará un usuario para que este empleado pueda acceder al sistema.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, crear usuario',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        // Mostrar loading
        Swal.fire({
            title: 'Creando usuario...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Hacer petición
        const response = await fetch(`/empleados/${empleadoId}/crear-usuario`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            await Swal.fire({
                title: '¡Usuario creado!',
                text: `Usuario: ${data.username}\nContraseña temporal: ${data.password}`,
                icon: 'success',
                confirmButtonColor: '#10b981'
            });

            // Recargar página para mostrar el nuevo usuario
            window.location.reload();
        } else {
            throw new Error(data.message || 'Error desconocido');
        }

    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: error.message || 'No se pudo crear el usuario',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    }
}
</script>
@endpush