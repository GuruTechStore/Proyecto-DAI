<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReparacionResource\Pages;
use App\Models\Reparacion;
use App\Models\Cliente;
use App\Models\Empleado;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReparacionResource extends Resource
{
    protected static ?string $model = Reparacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Reparaciones';

    protected static ?string $modelLabel = 'Reparación';

    protected static ?string $pluralModelLabel = 'Reparaciones';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('estado', ['recibido', 'diagnosticando', 'reparando'])->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Reparación')
                    ->description('Datos principales de la reparación')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        TextInput::make('codigo_ticket')
                            ->label('Código de Ticket')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'REP-' . strtoupper(uniqid()))
                            ->disabled()
                            ->dehydrated(),
                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->relationship('cliente', 'nombre')
                            ->searchable(['nombre', 'apellido', 'telefono'])
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('nombre')->required(),
                                TextInput::make('apellido'),
                                TextInput::make('telefono')->required(),
                                TextInput::make('email')->email(),
                            ]),
                        Select::make('empleado_id')
                            ->label('Técnico Asignado')
                            ->relationship('empleado', 'nombres')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'recibido' => 'Recibido',
                                'diagnosticando' => 'Diagnosticando',
                                'reparando' => 'Reparando',
                                'completado' => 'Completado',
                                'cancelado' => 'Cancelado',
                            ])
                            ->default('recibido')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(3),

                Section::make('Detalles del Equipo')
                    ->description('Información sobre el equipo a reparar')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->schema([
                        TextInput::make('tipo_equipo')
                            ->label('Tipo de Equipo')
                            ->required()
                            ->placeholder('Ej: Smartphone, Laptop, Tablet'),
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
                    ])
                    ->columns(4),

                Section::make('Diagnóstico y Reparación')
                    ->description('Detalles técnicos de la reparación')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Textarea::make('problema_reportado')
                            ->label('Problema Reportado')
                            ->required()
                            ->placeholder('Describe el problema reportado por el cliente')
                            ->rows(3),
                        Textarea::make('diagnostico')
                            ->label('Diagnóstico Técnico')
                            ->placeholder('Diagnóstico detallado del técnico')
                            ->rows(3),
                        Textarea::make('solucion')
                            ->label('Solución Aplicada')
                            ->placeholder('Describe la solución implementada')
                            ->rows(3),
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Observaciones adicionales')
                            ->rows(2),
                    ])
                    ->columns(1),

                Section::make('Fechas y Costos')
                    ->description('Información de fechas y montos')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        DateTimePicker::make('fecha_ingreso')
                            ->label('Fecha de Ingreso')
                            ->required()
                            ->default(now())
                            ->native(false),
                        DateTimePicker::make('fecha_estimada_entrega')
                            ->label('Fecha Estimada de Entrega')
                            ->native(false),
                        DateTimePicker::make('fecha_entrega')
                            ->label('Fecha de Entrega')
                            ->native(false),
                        TextInput::make('costo_estimado')
                            ->label('Costo Estimado')
                            ->numeric()
                            ->prefix('S/')
                            ->placeholder('0.00'),
                        TextInput::make('costo_final')
                            ->label('Costo Final')
                            ->numeric()
                            ->prefix('S/')
                            ->placeholder('0.00'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_ticket')
                    ->label('Ticket')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable(['cliente.nombre', 'cliente.apellido'])
                    ->formatStateUsing(fn ($record) => $record->cliente->nombre . ' ' . $record->cliente->apellido)
                    ->url(fn ($record) => ClienteResource::getUrl('view', ['record' => $record->cliente]))
                    ->color('primary'),
                TextColumn::make('empleado.nombres')
                    ->label('Técnico Asignado')
                    ->toggleable(),
                TextColumn::make('tipo_equipo')
                    ->label('Equipo')
                    ->formatStateUsing(fn ($record) => $record->marca . ' ' . $record->modelo)
                    ->wrap(),
                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'recibido',
                        'warning' => 'diagnosticando',
                        'primary' => 'reparando',
                        'success' => 'completado',
                        'danger' => 'cancelado',
                    ])
                    ->icons([
                        'heroicon-o-inbox' => 'recibido',
                        'heroicon-o-magnifying-glass' => 'diagnosticando',
                        'heroicon-o-wrench-screwdriver' => 'reparando',
                        'heroicon-o-check-circle' => 'completado',
                        'heroicon-o-x-circle' => 'cancelado',
                    ]),
                TextColumn::make('costo_estimado')
                    ->label('Costo Est.')
                    ->money('PEN')
                    ->toggleable(),
                TextColumn::make('costo_final')
                    ->label('Costo Final')
                    ->money('PEN')
                    ->toggleable(),
                TextColumn::make('fecha_ingreso')
                    ->label('Fecha Ingreso')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('fecha_estimada_entrega')
                    ->label('Entrega Est.')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fecha_ingreso', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'recibido' => 'Recibido',
                        'diagnosticando' => 'Diagnosticando',
                        'reparando' => 'Reparando',
                        'completado' => 'Completado',
                        'cancelado' => 'Cancelado',
                    ])
                    ->multiple(),
                SelectFilter::make('empleado_id')
                    ->label('Técnico')
                    ->relationship('empleado', 'nombres')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('tipo_equipo')
                    ->label('Tipo de Equipo')
                    ->options([
                        'smartphone' => 'Smartphone',
                        'tablet' => 'Tablet',
                        'laptop' => 'Laptop',
                        'desktop' => 'Desktop',
                    ]),
                DateRangeFilter::make('fecha_ingreso')
                    ->label('Fecha de Ingreso'),
                DateRangeFilter::make('fecha_estimada_entrega')
                    ->label('Fecha Estimada de Entrega'),
            ])
            ->actions([
                Tables\Actions\Action::make('cambiar_estado')
                    ->label('Cambiar Estado')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Select::make('estado')
                            ->label('Nuevo Estado')
                            ->options([
                                'recibido' => 'Recibido',
                                'diagnosticando' => 'Diagnosticando',
                                'reparando' => 'Reparando',
                                'completado' => 'Completado',
                                'cancelado' => 'Cancelado',
                            ])
                            ->required()
                            ->native(false),
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Agrega observaciones sobre el cambio de estado'),
                        DateTimePicker::make('fecha_entrega')
                            ->label('Fecha de Entrega')
                            ->visible(fn ($get) => $get('estado') === 'completado')
                            ->native(false),
                        TextInput::make('costo_final')
                            ->label('Costo Final')
                            ->numeric()
                            ->prefix('S/')
                            ->visible(fn ($get) => $get('estado') === 'completado'),
                    ])
                    ->action(function ($record, $data) {
                        $updateData = [
                            'estado' => $data['estado'],
                            'observaciones' => $data['observaciones'] ?? $record->observaciones,
                        ];

                        if ($data['estado'] === 'completado') {
                            $updateData['fecha_entrega'] = $data['fecha_entrega'] ?? now();
                            if (isset($data['costo_final'])) {
                                $updateData['costo_final'] = $data['costo_final'];
                            }
                        }

                        $record->update($updateData);

                        Notification::make()
                            ->title('Estado actualizado correctamente')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => auth()->user()->can('reparaciones.editar')),
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->visible(fn () => auth()->user()->can('reparaciones.editar')),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->visible(fn () => auth()->user()->can('reparaciones.eliminar')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('reparaciones.eliminar')),
                    Tables\Actions\ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->can('reparaciones.exportar')),
                ]),
            ])
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
            'index' => Pages\ListReparaciones::route('/'),
            'create' => Pages\CreateReparacion::route('/create'),
            'view' => Pages\ViewReparacion::route('/{record}'),
            'edit' => Pages\EditReparacion::route('/{record}/edit'),
        ];
    }

    // Políticas de acceso
    public static function canViewAny(): bool
    {
        return auth()->user()->can('reparaciones.ver');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('reparaciones.crear');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('reparaciones.editar');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('reparaciones.eliminar');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('reparaciones.ver');
    }
}

// Pages para el Resource
namespace App\Filament\Resources\ReparacionResource\Pages;

use App\Filament\Resources\ReparacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReparaciones extends ListRecords
{
    protected static string $resource = ReparacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()->can('reparaciones.crear')),
        ];
    }
}

class CreateReparacion extends \Filament\Resources\Pages\CreateRecord
{
    protected static string $resource = ReparacionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['codigo_ticket'] = 'REP-' . strtoupper(uniqid());
        return $data;
    }
}

class EditReparacion extends \Filament\Resources\Pages\EditRecord
{
    protected static string $resource = ReparacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->can('reparaciones.eliminar')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

class ViewReparacion extends \Filament\Resources\Pages\ViewRecord
{
    protected static string $resource = ReparacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()->can('reparaciones.editar')),
        ];
    }
}