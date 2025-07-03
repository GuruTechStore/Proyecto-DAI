<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserActivityResource\Pages;
use App\Models\UserActivity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserActivityResource extends Resource
{
    protected static ?string $model = UserActivity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Actividad de Usuarios';

    protected static ?string $modelLabel = 'Actividad';

    protected static ?string $pluralModelLabel = 'Actividades de Usuarios';

    protected static ?string $navigationGroup = 'Seguridad';

    protected static ?int $navigationSort = 3;

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
                    ->content(fn ($record) => $record?->user?->name),
                Forms\Components\Placeholder::make('action')
                    ->label('Acción')
                    ->content(fn ($record) => $record?->action),
                Forms\Components\Placeholder::make('model_type')
                    ->label('Modelo')
                    ->content(fn ($record) => class_basename($record?->model_type)),
                Forms\Components\Placeholder::make('model_id')
                    ->label('ID del Modelo')
                    ->content(fn ($record) => $record?->model_id),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->disabled(),
                Forms\Components\Textarea::make('properties')
                    ->label('Propiedades')
                    ->disabled()
                    ->formatStateUsing(fn ($state) => 
                        is_array($state) 
                            ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                            : $state
                    ),
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
                    ->searchable()
                    ->badge()
                    ->color('primary'),
                BadgeColumn::make('action')
                    ->label('Acción')
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                        'info' => ['viewed', 'exported'],
                        'gray' => 'other',
                    ])
                    ->icons([
                        'heroicon-o-plus-circle' => 'created',
                        'heroicon-o-pencil' => 'updated',
                        'heroicon-o-trash' => 'deleted',
                        'heroicon-o-eye' => 'viewed',
                        'heroicon-o-arrow-down-tray' => 'exported',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'created' => 'Creado',
                            'updated' => 'Actualizado',
                            'deleted' => 'Eliminado',
                            'viewed' => 'Visualizado',
                            'exported' => 'Exportado',
                            default => ucfirst($state),
                        };
                    }),
                TextColumn::make('model_type')
                    ->label('Modelo')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('model_id')
                    ->label('ID')
                    ->badge()
                    ->color('info'),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description)
                    ->wrap(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->label('Acción')
                    ->options([
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        'viewed' => 'Visualizado',
                        'exported' => 'Exportado',
                    ])
                    ->multiple(),
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('model_type')
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->can('actividad.exportar')),
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
    //         'index' => Pages\ListUserActivities::route('/'),
    //         'view' => Pages\ViewUserActivity::route('/{record}'),
    //     ];
    // }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Gerente']) && auth()->user()->can('actividad.ver');
    }

    public static function canCreate(): bool
    {
        return false; // Las actividades se registran automáticamente
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Las actividades no deben editarse
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole('Super Admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Gerente']) && auth()->user()->can('actividad.ver');
    }
}