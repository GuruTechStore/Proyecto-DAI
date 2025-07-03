<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProveedorResource\Pages;
use App\Models\Proveedor;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProveedorResource extends Resource
{
    protected static ?string $model = Proveedor::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Proveedores';

    protected static ?string $modelLabel = 'Proveedor';

    protected static ?string $pluralModelLabel = 'Proveedores';

    protected static ?string $navigationGroup = 'Gestión de Datos';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Proveedor')
                    ->description('Datos básicos del proveedor')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre de la Empresa')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('ruc')
                            ->label('RUC')
                            ->unique(ignoreRecord: true)
                            ->length(11)
                            ->numeric(),
                        TextInput::make('contacto')
                            ->label('Persona de Contacto')
                            ->maxLength(100),
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel()
                            ->placeholder('+51 999 999 999'),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('direccion')
                            ->label('Dirección')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Información Comercial')
                    ->description('Datos comerciales y de facturación')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        TextInput::make('condiciones_pago')
                            ->label('Condiciones de Pago')
                            ->placeholder('30 días, contado, etc.'),
                        TextInput::make('limite_credito')
                            ->label('Límite de Crédito')
                            ->numeric()
                            ->prefix('S/')
                            ->placeholder('0.00'),
                        Forms\Components\Toggle::make('activo')
                            ->label('Proveedor Activo')
                            ->default(true),
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->placeholder('Notas adicionales sobre el proveedor')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
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
                TextColumn::make('ruc')
                    ->label('RUC')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('contacto')
                    ->label('Contacto')
                    ->searchable(),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('productos_count')
                    ->counts('productos')
                    ->label('Productos')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\BadgeColumn::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueLabel('Solo Activos')
                    ->falseLabel('Solo Inactivos'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('proveedores.eliminar')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('proveedores.eliminar')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
    //         'index' => Pages\ListProveedores::route('/'),
    //         'create' => Pages\CreateProveedor::route('/create'),
    //         'view' => Pages\ViewProveedor::route('/{record}'),
    //         'edit' => Pages\EditProveedor::route('/{record}/edit'),
    //     ];
    // }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('proveedores.ver');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('proveedores.crear');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('proveedores.editar');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('proveedores.eliminar');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('proveedores.ver');
    }
}