<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpleadoResource\Pages;
use App\Models\Empleado;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmpleadoResource extends Resource
{
    protected static ?string $model = Empleado::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Empleados';

    protected static ?string $modelLabel = 'Empleado';

    protected static ?string $pluralModelLabel = 'Empleados';

    protected static ?string $navigationGroup = 'Gestión de Datos';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Personal')
                    ->description('Datos básicos del empleado')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('nombres')
                            ->label('Nombres')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('apellidos')
                            ->label('Apellidos')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('dni')
                            ->label('DNI')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->length(8)
                            ->numeric(),
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

                Section::make('Información Laboral')
                    ->description('Datos del cargo y área de trabajo')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        TextInput::make('cargo')
                            ->label('Cargo')
                            ->required()
                            ->placeholder('Técnico, Supervisor, etc.'),
                        TextInput::make('area')
                            ->label('Área')
                            ->placeholder('Reparaciones, Ventas, etc.'),
                        Forms\Components\DatePicker::make('fecha_ingreso')
                            ->label('Fecha de Ingreso')
                            ->required()
                            ->default(today())
                            ->native(false),
                        TextInput::make('salario')
                            ->label('Salario')
                            ->numeric()
                            ->prefix('S/')
                            ->placeholder('0.00'),
                        Forms\Components\Toggle::make('activo')
                            ->label('Empleado Activo')
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombres')
                    ->label('Nombres')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('apellidos')
                    ->label('Apellidos')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dni')
                    ->label('DNI')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('cargo')
                    ->label('Cargo')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('area')
                    ->label('Área')
                    ->badge()
                    ->color('success'),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('fecha_ingreso')
                    ->label('Fecha Ingreso')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('cargo')
                    ->options([
                        'tecnico' => 'Técnico',
                        'supervisor' => 'Supervisor',
                        'vendedor' => 'Vendedor',
                        'gerente' => 'Gerente',
                    ]),
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
                    ->visible(fn () => auth()->user()->can('empleados.eliminar')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->can('empleados.eliminar')),
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
    //         'index' => Pages\ListEmpleados::route('/'),
    //         'create' => Pages\CreateEmpleado::route('/create'),
    //         'view' => Pages\ViewEmpleado::route('/{record}'),
    //         'edit' => Pages\EditEmpleado::route('/{record}/edit'),
    //     ];
    // }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('empleados.ver');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('empleados.crear');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('empleados.editar');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('empleados.eliminar');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('empleados.ver');
    }
}