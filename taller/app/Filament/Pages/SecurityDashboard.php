<?php

namespace App\Filament\Pages;

use App\Models\SecurityLog;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;

class SecurityDashboard extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.pages.security-dashboard';

    protected static ?string $navigationLabel = 'Panel de Seguridad';

    protected static ?string $title = 'Panel de Seguridad';

    protected static ?string $navigationGroup = 'Seguridad';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Gerente']);
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->can('seguridad.ver'), 403);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\SecurityWidget::class,
            \App\Filament\Widgets\ActivityWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SecurityLog::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->default('Sistema')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('action')
                    ->label('Acción')
                    ->colors([
                        'success' => 'login_success',
                        'danger' => ['login_failed', 'login_blocked', 'access_denied'],
                        'warning' => ['logout', 'password_changed'],
                        'primary' => ['user_created', 'user_updated'],
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
                            default => ucfirst(str_replace('_', ' ', $state)),
                        };
                    }),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->copyable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('Navegador')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->user_agent)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Tipo de Acción')
                    ->options([
                        'login_success' => 'Login Exitoso',
                        'login_failed' => 'Login Fallido',
                        'login_blocked' => 'Login Bloqueado',
                        'access_denied' => 'Acceso Denegado',
                        'logout' => 'Logout',
                        'password_changed' => 'Cambio de Contraseña',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\DateRangeFilter::make('created_at')
                    ->label('Rango de Fechas'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn ($record) => view('filament.security.log-details', compact('record'))),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->poll('30s');
    }

    public function getHeading(): string
    {
        return 'Panel de Seguridad';
    }

    public function getSubheading(): string
    {
        return 'Monitoreo de actividad y logs de seguridad del sistema';
    }
}