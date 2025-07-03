<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\SecurityController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\EmailVerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Las rutas de autenticación básica (login/logout) se movieron a auth.php
| para evitar conflictos. Aquí solo van las rutas específicas de API.
|
*/

// API Version 1 - Todas las rutas con prefijo 'v1'
Route::prefix('v1')->name('api.v1.')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Rutas Públicas (Sin autenticación)
    |--------------------------------------------------------------------------
    */
    
    // Verificación de email (con token)
    Route::prefix('email')->name('email.')->group(function () {
        Route::post('/verify', [EmailVerificationController::class, 'verify'])->name('verify');
        Route::post('/resend', [EmailVerificationController::class, 'resend'])->name('resend');
    });
    
    // Validaciones públicas
    Route::prefix('validate')->name('validate.')->group(function () {
        Route::post('/username', [RegisterController::class, 'checkUsername'])->name('username');
        Route::post('/email', [RegisterController::class, 'checkEmail'])->name('email');
    });
    
    // Información del sistema (pública)
    Route::get('/system/info', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'app_name' => config('app.name'),
                'version' => '1.0.0',
                'environment' => app()->environment(),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale')
            ]
        ]);
    })->name('system.info');
    
    /*
    |--------------------------------------------------------------------------
    | Rutas Protegidas (Requieren autenticación)
    |--------------------------------------------------------------------------
    */
    
    Route::middleware(['auth:sanctum', 'active', 'track.activity'])->group(function () {
        
        // ===== GESTIÓN DE CONTRASEÑAS =====
        Route::prefix('password')->name('password.')->group(function () {
            Route::post('/change', [PasswordController::class, 'changePassword'])->name('change');
            Route::get('/check-expiry', [PasswordController::class, 'checkPasswordExpiry'])->name('check-expiry');
            // Solo administradores pueden forzar cambios
            Route::middleware(['role:Super Admin|Gerente'])->group(function () {
                Route::post('/force-change/{user}', [PasswordController::class, 'forcePasswordChange'])->name('force-change');
            });
        });
        
        // ===== VERIFICACIÓN DE EMAIL =====
        Route::prefix('email')->name('email.')->group(function () {
            Route::get('/status', [EmailVerificationController::class, 'status'])->name('status');
            Route::post('/change', [EmailVerificationController::class, 'changeEmail'])->name('change');
            // Solo administradores pueden forzar verificación
            Route::middleware(['role:Super Admin|Gerente'])->group(function () {
                Route::post('/force-verify/{user}', [EmailVerificationController::class, 'forceVerify'])->name('force-verify');
                Route::get('/unverified', [EmailVerificationController::class, 'unverifiedUsers'])->name('unverified');
            });
        });
        
        // ===== GESTIÓN DE USUARIOS =====
        Route::middleware(['role:Super Admin|Gerente'])->prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/available', [UserController::class, 'available'])->name('available');
            Route::get('/stats', [UserController::class, 'stats'])->name('stats');
            
            Route::prefix('{user}')->group(function () {
                Route::get('/', [UserController::class, 'show'])->name('show');
                Route::put('/', [UserController::class, 'update'])->name('update');
                Route::delete('/', [UserController::class, 'destroy'])->name('destroy');
                
                // Gestión de roles
                Route::post('/assign-role', [UserController::class, 'assignRole'])->name('assign-role');
                Route::delete('/remove-role', [UserController::class, 'removeRole'])->name('remove-role');
                
                // Información adicional
                Route::get('/permissions', [UserController::class, 'permissions'])->name('permissions');
                Route::get('/activity', [UserController::class, 'activity'])->name('activity');
                
                // Acciones administrativas
                Route::post('/toggle-block', [UserController::class, 'toggleBlock'])->name('toggle-block');
                Route::post('/force-password-change', [UserController::class, 'forcePasswordChange'])->name('force-password-change');
            });
        });
        
        // ===== REGISTRO DE USUARIOS (Solo administradores) =====
        Route::middleware(['role:Super Admin|Gerente'])->prefix('register')->name('register.')->group(function () {
            Route::post('/', [RegisterController::class, 'register'])->name('user');
            Route::post('/bulk', [RegisterController::class, 'bulkRegister'])->name('bulk');
            Route::get('/available-roles', [RegisterController::class, 'getAvailableRoles'])->name('available-roles');
        });
        
        // ===== LOGS DE SEGURIDAD =====
        Route::middleware(['role:Super Admin|Gerente'])->prefix('security')->name('security.')->group(function () {
            Route::get('/logs', [SecurityController::class, 'index'])->name('logs.index');
            Route::get('/logs/{log}', [SecurityController::class, 'show'])->name('logs.show');
            Route::post('/logs/{log}/resolve', [SecurityController::class, 'resolve'])->name('logs.resolve');
            Route::get('/dashboard', [SecurityController::class, 'dashboard'])->name('dashboard');
            Route::get('/stats', [SecurityController::class, 'stats'])->name('stats');
            Route::get('/export', [SecurityController::class, 'export'])->name('export');
            Route::get('/detect-patterns', [SecurityController::class, 'detectPatterns'])->name('detect-patterns');
        });
        
        // ===== ACTIVIDAD DE USUARIOS =====
        Route::middleware(['role:Super Admin|Gerente|Supervisor'])->prefix('activities')->name('activities.')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('index');
            Route::get('/dashboard', [ActivityController::class, 'dashboard'])->name('dashboard');
            Route::get('/export', [ActivityController::class, 'export'])->name('export');
            Route::get('/user-summary', [ActivityController::class, 'userSummary'])->name('user-summary');
            Route::get('/detect-anomalies', [ActivityController::class, 'detectAnomalies'])->name('detect-anomalies');
            
            // Por usuario específico
            Route::get('/user/{user}', [ActivityController::class, 'byUser'])->name('by-user');
            
            // Por módulo específico
            Route::get('/module/{module}', [ActivityController::class, 'byModule'])->name('by-module');
        });
        
        // ===== PERMISOS Y ROLES =====
        Route::middleware(['role:Super Admin'])->prefix('permissions')->name('permissions.')->group(function () {
            // Permisos
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::get('/stats', [PermissionController::class, 'stats'])->name('stats');
            Route::get('/matrix', [PermissionController::class, 'permissionMatrix'])->name('matrix');
            
            Route::prefix('{permission}')->group(function () {
                Route::get('/', [PermissionController::class, 'show'])->name('show');
                Route::put('/', [PermissionController::class, 'update'])->name('update');
                Route::delete('/', [PermissionController::class, 'destroy'])->name('destroy');
            });
        });
        
        Route::middleware(['role:Super Admin'])->prefix('roles')->name('roles.')->group(function () {
            // Roles
            Route::get('/', [PermissionController::class, 'roles'])->name('index');
            Route::post('/', [PermissionController::class, 'createRole'])->name('store');
            
            Route::prefix('{role}')->group(function () {
                Route::get('/', [PermissionController::class, 'showRole'])->name('show');
                Route::put('/', [PermissionController::class, 'updateRole'])->name('update');
                Route::delete('/', [PermissionController::class, 'deleteRole'])->name('destroy');
                Route::put('/permissions', [PermissionController::class, 'updateRolePermissions'])->name('permissions.update');
            });
        });
        
        // ===== DASHBOARD Y ESTADÍSTICAS GENERALES =====
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/overview', function () {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'user' => auth()->user()->load('roles'),
                        'stats' => [
                            'total_users' => \App\Models\Usuario::count(),
                            'active_users' => \App\Models\Usuario::where('activo', true)->count(),
                            'recent_activities' => \App\Models\UserActivity::where('created_at', '>=', now()->subDays(7))->count(),
                            'security_events' => \App\Models\SecurityLog::where('created_at', '>=', now()->subDays(7))->count()
                        ]
                    ]
                ]);
            })->name('overview');
        });
        
        // ===== CONFIGURACIÓN PERSONAL =====
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', function () {
                return response()->json([
                    'success' => true,
                    'data' => new \App\Http\Resources\Auth\UserProfileResource(auth()->user()->load('roles', 'permissions'))
                ]);
            })->name('show');
            
            Route::put('/', function (Illuminate\Http\Request $request) {
                $user = auth()->user();
                
                $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                    'nombres' => 'sometimes|string|max:100',
                    'apellidos' => 'sometimes|string|max:100',
                    'telefono' => 'nullable|string|max:20',
                    'fecha_nacimiento' => 'nullable|date|before:today',
                    'direccion' => 'nullable|string|max:255',
                    'timezone' => 'nullable|string',
                    'locale' => 'nullable|string|in:es,en',
                    'theme' => 'nullable|string|in:light,dark,auto'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Datos de validación incorrectos',
                        'errors' => $validator->errors()
                    ], 422);
                }

                $user->update($request->only([
                    'nombres', 'apellidos', 'telefono', 'fecha_nacimiento', 
                    'direccion', 'timezone', 'locale', 'theme'
                ]));

                // Log actividad
                \App\Models\UserActivity::create([
                    'usuario_id' => $user->id,
                    'accion' => 'profile_updated',
                    'modulo' => 'perfil',
                    'detalles' => json_encode(['updated_fields' => array_keys($request->only([
                        'nombres', 'apellidos', 'telefono', 'fecha_nacimiento', 
                        'direccion', 'timezone', 'locale', 'theme'
                    ]))]),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Perfil actualizado correctamente',
                    'data' => new \App\Http\Resources\Auth\UserProfileResource($user->fresh(['roles', 'permissions']))
                ]);
            })->name('update');
        });
        
        // ===== BÚSQUEDA GLOBAL =====
        Route::get('/search', function (Illuminate\Http\Request $request) {
            $query = $request->get('q');
            
            if (!$query || strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'La búsqueda debe tener al menos 2 caracteres'
                ], 400);
            }
            
            $results = [];
            $user = auth()->user();
            
            // Buscar usuarios (si tiene permisos)
            if ($user->can('viewAny', \App\Models\Usuario::class)) {
                $users = \App\Models\Usuario::where('username', 'like', "%{$query}%")
                    ->orWhere('nombres', 'like', "%{$query}%")
                    ->orWhere('apellidos', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->limit(5)
                    ->get(['id', 'username', 'nombres', 'apellidos', 'email']);
                
                foreach ($users as $foundUser) {
                    $results[] = [
                        'type' => 'user',
                        'id' => $foundUser->id,
                        'title' => $foundUser->nombres . ' ' . $foundUser->apellidos,
                        'subtitle' => $foundUser->username . ' - ' . $foundUser->email,
                        'url' => "/users/{$foundUser->id}"
                    ];
                }
            }
            
            // Log de búsqueda
            \App\Models\UserActivity::create([
                'usuario_id' => $user->id,
                'accion' => 'search_performed',
                'modulo' => 'busqueda',
                'detalles' => json_encode([
                    'query' => $query,
                    'results_count' => count($results)
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'results' => $results,
                    'total' => count($results)
                ]
            ]);
        })->name('search');
        
        // ===== EXPORTACIÓN DE DATOS =====
        Route::prefix('export')->name('export.')->group(function () {
            Route::post('/my-data', function (Illuminate\Http\Request $request) {
                $user = auth()->user();
                
                // Verificar si puede solicitar exportación
                if ($user->last_data_export && $user->last_data_export > now()->subMonth()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Solo puedes solicitar una exportación por mes'
                    ], 429);
                }
                
                // En un sistema real, esto iniciaría un job en background
                $user->update(['last_data_export' => now()]);
                
                // Log solicitud de exportación
                \App\Models\SecurityLog::create([
                    'evento' => 'data_export_requested',
                    'usuario_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'detalles' => json_encode(['export_type' => 'personal_data']),
                    'nivel_riesgo' => 'low'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Solicitud de exportación procesada. Recibirás un email cuando esté lista.'
                ]);
            })->name('my-data');
        });
        
        // ===== HEALTH CHECK =====
        Route::get('/health', function () {
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => 'healthy',
                    'timestamp' => now(),
                    'version' => '1.0.0',
                    'user_authenticated' => true,
                    'user_id' => auth()->id()
                ]
            ]);
        })->name('health');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Rutas de Rate Limiting Dinámico
    |--------------------------------------------------------------------------
    */
    
    // Aplicar rate limiting diferenciado según rol del usuario
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/rate-limit-info', function () {
            $user = auth()->user();
            $limit = 50; // Default
            
            if ($user->hasRole('Super Admin')) {
                $limit = null; // Sin límite
            } elseif ($user->hasRole(['Gerente', 'Supervisor'])) {
                $limit = 200;
            } elseif ($user->hasRole(['Técnico Senior', 'Técnico', 'Vendedor Senior', 'Vendedor'])) {
                $limit = 100;
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rate_limit' => $limit,
                    'rate_limit_per' => 'minute',
                    'current_user_role' => $user->roles->pluck('name'),
                    'reset_time' => now()->addMinute()
                ]
            ]);
        })->name('rate-limit-info');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas de Webhook y Integrations (futuras)
|--------------------------------------------------------------------------
*/

Route::prefix('webhooks')->name('webhooks.')->group(function () {
    // Placeholder para webhooks futuros
    Route::post('/test', function () {
        return response()->json(['received' => true]);
    })->name('test');
});

/*
|--------------------------------------------------------------------------
| Rutas de Métricas y Monitoreo
|--------------------------------------------------------------------------
*/

Route::prefix('metrics')->name('metrics.')->group(function () {
    Route::get('/public', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => \App\Models\Usuario::count(),
                'active_users' => \App\Models\Usuario::where('activo', true)->count(),
                'system_uptime' => '99.9%',
                'last_update' => now()
            ]
        ]);
    })->name('public');
});

