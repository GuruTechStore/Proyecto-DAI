<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use ZipArchive;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
    }

    /**
     * Mostrar configuración del sistema
     */
    public function index()
    {
        abort_unless(auth()->user()->can('configuracion.ver'), 403);

        // Obtener todas las configuraciones
        $settings = Setting::all()->mapWithKeys(function($setting) {
            return [$setting->key => $setting->value];
        });

        // Información del sistema
        $systemInfo = [
            'app_version' => config('app.version', '1.0.0'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database_driver' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
            'storage_driver' => config('filesystems.default'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];

        // Estadísticas del sistema
        $systemStats = [
            'total_users' => \App\Models\Usuario::count(),
            'active_users' => \App\Models\Usuario::where('activo', true)->count(),
            'total_clients' => \App\Models\Cliente::count(),
            'total_products' => \App\Models\Producto::count(),
            'total_sales' => \App\Models\Venta::count(),
            'total_repairs' => \App\Models\Reparacion::count(),
            'database_size' => $this->getDatabaseSize(),
            'storage_used' => $this->getStorageUsage(),
        ];

        // Configuraciones por categorías
        $categories = [
            'general' => [
                'app_name' => $settings->get('app_name', config('app.name')),
                'app_description' => $settings->get('app_description', ''),
                'company_name' => $settings->get('company_name', ''),
                'company_address' => $settings->get('company_address', ''),
                'company_phone' => $settings->get('company_phone', ''),
                'company_email' => $settings->get('company_email', ''),
                'company_website' => $settings->get('company_website', ''),
            ],
            'email' => [
                'mail_from_address' => $settings->get('mail_from_address', config('mail.from.address')),
                'mail_from_name' => $settings->get('mail_from_name', config('mail.from.name')),
                'email_notifications' => $settings->get('email_notifications', true),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
                'smtp_encryption' => config('mail.mailers.smtp.encryption'),
            ],
            'security' => [
                'session_lifetime' => $settings->get('session_lifetime', config('session.lifetime')),
                'password_expires_days' => $settings->get('password_expires_days', 90),
                'max_login_attempts' => $settings->get('max_login_attempts', 5),
                'lockout_duration' => $settings->get('lockout_duration', 15),
                'two_factor_enabled' => $settings->get('two_factor_enabled', false),
                'require_email_verification' => data_get($settings, 'require_email_verification', true),

            ],
            'system' => [
                'maintenance_mode' => app()->isDownForMaintenance(),
                'debug_mode' => config('app.debug'),
                'log_level' => config('logging.level'),
                'cache_enabled' => $settings->get('cache_enabled', true),
                'auto_backup' => $settings->get('auto_backup', false),
                'backup_frequency' => $settings->get('backup_frequency', 'weekly'),
                'cleanup_logs_days' => $settings->get('cleanup_logs_days', 30),
            ],
            'ui' => [
                'items_per_page' => $settings->get('items_per_page', 15),
                'date_format' => $settings->get('date_format', 'd/m/Y'),
                'time_format' => $settings->get('time_format', 'H:i'),
                'currency' => $settings->get('currency', 'PEN'),
                'currency_symbol' => $settings->get('currency_symbol', 'S/'),
                'language' => $settings->get('language', 'es'),
                'theme' => $settings->get('theme', 'light'),
            ]
        ];

        return view('admin.settings.index', compact(
            'categories',
            'systemInfo',
            'systemStats'
        ));
    }

    /**
     * Actualizar configuración
     */
    public function update(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.editar'), 403);

        $validatedData = $request->validate([
            'settings' => 'required|array',
            'category' => 'required|string|in:general,email,security,system,ui'
        ]);

        $settings = $validatedData['settings'];
        $category = $validatedData['category'];

        // Validaciones específicas por categoría
        switch ($category) {
            case 'security':
                $this->validateSecuritySettings($settings);
                break;
            case 'email':
                $this->validateEmailSettings($settings);
                break;
            case 'system':
                $this->validateSystemSettings($settings);
                break;
        }

        // Guardar configuraciones
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'category' => $category]
            );
        }

        // Limpiar cache de configuración
        Cache::forget('app_settings');

        // Log de la acción
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'actualizar_configuracion',
            'modulo' => 'admin',
            'descripcion' => "Configuración de {$category} actualizada",
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_adicionales' => [
                'category' => $category,
                'updated_keys' => array_keys($settings)
            ]
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada exitosamente'
            ]);
        }

        return back()->with('success', 'Configuración actualizada exitosamente');
    }

    /**
     * Página de backups
     */
    public function backup()
    {
        abort_unless(auth()->user()->can('configuracion.ver'), 403);

        // Obtener lista de backups existentes
        $backups = collect();
        if (Storage::disk('backups')->exists('/')) {
            $files = Storage::disk('backups')->files('/');
            $backups = collect($files)
                ->filter(function($file) {
                    return str_ends_with($file, '.zip');
                })
                ->map(function($file) {
                    return [
                        'name' => basename($file),
                        'size' => Storage::disk('backups')->size($file),
                        'created_at' => Carbon::createFromTimestamp(Storage::disk('backups')->lastModified($file))
                    ];
                })
                ->sortByDesc('created_at');
        }

        // Configuración de backup
        $backupConfig = [
            'auto_backup' => Setting::get('auto_backup', false),
            'backup_frequency' => Setting::get('backup_frequency', 'weekly'),
            'keep_backups' => Setting::get('keep_backups', 10),
            'backup_database' => Setting::get('backup_database', true),
            'backup_files' => Setting::get('backup_files', true),
        ];

        return view('admin.settings.backup', compact('backups', 'backupConfig'));
    }

    /**
     * Crear backup
     */
    public function createBackup(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.administrar'), 403);

        $request->validate([
            'include_database' => 'boolean',
            'include_files' => 'boolean',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupName = "backup_{$timestamp}.zip";
            $backupPath = storage_path("app/backups/{$backupName}");

            // Crear directorio si no existe
            if (!File::exists(dirname($backupPath))) {
                File::makeDirectory(dirname($backupPath), 0755, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($backupPath, ZipArchive::CREATE) !== TRUE) {
                throw new \Exception('No se pudo crear el archivo ZIP');
            }

            // Backup de base de datos
            if ($request->boolean('include_database', true)) {
                $this->addDatabaseToBackup($zip);
            }

            // Backup de archivos
            if ($request->boolean('include_files', true)) {
                $this->addFilesToBackup($zip);
            }

            // Agregar información del backup
            $backupInfo = [
                'created_at' => now()->toISOString(),
                'created_by' => auth()->user()->username,
                'description' => $request->description,
                'includes_database' => $request->boolean('include_database', true),
                'includes_files' => $request->boolean('include_files', true),
                'app_version' => config('app.version', '1.0.0'),
                'laravel_version' => app()->version(),
            ];

            $zip->addFromString('backup_info.json', json_encode($backupInfo, JSON_PRETTY_PRINT));
            $zip->close();

            // Log de la acción
            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'crear_backup',
                'modulo' => 'admin',
                'descripcion' => 'Backup del sistema creado',
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'datos_adicionales' => [
                    'backup_file' => $backupName,
                    'includes_database' => $request->boolean('include_database', true),
                    'includes_files' => $request->boolean('include_files', true),
                    'description' => $request->description
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup creado exitosamente',
                'backup_name' => $backupName,
                'backup_size' => File::size($backupPath)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar backup
     */
    public function restoreBackup(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.administrar'), 403);

        $request->validate([
            'backup_file' => 'required|string',
            'restore_database' => 'boolean',
            'restore_files' => 'boolean'
        ]);

        try {
            $backupPath = storage_path("app/backups/{$request->backup_file}");
            
            if (!File::exists($backupPath)) {
                throw new \Exception('Archivo de backup no encontrado');
            }

            $zip = new ZipArchive();
            if ($zip->open($backupPath) !== TRUE) {
                throw new \Exception('No se pudo abrir el archivo de backup');
            }

            // Crear directorio temporal
            $tempDir = storage_path('app/temp/restore_' . time());
            File::makeDirectory($tempDir, 0755, true);

            // Extraer backup
            $zip->extractTo($tempDir);
            $zip->close();

            // Restaurar base de datos
            if ($request->boolean('restore_database', true)) {
                $this->restoreDatabaseFromBackup($tempDir);
            }

            // Restaurar archivos
            if ($request->boolean('restore_files', true)) {
                $this->restoreFilesFromBackup($tempDir);
            }

            // Limpiar directorio temporal
            File::deleteDirectory($tempDir);

            // Log de la acción
            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'restaurar_backup',
                'modulo' => 'admin',
                'descripcion' => 'Backup restaurado',
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'datos_adicionales' => [
                    'backup_file' => $request->backup_file,
                    'restored_database' => $request->boolean('restore_database', true),
                    'restored_files' => $request->boolean('restore_files', true)
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup restaurado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar cache del sistema
     */
    public function clearCache(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.administrar'), 403);

        try {
            // Limpiar diferentes tipos de cache
            $cacheTypes = $request->get('cache_types', ['config', 'route', 'view', 'application']);

            $clearedCaches = [];

            if (in_array('config', $cacheTypes)) {
                Artisan::call('config:clear');
                $clearedCaches[] = 'Configuración';
            }

            if (in_array('route', $cacheTypes)) {
                Artisan::call('route:clear');
                $clearedCaches[] = 'Rutas';
            }

            if (in_array('view', $cacheTypes)) {
                Artisan::call('view:clear');
                $clearedCaches[] = 'Vistas';
            }

            if (in_array('application', $cacheTypes)) {
                Artisan::call('cache:clear');
                $clearedCaches[] = 'Aplicación';
            }

            // Log de la acción
            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'limpiar_cache',
                'modulo' => 'admin',
                'descripcion' => 'Cache del sistema limpiado',
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'datos_adicionales' => [
                    'cache_types' => $cacheTypes,
                    'cleared_caches' => $clearedCaches
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cache limpiado exitosamente: ' . implode(', ', $clearedCaches)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimizar sistema
     */
    public function optimizeSystem(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.administrar'), 403);

        try {
            $optimizations = [];

            // Cache de configuración
            Artisan::call('config:cache');
            $optimizations[] = 'Configuración cacheada';

            // Cache de rutas
            Artisan::call('route:cache');
            $optimizations[] = 'Rutas cacheadas';

            // Cache de vistas
            Artisan::call('view:cache');
            $optimizations[] = 'Vistas cacheadas';

            // Optimizar autoloader
            Artisan::call('optimize');
            $optimizations[] = 'Autoloader optimizado';

            // Log de la acción
            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'optimizar_sistema',
                'modulo' => 'admin',
                'descripcion' => 'Sistema optimizado',
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'datos_adicionales' => [
                    'optimizations' => $optimizations
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sistema optimizado exitosamente',
                'optimizations' => $optimizations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al optimizar sistema: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar configuraciones de seguridad
     */
    private function validateSecuritySettings($settings)
    {
        $rules = [
            'session_lifetime' => 'integer|min:30|max:1440',
            'password_expires_days' => 'integer|min:30|max:365',
            'max_login_attempts' => 'integer|min:3|max:10',
            'lockout_duration' => 'integer|min:5|max:60',
            'two_factor_enabled' => 'boolean',
            'require_email_verification' => 'boolean'
        ];

        $validator = validator($settings, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    /**
     * Validar configuraciones de email
     */
    private function validateEmailSettings($settings)
    {
        $rules = [
            'mail_from_address' => 'email',
            'mail_from_name' => 'string|max:255',
            'email_notifications' => 'boolean'
        ];

        $validator = validator($settings, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    /**
     * Validar configuraciones del sistema
     */
    private function validateSystemSettings($settings)
    {
        $rules = [
            'cache_enabled' => 'boolean',
            'auto_backup' => 'boolean',
            'backup_frequency' => 'in:daily,weekly,monthly',
            'cleanup_logs_days' => 'integer|min:7|max:365'
        ];

        $validator = validator($settings, $rules);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    /**
     * Obtener tamaño de la base de datos
     */
    private function getDatabaseSize()
    {
        try {
            $database = config('database.connections.' . config('database.default') . '.database');
            
            if (config('database.default') === 'mysql') {
                $size = DB::select("
                    SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'size'
                    FROM information_schema.tables 
                    WHERE table_schema = '{$database}'
                ")[0]->size ?? 0;
                
                return $size . ' MB';
            }
            
            return 'N/A';
        } catch (\Exception $e) {
            return 'Error';
        }
    }

    /**
     * Obtener uso de almacenamiento
     */
    private function getStorageUsage()
    {
        try {
            $storagePath = storage_path();
            $bytes = 0;
            
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($storagePath)) as $file) {
                if ($file->isFile()) {
                    $bytes += $file->getSize();
                }
            }
            
            return $this->formatBytes($bytes);
        } catch (\Exception $e) {
            return 'Error';
        }
    }

    /**
     * Formatear bytes a unidades legibles
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Agregar base de datos al backup
     */
    private function addDatabaseToBackup(ZipArchive $zip)
    {
        $database = config('database.connections.' . config('database.default') . '.database');
        $username = config('database.connections.' . config('database.default') . '.username');
        $password = config('database.connections.' . config('database.default') . '.password');
        $host = config('database.connections.' . config('database.default') . '.host');

        $dumpFile = storage_path('app/temp/database_dump.sql');
        
        // Crear directorio temporal si no existe
        if (!File::exists(dirname($dumpFile))) {
            File::makeDirectory(dirname($dumpFile), 0755, true);
        }

        if (config('database.default') === 'mysql') {
            $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > {$dumpFile}";
            exec($command);
            
            if (File::exists($dumpFile)) {
                $zip->addFile($dumpFile, 'database.sql');
            }
        }
    }

    /**
     * Agregar archivos al backup
     */
    private function addFilesToBackup(ZipArchive $zip)
    {
        $directories = [
            'storage/app/public',
            'storage/app/uploads',
            '.env'
        ];

        foreach ($directories as $dir) {
            $fullPath = base_path($dir);
            
            if (File::exists($fullPath)) {
                if (File::isFile($fullPath)) {
                    $zip->addFile($fullPath, $dir);
                } else {
                    $this->addDirectoryToZip($zip, $fullPath, $dir);
                }
            }
        }
    }

    /**
     * Agregar directorio completo al ZIP
     */
    private function addDirectoryToZip(ZipArchive $zip, $source, $destination)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $zip->addEmptyDir($destination . '/' . $iterator->getSubPathName());
            } elseif ($file->isFile()) {
                $zip->addFile($file, $destination . '/' . $iterator->getSubPathName());
            }
        }
    }

    /**
     * Restaurar base de datos desde backup
     */
    private function restoreDatabaseFromBackup($tempDir)
    {
        $sqlFile = $tempDir . '/database.sql';
        
        if (!File::exists($sqlFile)) {
            throw new \Exception('Archivo de base de datos no encontrado en el backup');
        }

        $database = config('database.connections.' . config('database.default') . '.database');
        $username = config('database.connections.' . config('database.default') . '.username');
        $password = config('database.connections.' . config('database.default') . '.password');
        $host = config('database.connections.' . config('database.default') . '.host');

        if (config('database.default') === 'mysql') {
            $command = "mysql --user={$username} --password={$password} --host={$host} {$database} < {$sqlFile}";
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \Exception('Error al restaurar la base de datos');
            }
        }
    }

    /**
     * Restaurar archivos desde backup
     */
    private function restoreFilesFromBackup($tempDir)
    {
        $filesToRestore = [
            'storage/app/public' => storage_path('app/public'),
            'storage/app/uploads' => storage_path('app/uploads'),
            '.env' => base_path('.env.backup')
        ];

        foreach ($filesToRestore as $source => $destination) {
            $sourcePath = $tempDir . '/' . $source;
            
            if (File::exists($sourcePath)) {
                if (File::isFile($sourcePath)) {
                    File::copy($sourcePath, $destination);
                } else {
                    File::copyDirectory($sourcePath, $destination);
                }
            }
        }
    }

    /**
     * Obtener información del sistema para API
     */
    public function getSystemInfo()
    {
        abort_unless(auth()->user()->can('configuracion.ver'), 403);

        return response()->json([
            'system' => [
                'app_version' => config('app.version', '1.0.0'),
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'environment' => app()->environment(),
                'debug_mode' => config('app.debug'),
                'maintenance_mode' => app()->isDownForMaintenance(),
            ],
            'database' => [
                'driver' => config('database.default'),
                'size' => $this->getDatabaseSize(),
            ],
            'storage' => [
                'used' => $this->getStorageUsage(),
                'driver' => config('filesystems.default'),
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'enabled' => Setting::get('cache_enabled', true),
            ],
            'mail' => [
                'driver' => config('mail.default'),
                'from' => config('mail.from.address'),
            ]
        ]);
    }
}