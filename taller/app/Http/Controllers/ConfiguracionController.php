<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
    }

    /**
     * Mostrar configuración general
     */
    public function index()
    {
        abort_unless(auth()->user()->can('configuracion.ver'), 403);

        $configuraciones = Setting::all()->mapWithKeys(function($setting) {
            return [$setting->key => $setting->value];
        });

        return view('configuracion.index', compact('configuraciones'));
    }

    /**
     * Configuración de empresa
     */
    public function empresa()
    {
        abort_unless(auth()->user()->can('configuracion.ver'), 403);

        $empresa = [
            'nombre' => Setting::get('empresa_nombre', ''),
            'ruc' => Setting::get('empresa_ruc', ''),
            'direccion' => Setting::get('empresa_direccion', ''),
            'telefono' => Setting::get('empresa_telefono', ''),
            'email' => Setting::get('empresa_email', ''),
            'website' => Setting::get('empresa_website', ''),
            'logo' => Setting::get('empresa_logo', ''),
            'descripcion' => Setting::get('empresa_descripcion', ''),
            'slogan' => Setting::get('empresa_slogan', ''),
            'horario_atencion' => Setting::get('empresa_horario_atencion', ''),
            'redes_sociales' => json_decode(Setting::get('empresa_redes_sociales', '{}'), true),
            'datos_facturacion' => json_decode(Setting::get('empresa_datos_facturacion', '{}'), true),
        ];

        return view('configuracion.empresa', compact('empresa'));
    }

    /**
     * Actualizar configuración de empresa
     */
    public function updateEmpresa(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.editar'), 403);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'ruc' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'slogan' => 'nullable|string|max:255',
            'horario_atencion' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'redes_sociales' => 'nullable|array',
            'redes_sociales.facebook' => 'nullable|url',
            'redes_sociales.instagram' => 'nullable|url',
            'redes_sociales.twitter' => 'nullable|url',
            'redes_sociales.linkedin' => 'nullable|url',
            'redes_sociales.youtube' => 'nullable|url',
            'datos_facturacion' => 'nullable|array',
            'datos_facturacion.nombre_fiscal' => 'nullable|string|max:255',
            'datos_facturacion.direccion_fiscal' => 'nullable|string|max:500',
            'datos_facturacion.ubigeo' => 'nullable|string|max:10',
            'datos_facturacion.urbanizacion' => 'nullable|string|max:255',
        ]);

        // Manejar subida de logo
        if ($request->hasFile('logo')) {
            // Eliminar logo anterior si existe
            $logoAnterior = Setting::get('empresa_logo');
            if ($logoAnterior && Storage::disk('public')->exists($logoAnterior)) {
                Storage::disk('public')->delete($logoAnterior);
            }

            // Guardar nuevo logo
            $logoPath = $request->file('logo')->store('empresa', 'public');
            $validated['logo'] = $logoPath;
        } else {
            // Mantener logo actual
            unset($validated['logo']);
        }

        // Guardar configuraciones
        foreach ($validated as $key => $value) {
            if (in_array($key, ['redes_sociales', 'datos_facturacion'])) {
                Setting::updateOrCreate(
                    ['key' => "empresa_{$key}"],
                    ['value' => json_encode($value), 'category' => 'empresa']
                );
            } else {
                Setting::updateOrCreate(
                    ['key' => "empresa_{$key}"],
                    ['value' => $value, 'category' => 'empresa']
                );
            }
        }

        // Limpiar cache
        Cache::forget('empresa_config');

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'actualizar_empresa',
            'modulo' => 'configuracion',
            'descripcion' => 'Configuración de empresa actualizada',
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_adicionales' => [
                'campos_actualizados' => array_keys($validated)
            ]
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Configuración de empresa actualizada exitosamente'
            ]);
        }

        return back()->with('success', 'Configuración de empresa actualizada exitosamente');
    }

    /**
     * Configuración del sistema
     */
    public function sistema()
    {
        abort_unless(auth()->user()->can('configuracion.ver'), 403);

        $sistema = [
            'moneda' => Setting::get('sistema_moneda', 'PEN'),
            'simbolo_moneda' => Setting::get('sistema_simbolo_moneda', 'S/'),
            'idioma' => Setting::get('sistema_idioma', 'es'),
            'zona_horaria' => Setting::get('sistema_zona_horaria', 'America/Lima'),
            'formato_fecha' => Setting::get('sistema_formato_fecha', 'd/m/Y'),
            'formato_hora' => Setting::get('sistema_formato_hora', 'H:i'),
            'items_por_pagina' => Setting::get('sistema_items_por_pagina', 15),
            'tema' => Setting::get('sistema_tema', 'light'),
            'notificaciones_email' => Setting::get('sistema_notificaciones_email', true),
            'notificaciones_sms' => Setting::get('sistema_notificaciones_sms', false),
            'backup_automatico' => Setting::get('sistema_backup_automatico', false),
            'frecuencia_backup' => Setting::get('sistema_frecuencia_backup', 'semanal'),
            'mantener_backups' => Setting::get('sistema_mantener_backups', 10),
            'logs_dias' => Setting::get('sistema_logs_dias', 30),
            'session_timeout' => Setting::get('sistema_session_timeout', 120),
            'max_intentos_login' => Setting::get('sistema_max_intentos_login', 5),
            'tiempo_bloqueo' => Setting::get('sistema_tiempo_bloqueo', 15),
            'verificacion_email' => Setting::get('sistema_verificacion_email', true),
            'two_factor' => Setting::get('sistema_two_factor', false),
            'stock_minimo_alerta' => Setting::get('sistema_stock_minimo_alerta', 5),
            'dias_vencimiento_password' => Setting::get('sistema_dias_vencimiento_password', 90),
        ];

        // Opciones disponibles
        $opciones = [
            'monedas' => [
                'PEN' => 'Sol Peruano (S/)',
                'USD' => 'Dólar Americano ($)',
                'EUR' => 'Euro (€)',
            ],
            'idiomas' => [
                'es' => 'Español',
                'en' => 'Inglés',
            ],
            'zonas_horarias' => [
                'America/Lima' => 'Lima (UTC-5)',
                'America/New_York' => 'Nueva York (UTC-5)',
                'Europe/Madrid' => 'Madrid (UTC+1)',
            ],
            'formatos_fecha' => [
                'd/m/Y' => '31/12/2024',
                'Y-m-d' => '2024-12-31',
                'm/d/Y' => '12/31/2024',
            ],
            'formatos_hora' => [
                'H:i' => '23:59',
                'h:i A' => '11:59 PM',
            ],
            'temas' => [
                'light' => 'Claro',
                'dark' => 'Oscuro',
                'auto' => 'Automático',
            ],
            'frecuencias_backup' => [
                'diario' => 'Diario',
                'semanal' => 'Semanal',
                'mensual' => 'Mensual',
            ],
        ];

        return view('configuracion.sistema', compact('sistema', 'opciones'));
    }

    /**
     * Actualizar configuración del sistema
     */
    public function updateSistema(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.editar'), 403);

        $validated = $request->validate([
            'moneda' => 'required|string|in:PEN,USD,EUR',
            'simbolo_moneda' => 'required|string|max:5',
            'idioma' => 'required|string|in:es,en',
            'zona_horaria' => 'required|string',
            'formato_fecha' => 'required|string',
            'formato_hora' => 'required|string',
            'items_por_pagina' => 'required|integer|min:5|max:100',
            'tema' => 'required|string|in:light,dark,auto',
            'notificaciones_email' => 'boolean',
            'notificaciones_sms' => 'boolean',
            'backup_automatico' => 'boolean',
            'frecuencia_backup' => 'required|string|in:diario,semanal,mensual',
            'mantener_backups' => 'required|integer|min:1|max:50',
            'logs_dias' => 'required|integer|min:7|max:365',
            'session_timeout' => 'required|integer|min:30|max:1440',
            'max_intentos_login' => 'required|integer|min:3|max:10',
            'tiempo_bloqueo' => 'required|integer|min:5|max:60',
            'verificacion_email' => 'boolean',
            'two_factor' => 'boolean',
            'stock_minimo_alerta' => 'required|integer|min:0|max:100',
            'dias_vencimiento_password' => 'required|integer|min:30|max:365',
        ]);

        // Manejar valores booleanos
        $booleanFields = [
            'notificaciones_email',
            'notificaciones_sms', 
            'backup_automatico',
            'verificacion_email',
            'two_factor'
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = $request->boolean($field);
        }

        // Guardar configuraciones
        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => "sistema_{$key}"],
                ['value' => $value, 'category' => 'sistema']
            );
        }

        // Limpiar cache
        Cache::forget('sistema_config');

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'actualizar_sistema',
            'modulo' => 'configuracion',
            'descripcion' => 'Configuración del sistema actualizada',
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_adicionales' => [
                'campos_actualizados' => array_keys($validated)
            ]
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Configuración del sistema actualizada exitosamente'
            ]);
        }

        return back()->with('success', 'Configuración del sistema actualizada exitosamente');
    }

    /**
     * Obtener configuración específica
     */
    public function getConfig($category = null)
    {
        abort_unless(auth()->user()->can('configuracion.ver'), 403);

        $query = Setting::query();
        
        if ($category) {
            $query->where('category', $category);
        }

        $settings = $query->get()->mapWithKeys(function($setting) {
            return [$setting->key => $setting->value];
        });

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Actualizar configuración específica
     */
    public function updateConfig(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.editar'), 403);

        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
            'category' => 'nullable|string'
        ]);

        Setting::updateOrCreate(
            ['key' => $request->key],
            [
                'value' => $request->value,
                'category' => $request->category ?? 'general'
            ]
        );

        // Limpiar cache relacionado
        Cache::forget($request->category . '_config');
        Cache::forget('app_settings');

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'actualizar_configuracion',
            'modulo' => 'configuracion',
            'descripcion' => "Configuración {$request->key} actualizada",
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_adicionales' => [
                'key' => $request->key,
                'category' => $request->category,
                'new_value' => $request->value
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada exitosamente'
        ]);
    }

    /**
     * Eliminar logo de empresa
     */
    public function deleteLogo(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.editar'), 403);

        $logoPath = Setting::get('empresa_logo');
        
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            Storage::disk('public')->delete($logoPath);
        }

        Setting::updateOrCreate(
            ['key' => 'empresa_logo'],
            ['value' => '', 'category' => 'empresa']
        );

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'eliminar_logo',
            'modulo' => 'configuracion',
            'descripcion' => 'Logo de empresa eliminado',
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logo eliminado exitosamente'
        ]);
    }

    /**
     * Resetear configuración a valores por defecto
     */
    public function resetConfig(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.editar'), 403);

        $request->validate([
            'category' => 'required|string|in:empresa,sistema,general'
        ]);

        $category = $request->category;

        // Eliminar configuraciones de la categoría
        Setting::where('category', $category)->delete();

        // Limpiar cache
        Cache::forget($category . '_config');
        Cache::forget('app_settings');

        // Log de actividad
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'resetear_configuracion',
            'modulo' => 'configuracion',
            'descripcion' => "Configuración de {$category} reseteada",
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'datos_adicionales' => [
                'category' => $category
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configuración reseteada exitosamente'
        ]);
    }

    /**
     * Exportar configuración
     */
    public function exportConfig()
    {
        abort_unless(auth()->user()->can('configuracion.ver'), 403);

        $settings = Setting::all()->mapWithKeys(function($setting) {
            return [$setting->key => [
                'value' => $setting->value,
                'category' => $setting->category
            ]];
        });

        $export = [
            'exported_at' => now()->toISOString(),
            'exported_by' => auth()->user()->username,
            'app_version' => config('app.version', '1.0.0'),
            'settings' => $settings
        ];

        $filename = 'configuracion_' . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->json($export)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Importar configuración
     */
    public function importConfig(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.editar'), 403);

        $request->validate([
            'config_file' => 'required|file|mimes:json',
            'overwrite' => 'boolean'
        ]);

        try {
            $file = $request->file('config_file');
            $content = json_decode(file_get_contents($file->path()), true);

            if (!isset($content['settings']) || !is_array($content['settings'])) {
                throw new \Exception('Formato de archivo inválido');
            }

            $imported = 0;
            $skipped = 0;

            foreach ($content['settings'] as $key => $data) {
                $exists = Setting::where('key', $key)->exists();

                if (!$exists || $request->boolean('overwrite')) {
                    Setting::updateOrCreate(
                        ['key' => $key],
                        [
                            'value' => $data['value'],
                            'category' => $data['category'] ?? 'general'
                        ]
                    );
                    $imported++;
                } else {
                    $skipped++;
                }
            }

            // Limpiar cache
            Cache::flush();

            // Log de actividad
            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'importar_configuracion',
                'modulo' => 'configuracion',
                'descripcion' => 'Configuración importada desde archivo',
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'datos_adicionales' => [
                    'filename' => $file->getClientOriginalName(),
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'overwrite' => $request->boolean('overwrite')
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => "Configuración importada exitosamente. {$imported} configuraciones importadas, {$skipped} omitidas.",
                'imported' => $imported,
                'skipped' => $skipped
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Probar conexión de email
     */
    public function testEmailConnection(Request $request)
    {
        abort_unless(auth()->user()->can('configuracion.editar'), 403);

        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            $emailConfig = [
                'host' => Setting::get('mail_host', config('mail.mailers.smtp.host')),
                'port' => Setting::get('mail_port', config('mail.mailers.smtp.port')),
                'username' => Setting::get('mail_username', config('mail.mailers.smtp.username')),
                'password' => Setting::get('mail_password', config('mail.mailers.smtp.password')),
                'encryption' => Setting::get('mail_encryption', config('mail.mailers.smtp.encryption')),
            ];

            // Configurar temporalmente el email
            config([
                'mail.mailers.smtp.host' => $emailConfig['host'],
                'mail.mailers.smtp.port' => $emailConfig['port'],
                'mail.mailers.smtp.username' => $emailConfig['username'],
                'mail.mailers.smtp.password' => $emailConfig['password'],
                'mail.mailers.smtp.encryption' => $emailConfig['encryption'],
            ]);

            // Enviar email de prueba
            \Mail::raw('Esta es una prueba de conexión de email desde el sistema de gestión de taller.', function($message) use ($request) {
                $message->to($request->email)
                        ->subject('Prueba de Conexión - Sistema de Gestión');
            });

            return response()->json([
                'success' => true,
                'message' => 'Email de prueba enviado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar configuración del sistema
     */
    public function verifySystemConfig()
    {
        abort_unless(auth()->user()->can('configuracion.ver'), 403);

        $checks = [
            'database' => $this->checkDatabaseConnection(),
            'storage' => $this->checkStoragePermissions(),
            'cache' => $this->checkCacheConnection(),
            'mail' => $this->checkMailConfiguration(),
            'permissions' => $this->checkFilePermissions(),
            'php_extensions' => $this->checkPhpExtensions(),
        ];

        $allPassed = collect($checks)->every(function($check) {
            return $check['status'] === 'ok';
        });

        return response()->json([
            'success' => true,
            'all_passed' => $allPassed,
            'checks' => $checks
        ]);
    }

    /**
     * Verificar conexión a base de datos
     */
    private function checkDatabaseConnection()
    {
        try {
            \DB::connection()->getPdo();
            return [
                'status' => 'ok',
                'message' => 'Conexión a base de datos exitosa'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar permisos de almacenamiento
     */
    private function checkStoragePermissions()
    {
        $paths = [
            storage_path('app'),
            storage_path('framework'),
            storage_path('logs'),
            public_path('storage')
        ];

        $errors = [];
        foreach ($paths as $path) {
            if (!is_writable($path)) {
                $errors[] = $path;
            }
        }

        if (empty($errors)) {
            return [
                'status' => 'ok',
                'message' => 'Permisos de almacenamiento correctos'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Directorios sin permisos de escritura: ' . implode(', ', $errors)
            ];
        }
    }

    /**
     * Verificar conexión de cache
     */
    private function checkCacheConnection()
    {
        try {
            Cache::put('test_key', 'test_value', 60);
            $value = Cache::get('test_key');
            Cache::forget('test_key');

            if ($value === 'test_value') {
                return [
                    'status' => 'ok',
                    'message' => 'Cache funcionando correctamente'
                ];
            } else {
                return [
                    'status' => 'warning',
                    'message' => 'Cache no está funcionando correctamente'
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error en cache: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar configuración de email
     */
    private function checkMailConfiguration()
    {
        $required = [
            'MAIL_MAILER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_FROM_ADDRESS'
        ];

        $missing = [];
        foreach ($required as $var) {
            if (!env($var)) {
                $missing[] = $var;
            }
        }

        if (empty($missing)) {
            return [
                'status' => 'ok',
                'message' => 'Configuración de email completa'
            ];
        } else {
            return [
                'status' => 'warning',
                'message' => 'Variables de email faltantes: ' . implode(', ', $missing)
            ];
        }
    }

    /**
     * Verificar permisos de archivos
     */
    private function checkFilePermissions()
    {
        $files = [
            base_path('.env'),
            storage_path('logs/laravel.log')
        ];

        $errors = [];
        foreach ($files as $file) {
            if (file_exists($file) && !is_readable($file)) {
                $errors[] = $file;
            }
        }

        if (empty($errors)) {
            return [
                'status' => 'ok',
                'message' => 'Permisos de archivos correctos'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Archivos sin permisos de lectura: ' . implode(', ', $errors)
            ];
        }
    }

    /**
     * Verificar extensiones de PHP
     */
    private function checkPhpExtensions()
    {
        $required = [
            'pdo',
            'mbstring',
            'openssl',
            'tokenizer',
            'xml',
            'ctype',
            'json',
            'bcmath',
            'fileinfo'
        ];

        $missing = [];
        foreach ($required as $extension) {
            if (!extension_loaded($extension)) {
                $missing[] = $extension;
            }
        }

        if (empty($missing)) {
            return [
                'status' => 'ok',
                'message' => 'Todas las extensiones requeridas están instaladas'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Extensiones faltantes: ' . implode(', ', $missing)
            ];
        }
    }
}