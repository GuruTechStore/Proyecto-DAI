<?php

// app/Helpers/SettingsHelper.php

namespace App\Helpers;

use App\Models\Setting;

class SettingsHelper
{
    /**
     * Obtener configuración con cache
     */
    public static function get($key, $default = null)
    {
        return Setting::get($key, $default);
    }

    /**
     * Establecer configuración
     */
    public static function set($key, $value, $type = null, $category = 'general')
    {
        if ($type === null) {
            $type = self::detectType($value);
        }
        
        return Setting::set($key, $value, $type, $category);
    }

    /**
     * Detectar tipo automáticamente
     */
    private static function detectType($value)
    {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_int($value)) {
            return 'integer';
        } elseif (is_float($value)) {
            return 'float';
        } elseif (is_array($value)) {
            return 'json';
        } else {
            return 'string';
        }
    }

    /**
     * Obtener configuraciones de empresa para mostrar públicamente
     */
    public static function getEmpresaInfo()
    {
        return [
            'nombre' => self::get('empresa_nombre', 'Taller de Reparaciones'),
            'ruc' => self::get('empresa_ruc', ''),
            'direccion' => self::get('empresa_direccion', ''),
            'telefono' => self::get('empresa_telefono', ''),
            'email' => self::get('empresa_email', ''),
            'logo' => self::get('empresa_logo', ''),
            'especialidades' => self::get('taller_especialidades', []),
        ];
    }

    /**
     * Obtener configuraciones del sistema
     */
    public static function getSistemaConfig()
    {
        return [
            'moneda' => self::get('sistema_moneda', 'PEN'),
            'simbolo_moneda' => self::get('sistema_simbolo_moneda', 'S/'),
            'timezone' => self::get('sistema_timezone', 'America/Lima'),
            'items_por_pagina' => self::get('sistema_items_por_pagina', 15),
            'stock_minimo_alerta' => self::get('sistema_stock_minimo_alerta', 5),
            'formato_fecha' => self::get('sistema_formato_fecha', 'd/m/Y'),
        ];
    }

    /**
     * Obtener configuraciones de seguridad
     */
    public static function getSeguridadConfig()
    {
        return [
            'max_intentos_login' => self::get('seguridad_max_intentos_login', 5),
            'tiempo_bloqueo_minutos' => self::get('seguridad_tiempo_bloqueo_minutos', 15),
            'duracion_sesion_minutos' => self::get('seguridad_duracion_sesion_minutos', 120),
            'expiracion_password_dias' => self::get('seguridad_expiracion_password_dias', 90),
            'historial_passwords' => self::get('seguridad_historial_passwords', 5),
        ];
    }

    /**
     * Verificar si una característica está habilitada
     */
    public static function isEnabled($feature)
    {
        return self::get($feature, false) === true;
    }

    /**
     * Configuraciones para frontend (solo públicas)
     */
    public static function getPublicConfig()
    {
        $publicSettings = Setting::getPublic();
        
        // Agregar configuraciones calculadas
        $publicSettings['app_name'] = config('app.name');
        $publicSettings['app_version'] = config('app.version', '1.0.0');
        
        return $publicSettings;
    }

    /**
     * Formatear moneda según configuración
     */
    public static function formatCurrency($amount)
    {
        $simbolo = self::get('sistema_simbolo_moneda', 'S/');
        return $simbolo . ' ' . number_format($amount, 2);
    }

    /**
     * Formatear fecha según configuración
     */
    public static function formatDate($date, $includeTime = false)
    {
        $formato = self::get('sistema_formato_fecha', 'd/m/Y');
        
        if ($includeTime) {
            $formato .= ' H:i';
        }
        
        return $date instanceof \Carbon\Carbon ? $date->format($formato) : 
               \Carbon\Carbon::parse($date)->format($formato);
    }

    /**
     * Obtener configuraciones por categoría
     */
    public static function getByCategory($category)
    {
        return Setting::getCategory($category);
    }

    /**
     * Actualizar múltiples configuraciones
     */
    public static function updateBatch(array $settings)
    {
        $updated = [];
        
        foreach ($settings as $key => $value) {
            $type = self::detectType($value);
            $setting = Setting::set($key, $value, $type);
            $updated[$key] = $setting;
        }
        
        return $updated;
    }

    /**
     * Resetear configuraciones a valores por defecto
     */
    public static function resetToDefaults($category = null)
    {
        $defaults = Setting::getDefaults();
        $reset = [];
        
        foreach ($defaults as $key => $config) {
            if ($category === null || $config['category'] === $category) {
                Setting::set(
                    $key,
                    $config['value'],
                    $config['type'],
                    $config['category'],
                    $config['is_public']
                );
                $reset[] = $key;
            }
        }
        
        return $reset;
    }

    /**
     * Validar configuración antes de guardar
     */
    public static function validate($key, $value, $type = null)
    {
        $errors = [];
        
        // Validaciones específicas por clave
        switch ($key) {
            case 'seguridad_max_intentos_login':
                if (!is_numeric($value) || $value < 1 || $value > 10) {
                    $errors[] = 'Los intentos de login deben ser entre 1 y 10';
                }
                break;
                
            case 'seguridad_tiempo_bloqueo_minutos':
                if (!is_numeric($value) || $value < 1 || $value > 1440) {
                    $errors[] = 'El tiempo de bloqueo debe ser entre 1 y 1440 minutos';
                }
                break;
                
            case 'sistema_items_por_pagina':
                if (!is_numeric($value) || $value < 5 || $value > 100) {
                    $errors[] = 'Los items por página deben ser entre 5 y 100';
                }
                break;
                
            case 'empresa_email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Email de empresa debe ser una dirección válida';
                }
                break;
        }
        
        return empty($errors) ? true : $errors;
    }

    /**
     * Obtener configuraciones críticas del sistema
     */
    public static function getCriticalSettings()
    {
        return [
            'sistema' => [
                'items_por_pagina' => self::get('sistema_items_por_pagina'),
                'stock_minimo_alerta' => self::get('sistema_stock_minimo_alerta'),
                'moneda' => self::get('sistema_moneda'),
            ],
            'seguridad' => [
                'max_intentos_login' => self::get('seguridad_max_intentos_login'),
                'tiempo_bloqueo_minutos' => self::get('seguridad_tiempo_bloqueo_minutos'),
                'expiracion_password_dias' => self::get('seguridad_expiracion_password_dias'),
            ],
            'inventario' => [
                'alertas_stock_bajo' => self::get('inventario_alertas_stock_bajo'),
                'auto_reducir_ventas' => self::get('inventario_auto_reducir_ventas'),
            ]
        ];
    }
}