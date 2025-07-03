<?php

namespace App\Filament\Widgets;

use App\Models\Producto;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventarioWidget extends TableWidget
{
    protected static ?string $heading = 'Productos con Stock Bajo';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Producto::query()
                    ->whereRaw('stock <= stock_minimo')
                    ->orderBy('stock', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Producto')
                    ->searchable()
                    ->weight('medium')
                    ->wrap(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state, $record) => 
                        $state == 0 ? 'danger' : 
                        ($state <= $record->stock_minimo * 0.5 ? 'warning' : 'success')
                    ),
                Tables\Columns\TextColumn::make('stock_minimo')
                    ->label('Mín.')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('reabastecer')
                    ->label('Reabastecer')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        Tables\Actions\Modal\Components\TextInput::make('cantidad')
                            ->label('Cantidad a agregar')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->placeholder('Ingresa la cantidad'),
                        Tables\Actions\Modal\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Motivo del reabastecimiento'),
                    ])
                    ->action(function ($record, $data) {
                        $record->update([
                            'stock' => $record->stock + $data['cantidad']
                        ]);

                        // Registrar movimiento de inventario
                        \App\Models\MovimientoInventario::create([
                            'producto_id' => $record->id,
                            'tipo' => 'entrada',
                            'cantidad' => $data['cantidad'],
                            'motivo' => 'reabastecimiento',
                            'observaciones' => $data['observaciones'] ?? null,
                            'usuario_id' => auth()->id(),
                        ]);

                        $this->dispatch('$refresh');
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Stock actualizado')
                            ->success()
                            ->body("Se agregaron {$data['cantidad']} unidades al producto {$record->nombre}")
                            ->send();
                    })
                    ->visible(fn () => auth()->user()->can('productos.editar')),
            ])
            ->emptyStateHeading('¡Excelente!')
            ->emptyStateDescription('No hay productos con stock bajo.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->striped()
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return auth()->user()->can('productos.ver');
    }
}