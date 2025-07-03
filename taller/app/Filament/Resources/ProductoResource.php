<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $modelLabel = 'Producto';

    protected static ?string $pluralModelLabel = 'Productos';

    protected static ?string $navigationGroup = 'Gestión de Datos';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereRaw('stock <= stock_minimo')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $stockBajo = static::getModel()::whereRaw('stock <= stock_minimo')->count();
        return $stockBajo > 0 ? 'danger' : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General')
                    ->description('Datos básicos del producto')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código del Producto')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('SKU-001')
                            ->alphaDash(),
                        TextInput::make('nombre')
                            ->label('Nombre del Producto')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nombre descriptivo del producto'),
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Descripción detallada del producto')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('categoria_id')
                            ->label('Categoría')
                            ->relationship('categoria', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->required()
                                    ->maxLength(100),
                                Textarea::make('descripcion')
                                    ->maxLength(255),
                            ]),
                        Select::make('proveedor_id')
                            ->label('Proveedor')
                            ->relationship('proveedor', 'nombre')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('nombre')->required(),
                                TextInput::make('contacto'),
                                TextInput::make('telefono'),
                                TextInput::make('email')->email(),
                            ]),
                    ])
                    ->columns(2),

                Section::make('Precios y Stock')
                    ->description('Información de inventario y precios')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        TextInput::make('precio_compra')
                            ->label('Precio de Compra')
                            ->numeric()
                            ->prefix('S/')
                            ->placeholder('0.00')
                            ->required(),
                        TextInput::make('precio_venta')
                            ->label('Precio de Venta')
                            ->numeric()
                            ->prefix('S/')
                            ->placeholder('0.00')
                            ->required(),
                        TextInput::make('stock')
                            ->label('Stock Actual')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->minValue(0),
                        TextInput::make('stock_minimo')
                            ->label('Stock Mínimo')
                            ->numeric()
                            ->default(5)
                            ->required()
                            ->minValue(0)
                            ->helperText('Cantidad mínima antes de alertar'),
                        TextInput::make('stock_maximo')
                            ->label('Stock Máximo')
                            ->numeric()
                            ->default(100)
                            ->minValue(0)
                            ->helperText('Cantidad máxima recomendada'),
                        TextInput::make('unidad_medida')
                            ->label('Unidad de Medida')
                            ->default('unidad')
                            ->placeholder('unidad, kg, litro, etc.'),
                    ])
                    ->columns(3),

                Section::make('Información Adicional')
                    ->description('Datos complementarios')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        FileUpload::make('imagen')
                            ->label('Imagen del Producto')
                            ->image()
                            ->directory('productos')
                            ->maxSize(2048)
                            ->imageEditor()
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('activo')
                            ->label('Producto Activo')
                            ->default(true)
                            ->helperText('Los productos inactivos no aparecen en ventas'),
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Notas adicionales sobre el producto')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagen')
                    ->label('Imagen')
                    ->circular()
                    ->defaultImageUrl(url('/images/no-image.png'))
                    ->size(50),
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->nombre),
                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('precio_venta')
                    ->label('Precio')
                    ->money('PEN')
                    ->sortable(),
                BadgeColumn::make('stock')
                    ->label('Stock')
                    ->colors([
                        'danger' => fn ($state, $record) => $state <= $record->stock_minimo,
                        'warning' => fn ($state, $record) => $state <= $record->stock_minimo * 1.5,
                        'success' => fn ($state, $record) => $state > $record->stock_minimo * 1.5,
                    ])
                    ->sortable(),
                TextColumn::make('stock_minimo')
                    ->label('Min')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('proveedor.nombre')
                    ->label('Proveedor')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('proveedor_id')
                    ->label('Proveedor')
                    ->relationship('proveedor', 'nombre')
                    ->searchable()
                    ->preload(),
                Filter::make('stock_bajo')
                    ->label('Stock Bajo')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('stock <= stock_minimo'))
                    ->toggle(),
                Filter::make('activo')
                    ->label('Solo Activos')
                    ->query(fn (Builder $query): Builder => $query->where('activo', true))
                    ->toggle()
                    ->default(),
                Filter::make('sin_stock')
                    ->label('Sin Stock')
                    ->query(fn (Builder $query): Builder => $query->where('stock', 0))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('adjustStock')
                    ->label('Ajustar Stock')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        Select::make('tipo')
                            ->label('Tipo de Movimiento')
                            ->options([
                                'entrada' => 'Entrada (Agregar)',
                                'salida' => 'Salida (Reducir)',
                                'ajuste' => 'Ajuste (Establecer cantidad exacta)',
                            ])
                            ->required()
                            ->reactive(),
                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Textarea::make('motivo')
                            ->label('Motivo')
                            ->required()
                            ->placeholder('Describe el motivo del ajuste'),
                    ])
                    ->action(function ($record, $data) {
                        $stockAnterior = $record->stock;
                        
                        match ($data['tipo']) {
                            'entrada' => $record->increment('stock', $data['cantidad']),
                            'salida' => $record->decrement('stock', $data['cantidad']),
                            'ajuste' => $record->update(['stock' => $data['cantidad']]),
                        };

                        // Registrar movimiento
                        \App\Models\MovimientoInventario::create([
                            'producto_id' => $record->id,
                            'tipo' => $data['tipo'],
                            'cantidad' => $data['cantidad'],
                            'stock_anterior' => $stockAnterior,
                            'stock_nuevo' => $record->fresh()->stock,
                            'motivo' => $data['motivo'],
                            'usuario_id' => auth()->id(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Stock actualizado correctamente')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => auth()->user()->can('productos.editar')),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('productos.eliminar')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('productos.eliminar')),
                    Tables\Actions\ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->can('productos.exportar')),
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'view' => Pages\ViewProducto::route('/{record}'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }

    // Políticas de acceso
    public static function canViewAny(): bool
    {
        return auth()->user()->can('productos.ver');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('productos.crear');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('productos.editar');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('productos.eliminar');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('productos.ver');
    }
}