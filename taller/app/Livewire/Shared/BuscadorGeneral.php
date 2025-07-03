<?php

namespace App\Livewire\Shared;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Reparacion;
use App\Models\Venta;
use Livewire\Component;

class BuscadorGeneral extends Component
{
    public string $search = '';
    public array $resultados = [];
    public bool $mostrarResultados = false;
    public int $maxResultados = 15;

    public function updatedSearch()
    {
        if (strlen($this->search) >= 3) {
            $this->buscar();
            $this->mostrarResultados = count($this->resultados) > 0;
        } else {
            $this->resultados = [];
            $this->mostrarResultados = false;
        }
    }

    public function buscar()
    {
        $this->resultados = [];
        $resultadosCount = 0;

        // Buscar clientes
        if (auth()->user()->can('clientes.ver') && $resultadosCount < $this->maxResultados) {
            $clientes = Cliente::where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido', 'like', '%' . $this->search . '%')
                      ->orWhere('telefono', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('dni', 'like', '%' . $this->search . '%');
            })
            ->where('activo', true)
            ->limit($this->maxResultados - $resultadosCount)
            ->get()
            ->map(function($cliente) {
                return [
                    'tipo' => 'cliente',
                    'titulo' => $cliente->nombre . ' ' . $cliente->apellido,
                    'subtitulo' => $cliente->telefono,
                    'descripcion' => $cliente->email ?? $cliente->dni ?? 'Sin email',
                    'url' => route('clientes.show', $cliente),
                    'icon' => 'user',
                    'badge' => 'Cliente',
                    'badge_color' => 'bg-blue-100 text-blue-800'
                ];
            });

            $this->resultados = array_merge($this->resultados, $clientes->toArray());
            $resultadosCount += $clientes->count();
        }

        // Buscar productos
        if (auth()->user()->can('productos.ver') && $resultadosCount < $this->maxResultados) {
            $productos = Producto::where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('codigo', 'like', '%' . $this->search . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            })
            ->where('activo', true)
            ->limit($this->maxResultados - $resultadosCount)
            ->get()
            ->map(function($producto) {
                return [
                    'tipo' => 'producto',
                    'titulo' => $producto->nombre,
                    'subtitulo' => $producto->codigo,
                    'descripcion' => "Stock: {$producto->stock_actual} | S/ {$producto->precio_venta}",
                    'url' => route('productos.show', $producto),
                    'icon' => 'package',
                    'badge' => 'Producto',
                    'badge_color' => 'bg-green-100 text-green-800'
                ];
            });

            $this->resultados = array_merge($this->resultados, $productos->toArray());
            $resultadosCount += $productos->count();
        }

        // Buscar reparaciones
        if (auth()->user()->can('reparaciones.ver') && $resultadosCount < $this->maxResultados) {
            $reparaciones = Reparacion::where(function($query) {
                $query->where('codigo_ticket', 'like', '%' . $this->search . '%')
                      ->orWhere('tipo_equipo', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%')
                      ->orWhere('modelo', 'like', '%' . $this->search . '%')
                      ->orWhereHas('cliente', function($cliente) {
                          $cliente->where('nombre', 'like', '%' . $this->search . '%')
                                  ->orWhere('telefono', 'like', '%' . $this->search . '%');
                      });
            })
            ->with('cliente')
            ->limit($this->maxResultados - $resultadosCount)
            ->get()
            ->map(function($reparacion) {
                return [
                    'tipo' => 'reparacion',
                    'titulo' => $reparacion->codigo_ticket,
                    'subtitulo' => $reparacion->tipo_equipo . ' - ' . $reparacion->marca,
                    'descripcion' => $reparacion->cliente->nombre . ' | ' . ucfirst($reparacion->estado),
                    'url' => route('reparaciones.show', $reparacion),
                    'icon' => 'wrench',
                    'badge' => 'Reparación',
                    'badge_color' => 'bg-yellow-100 text-yellow-800'
                ];
            });

            $this->resultados = array_merge($this->resultados, $reparaciones->toArray());
            $resultadosCount += $reparaciones->count();
        }

        // Buscar ventas
        if (auth()->user()->can('ventas.ver') && $resultadosCount < $this->maxResultados) {
            $ventas = Venta::where(function($query) {
                $query->where('codigo_venta', 'like', '%' . $this->search . '%')
                      ->orWhere('numero_boleta', 'like', '%' . $this->search . '%')
                      ->orWhereHas('cliente', function($cliente) {
                          $cliente->where('nombre', 'like', '%' . $this->search . '%')
                                  ->orWhere('telefono', 'like', '%' . $this->search . '%');
                      });
            })
            ->with('cliente')
            ->limit($this->maxResultados - $resultadosCount)
            ->get()
            ->map(function($venta) {
                return [
                    'tipo' => 'venta',
                    'titulo' => $venta->codigo_venta,
                    'subtitulo' => $venta->cliente ? $venta->cliente->nombre : 'Cliente Ocasional',
                    'descripcion' => 'S/ ' . number_format($venta->total, 2) . ' | ' . $venta->fecha->format('d/m/Y'),
                    'url' => route('ventas.show', $venta),
                    'icon' => 'shopping-cart',
                    'badge' => 'Venta',
                    'badge_color' => 'bg-indigo-100 text-indigo-800'
                ];
            });

            $this->resultados = array_merge($this->resultados, $ventas->toArray());
        }
    }

    public function limpiarBusqueda()
    {
        $this->search = '';
        $this->resultados = [];
        $this->mostrarResultados = false;
    }

    public function render()
    {
        return view('livewire.shared.buscador-general');
    }
}

