<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReparacionController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Ruta Principal
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    
    return view('welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Rutas Protegidas
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Dashboard Principal 
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshData'])->name('dashboard.refresh');

    /*
    |--------------------------------------------------------------------------
    | Gestión de Perfil de Usuario
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::get('/security', [ProfileController::class, 'security'])->name('security');
        Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');
        Route::post('/two-factor', [ProfileController::class, 'toggleTwoFactor'])->name('two-factor');
    });

/*
|--------------------------------------------------------------------------
| Gestión de Clientes
|--------------------------------------------------------------------------
*/
Route::middleware('permission:clientes.ver')->prefix('clientes')->name('clientes.')->group(function () {
    // Rutas principales del CRUD
    Route::get('/', [ClienteController::class, 'index'])->name('index');
    Route::get('/create', [ClienteController::class, 'create'])->name('create')->middleware('permission:clientes.crear');
    Route::post('/', [ClienteController::class, 'store'])->name('store')->middleware('permission:clientes.crear');
    Route::get('/{cliente}', [ClienteController::class, 'show'])->name('show');
    Route::get('/{cliente}/edit', [ClienteController::class, 'edit'])->name('edit')->middleware('permission:clientes.editar');
    Route::put('/{cliente}', [ClienteController::class, 'update'])->name('update')->middleware('permission:clientes.editar');
    Route::delete('/{cliente}', [ClienteController::class, 'destroy'])->name('destroy')->middleware('permission:clientes.eliminar');
    
    // Rutas adicionales
    Route::post('/bulk-delete', [ClienteController::class, 'bulkDelete'])->name('bulk-delete')->middleware('permission:clientes.eliminar');
    Route::get('/export/excel', [ClienteController::class, 'export'])->name('export');
    Route::get('/search/autocomplete', [ClienteController::class, 'search'])->name('search');
});

// Rutas API para clientes (fuera del middleware de permisos para evitar conflictos)
Route::prefix('api/clientes')->name('api.clientes.')->middleware(['auth', 'permission:clientes.ver'])->group(function () {
    Route::get('/', [ClienteController::class, 'apiIndex'])->name('index'); // Para paginación AJAX
    Route::get('/stats', [ClienteController::class, 'getStats'])->name('stats'); // Para estadísticas
    Route::get('/search', [ClienteController::class, 'search'])->name('search'); // Para búsqueda
    Route::get('/{cliente}/data', [ClienteController::class, 'getData'])->name('data'); // Para obtener datos específicos
});
    /*
    |--------------------------------------------------------------------------
    | Gestión de Productos
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:productos.ver')->prefix('productos')->name('productos.')->group(function () {
        Route::get('/', [ProductoController::class, 'index'])->name('index');
        Route::get('/create', [ProductoController::class, 'create'])
            ->middleware('permission:productos.crear')
            ->name('create');
        Route::post('/', [ProductoController::class, 'store'])
            ->middleware('permission:productos.crear')
            ->name('store');
        Route::get('/{producto}', [ProductoController::class, 'show'])->name('show');
        Route::get('/{producto}/edit', [ProductoController::class, 'edit'])
            ->middleware('permission:productos.editar')
            ->name('edit');
        Route::put('/{producto}', [ProductoController::class, 'update'])
            ->middleware('permission:productos.editar')
            ->name('update');
        Route::delete('/{producto}', [ProductoController::class, 'destroy'])
            ->middleware('permission:productos.eliminar')
            ->name('destroy');
        
        // Gestión de stock
        Route::post('/{producto}/adjust-stock', [ProductoController::class, 'adjustStock'])
            ->middleware('permission:productos.editar')
            ->name('adjust-stock');
        Route::get('/stock/alerts', [ProductoController::class, 'stockAlerts'])->name('stock-alerts');
        Route::get('/stock-bajo', [ProductoController::class, 'stockBajo'])->name('stock-bajo');
        Route::get('/sin-stock', [ProductoController::class, 'sinStock'])->name('sin-stock');
        Route::get('/export/excel', [ProductoController::class, 'exportExcel'])->name('export.excel');
        
        // API endpoints
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/search', [ProductoController::class, 'search'])->name('search');
            Route::get('/{producto}/stock', [ProductoController::class, 'getStock'])->name('stock');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Gestión de Categorías
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:productos.ver')->prefix('categorias')->name('categorias.')->group(function () {
        Route::get('/', [CategoriaController::class, 'index'])->name('index');
        Route::get('/create', [CategoriaController::class, 'create'])
            ->middleware('permission:productos.crear')
            ->name('create');
        Route::post('/', [CategoriaController::class, 'store'])
            ->middleware('permission:productos.crear')
            ->name('store');
        Route::get('/{categoria}', [CategoriaController::class, 'show'])->name('show');
        Route::get('/{categoria}/edit', [CategoriaController::class, 'edit'])
            ->middleware('permission:productos.editar')
            ->name('edit');
        Route::put('/{categoria}', [CategoriaController::class, 'update'])
            ->middleware('permission:productos.editar')
            ->name('update');
        Route::delete('/{categoria}', [CategoriaController::class, 'destroy'])
            ->middleware('permission:productos.eliminar')
            ->name('destroy');
        
        Route::get('/{categoria}/productos', [CategoriaController::class, 'productos'])->name('productos');
        Route::post('/bulk-delete', [CategoriaController::class, 'bulkDelete'])
            ->middleware('permission:productos.eliminar')
            ->name('bulk-delete');
    });

    /*
    |--------------------------------------------------------------------------
    | Gestión de Proveedores
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:proveedores.ver')->prefix('proveedores')->name('proveedores.')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('index');
        Route::get('/create', [ProveedorController::class, 'create'])
            ->middleware('permission:proveedores.crear')
            ->name('create');
        Route::post('/', [ProveedorController::class, 'store'])
            ->middleware('permission:proveedores.crear')
            ->name('store');
        Route::get('/{proveedor}', [ProveedorController::class, 'show'])->name('show');
        Route::get('/{proveedor}/edit', [ProveedorController::class, 'edit'])
            ->middleware('permission:proveedores.editar')
            ->name('edit');
        Route::put('/{proveedor}', [ProveedorController::class, 'update'])
            ->middleware('permission:proveedores.editar')
            ->name('update');
        Route::delete('/{proveedor}', [ProveedorController::class, 'destroy'])
            ->middleware('permission:proveedores.eliminar')
            ->name('destroy');
        
        Route::get('/{proveedor}/productos', [ProveedorController::class, 'productos'])->name('productos');
        Route::get('/{proveedor}/compras', [ProveedorController::class, 'compras'])->name('compras');
        Route::post('/bulk-delete', [ProveedorController::class, 'bulkDelete'])
            ->middleware('permission:proveedores.eliminar')
            ->name('bulk-delete');
        Route::get('/export/excel', [ProveedorController::class, 'exportExcel'])->name('export.excel');
    });

    /*
    |--------------------------------------------------------------------------
    | Gestión de Empleados
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:empleados.ver')->prefix('empleados')->name('empleados.')->group(function () {
        Route::get('/', [EmpleadoController::class, 'index'])->name('index');
        Route::get('/create', [EmpleadoController::class, 'create'])
            ->middleware('permission:empleados.crear')
            ->name('create');
        Route::post('/', [EmpleadoController::class, 'store'])
            ->middleware('permission:empleados.crear')
            ->name('store');
        Route::get('/{empleado}', [EmpleadoController::class, 'show'])->name('show');
        Route::get('/{empleado}/edit', [EmpleadoController::class, 'edit'])
            ->middleware('permission:empleados.editar')
            ->name('edit');
        Route::put('/{empleado}', [EmpleadoController::class, 'update'])
            ->middleware('permission:empleados.editar')
            ->name('update');
        Route::delete('/{empleado}', [EmpleadoController::class, 'destroy'])
            ->middleware('permission:empleados.eliminar')
            ->name('destroy');
        Route::post('/{empleado}/crear-usuario', [EmpleadoController::class, 'crearUsuario'])
            ->middleware('permission:usuarios.crear')
            ->name('crear-usuario');
        // Rutas adicionales
        Route::post('/{empleado}/toggle-status', [EmpleadoController::class, 'toggleStatus'])
            ->middleware('permission:empleados.editar')
            ->name('toggle-status');
        Route::get('/{empleado}/reparaciones', [EmpleadoController::class, 'reparaciones'])->name('reparaciones');
        Route::get('/{empleado}/rendimiento', [EmpleadoController::class, 'rendimiento'])->name('rendimiento');
        Route::post('/bulk-delete', [EmpleadoController::class, 'bulkDelete'])
            ->middleware('permission:empleados.eliminar')
            ->name('bulk-delete');
        Route::get('/export/excel', [EmpleadoController::class, 'exportExcel'])->name('export.excel');
        
        // API endpoints
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/search', [EmpleadoController::class, 'search'])->name('search');
            Route::get('/activos', [EmpleadoController::class, 'activos'])->name('activos');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Gestión de Reparaciones
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:reparaciones.ver')->prefix('reparaciones')->name('reparaciones.')->group(function () {
        Route::get('/', [ReparacionController::class, 'index'])->name('index');
        Route::get('/create', [ReparacionController::class, 'create'])
            ->middleware('permission:reparaciones.crear')
            ->name('create');
        Route::post('/', [ReparacionController::class, 'store'])
            ->middleware('permission:reparaciones.crear')
            ->name('store');
        Route::get('/{reparacion}', [ReparacionController::class, 'show'])->name('show');
        Route::get('/{reparacion}/edit', [ReparacionController::class, 'edit'])
            ->middleware('permission:reparaciones.editar')
            ->name('edit');
        Route::put('/{reparacion}', [ReparacionController::class, 'update'])
            ->middleware('permission:reparaciones.editar')
            ->name('update');
        Route::delete('/{reparacion}', [ReparacionController::class, 'destroy'])
            ->middleware('permission:reparaciones.eliminar')
            ->name('destroy');
        
        // Gestión de estados
        Route::post('/{reparacion}/change-status', [ReparacionController::class, 'changeStatus'])
            ->middleware('permission:reparaciones.editar')
            ->name('change-status');
        Route::post('/{reparacion}/add-diagnostic', [ReparacionController::class, 'addDiagnostic'])
            ->middleware('permission:reparaciones.editar')
            ->name('add-diagnostic');
        Route::post('/{reparacion}/complete', [ReparacionController::class, 'complete'])
            ->middleware('permission:reparaciones.editar')
            ->name('complete');
        
        // Filtros especiales
        Route::get('/pendientes', [ReparacionController::class, 'pendientes'])->name('pendientes');
        Route::get('/en-proceso', [ReparacionController::class, 'enProceso'])->name('en-proceso');
        Route::get('/completadas', [ReparacionController::class, 'completadas'])->name('completadas');
        
        // Gestión de archivos
        Route::post('/{reparacion}/upload-file', [ReparacionController::class, 'uploadFile'])
            ->middleware('permission:reparaciones.editar')
            ->name('upload-file');
        Route::delete('/{reparacion}/files/{file}', [ReparacionController::class, 'deleteFile'])
            ->middleware('permission:reparaciones.editar')
            ->name('delete-file');
        
        // API endpoints
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/search', [ReparacionController::class, 'search'])->name('search');
            Route::get('/{reparacion}/timeline', [ReparacionController::class, 'getTimeline'])->name('timeline');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Gestión de Ventas
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:ventas.ver')->prefix('ventas')->name('ventas.')->group(function () {
        Route::get('/', [VentaController::class, 'index'])->name('index');
        Route::get('/create', [VentaController::class, 'create'])
            ->middleware('permission:ventas.crear')
            ->name('create');
        Route::post('/', [VentaController::class, 'store'])
            ->middleware('permission:ventas.crear')
            ->name('store');
        Route::get('/{venta}', [VentaController::class, 'show'])->name('show');
        Route::get('/{venta}/edit', [VentaController::class, 'edit'])
            ->middleware('permission:ventas.editar')
            ->name('edit');
        Route::put('/{venta}', [VentaController::class, 'update'])
            ->middleware('permission:ventas.editar')
            ->name('update');
        Route::delete('/{venta}', [VentaController::class, 'destroy'])
            ->middleware('permission:ventas.eliminar')
            ->name('destroy');
        
        // Funcionalidades específicas
        Route::post('/{venta}/refund', [VentaController::class, 'refund'])
            ->middleware('permission:ventas.editar')
            ->name('refund');
        Route::get('/{venta}/invoice', [VentaController::class, 'generateInvoice'])->name('invoice');
        Route::get('/{venta}/receipt', [VentaController::class, 'generateReceipt'])->name('receipt');
        
        // API endpoints
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/search', [VentaController::class, 'search'])->name('search');
            Route::post('/calculate-total', [VentaController::class, 'calculateTotal'])->name('calculate-total');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Sistema de Reportes
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:reportes.ver')->prefix('reportes')->name('reportes.')->group(function () {
        // Dashboard principal
        Route::get('/', [ReporteController::class, 'index'])->name('index');
        Route::get('/dashboard', [ReporteController::class, 'dashboard'])->name('dashboard');
        
        // Reportes de Ventas
        Route::middleware('permission:reportes.ventas')->prefix('ventas')->name('ventas.')->group(function () {
            Route::get('/', [ReporteController::class, 'ventasIndex'])->name('index');
            Route::get('/dashboard', [ReporteController::class, 'ventasDashboard'])->name('dashboard');
            Route::get('/diario', [ReporteController::class, 'ventasDiario'])->name('diario');
            Route::get('/mensual', [ReporteController::class, 'ventasMensual'])->name('mensual');
            Route::get('/por-producto', [ReporteController::class, 'ventasPorProducto'])->name('por-producto');
            Route::get('/por-empleado', [ReporteController::class, 'ventasPorEmpleado'])->name('por-empleado');
            Route::post('/export', [ReporteController::class, 'exportVentas'])->name('export');
        });
        
        // Reportes de Inventario
        Route::middleware('permission:reportes.inventario')->prefix('inventario')->name('inventario.')->group(function () {
            Route::get('/', [ReporteController::class, 'inventarioIndex'])->name('index');
            Route::get('/dashboard', [ReporteController::class, 'inventarioDashboard'])->name('dashboard');
            Route::get('/stock-actual', [ReporteController::class, 'stockActual'])->name('stock-actual');
            Route::get('/movimientos', [ReporteController::class, 'movimientosStock'])->name('movimientos');
            Route::get('/valorizado', [ReporteController::class, 'inventarioValorizado'])->name('valorizado');
            Route::post('/export', [ReporteController::class, 'exportInventario'])->name('export');
        });
        
        // Reportes de Reparaciones
        Route::middleware('permission:reportes.reparaciones')->prefix('reparaciones')->name('reparaciones.')->group(function () {
            Route::get('/', [ReporteController::class, 'reparacionesIndex'])->name('index');
            Route::get('/dashboard', [ReporteController::class, 'reparacionesDashboard'])->name('dashboard');
            Route::get('/pendientes', [ReporteController::class, 'reparacionesPendientes'])->name('pendientes');
            Route::get('/completadas', [ReporteController::class, 'reparacionesCompletadas'])->name('completadas');
            Route::get('/por-tecnico', [ReporteController::class, 'reparacionesPorTecnico'])->name('por-tecnico');
            Route::get('/tiempo-promedio', [ReporteController::class, 'tiempoPromedio'])->name('tiempo-promedio');
            Route::post('/export', [ReporteController::class, 'exportReparaciones'])->name('export');
        });
        
        // Reportes Financieros
        Route::middleware('permission:reportes.financieros')->prefix('financieros')->name('financieros.')->group(function () {
            Route::get('/', [ReporteController::class, 'financierosIndex'])->name('index');
            Route::get('/dashboard', [ReporteController::class, 'financierosDashboard'])->name('dashboard');
            Route::get('/ingresos', [ReporteController::class, 'reporteIngresos'])->name('ingresos');
            Route::get('/gastos', [ReporteController::class, 'reporteGastos'])->name('gastos');
            Route::get('/utilidades', [ReporteController::class, 'reporteUtilidades'])->name('utilidades');
            Route::get('/flujo-caja', [ReporteController::class, 'flujoCaja'])->name('flujo-caja');
            Route::post('/export', [ReporteController::class, 'exportFinancieros'])->name('export');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Administración del Sistema
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Super Admin|Gerente')->prefix('admin')->name('admin.')->group(function () {
        
        // Gestión de Usuarios
        Route::middleware('permission:usuarios.ver')->prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::get('/create', [UserManagementController::class, 'create'])
                ->middleware('permission:usuarios.crear')
                ->name('create');
            Route::post('/', [UserManagementController::class, 'store'])
                ->middleware('permission:usuarios.crear')
                ->name('store');
            Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserManagementController::class, 'edit'])
                ->middleware('permission:usuarios.editar')
                ->name('edit');
            Route::put('/{user}', [UserManagementController::class, 'update'])
                ->middleware('permission:usuarios.editar')
                ->name('update');
            Route::delete('/{user}', [UserManagementController::class, 'destroy'])
                ->middleware('permission:usuarios.eliminar')
                ->name('destroy');
            
            // Gestión de roles y permisos
            Route::post('/{user}/assign-role', [UserManagementController::class, 'assignRole'])
                ->middleware('permission:usuarios.editar')
                ->name('assign-role');
            Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])
                ->middleware('permission:usuarios.editar')
                ->name('toggle-status');
            Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
                ->middleware('permission:usuarios.editar')
                ->name('reset-password');
        });
        
        // Panel de Seguridad
        Route::middleware('permission:seguridad.ver')->prefix('security')->name('security.')->group(function () {
            Route::get('/', [SecurityController::class, 'dashboard'])->name('dashboard');
            Route::get('/logs', [SecurityController::class, 'logs'])->name('logs');
            Route::get('/sessions', [SecurityController::class, 'activeSessions'])->name('sessions');
            Route::post('/sessions/{session}/revoke', [SecurityController::class, 'revokeSession'])
                ->middleware('permission:seguridad.administrar')
                ->name('revoke-session');
            Route::get('/failed-logins', [SecurityController::class, 'failedLogins'])->name('failed-logins');
            Route::post('/block-ip', [SecurityController::class, 'blockIP'])
                ->middleware('permission:seguridad.administrar')
                ->name('block-ip');
        });
        
        // Actividad del Sistema
        Route::middleware('permission:actividad.ver')->prefix('activity')->name('activity.')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('index');
            Route::get('/user/{user}', [ActivityController::class, 'userActivity'])->name('user');
            Route::get('/export', [ActivityController::class, 'export'])->name('export');
            Route::post('/clear-old', [ActivityController::class, 'clearOldActivity'])
                ->middleware('permission:actividad.administrar')
                ->name('clear-old');
        });
        
        // Configuración del Sistema
        Route::middleware('permission:configuracion.ver')->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::post('/update', [SettingsController::class, 'update'])
                ->middleware('permission:configuracion.editar')
                ->name('update');
            Route::get('/backup', [SettingsController::class, 'backup'])->name('backup');
            Route::post('/create-backup', [SettingsController::class, 'createBackup'])
                ->middleware('permission:configuracion.administrar')
                ->name('create-backup');
            Route::post('/restore-backup', [SettingsController::class, 'restoreBackup'])
                ->middleware('permission:configuracion.administrar')
                ->name('restore-backup');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Configuración General
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:configuracion.ver')->prefix('configuracion')->name('configuracion.')->group(function () {
        Route::get('/', [ConfiguracionController::class, 'index'])->name('index');
        Route::get('/empresa', [ConfiguracionController::class, 'empresa'])->name('empresa');
        Route::post('/empresa', [ConfiguracionController::class, 'updateEmpresa'])
            ->middleware('permission:configuracion.editar')
            ->name('empresa.update');
        Route::get('/sistema', [ConfiguracionController::class, 'sistema'])->name('sistema');
        Route::post('/sistema', [ConfiguracionController::class, 'updateSistema'])
            ->middleware('permission:configuracion.editar')
            ->name('sistema.update');
    });

    /*
    |--------------------------------------------------------------------------
    | API Endpoints Generales
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {
        // Búsqueda global
        Route::get('/search', function (Request $request) {
            $query = $request->get('q');
            $results = [];
            
            if (auth()->user()->can('clientes.ver')) {
                $clientes = \App\Models\Cliente::where('nombres', 'like', "%{$query}%")
                    ->orWhere('apellidos', 'like', "%{$query}%")
                    ->orWhere('documento', 'like', "%{$query}%")
                    ->limit(5)->get();
                $results['clientes'] = $clientes;
            }
            
            if (auth()->user()->can('productos.ver')) {
                $productos = \App\Models\Producto::where('nombre', 'like', "%{$query}%")
                    ->orWhere('codigo', 'like', "%{$query}%")
                    ->limit(5)->get();
                $results['productos'] = $productos;
            }
            
            return response()->json($results);
        })->name('search');
        
        // Contadores para sidebar
        Route::get('/sidebar/counters', function () {
            $user = auth()->user();
            $counters = [];
            
            if ($user->can('clientes.ver')) {
                $counters['clientes_nuevos'] = \App\Models\Cliente::whereDate('created_at', today())->count();
            }
            
            if ($user->can('reparaciones.ver')) {
                $counters['reparaciones_pendientes'] = \App\Models\Reparacion::whereIn('estado', ['recibido', 'diagnostico'])->count();
            }
            
            if ($user->can('productos.ver')) {
                $counters['productos_stock_bajo'] = \App\Models\Producto::whereRaw('stock <= stock_minimo')->count();
            }
            
            if ($user->can('ventas.ver')) {
                $counters['ventas_hoy'] = \App\Models\Venta::whereDate('fecha_venta', today())->count();
            }
            
            return response()->json($counters);
        })->name('sidebar.counters');
        
        // Datos para gráficos del dashboard
        Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
        Route::get('/dashboard/stats', [DashboardController::class, 'apiStats'])->name('dashboard.stats');
        
        // Notificaciones
        Route::get('/notifications', [DashboardController::class, 'getNotifications'])->name('notifications');
        Route::post('/notifications/{id}/mark-read', [DashboardController::class, 'markNotificationRead'])->name('notifications.mark-read');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas de Utilidad
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/help', function () {
        return view('help.index');
    })->name('help');

    Route::get('/support', function () {
        return view('support.index');
    })->name('support');

    Route::get('/docs', function () {
        return view('docs.index');
    })->name('docs');
});

/*
|--------------------------------------------------------------------------
| Rutas de Recursos (Imágenes)
|--------------------------------------------------------------------------
*/
Route::get('/storage/productos/{filename}', function ($filename) {
    $path = storage_path('app/public/productos/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }

    $file = file_get_contents($path);
    $type = mime_content_type($path);

    return response($file, 200)->header("Content-Type", $type);
})->name('productos.image');

Route::get('/storage/empleados/{filename}', function ($filename) {
    $path = storage_path('app/public/empleados/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }

    $file = file_get_contents($path);
    $type = mime_content_type($path);

    return response($file, 200)->header("Content-Type", $type);
})->name('empleados.image');

/*
|--------------------------------------------------------------------------
| Rutas de Mantenimiento y Salud del Sistema
|--------------------------------------------------------------------------
*/

// Health check para monitoreo
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0'),
        'environment' => app()->environment(),
        'database' => 'connected',
        'cache' => 'working',
    ]);
})->name('health.check');

// Página de mantenimiento personalizada
Route::get('/maintenance', function () {
    if (!app()->isDownForMaintenance()) {
        return redirect()->route('home');
    }
    
    return view('maintenance');
})->name('maintenance');

/*
|--------------------------------------------------------------------------
| Rutas de Fallback y Manejo de Errores
|--------------------------------------------------------------------------
*/

// Página 404 personalizada
Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json([
            'message' => 'Endpoint no encontrado',
            'error' => 'Not Found'
        ], 404);
    }
    
    return response()->view('errors.404', [], 404);
});

/*
|--------------------------------------------------------------------------
| Rutas de Desarrollo (Solo en modo de desarrollo)
|--------------------------------------------------------------------------
*/
if (app()->environment('local', 'development')) {
    Route::prefix('dev')->name('dev.')->group(function () {
        Route::get('/test-dashboard', [DashboardController::class, 'index'])->name('test-dashboard');
        Route::get('/phpinfo', function () {
            return phpinfo();
        })->name('phpinfo');
        Route::get('/clear-cache', function () {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            return 'Cache cleared!';
        })->name('clear-cache');
    });
}