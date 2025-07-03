<?php

namespace App\Livewire\Dashboard;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Reparacion;
use App\Models\Venta;
use Livewire\Component;
use Carbon\Carbon;

class ModuleStats extends Component
{
    public string $periodo = '30'; // días
    public array $estadisticas = [];

    public function mount()
    {
        $this->actualizarEstadisticas();
    }

    public function updatedPeriodo()
    {
        $this->actualizarEstadisticas();
    }

    public function actualizarEstadisticas()
    {
        $fechaInicio = Carbon::now()->subDays((int)$this->periodo);
        $fechaFin = Carbon::now();

        $this->estadisticas = [
            'clientes' => $this->getEstadisticasClientes($fechaInicio, $fechaFin),
            'productos' => $this->getEstadisticasProductos(),
            'reparaciones' => $this->getEstadisticasReparaciones($fechaInicio, $fechaFin),
            'ventas' => $this->getEstadisticasVentas($fechaInicio, $fechaFin),
        ];
    }

    private function getEstadisticasClientes($fechaInicio, $fechaFin)
    {
        if (!auth()->user()->can('clientes.ver')) {
            return null;
        }

        $total = Cliente::where('activo', true)->count();
        $nuevos = Cliente::whereBetween('created_at', [$fechaInicio, $fechaFin])->count();
        $activos = Cliente::where(function($query) use ($fechaInicio, $fechaFin) {
            $query->whereHas('reparaciones', function($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin]);
            });
        })->orWhereHas('ventas', function($query) use ($fechaInicio, $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        })->distinct()->count();

        return [
            'total' => $total,
            'nuevos' => $nuevos,
            'activos' => $activos,
            'crecimiento' => $this->calcularCrecimiento(
                Cliente::where('created_at', '<', $fechaInicio)->count(),
                $total
            )
        ];
    }

    private function getEstadisticasProductos()
    {
        if (!auth()->user()->can('productos.ver')) {
            return null;
        }

        $total = Producto::where('activo', true)->count();
        $sinStock = Producto::where('activo', true)->where('stock_actual', 0)->count();
        $bajoStock = Producto::where('activo', true)
            ->whereRaw('stock_actual <= stock_minimo AND stock_actual > 0')
            ->count();

        return [
            'total' => $total,
            'sin_stock' => $sinStock,
            'bajo_stock' => $bajoStock,
            'valor_inventario' => Producto::where('activo', true)
                ->selectRaw('SUM(stock_actual * precio_venta) as total')
                ->first()->total ?? 0
        ];
    }

    private function getEstadisticasReparaciones($fechaInicio, $fechaFin)
    {
        if (!auth()->user()->can('reparaciones.ver')) {
            return null;
        }

        $total = Reparacion::whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin])->count();
        $pendientes = Reparacion::whereNotIn('estado', ['entregado', 'cancelado'])->count();
        $completadas = Reparacion::whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin])
            ->where('estado', 'entregado')->count();
        
        $tiempoPromedio = Reparacion::whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin])
            ->where('estado', 'entregado')
            ->whereNotNull('fecha_entrega')
            ->selectRaw('AVG(DATEDIFF(fecha_entrega, fecha_ingreso)) as promedio')
            ->first()->promedio ?? 0;

        return [
            'total' => $total,
            'pendientes' => $pendientes,
            'completadas' => $completadas,
            'tiempo_promedio' => round($tiempoPromedio, 1),
            'tasa_completado' => $total > 0 ? round(($completadas / $total) * 100, 1) : 0
        ];
    }

    private function getEstadisticasVentas($fechaInicio, $fechaFin)
    {
        if (!auth()->user()->can('ventas.ver')) {
            return null;
        }

        $ventas = Venta::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado', 'completada');
        
        $total = $ventas->sum('total');
        $cantidad = $ventas->count();
        $promedio = $cantidad > 0 ? $total / $cantidad : 0;
        
        $ventasHoy = Venta::whereDate('fecha', today())
            ->where('estado', 'completada')
            ->sum('total');

        return [
            'total' => $total,
            'cantidad' => $cantidad,
            'promedio' => $promedio,
            'hoy' => $ventasHoy,
            'crecimiento' => $this->calcularCrecimiento(
                Venta::where('fecha', '<', $fechaInicio)
                    ->where('estado', 'completada')
                    ->sum('total'),
                $total
            )
        ];
    }

    private function calcularCrecimiento($anterior, $actual)
    {
        if ($anterior == 0) {
            return $actual > 0 ? 100 : 0;
        }
        return round((($actual - $anterior) / $anterior) * 100, 1);
    }

    public function render()
    {
        return view('livewire.dashboard.module-stats');
    }
}
