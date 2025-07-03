<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'is_public',
        'description'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * MÉTODOS PRINCIPALES DE CONFIGURACIÓN
     */
    public static function get($key, $default = null)
    {
        $cacheKey = "setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function() use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return self::parseValue($setting->value, $setting->type);
        });
    }

    public static function set($key, $value, $type = 'string', $category = 'general', $isPublic = false)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => self::formatValue($value, $type),
                'type' => $type,
                'category' => $category,
                'is_public' => $isPublic
            ]
        );

        // Limpiar cache
        self::clearCacheForKey($key, $category);

        return $setting;
    }

    public static function getCategory($category)
    {
        $cacheKey = "settings_{$category}";
        
        return Cache::remember($cacheKey, 3600, function() use ($category) {
            return self::where('category', $category)
                ->get()
                ->mapWithKeys(function($setting) {
                    return [
                        $setting->key => self::parseValue($setting->value, $setting->type)
                    ];
                });
        });
    }

    public static function getPublic()
    {
        return Cache::remember('public_settings', 3600, function() {
            return self::where('is_public', true)
                ->get()
                ->mapWithKeys(function($setting) {
                    return [
                        $setting->key => self::parseValue($setting->value, $setting->type)
                    ];
                });
        });
    }

    public static function getAll()
    {
        return Cache::remember('all_settings', 3600, function() {
            return self::all()->mapWithKeys(function($setting) {
                return [
                    $setting->key => self::parseValue($setting->value, $setting->type)
                ];
            });
        });
    }

    /**
     * MÉTODOS DE PARSEO Y FORMATO
     */
    private static function parseValue($value, $type)
    {
        if ($value === null) {
            return null;
        }

        return match($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            default => (string) $value
        };
    }

    private static function formatValue($value, $type)
    {
        return match($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value
        };
    }

    /**
     * MÉTODOS DE CACHE
     */
    private static function clearCacheForKey($key, $category)
    {
        Cache::forget("setting_{$key}");
        Cache::forget("settings_{$category}");
        Cache::forget('all_settings');
        Cache::forget('public_settings');
    }

    public static function clearCache()
    {
        $categories = self::distinct('category')->pluck('category');
        
        foreach ($categories as $category) {
            Cache::forget("settings_{$category}");
        }
        
        Cache::forget('all_settings');
        Cache::forget('public_settings');
        
        $keys = self::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting_{$key}");
        }
    }

    /**
     * MÉTODOS DE UTILIDAD
     */
    public static function has($key)
    {
        return self::where('key', $key)->exists();
    }

    public static function remove($key)
    {
        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            $category = $setting->category;
            $setting->delete();
            
            self::clearCacheForKey($key, $category);
            return true;
        }
        
        return false;
    }

    public static function toggle($key)
    {
        $currentValue = self::get($key, false);
        $newValue = !$currentValue;
        
        $setting = self::where('key', $key)->first();
        if ($setting) {
            $setting->update(['value' => self::formatValue($newValue, 'boolean')]);
            self::clearCacheForKey($key, $setting->category);
        }
        
        return $newValue;
    }

    /**
     * SCOPES
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * ACCESSORS
     */
    public function getValueAttribute($value)
    {
        return self::parseValue($value, $this->type ?? 'string');
    }

    public function getParsedValueAttribute()
    {
        return self::parseValue($this->attributes['value'] ?? null, $this->type);
    }

    /**
     * MUTATORS
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = self::formatValue($value, $this->type ?? 'string');
    }

    /**
     * CONFIGURACIONES POR DEFECTO
     */
    public static function getDefaults()
    {
        return [
            // === EMPRESA ===
            'empresa_nombre' => [
                'value' => '',
                'type' => 'string',
                'category' => 'empresa',
                'is_public' => true,
                'description' => 'Nombre de la empresa'
            ],
            'empresa_ruc' => [
                'value' => '',
                'type' => 'string',
                'category' => 'empresa',
                'is_public' => false,
                'description' => 'RUC de la empresa'
            ],
            'empresa_direccion' => [
                'value' => '',
                'type' => 'string',
                'category' => 'empresa',
                'is_public' => true,
                'description' => 'Dirección de la empresa'
            ],
            'empresa_telefono' => [
                'value' => '',
                'type' => 'string',
                'category' => 'empresa',
                'is_public' => true,
                'description' => 'Teléfono de la empresa'
            ],
            'empresa_email' => [
                'value' => '',
                'type' => 'string',
                'category' => 'empresa',
                'is_public' => true,
                'description' => 'Email de la empresa'
            ],
            'empresa_logo' => [
                'value' => '',
                'type' => 'string',
                'category' => 'empresa',
                'is_public' => true,
                'description' => 'Logo de la empresa'
            ],

            // === SISTEMA ===
            'sistema_moneda' => [
                'value' => 'PEN',
                'type' => 'string',
                'category' => 'sistema',
                'is_public' => true,
                'description' => 'Moneda del sistema'
            ],
            'sistema_simbolo_moneda' => [
                'value' => 'S/',
                'type' => 'string',
                'category' => 'sistema',
                'is_public' => true,
                'description' => 'Símbolo de la moneda'
            ],
            'sistema_timezone' => [
                'value' => 'America/Lima',
                'type' => 'string',
                'category' => 'sistema',
                'is_public' => true,
                'description' => 'Zona horaria del sistema'
            ],
            'sistema_items_por_pagina' => [
                'value' => 15,
                'type' => 'integer',
                'category' => 'sistema',
                'is_public' => false,
                'description' => 'Items por página en listados'
            ],
            'sistema_stock_minimo_alerta' => [
                'value' => 5,
                'type' => 'integer',
                'category' => 'sistema',
                'is_public' => false,
                'description' => 'Stock mínimo para generar alertas'
            ],
            'sistema_formato_fecha' => [
                'value' => 'd/m/Y',
                'type' => 'string',
                'category' => 'sistema',
                'is_public' => true,
                'description' => 'Formato de fecha del sistema'
            ],

            // === SEGURIDAD ===
            'seguridad_max_intentos_login' => [
                'value' => 5,
                'type' => 'integer',
                'category' => 'seguridad',
                'is_public' => false,
                'description' => 'Máximo intentos de login antes de bloquear'
            ],
            'seguridad_tiempo_bloqueo_minutos' => [
                'value' => 15,
                'type' => 'integer',
                'category' => 'seguridad',
                'is_public' => false,
                'description' => 'Tiempo de bloqueo en minutos'
            ],
            'seguridad_duracion_sesion_minutos' => [
                'value' => 120,
                'type' => 'integer',
                'category' => 'seguridad',
                'is_public' => false,
                'description' => 'Duración de sesión en minutos'
            ],
            'seguridad_expiracion_password_dias' => [
                'value' => 90,
                'type' => 'integer',
                'category' => 'seguridad',
                'is_public' => false,
                'description' => 'Días para expiración de contraseña'
            ],
            'seguridad_historial_passwords' => [
                'value' => 5,
                'type' => 'integer',
                'category' => 'seguridad',
                'is_public' => false,
                'description' => 'Número de contraseñas anteriores a recordar'
            ],

            // === EMAIL ===
            'email_notificaciones_habilitadas' => [
                'value' => true,
                'type' => 'boolean',
                'category' => 'email',
                'is_public' => false,
                'description' => 'Habilitar notificaciones por email'
            ],
            'email_from_address' => [
                'value' => config('mail.from.address'),
                'type' => 'string',
                'category' => 'email',
                'is_public' => false,
                'description' => 'Email remitente'
            ],
            'email_from_name' => [
                'value' => config('mail.from.name'),
                'type' => 'string',
                'category' => 'email',
                'is_public' => false,
                'description' => 'Nombre del remitente'
            ],

            // === INVENTARIO ===
            'inventario_alertas_stock_bajo' => [
                'value' => true,
                'type' => 'boolean',
                'category' => 'inventario',
                'is_public' => false,
                'description' => 'Habilitar alertas de stock bajo'
            ],
            'inventario_auto_reducir_ventas' => [
                'value' => true,
                'type' => 'boolean',
                'category' => 'inventario',
                'is_public' => false,
                'description' => 'Reducir stock automáticamente en ventas'
            ],

            // === BACKUP ===
            'backup_automatico_habilitado' => [
                'value' => false,
                'type' => 'boolean',
                'category' => 'backup',
                'is_public' => false,
                'description' => 'Backup automático habilitado'
            ],
            'backup_frecuencia' => [
                'value' => 'semanal',
                'type' => 'string',
                'category' => 'backup',
                'is_public' => false,
                'description' => 'Frecuencia de backup automático'
            ],
            'backup_mantener_cantidad' => [
                'value' => 10,
                'type' => 'integer',
                'category' => 'backup',
                'is_public' => false,
                'description' => 'Cantidad de backups a mantener'
            ],

            // === REPORTES ===
            'reportes_cache_duracion_minutos' => [
                'value' => 30,
                'type' => 'integer',
                'category' => 'reportes',
                'is_public' => false,
                'description' => 'Duración del cache de reportes en minutos'
            ],

            // === NOTIFICACIONES ===
            'notificaciones_push_habilitadas' => [
                'value' => true,
                'type' => 'boolean',
                'category' => 'notificaciones',
                'is_public' => false,
                'description' => 'Habilitar notificaciones push'
            ],
            'notificaciones_mantener_dias' => [
                'value' => 90,
                'type' => 'integer',
                'category' => 'notificaciones',
                'is_public' => false,
                'description' => 'Días para mantener notificaciones'
            ],
        ];
    }

    /**
     * INICIALIZAR CONFIGURACIONES POR DEFECTO
     */
    public static function initializeDefaults()
    {
        $defaults = self::getDefaults();
        $createdCount = 0;
        
        foreach ($defaults as $key => $config) {
            if (!self::has($key)) {
                self::create([
                    'key' => $key,
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'category' => $config['category'],
                    'is_public' => $config['is_public'],
                    'description' => $config['description']
                ]);
                $createdCount++;
            }
        }
        
        return $createdCount;
    }

    /**
     * MÉTODOS DE BACKUP Y RESTAURACIÓN
     */
    public static function exportSettings()
    {
        return self::all()->map(function($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->parsed_value,
                'type' => $setting->type,
                'category' => $setting->category,
                'is_public' => $setting->is_public,
                'description' => $setting->description
            ];
        })->toArray();
    }

    public static function importSettings(array $settings, $overwrite = false)
    {
        $imported = 0;
        $skipped = 0;
        
        foreach ($settings as $settingData) {
            if (!$overwrite && self::has($settingData['key'])) {
                $skipped++;
                continue;
            }
            
            self::set(
                $settingData['key'],
                $settingData['value'],
                $settingData['type'],
                $settingData['category'],
                $settingData['is_public']
            );
            
            // Actualizar descripción si existe
            if (isset($settingData['description'])) {
                self::where('key', $settingData['key'])
                    ->update(['description' => $settingData['description']]);
            }
            
            $imported++;
        }
        
        return compact('imported', 'skipped');
    }

    /**
     * BOOT METHOD
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            self::clearCacheForKey($setting->key, $setting->category);
        });

        static::deleted(function ($setting) {
            self::clearCacheForKey($setting->key, $setting->category);
        });
    }
}