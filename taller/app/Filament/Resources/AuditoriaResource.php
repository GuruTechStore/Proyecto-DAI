<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditoriaResource\Pages;
use App\Models\Auditoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AuditoriaResource extends Resource
{
    protected static ?string $model = Auditoria::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationLabel = 'Auditoría';

    protected static ?string $modelLabel = 'Auditoría';

    protected static ?string $pluralModelLabel = 'Auditorías';

    protected static ?string $navigationGroup = 'Seguridad';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('created_at')
                    ->label('Fecha y Hora')
                    ->content(fn ($record) => $record?->created_at?->format('d/m/Y H:i:s')),
                Forms\Components\Placeholder::make('user.name')
                    ->label('Usuario')
                    ->content(fn ($record) => $record?->user?->name ?? 'Sistema'),
                Forms\Components\Placeholder::make('event')
                    ->label('Evento')
                    ->content(fn ($record) => match ($record?->event) {
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        'restored' => 'Restaurado',
                        default => ucfirst($record?->event ?? ''),
                    }),
                Forms\Components\Placeholder::make('auditable_type')
                    ->label('Modelo')
                    ->content(fn ($record) => class_basename($record?->auditable_type)),
                Forms\Components\Placeholder::make('auditable_id')
                    ->label('ID del Registro')
                    ->content(fn ($record) => $record?->auditable_id),
                Forms\Components\Textarea::make('old_values')
                    ->label('Valores Anteriores')
                    ->disabled()
                    ->formatStateUsing(fn ($state) => 
                        is_array($state) 
                            ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                            : $state
                    ),
                Forms\Components\Textarea::make('new_values')
                    ->label('Valores Nuevos')
                    ->disabled()
                    ->formatStateUsing(fn ($state) => 
                        is_array($state) 
                            ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                            : $state
                    ),
                Forms\Components\Placeholder::make('ip_address')
                    ->label('Dirección IP')
                    ->content(fn ($record) => $record?->ip_address),
                Forms\Components\Placeholder::make('user_agent')
                    ->label('User Agent')
                    ->content(fn ($record) => $record?->user_agent),
                Forms\Components\Textarea::make('url')
                    ->label('URL')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->default('Sistema')
                    ->searchable()
                    ->badge()
                    ->color('gray'),
                BadgeColumn::make('event')
                    ->label('Evento')
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                        'info' => 'restored',
                    ])
                    ->icons([
                        'heroicon-o-plus-circle' => 'created',
                        'heroicon-o-pencil' => 'updated',
                        'heroicon-o-trash' => 'deleted',
                        'heroicon-o-arrow-path' => 'restored',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'created' => 'Creado',
                            'updated' => 'Actualizado',
                            'deleted' => 'Eliminado',
                            'restored' => 'Restaurado',
                            default => ucfirst($state),
                        };
                    }),
                TextColumn::make('auditable_type')
                    ->label('Modelo')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->badge()
                    ->color('primary'),
                TextColumn::make('auditable_id')
                    ->label('ID')
                    ->badge()
                    ->color('info'),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('url')
                    ->label('URL')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->url)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->label('Evento')
                    ->options([
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        'restored' => 'Restaurado',
                    ])
                    ->multiple(),
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('auditable_type')
                    ->label('Modelo')
                    ->options([
                        'App\Models\Cliente' => 'Cliente',
                        'App\Models\Producto' => 'Producto',
                        'App\Models\Venta' => 'Venta',
                        'App\Models\Reparacion' => 'Reparación',
                        'App\Models\User' => 'Usuario',
                        'App\Models\Empleado' => 'Empleado',
                        'App\Models\Proveedor' => 'Proveedor',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('today')
                    ->label('Solo Hoy')
                    ->query(fn ($query) => $query->whereDate('created_at', today()))
                    ->toggle(),
                Tables\Filters\Filter::make('changes_only')
                    ->label('Solo con Cambios')
                    ->query(fn ($query) => $query->whereNotNull('old_values'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('compare_changes')
                    ->label('Ver Cambios')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->modalContent(fn ($record) => view('filament.audit.compare-changes', compact('record')))
                    ->modalHeading('Comparar Cambios')
                    ->modalWidth('5xl')
                    ->visible(fn ($record) => $record->event === 'updated' && $record->old_values),
                Tables\Actions\Action::make('view_related')
                    ->label('Ver Registro')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(function ($record) {
                        $model = $record->auditable_type;
                        $id = $record->auditable_id;
                        
                        // Mapear modelos a sus resources
                        $resourceMap = [
                            'App\Models\Cliente' => \App\Filament\Resources\ClienteResource::class,
                            'App\Models\Producto' => \App\Filament\Resources\ProductoResource::class,
                            'App\Models\Venta' => \App\Filament\Resources\VentaResource::class,
                            'App\Models\Reparacion' => \App\Filament\Resources\ReparacionResource::class,
                            'App\Models\User' => \App\Filament\Resources\UsuarioResource::class,
                            'App\Models\Empleado' => \App\Filament\Resources\EmpleadoResource::class,
                            'App\Models\Proveedor' => \App\Filament\Resources\ProveedorResource::class,
                        ];
                        
                        if (isset($resourceMap[$model])) {
                            try {
                                return $resourceMap[$model]::getUrl('view', ['record' => $id]);
                            } catch (\Exception $e) {
                                return null;
                            }
                        }
                        
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->auditable && $record->event !== 'deleted'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->can('auditoria.exportar')),
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasRole('Super Admin')),
                ]),
            ])
            ->striped()
            ->poll('60s')
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
    //         'index' => Pages\ListAuditorias::route('/'),
    //         'view' => Pages\ViewAuditoria::route('/{record}'),
    //     ];
    // }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Gerente']) && auth()->user()->can('auditoria.ver');
    }

    public static function canCreate(): bool
    {
        return false; // Las auditorías se crean automáticamente
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Las auditorías no deben editarse
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole('Super Admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Gerente']) && auth()->user()->can('auditoria.ver');
    }
}