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
            <input type="text" name="dni" id="dni" value="{{ old('dni', $empleado->dni ?? '') }}" required
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
            <input type="text" name="nombres" id="nombres" value="{{ old('nombres', $empleado->nombres ?? '') }}" required
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
            <input type="text" name="apellidos" id="apellidos" value="{{ old('apellidos', $empleado->apellidos ?? '') }}" required
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
            <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $empleado->telefono ?? '') }}"
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
            <input type="email" name="email" id="email" value="{{ old('email', $empleado->email ?? '') }}"
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
            <input type="text" name="especialidad" id="especialidad" value="{{ old('especialidad', $empleado->especialidad ?? '') }}"
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
                   value="{{ old('fecha_contratacion', isset($empleado) ? $empleado->fecha_contratacion->format('Y-m-d') : date('Y-m-d')) }}" required
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
                           {{ old('activo', $empleado->activo ?? true) ? 'checked' : '' }}
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