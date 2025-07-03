{{-- resources/views/errors/404.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-gestion-600">404</h1>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                    Página no encontrada
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    La página que buscas no existe o ha sido movida.
                </p>
            </div>
            
            <div class="space-y-4">
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gestion-600 text-white font-medium rounded-lg hover:bg-gestion-700 transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Volver al Dashboard
                </a>
                
                <div>
                    <button onclick="history.back()" 
                            class="text-gestion-600 hover:text-gestion-800 font-medium">
                        ← Volver atrás
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

{{-- resources/views/errors/500.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del servidor - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-red-600">500</h1>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                    Error del servidor
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    Algo salió mal en nuestro servidor. Estamos trabajando para solucionarlo.
                </p>
            </div>
            
            <div class="space-y-4">
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gestion-600 text-white font-medium rounded-lg hover:bg-gestion-700 transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Volver al Dashboard
                </a>
                
                <div>
                    <button onclick="location.reload()" 
                            class="text-gestion-600 hover:text-gestion-800 font-medium">
                        <i class="fas fa-sync-alt mr-1"></i>
                        Intentar de nuevo
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

{{-- resources/views/errors/503.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicio no disponible - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <h1 class="text-9xl font-bold text-yellow-600">503</h1>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                    Servicio en mantenimiento
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    Estamos realizando mantenimiento. Volveremos pronto.
                </p>
            </div>
            
            <div class="space-y-4">
                <button onclick="location.reload()" 
                        class="inline-flex items-center px-6 py-3 bg-gestion-600 text-white font-medium rounded-lg hover:bg-gestion-700 transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Verificar nuevamente
                </button>
            </div>
        </div>
    </div>
</body>
</html>