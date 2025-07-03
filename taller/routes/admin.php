<?php

use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Api\SecurityController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\PermissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes - CORREGIDO
|--------------------------------------------------------------------------
*/

// Todas las rutas administrativas requieren autenticación WEB (no sanctum)
Route::middleware(['auth', 'active', 'track.activity'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Panel Administrativo Principal
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Dashboard administrativo
        Route::get('/', function () {
            return view('admin.dashboard');
        })->middleware('role:Super Admin|Admin|Gerente')->name('dashboard');
        
        /*
        |--------------------------------------------------------------------------
        | Gestión de Usuarios
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:Super Admin|Admin|Gerente')->prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::get('/create', [UserManagementController::class, 'create'])->name('create');
            Route::post('/', [UserManagementController::class, 'store'])->name('store');
            Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
            
            // Gestión de roles
            Route::post('/{user}/assign-role', [UserManagementController::class, 'assignRole'])->name('assign-role');
            Route::delete('/{user}/remove-role', [UserManagementController::class, 'removeRole'])->name('remove-role');
            
            // Acciones de seguridad
            Route::post('/{user}/lock', [UserManagementController::class, 'lockUser'])->name('lock');
            Route::post('/{user}/unlock', [UserManagementController::class, 'unlockUser'])->name('unlock');
        });
        
        /*
        |--------------------------------------------------------------------------
        | Seguridad y Monitoreo
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:Super Admin|Admin|Gerente')->prefix('security')->name('security.')->group(function () {
            Route::get('/', [SecurityController::class, 'dashboard'])->name('dashboard');
            Route::get('/logs', [SecurityController::class, 'index'])->name('logs');
            Route::post('/logs/{log}/resolve', [SecurityController::class, 'resolve'])->name('resolve');
            Route::get('/stats', [SecurityController::class, 'stats'])->name('stats');
            Route::get('/export', [SecurityController::class, 'export'])->name('export');
        });
        
        /*
        |--------------------------------------------------------------------------
        | Logs de Actividad
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:Super Admin|Admin|Gerente')->prefix('activity')->name('activity.')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('index');
            Route::get('/dashboard', [ActivityController::class, 'dashboard'])->name('dashboard');
            Route::get('/user/{user}', [ActivityController::class, 'byUser'])->name('by-user');
            Route::get('/module/{module}', [ActivityController::class, 'byModule'])->name('by-module');
            Route::get('/export', [ActivityController::class, 'export'])->name('export');
        });
        
        /*
        |--------------------------------------------------------------------------
        | Configuración del Sistema
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:Super Admin|Admin')->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::put('/', [SettingsController::class, 'update'])->name('update');
            Route::post('/maintenance', [SettingsController::class, 'toggleMaintenance'])->name('maintenance');
        });
        
        /*
        |--------------------------------------------------------------------------
        | Reportes Administrativos
        |--------------------------------------------------------------------------
        */
        Route::middleware('role:Super Admin|Admin|Gerente')->prefix('reports')->name('reports.')->group(function () {
            Route::get('/', function () {
                return view('admin.reports.index');
            })->name('index');
            
            Route::get('/users', function () {
                return view('admin.reports.users');
            })->name('users');
            
            Route::get('/security', function () {
                return view('admin.reports.security');
            })->name('security');
            
            Route::get('/system', function () {
                return view('admin.reports.system');
            })->name('system');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | API Administrativo
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/admin')->name('api.admin.')->group(function () {
        // APIs para usuarios
        Route::middleware('role:Super Admin|Admin|Gerente')->group(function () {
            Route::get('/users/search', [UserManagementController::class, 'search'])->name('users.search');
            Route::get('/users/stats', [UserManagementController::class, 'stats'])->name('users.stats');
            Route::get('/security/dashboard', [SecurityController::class, 'dashboard'])->name('security.dashboard');
            Route::get('/activity/recent', [ActivityController::class, 'recent'])->name('activity.recent');
        });
    });
});