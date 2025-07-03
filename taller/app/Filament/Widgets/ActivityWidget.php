<?php

namespace App\Filament\Widgets;

use App\Models\SecurityLog;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityWidget extends TableWidget
{
    protected static ?string $heading = 'Actividad Reciente';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SecurityLog::query()
                    ->with('user')
                    ->latest()
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Hora')
                    ->since()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->default('Sistema')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\BadgeColumn::make('action')
                    ->label('Acción')
                    ->colors([
                        'success' => 'login_success',
                        'danger' => ['login_failed', 'login_blocked', 'access_denied'],
                        'warning' => ['logout', 'password_changed'],
                        'primary' => ['user_created', 'user_updated'],
                        'info' => ['data_export', 'report_generated'],
                    ])
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'login_success' => 'Inicio de sesión',
                            'login_failed' => 'Login fallido',
                            'login_blocked' => 'IP bloqueada',
                            'access_denied' => 'Acceso denegado',
                            'logout' => 'Cerró sesión',
                            'password_changed' => 'Cambió contraseña',
                            'user_created' => 'Usuario creado',
                            'user_updated' => 'Usuario actualizado',
                            'data_export' => 'Exportó datos',
                            'report_generated' => 'Generó reporte',
                            default => ucfirst(str_replace('_', ' ', $state)),
                        };
                    }),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('details')
                    ->label('Detalles')
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
                        
                        return '-';
                    })
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->details),
            ])
            ->actions([
                Tables\Actions\Action::make('details')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalContent(fn ($record) => view('filament.security.activity-details', compact('record')))
                    ->modalHeading('Detalles de Actividad'),
            ])
            ->emptyStateHeading('Sin actividad reciente')
            ->emptyStateDescription('No se ha registrado actividad en el sistema.')
            ->emptyStateIcon('heroicon-o-clock')
            ->striped()
            ->paginated(false)
            ->poll('10s');
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Gerente']) && auth()->user()->can('seguridad.ver');
    }
}