@extends('layouts.app')

@section('title', 'Editar Empleado')

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
        <a href="{{ route('empleados.index') }}" class="text-gray-400 hover:text-gray-500">Empleados</a>
    </div>
</li>
<li>
    <div class="flex items-center">
        <svg class="h-4 w-4 text-gray-400 mx-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        <span class="text-gray-500 font-medium">Editar</span>
    </div>
</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl sm:truncate">
                Editar Empleado: {{ $empleado->nombre_completo }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Modifica los datos del empleado
            </p>
        </div>
        <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
            <a href="{{ route('empleados.show', $empleado) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Ver Detalles
            </a>
            <a href="{{ route('empleados.index') }}" 
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
        <form action="{{ route('empleados.update', $empleado) }}" method="POST" class="space-y-6 p-6">
            @csrf
            @method('PUT')
            
            <!-- Información Personal -->
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Información Personal
                </h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- DNI -->
                    <div>
                        <label for="dni" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            DNI <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="dni" id="dni" value="{{ old('dni', $empleado->dni) }}" required
                               class="mt-1 focus:ring-gestion-500 focus:border-gestion-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('dni')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nombres -->
                    <div>
                        <label for="nombres" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nombres <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombres" id="nombres" value="{{ old('nombres', $empleado->nombres) }}" required
                               class="mt-1 focus:ring-gestion-500 focus:border-gestion-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('nombres')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Apellidos -->
                    <div>
                        <label for="apellidos" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Apellidos <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos', $empleado->apellidos) }}" required
                               class="mt-1 focus:ring-gestion-500 focus:border-gestion-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('apellidos')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Teléfono
                        </label>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $empleado->telefono) }}"
                               class="mt-1 focus:ring-gestion-500 focus:border-gestion-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('telefono')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email', $empleado->email) }}"
                               class="mt-1 focus:ring-gestion-500 focus:border-gestion-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Especialidad -->
                    <div>
                        <label for="especialidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Especialidad
                        </label>
                        <input type="text" name="especialidad" id="especialidad" value="{{ old('especialidad', $empleado->especialidad) }}"
                               list="especialidades-list"
                               class="mt-1 focus:ring-gestion-500 focus:border-gestion-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <datalist id="especialidades-list">
                            @foreach($especialidades as $especialidad)
                                <option value="{{ $especialidad }}">
                            @endforeach
                        </datalist>
                        @error('especialidad')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información Laboral -->
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Información Laboral
                </h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Fecha de Contratación -->
                    <div>
                        <label for="fecha_contratacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Fecha de Contratación <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="fecha_contratacion" id="fecha_contratacion" 
                               value="{{ old('fecha_contratacion', $empleado->fecha_contratacion->format('Y-m-d')) }}" required
                               class="mt-1 focus:ring-gestion-500 focus:border-gestion-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('fecha_contratacion')
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
                                       {{ old('activo', $empleado->activo) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-gestion-600 shadow-sm focus:border-gestion-300 focus:ring focus:ring-gestion-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Empleado activo</span>
                            </label>
                        </div>
                        @error('activo')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información del Sistema -->
            @if($empleado->usuario)
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Usuario del Sistema
                </h3>
                
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                Usuario: {{ $empleado->usuario->username }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Email: {{ $empleado->usuario->email }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Roles: 
                                @if($empleado->usuario->roles->count() > 0)
                                    {{ $empleado->usuario->roles->pluck('name')->implode(', ') }}
                                @else
                                    Sin roles asignados
                                @endif
                            </p>
                        </div>
                        @can('usuarios.editar')
                        <div>
                            <a href="#" class="text-gestion-600 hover:text-gestion-900 text-sm font-medium">
                                Editar Usuario
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
            @else
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Usuario del Sistema
                </h3>
                
                <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1 md:flex md:justify-between">
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                Este empleado no tiene un usuario del sistema asociado.
                            </p>
                            @can('usuarios.crear')
                            <p class="mt-3 text-sm md:mt-0 md:ml-6">
                                <a href="#" class="whitespace-nowrap font-medium text-yellow-700 dark:text-yellow-300 hover:text-yellow-600">
                                    Crear Usuario
                                </a>
                            </p>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Botones de Acción -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('empleados.show', $empleado) }}" 
                   class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" 
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Actualizar Empleado
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Validación en tiempo real del DNI
    document.getElementById('dni').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Solo números
        if (value.length > 8) {
            value = value.substring(0, 8);
        }
        e.target.value = value;
    });

    // Auto-capitalización de nombres y apellidos
    ['nombres', 'apellidos'].forEach(function(fieldId) {
        document.getElementById(fieldId).addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\b\w/g, function(l) { 
                return l.toUpperCase(); 
            });
        });
    });
</script>
@endpush