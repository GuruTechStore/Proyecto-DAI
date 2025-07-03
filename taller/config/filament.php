<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Seguridad
    |--------------------------------------------------------------------------
    |
    | Configuraciones relacionadas con la seguridad del panel administrativo
    |
    */

    'security' => [
        'session_timeout' => env('FILAMENT_SESSION_TIMEOUT', 120), // minutos
        'max_login_attempts' => env('FILAMENT_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('FILAMENT_LOCKOUT_DURATION', 300), // segundos
        'password_min_length' => env('FILAMENT_PASSWORD_MIN_LENGTH', 8),
        'require_email_verification' => env('FILAMENT_REQUIRE_EMAIL_VERIFICATION', true),
        'log_user_activity' => env('FILAMENT_LOG_USER_ACTIVITY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Dashboard
    |--------------------------------------------------------------------------
    |
    | Configuraciones para el dashboard principal
    |
    */

    'dashboard' => [
        'refresh_interval' => env('FILAMENT_DASHBOARD_REFRESH', 30), // segundos
        'enable_notifications' => env('FILAMENT_ENABLE_NOTIFICATIONS', true),
        'notification_polling' => env('FILAMENT_NOTIFICATION_POLLING', '30s'),
        'widgets_per_row' => env('FILAMENT_WIDGETS_PER_ROW', 4),
        'enable_dark_mode' => env('FILAMENT_ENABLE_DARK_MODE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Exportación
    |--------------------------------------------------------------------------
    |
    | Configuraciones para la exportación de datos
    |
    */

    'exports' => [
        'enabled' => env('FILAMENT_EXPORTS_ENABLED', true),
        'max_rows' => env('FILAMENT_EXPORTS_MAX_ROWS', 10000),
        'formats' => ['xlsx', 'csv', 'pdf'],
        'queue' => env('FILAMENT_EXPORTS_QUEUE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Backup
    |--------------------------------------------------------------------------
    |
    | Configuraciones para respaldos automáticos
    |
    */

    'backup' => [
        'enabled' => env('FILAMENT_BACKUP_ENABLED', true),
        'frequency' => env('FILAMENT_BACKUP_FREQUENCY', 'daily'),
        'retention_days' => env('FILAMENT_BACKUP_RETENTION_DAYS', 30),
        'include_uploads' => env('FILAMENT_BACKUP_INCLUDE_UPLOADS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Performance
    |--------------------------------------------------------------------------
    |
    | Configuraciones para optimización de rendimiento
    |
    */

    'performance' => [
        'enable_caching' => env('FILAMENT_ENABLE_CACHING', true),
        'cache_duration' => env('FILAMENT_CACHE_DURATION', 3600), // segundos
        'enable_compression' => env('FILAMENT_ENABLE_COMPRESSION', true),
        'lazy_load_relations' => env('FILAMENT_LAZY_LOAD_RELATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Interfaz
    |--------------------------------------------------------------------------
    |
    | Configuraciones de la interfaz de usuario
    |
    */

    'ui' => [
        'sidebar_collapsible' => env('FILAMENT_SIDEBAR_COLLAPSIBLE', true),
        'max_content_width' => env('FILAMENT_MAX_CONTENT_WIDTH', 'full'),
        'top_navigation' => env('FILAMENT_TOP_NAVIGATION', false),
        'breadcrumbs' => env('FILAMENT_BREADCRUMBS', true),
        'global_search' => env('FILAMENT_GLOBAL_SEARCH', true),
        'spa_mode' => env('FILAMENT_SPA_MODE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Notificaciones
    |--------------------------------------------------------------------------
    |
    | Configuraciones para el sistema de notificaciones
    |
    */

    'notifications' => [
        'database_enabled' => env('FILAMENT_DB_NOTIFICATIONS', true),
        'broadcast_enabled' => env('FILAMENT_BROADCAST_NOTIFICATIONS', false),
        'email_enabled' => env('FILAMENT_EMAIL_NOTIFICATIONS', true),
        'slack_enabled' => env('FILAMENT_SLACK_NOTIFICATIONS', false),
        'retention_days' => env('FILAMENT_NOTIFICATIONS_RETENTION', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Auditoría
    |--------------------------------------------------------------------------
    |
    | Configuraciones para el sistema de auditoría
    |
    */

    'audit' => [
        'enabled' => env('FILAMENT_AUDIT_ENABLED', true),
        'log_creates' => env('FILAMENT_AUDIT_CREATES', true),
        'log_updates' => env('FILAMENT_AUDIT_UPDATES', true),
        'log_deletes' => env('FILAMENT_AUDIT_DELETES', true),
        'log_views' => env('FILAMENT_AUDIT_VIEWS', false),
        'retention_months' => env('FILAMENT_AUDIT_RETENTION', 12),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Reportes
    |--------------------------------------------------------------------------
    |
    | Configuraciones para el sistema de reportes
    |
    */

    'reports' => [
        'enabled' => env('FILAMENT_REPORTS_ENABLED', true),
        'cache_enabled' => env('FILAMENT_REPORTS_CACHE', true),
        'cache_duration' => env('FILAMENT_REPORTS_CACHE_DURATION', 1800), // segundos
        'max_execution_time' => env('FILAMENT_REPORTS_MAX_TIME', 300), // segundos
        'queue_heavy_reports' => env('FILAMENT_REPORTS_QUEUE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de API
    |--------------------------------------------------------------------------
    |
    | Configuraciones para integraciones de API
    |
    */

    'api' => [
        'enabled' => env('FILAMENT_API_ENABLED', true),
        'rate_limit' => env('FILAMENT_API_RATE_LIMIT', '60,1'),
        'cache_responses' => env('FILAMENT_API_CACHE', true),
        'log_requests' => env('FILAMENT_API_LOG_REQUESTS', true),
    ],


    'broadcasting' => [

        // 'echo' => [
        //     'broadcaster' => 'pusher',
        //     'key' => env('VITE_PUSHER_APP_KEY'),
        //     'cluster' => env('VITE_PUSHER_APP_CLUSTER'),
        //     'wsHost' => env('VITE_PUSHER_HOST'),
        //     'wsPort' => env('VITE_PUSHER_PORT'),
        //     'wssPort' => env('VITE_PUSHER_PORT'),
        //     'authEndpoint' => '/broadcasting/auth',
        //     'disableStats' => true,
        //     'encrypted' => true,
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | This is the storage disk Filament will use to store files. You may use
    | any of the disks defined in the `config/filesystems.php`.
    |
    */

    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Assets Path
    |--------------------------------------------------------------------------
    |
    | This is the directory where Filament's assets are published to. It is
    | relative to the `public` directory of your Laravel application.
    |
    */

    'assets_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache Path
    |--------------------------------------------------------------------------
    |
    | This is the directory that Filament will use to store cache files that
    | are used to optimize the framework. It is relative to your application's
    | `storage` directory.
    |
    */

    'cache_path' => env('FILAMENT_CACHE_PATH', 'framework/cache/filament'),

    /*
    |--------------------------------------------------------------------------
    | Livewire Loading Delay
    |--------------------------------------------------------------------------
    |
    | This sets the delay before showing Livewire's loading indicators. Setting
    | this to 'none' will disable loading indicators entirely.
    |
    */

    'livewire_loading_delay' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Configuraciones Personalizadas
    |--------------------------------------------------------------------------
    |
    | Configuraciones específicas para nuestro sistema de gestión empresarial
    |
    */

    'company' => [
        'name' => env('COMPANY_NAME', 'Gestión Empresarial'),
        'logo' => env('COMPANY_LOGO', '/images/logo.png'),
        'favicon' => env('COMPANY_FAVICON', '/favicon.ico'),
        'address' => env('COMPANY_ADDRESS', 'Lima, Perú'),
        'phone' => env('COMPANY_PHONE', '+51 999 999 999'),
        'email' => env('COMPANY_EMAIL', 'contacto@gestion.com'),
        'website' => env('COMPANY_WEBSITE', 'https://gestion.com'),
    ],

    ];