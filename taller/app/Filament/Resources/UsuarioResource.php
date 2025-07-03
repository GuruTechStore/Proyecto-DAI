<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsuarioResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuarioResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Personal')
                    ->description('Datos básicos del usuario')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre Completo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nombre y apellidos del usuario'),
                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('usuario@empresa.com'),
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->placeholder('+51 999 999 999'),
                        TextInput::make('dni')
                            ->label('DNI')
                            ->unique(ignoreRecord: true)
                            ->length(8)
                            ->numeric()
                            ->placeholder('12345678'),
                    ])
                    ->columns(2),

                Section::make('Contraseña')
                    ->description('Configuración de acceso')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->same('password_confirmation')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->placeholder('Mínimo 8 caracteres'),
                        TextInput::make('password_confirmation')
                            ->label('Confirmar Contraseña')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false)
                            ->placeholder('Confirma la contraseña'),
                    ])
                    ->columns(2)
                    ->hiddenOn('view'),

                Section::make('Roles y Permisos')
                    ->description('Asignación de roles y estado')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Select::make('roles')
                            ->label('Roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->placeholder('Selecciona uno o más roles')
                            ->helperText('Los roles determinan los permisos del usuario'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Usuario Activo')
                            ->default(true)
                            ->helperText('Los usuarios inactivos no pueden iniciar sesión'),
                        Forms\Components\Toggle::make('email_verified_at')
                            ->label('Email Verificado')
                            ->formatStateUsing(fn ($state) => !is_null($state))
                            ->dehydrateStateUsing(fn ($state) => $state ? now() : null)
                            ->visible(fn () => auth()->user()->hasRole('Super Admin')),
                    ])
                    ->columns(2),

                Section::make('Información Adicional')
                    ->description('Datos complementarios')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Notas adicionales sobre el usuario')
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Fecha de Creación')
                            ->content(fn ($record) => $record?->created_at?->format('d/m/Y H:i:s') ?? 'Nuevo usuario'),
                        Forms\Components\Placeholder::make('last_login_at')
                            ->label('Último Acceso')
                            ->content(fn ($record) => $record?->last_login_at?->format('d/m/Y H:i:s') ?? 'Nunca'),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->colors([
                        'danger' => 'Super Admin',
                        'warning' => 'Gerente',
                        'primary' => 'Supervisor',
                        'success' => 'Empleado',
                        'gray' => 'Cliente',
                    ])
                    ->separator(','),
                BadgeColumn::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                BadgeColumn::make('email_verified_at')
                    ->label('Email Verificado')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->formatStateUsing(fn ($state) => !is_null($state))
                    ->toggleable(),
                TextColumn::make('last_login_at')
                    ->label('Último Acceso')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Nunca')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Rol')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->boolean()
                    ->trueLabel('Solo Activos')
                    ->falseLabel('Solo Inactivos')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Verificado')
                    ->nullable()
                    ->trueLabel('Verificado')
                    ->falseLabel('No Verificado')
                    ->native(false),
                Tables\Filters\DateRangeFilter::make('created_at')
                    ->label('Fecha de Creación'),
                Tables\Filters\DateRangeFilter::make('last_login_at')
                    ->label('Último Acceso'),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn ($record) => $record->is_active ? 'Desactivar' : 'Activar')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['is_active' => !$record->is_active]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Estado actualizado')
                            ->success()
                            ->body('Usuario ' . ($record->is_active ? 'activado' : 'desactivado') . ' correctamente')
                            ->send();
                    })
                    ->visible(fn () => auth()->user()->can('usuarios.editar')),
                Tables\Actions\Action::make('reset_password')
                    ->label('Resetear Contraseña')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        TextInput::make('new_password')
                            ->label('Nueva Contraseña')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->same('confirm_password'),
                        TextInput::make('confirm_password')
                            ->label('Confirmar Contraseña')
                            ->password()
                            ->required()
                            ->dehydrated(false),
                    ])
                    ->action(function ($record, $data) {
                        $record->update([
                            'password' => Hash::make($data['new_password'])
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Contraseña actualizada')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => auth()->user()->can('usuarios.editar')),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->can('usuarios.editar')),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('usuarios.eliminar')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('usuarios.eliminar')),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar Seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        })
                        ->visible(fn () => auth()->user()->can('usuarios.editar')),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar Seleccionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        })
                        ->visible(fn () => auth()->user()->can('usuarios.editar')),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsuarios::route('/'),
            'create' => Pages\CreateUsuario::route('/create'),
            'view' => Pages\ViewUsuario::route('/{record}'),
            'edit' => Pages\EditUsuario::route('/{record}/edit'),
        ];
    }

    // Políticas de acceso
    public static function canViewAny(): bool
    {
        return auth()->user()->can('usuarios.ver');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('usuarios.crear');
    }

    public static function canEdit(Model $record): bool
    {
        // No puede editarse a sí mismo si es Super Admin
        if ($record->id === auth()->id() && auth()->user()->hasRole('Super Admin')) {
            return false;
        }
        return auth()->user()->can('usuarios.editar');
    }

    public static function canDelete(Model $record): bool
    {
        // No puede eliminarse a sí mismo
        if ($record->id === auth()->id()) {
            return false;
        }
        return auth()->user()->can('usuarios.eliminar');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('usuarios.ver');
    }
}