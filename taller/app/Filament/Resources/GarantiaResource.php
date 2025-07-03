<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GarantiaResource\Pages;
use App\Models\Garantia;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class GarantiaResource extends Resource
{
    protected static ?string $model = Garantia::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Garantías';

    protected static ?string $modelLabel = 'Garantía';

    protected static ?string $pluralModelLabel = 'Garantías';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('estado', 'activa')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $vencidas = static::getModel()::where('fecha_vencimiento', '<', now())
            ->where('estado', 'activa')
            ->count();
        
        return $vencidas > 0 ? 'warning' : 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Garantía')
                    ->description('Datos básicos de la garantía')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código de Garantía')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'GAR-' . strtoupper(uniqid()))
                            ->disabled()
                            ->dehydrated(),
                        Select::make('reparacion_id')
                            ->label('Reparación')
                            ->relationship('reparacion', 'codigo_ticket')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('venta_id')
                            ->label('Venta (Opcional)')
                            ->relationship('venta', 'numero_factura')
                            ->searchable()
                            ->preload(),
                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'activa' => 'Activa',
                                'vencida' => 'Vencida',
                                'utilizada' => 'Utilizada',
                                'cancelada' => 'Cancelada',
                            ])
                            ->default('activa')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Fechas y Duración')
                    ->description('Período de vigencia de la garantía')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->default(today())
                            ->native(false),
                        DatePicker::make('fecha_vencimiento')
                            ->label('Fecha de Vencimiento')
                            ->required()
                            ->native(false)
                            ->after('fecha_inicio'),
                        TextInput::make('duracion_dias')
                            ->label('Duración (días)')
                            ->numeric()
                            ->default(90)
                            ->required()
                            ->minValue(1)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state && $get('fecha_inicio')) {
                                    $fechaInicio = \Carbon\Carbon::parse($get('fecha_inicio'));
                                    $fechaVencimiento = $fechaInicio->addDays($state);
                                    $set('fecha_vencimiento', $fechaVencimiento->format('Y-m-d'));
                                }
                            }),
                    ])
                    ->columns(3),

                Section::make('Términos y Condiciones')
                    ->description('Detalles de cobertura y condiciones')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Select::make('tipo_garantia')
                            ->label('Tipo de Garantía')
                            ->options([
                                'reparacion' => 'Garantía de Reparación',
                                'producto' => 'Garantía de Producto',
                                'servicio' => 'Garantía de Servicio',
                                'repuesto' => 'Garantía de Repuesto',
                            ])
                            ->required()
                            ->native(false),
                        Textarea::make('cobertura')
                            ->label('Cobertura')
                            ->placeholder('Describe qué cubre esta garantía')
                            ->required()
                            ->rows(3),
                        Textarea::make('exclusiones')
                            ->label('Exclusiones')
                            ->placeholder('Describe qué NO cubre esta garantía')
                            ->rows(3),
                        Textarea::make('condiciones')
                            ->label('Condiciones')
                            ->placeholder('Términos y condiciones adicionales')
                            ->rows(3),
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Notas adicionales')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('reparacion.codigo_ticket')
                    ->label('Reparación')
                    ->searchable()
                    ->url(fn ($record) => ReparacionResource::getUrl('view', ['record' => $record->reparacion]))
                    ->color('info'),
                TextColumn::make('reparacion.cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => 
                        $record->reparacion->cliente->nombre . ' ' . $record->reparacion->cliente->apellido
                    ),
                BadgeColumn::make('tipo_garantia')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'reparacion',
                        'success' => 'producto',
                        'warning' => 'servicio',
                        'info' => 'repuesto',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'reparacion' => 'Reparación',
                            'producto' => 'Producto',
                            'servicio' => 'Servicio',
                            'repuesto' => 'Repuesto',
                            default => ucfirst($state),
                        };
                    }),
                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'activa',
                        'danger' => 'vencida',
                        'warning' => 'utilizada',
                        'gray' => 'cancelada',
                    ])
                    ->icons([
                        'heroicon-o-shield-check' => 'activa',
                        'heroicon-o-shield-exclamation' => 'vencida',
                        'heroicon-o-check-circle' => 'utilizada',
                        'heroicon-o-x-circle' => 'cancelada',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'activa' => 'Activa',
                            'vencida' => 'Vencida',
                            'utilizada' => 'Utilizada',
                            'cancelada' => 'Cancelada',
                            default => ucfirst($state),
                        };
                    }),
                TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => 
                        $record->fecha_vencimiento < now() ? 'danger' : 
                        ($record->fecha_vencimiento < now()->addDays(7) ? 'warning' : 'success')
                    ),
                TextColumn::make('duracion_dias')
                    ->label('Duración')
                    ->badge()
                    ->suffix(' días')
                    ->color('gray'),
                TextColumn::make('dias_restantes')
                    ->label('Días Rest.')
                    ->state(function ($record) {
                        if ($record->estado !== 'activa') return '-';
                        $dias = now()->diffInDays($record->fecha_vencimiento, false);
                        return $dias > 0 ? $dias : 0;
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->estado !== 'activa') return 'gray';
                        $dias = now()->diffInDays($record->fecha_vencimiento, false);
                        return $dias <= 0 ? 'danger' : ($dias <= 7 ? 'warning' : 'success');
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'activa' => 'Activa',
                        'vencida' => 'Vencida',
                        'utilizada' => 'Utilizada',
                        'cancelada' => 'Cancelada',
                    ])
                    ->multiple(),
                SelectFilter::make('tipo_garantia')
                    ->label('Tipo de Garantía')
                    ->options([
                        'reparacion' => 'Reparación',
                        'producto' => 'Producto',
                        'servicio' => 'Servicio',
                        'repuesto' => 'Repuesto',
                    ])
                    ->multiple(),
                Tables\Filters\Filter::make('fecha_vencimiento')
                    ->form([
                        Forms\Components\DatePicker::make('vence_desde')
                            ->label('Vence Desde'),
                        Forms\Components\DatePicker::make('vence_hasta')
                            ->label('Vence Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['vence_desde'],
                                fn ($query, $date) => $query->whereDate('fecha_vencimiento', '>=', $date),
                            )
                            ->when(
                                $data['vence_hasta'],
                                fn ($query, $date) => $query->whereDate('fecha_vencimiento', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('por_vencer')
                    ->label('Por Vencer (7 días)')
                    ->query(fn ($query) => $query
                        ->where('estado', 'activa')
                        ->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])
                    )
                    ->toggle(),
                Tables\Filters\Filter::make('vencidas')
                    ->label('Vencidas')
                    ->query(fn ($query) => $query
                        ->where('estado', 'activa')
                        ->where('fecha_vencimiento', '<', now())
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('utilizar_garantia')
                    ->label('Utilizar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Utilizar Garantía')
                    ->modalDescription('¿Confirmas que deseas marcar esta garantía como utilizada?')
                    ->form([
                        Textarea::make('motivo_uso')
                            ->label('Motivo de Uso')
                            ->required()
                            ->placeholder('Describe por qué se utiliza la garantía'),
                    ])
                    ->action(function ($record, $data) {
                        $record->update([
                            'estado' => 'utilizada',
                            'fecha_uso' => now(),
                            'motivo_uso' => $data['motivo_uso'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Garantía utilizada')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->estado === 'activa' && auth()->user()->can('garantias.utilizar')),
                Tables\Actions\Action::make('extender_garantia')
                    ->label('Extender')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->form([
                        TextInput::make('dias_extension')
                            ->label('Días de Extensión')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(365),
                        Textarea::make('motivo_extension')
                            ->label('Motivo de Extensión')
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $nuevaFecha = \Carbon\Carbon::parse($record->fecha_vencimiento)
                            ->addDays($data['dias_extension']);
                        
                        $record->update([
                            'fecha_vencimiento' => $nuevaFecha,
                            'duracion_dias' => $record->duracion_dias + $data['dias_extension'],
                            'motivo_extension' => $data['motivo_extension'],
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Garantía extendida')
                            ->body("Nueva fecha de vencimiento: {$nuevaFecha->format('d/m/Y')}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => in_array($record->estado, ['activa', 'vencida']) && auth()->user()->can('garantias.extender')),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->can('garantias.editar')),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('garantias.eliminar')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('garantias.eliminar')),
                    Tables\Actions\ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->can('garantias.exportar')),
                    Tables\Actions\BulkAction::make('actualizar_vencidas')
                        ->label('Marcar como Vencidas')
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->fecha_vencimiento < now() && $record->estado === 'activa') {
                                    $record->update(['estado' => 'vencida']);
                                }
                            });
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Garantías actualizadas')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => auth()->user()->can('garantias.editar')),
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

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => Pages\ListGarantias::route('/'),
    //         'create' => Pages\CreateGarantia::route('/create'),
    //         'view' => Pages\ViewGarantia::route('/{record}'),
    //         'edit' => Pages\EditGarantia::route('/{record}/edit'),
    //     ];
    // }

    // Políticas de acceso
    public static function canViewAny(): bool
    {
        return auth()->user()->can('garantias.ver');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('garantias.crear');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('garantias.editar');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('garantias.eliminar');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('garantias.ver');
    }

    // Actualizar garantías vencidas automáticamente
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Actualizar garantías vencidas automáticamente
        static::getModel()::where('estado', 'activa')
            ->where('fecha_vencimiento', '<', now())
            ->update(['estado' => 'vencida']);
        
        return parent::getEloquentQuery();
    }
}