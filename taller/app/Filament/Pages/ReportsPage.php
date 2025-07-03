<?php

namespace App\Filament\Pages;

use App\Models\Venta;
use App\Models\Reparacion;
use App\Models\Producto;
use App\Models\Cliente;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Carbon;

class ReportsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string $view = 'filament.pages.reports';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $title = 'Centro de Reportes';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'fecha_inicio' => now()->startOfMonth(),
            'fecha_fin' => now()->endOfMonth(),
            'incluir_cancelados' => false,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filtros de Reporte')
                    ->description('Configura los parámetros para generar reportes')
                    ->schema([
                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->default(now()->startOfMonth())
                            ->native(false),
                        DatePicker::make('fecha_fin')
                            ->label('Fecha de Fin')
                            ->required()
                            ->default(now()->endOfMonth())
                            ->native(false),
                        Select::make('tipo_reporte')
                            ->label('Tipo de Reporte')
                            ->options([
                                'ventas' => 'Reporte de Ventas',
                                'reparaciones' => 'Reporte de Reparaciones',
                                'inventario' => 'Reporte de Inventario',
                                'clientes' => 'Reporte de Clientes',
                                'financiero' => 'Reporte Financiero',
                            ])
                            ->default('ventas')
                            ->native(false),
                        Toggle::make('incluir_cancelados')
                            ->label('Incluir Cancelados')
                            ->default(false),
                        Toggle::make('agrupar_por_mes')
                            ->label('Agrupar por Mes')
                            ->default(false),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar_reporte')
                ->label('Generar Reporte')
                ->icon('heroicon-o-document-chart-bar')
                ->color('primary')
                ->action('generarReporte'),
            Action::make('exportar_excel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportarExcel'),
            Action::make('exportar_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document')
                ->color('danger')
                ->action('exportarPdf'),
        ];
    }

    public function generarReporte(): void
    {
        $data = $this->form->getState();
        
        // Aquí implementarías la lógica para generar el reporte
        $this->dispatch('mostrar-reporte', $data);
        
        \Filament\Notifications\Notification::make()
            ->title('Reporte generado')
            ->success()
            ->send();
    }

    public function exportarExcel(): void
    {
        $data = $this->form->getState();
        
        // Implementar lógica de exportación a Excel
        \Filament\Notifications\Notification::make()
            ->title('Exportando a Excel...')
            ->body('El archivo se descargará automáticamente')
            ->info()
            ->send();
    }

    public function exportarPdf(): void
    {
        $data = $this->form->getState();
        
        // Implementar lógica de exportación a PDF
        \Filament\Notifications\Notification::make()
            ->title('Exportando a PDF...')
            ->body('El archivo se descargará automáticamente')
            ->info()
            ->send();
    }

    public function getEstadisticasVentas(): array
    {
        $fechaInicio = $this->data['fecha_inicio'] ?? now()->startOfMonth();
        $fechaFin = $this->data['fecha_fin'] ?? now()->endOfMonth();
        
        $ventas = Venta::whereBetween('created_at', [$fechaInicio, $fechaFin]);
        
        if (!($this->data['incluir_cancelados'] ?? false)) {
            $ventas->where('estado', '!=', 'cancelado');
        }
        
        return [
            'total_ventas' => $ventas->sum('total'),
            'cantidad_ventas' => $ventas->count(),
            'promedio_venta' => $ventas->avg('total'),
            'venta_mayor' => $ventas->max('total'),
        ];
    }

    public function getEstadisticasReparaciones(): array
    {
        $fechaInicio = $this->data['fecha_inicio'] ?? now()->startOfMonth();
        $fechaFin = $this->data['fecha_fin'] ?? now()->endOfMonth();
        
        $reparaciones = Reparacion::whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin]);
        
        return [
            'total_reparaciones' => $reparaciones->count(),
            'completadas' => $reparaciones->where('estado', 'completado')->count(),
            'pendientes' => $reparaciones->whereIn('estado', ['recibido', 'diagnosticando', 'reparando'])->count(),
            'canceladas' => $reparaciones->where('estado', 'cancelado')->count(),
            'ingresos_reparaciones' => $reparaciones->where('estado', 'completado')->sum('costo_final'),
        ];
    }

    public function getEstadisticasInventario(): array
    {
        return [
            'total_productos' => Producto::count(),
            'productos_activos' => Producto::where('activo', true)->count(),
            'stock_bajo' => Producto::whereRaw('stock <= stock_minimo')->count(),
            'sin_stock' => Producto::where('stock', 0)->count(),
            'valor_inventario' => Producto::selectRaw('SUM(stock * precio_compra)')->value('SUM(stock * precio_compra)') ?? 0,
        ];
    }

    public function getEstadisticasClientes(): array
    {
        $fechaInicio = $this->data['fecha_inicio'] ?? now()->startOfMonth();
        $fechaFin = $this->data['fecha_fin'] ?? now()->endOfMonth();
        
        return [
            'total_clientes' => Cliente::count(),
            'nuevos_clientes' => Cliente::whereBetween('created_at', [$fechaInicio, $fechaFin])->count(),
            'clientes_activos' => Cliente::whereHas('reparaciones', function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin]);
            })->count(),
            'clientes_con_equipos' => Cliente::has('equipos')->count(),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('reportes.ver');
    }

    public function getHeading(): string
    {
        return 'Centro de Reportes';
    }

    public function getSubheading(): string
    {
        return 'Genera reportes detallados de ventas, reparaciones, inventario y más';
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}