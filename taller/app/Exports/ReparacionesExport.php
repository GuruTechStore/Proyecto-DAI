<?php

namespace App\Exports;

use App\Models\Reparacion;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class ReparacionesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $tecnicoId;
    protected $estado;
    
    public function __construct($fechaInicio, $fechaFin, $tecnicoId = null, $estado = null)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->tecnicoId = $tecnicoId;
        $this->estado = $estado;
    }
    
    public function query()
    {
        $query = Reparacion::with(['cliente', 'empleado', 'equipo'])
            ->whereBetween('created_at', [$this->fechaInicio, $this->fechaFin]);
            
        if ($this->tecnicoId) {
            $query->where('empleado_id', $this->tecnicoId);
        }
        
        if ($this->estado) {
            $query->where('estado', $this->estado);
        }
        
        return $query->orderBy('created_at', 'desc');
    }
    
    public function headings(): array
    {
        return [
            'Código Ticket',
            'Fecha Ingreso',
            'Cliente',
            'Teléfono',
            'Equipo',
            'Problema',
            'Técnico',
            'Estado',
            'Costo Estimado',
            'Costo Final',
            'Fecha Entrega',
            'Días Transcurridos',
            'Diagnóstico',
            'Solución'
        ];
    }
    
    public function map($reparacion): array
    {
        $diasTranscurridos = $this->calcularDiasTranscurridos($reparacion);
        $equipoDescripcion = $this->getEquipoDescripcion($reparacion);
        
        return [
            $reparacion->codigo_ticket,
            $reparacion->fecha_ingreso ? Carbon::parse($reparacion->fecha_ingreso)->format('d/m/Y H:i') : '',
            $reparacion->cliente->nombre . ' ' . $reparacion->cliente->apellido,
            $reparacion->cliente->telefono ?? 'No registrado',
            $equipoDescripcion,
            $this->limitarTexto($reparacion->problema_reportado, 100),
            $reparacion->empleado->nombres . ' ' . $reparacion->empleado->apellidos,
            $this->formatearEstado($reparacion->estado),
            $reparacion->costo_estimado ? 'S/. ' . number_format($reparacion->costo_estimado, 2) : 'No estimado',
            $reparacion->costo_final ? 'S/. ' . number_format($reparacion->costo_final, 2) : 'Pendiente',
            $reparacion->fecha_entrega ? Carbon::parse($reparacion->fecha_entrega)->format('d/m/Y H:i') : 'Pendiente',
            $diasTranscurridos,
            $this->limitarTexto($reparacion->diagnostico ?? 'Pendiente', 150),
            $this->limitarTexto($reparacion->solucion ?? 'Pendiente', 150)
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
                    'startColor' => ['rgb' => 'F59E0B'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Estilo para todas las celdas
            'A:N' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // Columnas de costos alineadas a la derecha
            'I:J' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            
            // Columna de días centrada
            'L' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];

        // Aplicar colores condicionales según el estado
        $query = $this->query();
        $reparaciones = $query->get();
        $row = 2;
        
        foreach ($reparaciones as $reparacion) {
            $colorEstado = $this->getColorEstado($reparacion->estado);
            
            if ($colorEstado) {
                $styles["H{$row}"] = [
                    'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $colorEstado],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
            }
            
            // Resaltar reparaciones vencidas (más de 7 días en estado pendiente)
            $diasTranscurridos = $this->calcularDiasTranscurridos($reparacion);
            if ($diasTranscurridos > 7 && in_array($reparacion->estado, ['recibido', 'diagnosticando', 'reparando'])) {
                $styles["L{$row}"] = [
                    'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DC2626'],
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
        $titulo = 'Reporte de Reparaciones';
        
        if ($this->estado) {
            $titulo .= ' - ' . ucfirst($this->estado);
        }
        
        return $titulo;
    }
    
    private function calcularDiasTranscurridos($reparacion): int
    {
        $fechaInicio = Carbon::parse($reparacion->fecha_ingreso);
        $fechaFin = $reparacion->fecha_entrega ? Carbon::parse($reparacion->fecha_entrega) : now();
        
        return $fechaInicio->diffInDays($fechaFin);
    }
    
    private function getEquipoDescripcion($reparacion): string
    {
        if ($reparacion->equipo) {
            return $reparacion->equipo->tipo . ' ' . $reparacion->equipo->marca . ' ' . $reparacion->equipo->modelo;
        }
        
        // Fallback para reparaciones sin equipo asociado
        return ($reparacion->tipo_equipo ?? 'Equipo') . ' ' . 
               ($reparacion->marca ?? '') . ' ' . 
               ($reparacion->modelo ?? '');
    }
    
    private function formatearEstado(string $estado): string
    {
        $estados = [
            'recibido' => 'Recibido',
            'diagnosticando' => 'Diagnosticando',
            'reparando' => 'Reparando',
            'completada' => 'Completado',
            'entregada' => 'Entregado',
            'cancelada' => 'Cancelado'
        ];
        
        return $estados[$estado] ?? ucfirst($estado);
    }
    
    private function getColorEstado(string $estado): ?string
    {
        $colores = [
            'recibido' => '6B7280',      // Gris
            'diagnosticando' => '3B82F6', // Azul
            'reparando' => 'F59E0B',      // Amarillo
            'completada' => '10B981',     // Verde
            'entregada' => '059669',      // Verde oscuro
            'cancelada' => 'DC2626'       // Rojo
        ];
        
        return $colores[$estado] ?? null;
    }
    
    private function limitarTexto(string $texto, int $limite): string
    {
        return strlen($texto) > $limite ? substr($texto, 0, $limite) . '...' : $texto;
    }
}