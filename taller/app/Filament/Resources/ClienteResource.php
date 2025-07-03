<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static ?string $navigationGroup = 'Gestión de Datos';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Personal')
                    ->description('Datos básicos del cliente')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Nombre del cliente'),
                        TextInput::make('apellido')
                            ->label('Apellido')
                            ->maxLength(100)
                            ->placeholder('Apellido del cliente'),
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->required()
                            ->tel()
                            ->rule('regex:/^[0-9\-\+\(\)\s]+$/')
                            ->placeholder('+51 999 999 999'),
                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->placeholder('cliente@ejemplo.com'),
                        Forms\Components\Textarea::make('direccion')
                            ->label('Dirección')
                            ->maxLength(255)
                            ->placeholder('Dirección completa del cliente')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Equipos del Cliente')
                    ->description('Equipos registrados para este cliente')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->schema([
                        Repeater::make('equipos')
                            ->relationship()
                            ->schema([
                                Select::make('tipo')
                                    ->label('Tipo de Equipo')
                                    ->options([
                                        'smartphone' => 'Smartphone',
                                        'tablet' => 'Tablet',
                                        'laptop' => 'Laptop',
                                        'desktop' => 'Desktop',
                                        'smartwatch' => 'Smartwatch',
                                        'consola' => 'Consola de Juegos',
                                        'otro' => 'Otro',
                                    ])
                                    ->required()
                                    ->native(false),
                                TextInput::make('marca')
                                    ->label('Marca')
                                    ->required()
                                    ->placeholder('Ej: Apple, Samsung, Huawei'),
                                TextInput::make('modelo')
                                    ->label('Modelo')
                                    ->required()
                                    ->placeholder('Ej: iPhone 15, Galaxy S24'),
                                TextInput::make('imei')
                                    ->label('IMEI/Serial')
                                    ->placeholder('Número de serie o IMEI'),
                                Forms\Components\Textarea::make('descripcion')
                                    ->label('Descripción')
                                    ->placeholder('Detalles adicionales del equipo')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar Equipo')
                            ->deleteAction(
                                fn ($action) => $action->requiresConfirmation()
                            ),
                    ])
                    ->hiddenOn('create')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('apellido')
                    ->label('Apellido')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(),
                TextColumn::make('equipos_count')
                    ->counts('equipos')
                    ->label('Equipos')
                    ->badge()
                    ->color('success'),
                TextColumn::make('reparaciones_count')
                    ->counts('reparaciones')
                    ->label('Reparaciones')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('created_at')
                    ->label('Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('has_equipos')
                    ->label('Estado de Equipos')
                    ->options([
                        'with' => 'Con equipos',
                        'without' => 'Sin equipos',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'with' => $query->has('equipos'),
                            'without' => $query->doesntHave('equipos'),
                            default => $query,
                        };
                    }),
                SelectFilter::make('has_reparaciones')
                    ->label('Estado de Reparaciones')
                    ->options([
                        'with' => 'Con reparaciones',
                        'without' => 'Sin reparaciones',
                        'active' => 'Con reparaciones activas',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'with' => $query->has('reparaciones'),
                            'without' => $query->doesntHave('reparaciones'),
                            'active' => $query->whereHas('reparaciones', fn ($q) => 
                                $q->whereNotIn('estado', ['completado', 'cancelado'])),
                            default => $query,
                        };
                    }),
                DateRangeFilter::make('created_at')
                    ->label('Fecha de registro'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->visible(fn () => auth()->user()->can('clientes.eliminar')),
                Tables\Actions\Action::make('nueva_reparacion')
                    ->label('Nueva Reparación')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->url(fn (Cliente $record): string => ReparacionResource::getUrl('create', ['cliente_id' => $record->id]))
                    ->visible(fn () => auth()->user()->can('reparaciones.crear')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('clientes.eliminar')),
                    Tables\Actions\ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->can('clientes.exportar')),
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
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'view' => Pages\ViewCliente::route('/{record}'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }

    // Políticas de acceso
    public static function canViewAny(): bool
    {
        return auth()->user()->can('clientes.ver');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('clientes.crear');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('clientes.editar');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('clientes.eliminar');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('clientes.eliminar');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('clientes.ver');
    }
}

// Pages para el Resource
namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClientes extends ListRecords
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()->can('clientes.crear')),
        ];
    }
}

class CreateCliente extends \Filament\Resources\Pages\CreateRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

class EditCliente extends \Filament\Resources\Pages\EditRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->can('clientes.eliminar')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

class ViewCliente extends \Filament\Resources\Pages\ViewRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()->can('clientes.editar')),
        ];
    }
}