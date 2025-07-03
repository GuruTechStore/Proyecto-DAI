<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventarioResource\Pages;
use App\Models\MovimientoInventario;
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
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InventarioResource extends Resource
{
    protected static ?string $model = MovimientoInventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Movimientos';

    protected static ?string $modelLabel = 'Movimiento de Inventario';

    protected static ?string $pluralModelLabel = 'Movimientos de Inventario';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Movimiento')
                    ->description('Datos del movimiento de inventario')
                    ->icon('heroicon-o-arrows-right-left')
                    ->schema([
                        Select::make('producto_id')
                            ->label('Producto')
                            ->relationship('producto', 'nombre')
                            ->searchable(['nombre', 'codigo'])
                            ->preload()
                            ->required(),
                        Select::make('tipo')
                            ->label('Tipo de Movimiento')
                            ->options([
                                'entrada' => 'Entrada',
                                'salida' => 'Salida',
                                'ajuste' => 'Ajuste',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        TextInput::make('stock_anterior')
                            ->label('Stock Anterior')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('stock_nuevo')
                            ->label('Stock Nuevo')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(3),

                Section::make('Detalles')
                    ->description('Información adicional del movimiento')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Select::make('motivo')
                            ->label('Motivo')
                            ->options([
                                'compra' => 'Compra',
                                'venta' => 'Venta',
                                'devolucion' => 'Devolución',
                                'ajuste' => 'Ajuste de Inventario',
                                'merma' => 'Merma',
                                'robo' => 'Robo/Pérdida',
                                'reabastecimiento' => 'Reabastecimiento',
                                'traslado' => 'Traslado',
                            ])
                            ->required()
                            ->native(false),
                        TextInput::make('referencia_id')
                            ->label('ID de Referencia')
                            ->numeric()
                            ->placeholder('ID de venta, compra, etc.'),
                        TextInput::make('referencia_tipo')
                            ->label('Tipo de Referencia')
                            ->placeholder('venta, compra, ajuste, etc.'),
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Detalles adicionales del movimiento')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->limit(30)
                    ->url(fn ($record) => ProductoResource::getUrl('view', ['record' => $record->producto])),
                TextColumn::make('producto.codigo')
                    ->label('Código')
                    ->searchable()
                    ->copyable(),
                BadgeColumn::make('tipo')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'entrada',
                        'danger' => 'salida',
                        'warning' => 'ajuste',
                    ])
                    ->icons([
                        'heroicon-o-arrow-down-tray' => 'entrada',
                        'heroicon-o-arrow-up-tray' => 'salida',
                        'heroicon-o-adjustments-horizontal' => 'ajuste',
                    ]),
                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->badge()
                    ->color(fn ($record) => match ($record->tipo) {
                        'entrada' => 'success',
                        'salida' => 'danger',
                        'ajuste' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('stock_anterior')
                    ->label('Stock Ant.')
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('stock_nuevo')
                    ->label('Stock Nuevo')
                    ->numeric()
                    ->toggleable(),
                BadgeColumn::make('motivo')
                    ->label('Motivo')
                    ->colors([
                        'primary' => ['compra', 'reabastecimiento'],
                        'success' => 'venta',
                        'warning' => ['ajuste', 'devolucion'],
                        'danger' => ['merma', 'robo'],
                        'info' => 'traslado',
                    ]),
                TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo de Movimiento')
                    ->options([
                        'entrada' => 'Entrada',
                        'salida' => 'Salida',
                        'ajuste' => 'Ajuste',
                    ])
                    ->multiple(),
                SelectFilter::make('motivo')
                    ->label('Motivo')
                    ->options([
                        'compra' => 'Compra',
                        'venta' => 'Venta',
                        'devolucion' => 'Devolución',
                        'ajuste' => 'Ajuste de Inventario',
                        'merma' => 'Merma',
                        'robo' => 'Robo/Pérdida',
                        'reabastecimiento' => 'Reabastecimiento',
                        'traslado' => 'Traslado',
                    ])
                    ->multiple(),
                SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre')
                    ->searchable()
                    ->preload(),
                // DateRangeFilter::make('created_at')
                //     ->label('Rango de Fechas'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->can('inventario.editar')),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('inventario.eliminar')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('inventario.eliminar')),
                    Tables\Actions\ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->can('inventario.exportar')),
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
    //         'index' => Pages\ListMovimientos::route('/'),
    //         'create' => Pages\CreateMovimiento::route('/create'),
    //         'view' => Pages\ViewMovimiento::route('/{record}'),
    //         'edit' => Pages\EditMovimiento::route('/{record}/edit'),
    //     ];
    // }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('inventario.ver');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('inventario.crear');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('inventario.editar');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('inventario.eliminar');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('inventario.ver');
    }
}