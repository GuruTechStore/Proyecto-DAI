@extends('layouts.app')

@section('title', 'Nuevo Empleado')

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
                Nuevo Empleado
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Complete los datos del nuevo empleado
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="{{ route('empleados.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver a la lista
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <form action="{{ route('empleados.store') }}" method="POST" class="space-y-6 p-6">
            @csrf
            
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
                        <input type="text" name="dni" id="dni" value="{{ old('dni') }}" required
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
                        <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" required
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
                        <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos') }}" required
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
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}"
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
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
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
                        <input type="text" name="especialidad" id="especialidad" value="{{ old('especialidad') }}"
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
                               value="{{ old('fecha_contratacion', date('Y-m-d')) }}" required
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
                                       {{ old('activo', true) ? 'checked' : '' }}
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

            <!-- Botones de Acción -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('empleados.index') }}" 
                   class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
                    Cancelar
                </a>
                <button type="submit" 
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gestion-600 hover:bg-gestion-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gestion-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Guardar Empleado
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