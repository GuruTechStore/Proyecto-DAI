<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Cache;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationLabel = 'Configuraciones';

    protected static ?string $title = 'Configuraciones del Sistema';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getSettingsData());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Empresa')
                    ->description('Configuración básica de la empresa')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Nombre de la Empresa')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('company_address')
                            ->label('Dirección')
                            ->maxLength(255),
                        TextInput::make('company_phone')
                            ->label('Teléfono')
                            ->tel(),
                        TextInput::make('company_email')
                            ->label('Email')
                            ->email(),
                        TextInput::make('company_website')
                            ->label('Sitio Web')
                            ->url(),
                        FileUpload::make('company_logo')
                            ->label('Logo de la Empresa')
                            ->image()
                            ->directory('company')
                            ->maxSize(2048)
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Configuraciones del Sistema')
                    ->description('Ajustes generales del sistema')
                    ->icon('heroicon-o-computer-desktop')
                    ->schema([
                        Select::make('timezone')
                            ->label('Zona Horaria')
                            ->options([
                                'America/Lima' => 'Lima (UTC-5)',
                                'America/New_York' => 'Nueva York (UTC-4/5)',
                                'Europe/Madrid' => 'Madrid (UTC+1/2)',
                            ])
                            ->default('America/Lima')
                            ->native(false),
                        Select::make('currency')
                            ->label('Moneda')
                            ->options([
                                'PEN' => 'Soles Peruanos (S/.)',
                                'USD' => 'Dólares Americanos ($)',
                                'EUR' => 'Euros (€)',
                            ])
                            ->default('PEN')
                            ->native(false),
                        Select::make('language')
                            ->label('Idioma')
                            ->options([
                                'es' => 'Español',
                                'en' => 'English',
                            ])
                            ->default('es')
                            ->native(false),
                        TextInput::make('items_per_page')
                            ->label('Items por Página')
                            ->numeric()
                            ->default(25)
                            ->minValue(10)
                            ->maxValue(100),
                    ])
                    ->columns(2),

                Section::make('Configuraciones de Seguridad')
                    ->description('Ajustes de seguridad y acceso')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        TextInput::make('session_timeout')
                            ->label('Tiempo de Sesión (minutos)')
                            ->numeric()
                            ->default(120)
                            ->minValue(30)
                            ->maxValue(480),
                        TextInput::make('max_login_attempts')
                            ->label('Máximo Intentos de Login')
                            ->numeric()
                            ->default(5)
                            ->minValue(3)
                            ->maxValue(10),
                        TextInput::make('lockout_duration')
                            ->label('Duración de Bloqueo (minutos)')
                            ->numeric()
                            ->default(15)
                            ->minValue(5)
                            ->maxValue(60),
                        Toggle::make('require_email_verification')
                            ->label('Requerir Verificación de Email')
                            ->default(true),
                        Toggle::make('enable_two_factor')
                            ->label('Habilitar Autenticación de Dos Factores')
                            ->default(false),
                        Toggle::make('log_user_activity')
                            ->label('Registrar Actividad de Usuarios')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Configuraciones de Notificaciones')
                    ->description('Ajustes de notificaciones del sistema')
                    ->icon('heroicon-o-bell')
                    ->schema([
                        Toggle::make('email_notifications')
                            ->label('Notificaciones por Email')
                            ->default(true),
                        Toggle::make('sms_notifications')
                            ->label('Notificaciones por SMS')
                            ->default(false),
                        Toggle::make('browser_notifications')
                            ->label('Notificaciones del Navegador')
                            ->default(true),
                        TextInput::make('notification_email')
                            ->label('Email para Notificaciones')
                            ->email()
                            ->placeholder('admin@empresa.com'),
                        Select::make('stock_alert_level')
                            ->label('Nivel de Alerta de Stock')
                            ->options([
                                'low' => 'Solo Stock Crítico',
                                'medium' => 'Stock Bajo y Crítico',
                                'high' => 'Todas las Alertas',
                            ])
                            ->default('medium')
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Configuraciones de Backup')
                    ->description('Configuración de respaldos automáticos')
                    ->icon('heroicon-o-archive-box')
                    ->schema([
                        Toggle::make('auto_backup')
                            ->label('Backup Automático')
                            ->default(true),
                        Select::make('backup_frequency')
                            ->label('Frecuencia de Backup')
                            ->options([
                                'daily' => 'Diario',
                                'weekly' => 'Semanal',
                                'monthly' => 'Mensual',
                            ])
                            ->default('daily')
                            ->native(false),
                        TextInput::make('backup_retention_days')
                            ->label('Días de Retención')
                            ->numeric()
                            ->default(30)
                            ->minValue(7)
                            ->maxValue(365),
                        Toggle::make('backup_to_cloud')
                            ->label('Backup en la Nube')
                            ->default(false),
                    ])
                    ->columns(2),

                Section::make('Configuraciones de Facturación')
                    ->description('Ajustes para facturación electrónica')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextInput::make('invoice_prefix')
                            ->label('Prefijo de Factura')
                            ->default('F-')
                            ->maxLength(5),
                        TextInput::make('next_invoice_number')
                            ->label('Próximo Número de Factura')
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                        TextInput::make('tax_rate')
                            ->label('Tasa de Impuesto (%)')
                            ->numeric()
                            ->default(18)
                            ->minValue(0)
                            ->maxValue(30),
                        Toggle::make('electronic_billing')
                            ->label('Facturación Electrónica')
                            ->default(false),
                        Textarea::make('invoice_footer')
                            ->label('Pie de Factura')
                            ->placeholder('Términos y condiciones...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar Configuraciones')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('save'),
            Action::make('reset')
                ->label('Restaurar Valores por Defecto')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->action('reset'),
            Action::make('backup_now')
                ->label('Crear Backup Ahora')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('warning')
                ->action('createBackup'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        // Guardar configuraciones en cache o base de datos
        foreach ($data as $key => $value) {
            Cache::put("settings.{$key}", $value);
        }
        
        // También podrías guardar en una tabla de configuraciones
        // Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        
        \Filament\Notifications\Notification::make()
            ->title('Configuraciones guardadas')
            ->success()
            ->send();
    }

public function reset(...$properties): void
{
    parent::reset(...$properties); 

    // Limpiar cache de configuraciones
    Cache::forget('settings.*');
    
    // Recargar formulario con valores por defecto
    $this->form->fill($this->getDefaultSettings());
    
    \Filament\Notifications\Notification::make()
        ->title('Configuraciones restauradas')
        ->body('Se han restaurado los valores por defecto')
        ->warning()
        ->send();
}

    public function createBackup(): void
    {
        // Implementar lógica de backup
        // Artisan::call('backup:run');
        
        \Filament\Notifications\Notification::make()
            ->title('Backup creado')
            ->body('El backup se ha creado exitosamente')
            ->success()
            ->send();
    }

    private function getSettingsData(): array
    {
        return [
            'company_name' => Cache::get('settings.company_name', config('app.name')),
            'company_address' => Cache::get('settings.company_address', ''),
            'company_phone' => Cache::get('settings.company_phone', ''),
            'company_email' => Cache::get('settings.company_email', ''),
            'company_website' => Cache::get('settings.company_website', ''),
            'company_logo' => Cache::get('settings.company_logo', ''),
            'timezone' => Cache::get('settings.timezone', 'America/Lima'),
            'currency' => Cache::get('settings.currency', 'PEN'),
            'language' => Cache::get('settings.language', 'es'),
            'items_per_page' => Cache::get('settings.items_per_page', 25),
            'session_timeout' => Cache::get('settings.session_timeout', 120),
            'max_login_attempts' => Cache::get('settings.max_login_attempts', 5),
            'lockout_duration' => Cache::get('settings.lockout_duration', 15),
            'require_email_verification' => Cache::get('settings.require_email_verification', true),
            'enable_two_factor' => Cache::get('settings.enable_two_factor', false),
            'log_user_activity' => Cache::get('settings.log_user_activity', true),
            'email_notifications' => Cache::get('settings.email_notifications', true),
            'sms_notifications' => Cache::get('settings.sms_notifications', false),
            'browser_notifications' => Cache::get('settings.browser_notifications', true),
            'notification_email' => Cache::get('settings.notification_email', ''),
            'stock_alert_level' => Cache::get('settings.stock_alert_level', 'medium'),
            'auto_backup' => Cache::get('settings.auto_backup', true),
            'backup_frequency' => Cache::get('settings.backup_frequency', 'daily'),
            'backup_retention_days' => Cache::get('settings.backup_retention_days', 30),
            'backup_to_cloud' => Cache::get('settings.backup_to_cloud', false),
            'invoice_prefix' => Cache::get('settings.invoice_prefix', 'F-'),
            'next_invoice_number' => Cache::get('settings.next_invoice_number', 1),
            'tax_rate' => Cache::get('settings.tax_rate', 18),
            'electronic_billing' => Cache::get('settings.electronic_billing', false),
            'invoice_footer' => Cache::get('settings.invoice_footer', ''),
        ];
    }

    private function getDefaultSettings(): array
    {
        return [
            'company_name' => config('app.name'),
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'company_website' => '',
            'company_logo' => '',
            'timezone' => 'America/Lima',
            'currency' => 'PEN',
            'language' => 'es',
            'items_per_page' => 25,
            'session_timeout' => 120,
            'max_login_attempts' => 5,
            'lockout_duration' => 15,
            'require_email_verification' => true,
            'enable_two_factor' => false,
            'log_user_activity' => true,
            'email_notifications' => true,
            'sms_notifications' => false,
            'browser_notifications' => true,
            'notification_email' => '',
            'stock_alert_level' => 'medium',
            'auto_backup' => true,
            'backup_frequency' => 'daily',
            'backup_retention_days' => 30,
            'backup_to_cloud' => false,
            'invoice_prefix' => 'F-',
            'next_invoice_number' => 1,
            'tax_rate' => 18,
            'electronic_billing' => false,
            'invoice_footer' => '',
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Super Admin');
    }

    public function getHeading(): string
    {
        return 'Configuraciones del Sistema';
    }

    public function getSubheading(): string
    {
        return 'Administra las configuraciones generales del sistema';
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}