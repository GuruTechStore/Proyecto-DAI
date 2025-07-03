<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VentaResource\Pages;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateRangeFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VentaResource extends Resource
{
    protected static ?string $model = Venta::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $modelLabel = 'Venta';

    protected static ?string $pluralModelLabel = 'Ventas';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Venta')
                    ->description('Datos principales de la venta')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        TextInput::make('numero_factura')
                            ->label('Número de Factura')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'F-' . str_pad(Venta::count() + 1, 6, '0', STR_PAD_LEFT))
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
                        Select::make('metodo_pago')
                            ->label('Método de Pago')
                            ->options([
                                'efectivo' => 'Efectivo',
                                'tarjeta' => 'Tarjeta',
                                'transferencia' => 'Transferencia',
                                'yape' => 'Yape',
                                'plin' => 'Plin',
                            ])
                            ->required()
                            ->native(false),
                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'pagado' => 'Pagado',
                                'cancelado' => 'Cancelado',
                            ])
                            ->default('pendiente')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Productos')
                    ->description('Productos incluidos en la venta')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Repeater::make('detalles')
                            ->relationship()
                            ->schema([
                                Select::make('producto_id')
                                    ->label('Producto')
                                    ->relationship('producto', 'nombre')
                                    ->searchable(['nombre', 'codigo'])
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $producto = Producto::find($state);
                                            $set('precio_unitario', $producto->precio_venta ?? 0);
                                        }
                                    }),
                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => 
                                        $set('subtotal', $state * $get('precio_unitario'))),
                                TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->numeric()
                                    ->prefix('S/')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => 
                                        $set('subtotal', $state * $get('cantidad'))),
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('S/')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar Producto')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $total = collect($state)->sum('subtotal');
                                $set('total', $total);
                            }),
                    ]),

                Section::make('Resumen')
                    ->description('Total de la venta')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Placeholder::make('total_calculado')
                            ->label('Total Calculado')
                            ->content(function (Forms\Get $get) {
                                $detalles = $get('detalles') ?? [];
                                $total = collect($detalles)->sum('subtotal');
                                return 'S/. ' . number_format($total, 2);
                            }),
                        TextInput::make('total')
                            ->label('Total Final')
                            ->numeric()
                            ->prefix('S/')
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Notas adicionales sobre la venta')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_factura')
                    ->label('Factura')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable(['cliente.nombre', 'cliente.apellido'])
                    ->formatStateUsing(fn ($record) => $record->cliente->nombre . ' ' . $record->cliente->apellido)
                    ->url(fn ($record) => ClienteResource::getUrl('view', ['record' => $record->cliente]))
                    ->color('gray'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('metodo_pago')
                    ->label('Método de Pago')
                    ->colors([
                        'success' => 'efectivo',
                        'primary' => 'tarjeta',
                        'warning' => 'transferencia',
                        'info' => ['yape', 'plin'],
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'efectivo' => 'Efectivo',
                        'tarjeta' => 'Tarjeta',
                        'transferencia' => 'Transferencia',
                        'yape' => 'Yape',
                        'plin' => 'Plin',
                        default => ucfirst($state),
                    }),
                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'pagado',
                        'danger' => 'cancelado',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pendiente',
                        'heroicon-o-check-circle' => 'pagado',
                        'heroicon-o-x-circle' => 'cancelado',
                    ]),
                TextColumn::make('detalles_count')
                    ->counts('detalles')
                    ->label('Items')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('usuario.name')
                    ->label('Vendedor')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'pagado' => 'Pagado',
                        'cancelado' => 'Cancelado',
                    ])
                    ->multiple(),
                SelectFilter::make('metodo_pago')
                    ->label('Método de Pago')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'tarjeta' => 'Tarjeta',
                        'transferencia' => 'Transferencia',
                        'yape' => 'Yape',
                        'plin' => 'Plin',
                    ])
                    ->multiple(),
                SelectFilter::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nombre')
                    ->searchable()
                    ->preload(),
                DateRangeFilter::make('created_at')
                    ->label('Rango de Fechas'),
                Tables\Filters\Filter::make('ventas_hoy')
                    ->label('Ventas de Hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),
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
                                'pendiente' => 'Pendiente',
                                'pagado' => 'Pagado',
                                'cancelado' => 'Cancelado',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Motivo del cambio de estado'),
                    ])
                    ->action(function ($record, $data) {
                        $record->update($data);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Estado actualizado correctamente')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => auth()->user()->can('ventas.editar')),
                Tables\Actions\Action::make('imprimir')
                    ->label('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn ($record) => route('ventas.factura', $record))
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()->can('ventas.imprimir')),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->can('ventas.editar')),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('ventas.eliminar')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('ventas.eliminar')),
                    Tables\Actions\ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->can('ventas.exportar')),
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
            'index' => Pages\ListVentas::route('/'),
            'create' => Pages\CreateVenta::route('/create'),
            'view' => Pages\ViewVenta::route('/{record}'),
            'edit' => Pages\EditVenta::route('/{record}/edit'),
        ];
    }

    // Políticas de acceso
    public static function canViewAny(): bool
    {
        return auth()->user()->can('ventas.ver');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('ventas.crear');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('ventas.editar');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('ventas.eliminar');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('ventas.ver');
    }
}

// Pages para el Resource
namespace App\Filament\Resources\VentaResource\Pages;

use App\Filament\Resources\VentaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVentas extends ListRecords
{
    protected static string $resource = VentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()->can('ventas.crear')),
        ];
    }
}

class CreateVenta extends \Filament\Resources\Pages\CreateRecord
{
    protected static string $resource = VentaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] = auth()->id();
        $data['numero_factura'] = 'F-' . str_pad(
            \App\Models\Venta::count() + 1, 
            6, 
            '0', 
            STR_PAD_LEFT
        );
        return $data;
    }

    protected function afterCreate(): void
    {
        // Actualizar stock de productos
        foreach ($this->record->detalles as $detalle) {
            $producto = $detalle->producto;
            $producto->decrement('stock', $detalle->cantidad);
            
            // Registrar movimiento de inventario
            \App\Models\MovimientoInventario::create([
                'producto_id' => $producto->id,
                'tipo' => 'salida',
                'cantidad' => $detalle->cantidad,
                'motivo' => 'venta',
                'referencia_id' => $this->record->id,
                'referencia_tipo' => 'venta',
                'usuario_id' => auth()->id(),
            ]);
        }
    }
}

class EditVenta extends \Filament\Resources\Pages\EditRecord
{
    protected static string $resource = VentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->can('ventas.eliminar')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

class ViewVenta extends \Filament\Resources\Pages\ViewRecord
{
    protected static string $resource = VentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()->can('ventas.editar')),
        ];
    }
}