<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;


// Rutas de login (solo para invitados)
Route::middleware('guest')->group(function () {
    // Mostrar formulario de login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    
    // Procesar login
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Rutas de logout (solo para usuarios autenticados)
Route::middleware('auth')->group(function () {
    // Logout básico
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    
    // Logout de todos los dispositivos
    Route::post('/logout-all-devices', [LogoutController::class, 'logoutAllDevices'])->name('logout.all');
    
    // Obtener sesiones activas
    Route::get('/active-sessions', [LogoutController::class, 'getActiveSessions'])->name('sessions.active');
    
    // Logout de token específico
    Route::delete('/logout-token', [LogoutController::class, 'logoutToken'])->name('logout.token');
});

// Rutas de API para autenticación
Route::prefix('api/auth')->group(function () {
    // Login API (para Sanctum)
    Route::post('/login', [LoginController::class, 'apiLogin'])->name('api.login');
    
    // Verificar estado de login
    Route::get('/status', [LoginController::class, 'checkLoginStatus'])->name('api.login.status');
    
    // Verificar sesión
    Route::get('/check-session', [LogoutController::class, 'checkSession'])->name('api.session.check');
    
    // Logout API
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [LogoutController::class, 'logout'])->name('api.logout');
        Route::post('/logout-all', [LogoutController::class, 'logoutAllDevices'])->name('api.logout.all');
    });
});

// Rutas administrativas de autenticación (solo para administradores)
Route::middleware(['auth', 'active', 'role:Super Admin,Admin'])->prefix('admin/auth')->group(function () {
    // Forzar logout de usuario
    Route::post('/force-logout/{user}', [LogoutController::class, 'forceLogout'])->name('admin.force.logout');
});

// Rutas de verificación y utilidades
Route::middleware('web')->group(function () {
    // Verificar si el usuario está autenticado (para AJAX)
    Route::get('/auth-check', function () {
        return response()->json([
            'authenticated' => auth()->check(),
            'user' => auth()->user()
        ]);
    })->name('auth.check');
    
    // Refrescar token CSRF
    Route::get('/csrf-token', function () {
        return response()->json([
            'csrf_token' => csrf_token()
        ]);
    })->name('csrf.refresh');
});

/*
|--------------------------------------------------------------------------
| Rutas de Redirección
|--------------------------------------------------------------------------
|
| Rutas para manejar redirecciones comunes después de la autenticación
|
*/

// Redirección después del login
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->name('home');

// Ruta raíz - redirigir según estado de autenticación
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('root');