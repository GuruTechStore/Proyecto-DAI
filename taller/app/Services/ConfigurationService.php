<?php

namespace App\Services;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class ConfigurationService
{
    protected array $defaultConfigs = [
        // Configuraciones de la empresa
        'empresa.nombre' => 'Gestión Empresarial',
        'empresa.ruc' => '',
        'empresa.direccion' => '',
        'empresa.telefono' => '',
        'empresa.email' => '',
        'empresa.website' => '',
        'empresa.logo' => '',
        
        // Configuraciones del sistema
        'sistema.timezone' => 'America/Lima',
        'sistema.language' => 'es',
        'sistema.currency' => 'PEN',
        'sistema.date_format' => 'd/m/Y',
        'sistema.time_format' => 'H:i',
        'sistema.decimal_places' => '2',
        'sistema.thousands_separator' => ',',
        'sistema.decimal_separator' => '.',
        
        // Configuraciones de notificaciones
        'notificaciones.email_enabled' => true,
        'notificaciones.push_enabled' => true,
        'notificaciones.stock_bajo_threshold' => 5,
        'notificaciones.reparacion_vencida_dias' => 7,
        'notificaciones.auto_cleanup_dias' => 30,
        
        // Configuraciones de reportes
        'reportes.auto_generate' => false,
        'reportes.frequency' => 'weekly',
        'reportes.email_recipients' => '',
        'reportes.retention_days' => 90,
        
        // Configuraciones de seguridad
        'seguridad.session_timeout' => 120,
        'seguridad.max_login_attempts' => 5,
        'seguridad.password_min_length' => 8,
        'seguridad.require_password_change' => false,
        'seguridad.two_factor_enabled' => false,
        
        // Configuraciones de inventario
        'inventario.auto_update_stock' => true,
        'inventario.alert_low_stock' => true,
        'inventario.minimum_stock_default' => 10,
        'inventario.auto_reorder' => false,
        
        // Configuraciones de ventas
        'ventas.tax_rate' => 18.0,
        'ventas.default_payment_method' => 'efectivo',
        'ventas.require_customer' => false,
        'ventas.auto_print_receipt' => false,
        'ventas.allow_discounts' => true,
        'ventas.max_discount_percent' => 20.0,
        
        // Configuraciones de reparaciones
        'reparaciones.auto_assign_technician' => false,
        'reparaciones.default_warranty_days' => 30,
        'reparaciones.require_diagnosis' => true,
        'reparaciones.auto_notify_completion' => true,
    ];

    public function get(string $key, $default = null)
    {
        return Cache::remember("config_{$key}", 3600, function () use ($key, $default) {
            $config = Configuracion::where('clave', $key)->first();
            
            if ($config) {
                return $this->castValue($config->valor, $key);
            }
            
            // Si no existe, crear con valor por defecto
            if (isset($this->defaultConfigs[$key])) {
                $this->set($key, $this->defaultConfigs[$key]);
                return $this->defaultConfigs[$key];
            }
            
            return $default;
        });
    }

    public function set(string $key, $value, string $descripcion = null): bool
    {
        try {
            Configuracion::updateOrCreate(
                ['clave' => $key],
                [
                    'valor' => $this->prepareValue($value),
                    'descripcion' => $descripcion ?? $this->getDefaultDescription($key),
                ]
            );
            
            // Limpiar cache
            Cache::forget("config_{$key}");
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al guardar configuración', [
                'key' => $key,
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function getAll(): array
    {
        $configs = Configuracion::all()->pluck('valor', 'clave')->toArray();
        
        // Agregar configuraciones por defecto si no existen
        foreach ($this->defaultConfigs as $key => $defaultValue) {
            if (!isset($configs[$key])) {
                $configs[$key] = $defaultValue;
            } else {
                $configs[$key] = $this->castValue($configs[$key], $key);
            }
        }
        
        return $configs;
    }

    public function getByCategory(string $category): array
    {
        $allConfigs = $this->getAll();
        $categoryConfigs = [];
        
        foreach ($allConfigs as $key => $value) {
            if (str_starts_with($key, $category . '.')) {
                $categoryConfigs[$key] = $value;
            }
        }
        
        return $categoryConfigs;
    }

    public function updateCategory(string $category, array $configs): bool
    {
        $success = true;
        
        foreach ($configs as $key => $value) {
            $fullKey = str_starts_with($key, $category . '.') ? $key : $category . '.' . $key;
            
            if (!$this->set($fullKey, $value)) {
                $success = false;
            }
        }
        
        // Limpiar todo el cache de configuraciones
        $this->clearCache();
        
        return $success;
    }

    public function reset(string $key = null): bool
    {
        try {
            if ($key) {
                // Resetear configuración específica
                if (isset($this->defaultConfigs[$key])) {
                    $this->set($key, $this->defaultConfigs[$key]);
                } else {
                    Configuracion::where('clave', $key)->delete();
                    Cache::forget("config_{$key}");
                }
            } else {
                // Resetear todas las configuraciones
                Configuracion::truncate();
                $this->initializeDefaults();
                $this->clearCache();
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al resetear configuración', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function initializeDefaults(): void
    {
        foreach ($this->defaultConfigs as $key => $value) {
            if (!Configuracion::where('clave', $key)->exists()) {
                $this->set($key, $value);
            }
        }
    }

    public function backup(): string
    {
        $configs = $this->getAll();
        $backup = [
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'configurations' => $configs,
        ];
        
        $filename = 'config-backup-' . now()->format('Y-m-d-H-i-s') . '.json';
        $path = 'backups/configurations/' . $filename;
        
        Storage::disk('local')->put($path, json_encode($backup, JSON_PRETTY_PRINT));
        
        return $path;
    }

    public function restore(string $backupPath): bool
    {
        try {
            if (!Storage::disk('local')->exists($backupPath)) {
                throw new \Exception('Archivo de backup no encontrado');
            }
            
            $content = Storage::disk('local')->get($backupPath);
            $backup = json_decode($content, true);
            
            if (!isset($backup['configurations'])) {
                throw new \Exception('Formato de backup inválido');
            }
            
            // Limpiar configuraciones existentes
            Configuracion::truncate();
            
            // Restaurar configuraciones
            foreach ($backup['configurations'] as $key => $value) {
                $this->set($key, $value);
            }
            
            $this->clearCache();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al restaurar backup de configuración', [
                'backup_path' => $backupPath,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function export(array $categories = []): array
    {
        $configs = $this->getAll();
        
        if (!empty($categories)) {
            $filtered = [];
            foreach ($categories as $category) {
                $categoryConfigs = $this->getByCategory($category);
                $filtered = array_merge($filtered, $categoryConfigs);
            }
            $configs = $filtered;
        }
        
        return [
            'exported_at' => now()->toISOString(),
            'categories' => $categories,
            'configurations' => $configs,
        ];
    }

    public function import(array $data): bool
    {
        try {
            if (!isset($data['configurations'])) {
                throw new \Exception('Datos de importación inválidos');
            }
            
            foreach ($data['configurations'] as $key => $value) {
                $this->set($key, $value);
            }
            
            $this->clearCache();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al importar configuraciones', [
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function validate(string $key, $value): bool
    {
        $validators = [
            'empresa.email' => 'email',
            'empresa.website' => 'url',
            'sistema.timezone' => function($value) {
                return in_array($value, timezone_identifiers_list());
            },
            'notificaciones.stock_bajo_threshold' => function($value) {
                return is_numeric($value) && $value >= 0;
            },
            'seguridad.session_timeout' => function($value) {
                return is_numeric($value) && $value > 0;
            },
            'ventas.tax_rate' => function($value) {
                return is_numeric($value) && $value >= 0 && $value <= 100;
            },
        ];
        
        if (!isset($validators[$key])) {
            return true; // No validator, assume valid
        }
        
        $validator = $validators[$key];
        
        if (is_string($validator)) {
            return match($validator) {
                'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
                'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
                default => true,
            };
        }
        
        if (is_callable($validator)) {
            return $validator($value);
        }
        
        return true;
    }

    public function clearCache(): void
    {
        $configs = array_keys($this->defaultConfigs);
        foreach ($configs as $key) {
            Cache::forget("config_{$key}");
        }
        
        // También limpiar cache general
        Cache::tags(['configurations'])->flush();
    }

    public function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_type' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'timezone' => $this->get('sistema.timezone'),
            'language' => $this->get('sistema.language'),
            'total_configurations' => Configuracion::count(),
            'last_backup' => $this->getLastBackupDate(),
            'disk_space' => $this->getDiskSpace(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];
    }

    // Métodos privados auxiliares

    private function castValue($value, string $key)
    {
        // Determinar el tipo basado en la clave o valor por defecto
        $defaultValue = $this->defaultConfigs[$key] ?? null;
        
        if (is_bool($defaultValue)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        
        if (is_int($defaultValue) || is_numeric($defaultValue)) {
            return is_numeric($value) ? (float) $value : $value;
        }
        
        return $value;
    }

    private function prepareValue($value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        
        return (string) $value;
    }

    private function getDefaultDescription(string $key): string
    {
        $descriptions = [
            'empresa.nombre' => 'Nombre de la empresa',
            'empresa.ruc' => 'RUC de la empresa',
            'empresa.direccion' => 'Dirección de la empresa',
            'empresa.telefono' => 'Teléfono de la empresa',
            'empresa.email' => 'Email de contacto de la empresa',
            'empresa.website' => 'Sitio web de la empresa',
            'empresa.logo' => 'Ruta del logo de la empresa',
            
            'sistema.timezone' => 'Zona horaria del sistema',
            'sistema.language' => 'Idioma del sistema',
            'sistema.currency' => 'Moneda del sistema',
            'sistema.date_format' => 'Formato de fecha',
            'sistema.time_format' => 'Formato de hora',
            
            'notificaciones.email_enabled' => 'Habilitar notificaciones por email',
            'notificaciones.push_enabled' => 'Habilitar notificaciones push',
            'notificaciones.stock_bajo_threshold' => 'Umbral para alerta de stock bajo',
            
            'seguridad.session_timeout' => 'Tiempo de expiración de sesión (minutos)',
            'seguridad.max_login_attempts' => 'Máximo número de intentos de login',
            
            'ventas.tax_rate' => 'Tasa de impuesto para ventas (%)',
            'ventas.allow_discounts' => 'Permitir descuentos en ventas',
        ];
        
        return $descriptions[$key] ?? 'Configuración del sistema';
    }

    private function getLastBackupDate(): ?string
    {
        $backups = Storage::disk('local')->files('backups/configurations');
        
        if (empty($backups)) {
            return null;
        }
        
        $latestBackup = collect($backups)->sortByDesc(function ($file) {
            return Storage::disk('local')->lastModified($file);
        })->first();
        
        return $latestBackup ? Carbon::createFromTimestamp(
            Storage::disk('local')->lastModified($latestBackup)
        )->format('d/m/Y H:i') : null;
    }

    private function getDiskSpace(): array
    {
        $path = storage_path();
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;
        
        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage_used' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}