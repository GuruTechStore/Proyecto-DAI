<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SecurityLogResource\Pages;
use App\Models\SecurityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SecurityLogResource extends Resource
{
    protected static ?string $model = SecurityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'Logs de Seguridad';

    protected static ?string $modelLabel = 'Log de Seguridad';

    protected static ?string $pluralModelLabel = 'Logs de Seguridad';

    protected static ?string $navigationGroup = 'Seguridad';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $alertas = static::getModel()::whereIn('action', ['login_failed', 'login_blocked', 'access_denied'])
            ->whereDate('created_at', today())
            ->count();
        
        return $alertas > 5 ? 'danger' : ($alertas > 0 ? 'warning' : 'success');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('created_at')
                    ->label('Fecha y Hora')
                    ->content(fn ($record) => $record?->created_at?->format('d/m/Y H:i:s')),
                Forms\Components\Placeholder::make('user.name')
                    ->label('Usuario')
                    ->content(fn ($record) => $record?->user?->name ?? 'Sistema'),
                Forms\Components\Placeholder::make('action')
                    ->label('Acción')
                    ->content(fn ($record) => match ($record?->action) {
                        'login_success' => 'Login Exitoso',
                        'login_failed' => 'Login Fallido',
                        'login_blocked' => 'Login Bloqueado',
                        'access_denied' => 'Acceso Denegado',
                        'logout' => 'Logout',
                        'password_changed' => 'Cambio de Contraseña',
                        default => ucfirst(str_replace('_', ' ', $record?->action ?? '')),
                    }),
                Forms\Components\Placeholder::make('ip_address')
                    ->label('Dirección IP')
                    ->content(fn ($record) => $record?->ip_address),
                Forms\Components\Textarea::make('user_agent')
                    ->label('User Agent')
                    ->disabled(),
                Forms\Components\Textarea::make('details')
                    ->label('Detalles')
                    ->disabled()
                    ->formatStateUsing(fn ($state) => 
                        is_string($state) 
                            ? json_encode(json_decode($state), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                            : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->default('Sistema')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                BadgeColumn::make('action')
                    ->label('Acción')
                    ->colors([
                        'success' => 'login_success',
                        'danger' => ['login_failed', 'login_blocked', 'access_denied'],
                        'warning' => ['logout', 'password_changed'],
                        'primary' => ['user_created', 'user_updated'],
                        'info' => ['data_export', 'report_generated'],
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'login_success',
                        'heroicon-o-x-circle' => ['login_failed', 'access_denied'],
                        'heroicon-o-shield-exclamation' => 'login_blocked',
                        'heroicon-o-arrow-right-on-rectangle' => 'logout',
                        'heroicon-o-key' => 'password_changed',
                        'heroicon-o-user-plus' => 'user_created',
                        'heroicon-o-pencil' => 'user_updated',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'login_success' => 'Login Exitoso',
                            'login_failed' => 'Login Fallido',
                            'login_blocked' => 'Login Bloqueado',
                            'access_denied' => 'Acceso Denegado',
                            'logout' => 'Logout',
                            'password_changed' => 'Cambio de Contraseña',
                            'user_created' => 'Usuario Creado',
                            'user_updated' => 'Usuario Actualizado',
                            'data_export' => 'Exportación de Datos',
                            'report_generated' => 'Reporte Generado',
                            default => ucfirst(str_replace('_', ' ', $state)),
                        };
                    }),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('user_agent')
                    ->label('Navegador')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->user_agent)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('details')
                    ->label('Detalles')
                    ->limit(50)
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) return '-';
                        
                        $details = is_string($state) ? json_decode($state, true) : $state;
                        
                        if (isset($details['email'])) {
                            return $details['email'];
                        }
                        
                        if (isset($details['reason'])) {
                            return match ($details['reason']) {
                                'invalid_credentials' => 'Credenciales inválidas',
                                'rate_limit_exceeded' => 'Demasiados intentos',
                                'insufficient_permissions' => 'Sin permisos',
                                default => $details['reason'],
                            };
                        }
                        
                        return 'Ver detalles';
                    })
                    ->tooltip(fn ($record) => $record->details),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->label('Tipo de Acción')
                    ->options([
                        'login_success' => 'Login Exitoso',
                        'login_failed' => 'Login Fallido',
                        'login_blocked' => 'Login Bloqueado',
                        'access_denied' => 'Acceso Denegado',
                        'logout' => 'Logout',
                        'password_changed' => 'Cambio de Contraseña',
                        'user_created' => 'Usuario Creado',
                        'user_updated' => 'Usuario Actualizado',
                    ])
                    ->multiple(),
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                 
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'Desde ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                 
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Hasta ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                 
                        return $indicators;
                    }),
                Tables\Filters\Filter::make('critical_events')
                    ->label('Eventos Críticos')
                    ->query(fn ($query) => $query->whereIn('action', ['login_failed', 'login_blocked', 'access_denied']))
                    ->toggle(),
                Tables\Filters\Filter::make('today')
                    ->label('Solo Hoy')
                    ->query(fn ($query) => $query->whereDate('created_at', today()))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn ($record) => view('filament.security.log-details', compact('record'))),
                Tables\Actions\Action::make('block_ip')
                    ->label('Bloquear IP')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // Aquí implementarías la lógica para bloquear la IP
                        // Por ejemplo, agregándola a una tabla de IPs bloqueadas
                        
                        \Filament\Notifications\Notification::make()
                            ->title('IP Bloqueada')
                            ->body("La IP {$record->ip_address} ha sido bloqueada")
                            ->danger()
                            ->send();
                    })
                    ->visible(fn ($record) => 
                        in_array($record->action, ['login_failed', 'login_blocked']) && 
                        auth()->user()->hasRole('Super Admin')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->can('seguridad.exportar')),
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('Super Admin')),
                ]),
            ])
            ->striped()
            ->poll('30s')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => Pages\ListSecurityLogs::route('/'),
    //         'view' => Pages\ViewSecurityLog::route('/{record}'),
    //     ];
    // }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Gerente']) && auth()->user()->can('seguridad.ver');
    }

    public static function canCreate(): bool
    {
        return false; // Los logs se crean automáticamente
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Los logs no deben editarse
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole('Super Admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Gerente']) && auth()->user()->can('seguridad.ver');
    }
}