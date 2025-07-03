<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ProductosExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $categoriaId;
    protected $estadoStock;
    
    public function __construct($categoriaId = null, $estadoStock = null)
    {
        $this->categoriaId = $categoriaId;
        $this->estadoStock = $estadoStock;
    }
    
    public function query()
    {
        $query = Producto::with(['categoria']);
        
        if ($this->categoriaId) {
            $query->where('categoria_id', $this->categoriaId);
        }
        
        if ($this->estadoStock) {
            switch ($this->estadoStock) {
                case 'bajo':
                    $query->whereRaw('stock_actual <= stock_minimo');
                    break;
                case 'agotado':
                    $query->where('stock_actual', 0);
                    break;
                case 'disponible':
                    $query->where('stock_actual', '>', 0);
                    break;
            }
        }
        
        return $query->orderBy('nombre');
    }
    
    public function headings(): array
    {
        return [
            'Código',
            'Nombre',
            'Categoría',
            'Stock Actual',
            'Stock Mínimo',
            'Estado Stock',
            'Precio Compra',
            'Precio Venta',
            'Margen (%)',
            'Valor Stock',
            'Estado',
            'Fecha Creación'
        ];
    }
    
    public function map($producto): array
    {
        $estadoStock = $this->determinarEstadoStock($producto);
        $margen = $this->calcularMargen($producto);
        $valorStock = $producto->stock_actual * $producto->precio_compra;
        
        return [
            $producto->codigo,
            $producto->nombre,
            $producto->categoria ? $producto->categoria->nombre : 'Sin categoría',
            $producto->stock_actual,
            $producto->stock_minimo,
            $estadoStock,
            number_format($producto->precio_compra, 2),
            number_format($producto->precio_venta, 2),
            number_format($margen, 2),
            number_format($valorStock, 2),
            $producto->activo ? 'Activo' : 'Inactivo',
            $producto->created_at->format('d/m/Y')
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        $styles = [
            // Estilo para el encabezado
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Estilo para todas las celdas
            'A:L' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Columnas numéricas alineadas a la derecha
            'D:E' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            
            // Columnas de precios
            'G:J' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '"S/. "#,##0.00',
                ],
            ],
            
            // Columna de margen
            'I' => [
                'numberFormat' => [
                    'formatCode' => '0.00"%"',
                ],
            ],
        ];

        // Aplicar colores condicionales para el estado del stock
        $query = $this->query();
        $productos = $query->get();
        $row = 2;
        
        foreach ($productos as $producto) {
            $estadoStock = $this->determinarEstadoStock($producto);
            
            if ($estadoStock === 'AGOTADO') {
                $styles["F{$row}"] = [
                    'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DC2626'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
            } elseif ($estadoStock === 'BAJO STOCK') {
                $styles["F{$row}"] = [
                    'font' => ['color' => ['rgb' => '000000'], 'bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCD34D'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
            } else {
                $styles["F{$row}"] = [
                    'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '10B981'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
            }
            
            $row++;
        }
        
        return $styles;
    }
    
    public function title(): string
    {
        $titulo = 'Inventario de Productos';
        
        if ($this->estadoStock) {
            $estados = [
                'bajo' => 'Bajo Stock',
                'agotado' => 'Agotados',
                'disponible' => 'Disponibles'
            ];
            $titulo .= ' - ' . ($estados[$this->estadoStock] ?? 'Filtrado');
        }
        
        return $titulo;
    }
    
    private function determinarEstadoStock($producto): string
    {
        if ($producto->stock_actual == 0) {
            return 'AGOTADO';
        } elseif ($producto->stock_actual <= $producto->stock_minimo) {
            return 'BAJO STOCK';
        } else {
            return 'DISPONIBLE';
        }
    }
    
    private function calcularMargen($producto): float
    {
        if ($producto->precio_compra <= 0) {
            return 0;
        }
        
        return (($producto->precio_venta - $producto->precio_compra) / $producto->precio_compra) * 100;
    }
}