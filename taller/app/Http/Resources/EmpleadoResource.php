<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpleadoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo_empleado' => $this->codigo_empleado,
            'numero_documento' => $this->numero_documento,
            'tipo_documento' => $this->tipo_documento,
            
            // Información personal
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'nombre_completo' => $this->nombres . ' ' . $this->apellidos,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'edad' => $this->fecha_nacimiento ? now()->diffInYears($this->fecha_nacimiento) : null,
            'genero' => $this->genero,
            'estado_civil' => $this->estado_civil,
            'nacionalidad' => $this->nacionalidad,
            
            // Información de contacto
            'telefono' => $this->telefono,
            'telefono_emergencia' => $this->telefono_emergencia,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'departamento' => $this->departamento,
            'codigo_postal' => $this->codigo_postal,
            
            // Información laboral
            'cargo' => $this->cargo,
            'departamento_trabajo' => $this->departamento_trabajo,
            'area' => $this->area,
            'jefe_inmediato_id' => $this->jefe_inmediato_id,
            'jefe_inmediato' => $this->whenLoaded('jefeInmediato', function () {
                return [
                    'id' => $this->jefeInmediato->id,
                    'codigo_empleado' => $this->jefeInmediato->codigo_empleado,
                    'nombre_completo' => $this->jefeInmediato->nombres . ' ' . $this->jefeInmediato->apellidos,
                    'cargo' => $this->jefeInmediato->cargo,
                    'email' => $this->jefeInmediato->email
                ];
            }),
            
            // Subordinados
            'subordinados' => $this->whenLoaded('subordinados', function () {
                return $this->subordinados->map(function ($subordinado) {
                    return [
                        'id' => $subordinado->id,
                        'codigo_empleado' => $subordinado->codigo_empleado,
                        'nombre_completo' => $subordinado->nombres . ' ' . $subordinado->apellidos,
                        'cargo' => $subordinado->cargo,
                        'email' => $subordinado->email,
                        'activo' => $subordinado->activo
                    ];
                });
            }),
            
            'subordinados_count' => $this->whenLoaded('subordinados', function () {
                return $this->subordinados->count();
            }),
            
            // Fechas importantes
            'fecha_ingreso' => $this->fecha_ingreso,
            'fecha_fin_contrato' => $this->fecha_fin_contrato,
            'antigüedad_años' => $this->fecha_ingreso ? now()->diffInYears($this->fecha_ingreso) : 0,
            'antigüedad_meses' => $this->fecha_ingreso ? now()->diffInMonths($this->fecha_ingreso) : 0,
            'tiempo_hasta_fin_contrato' => $this->fecha_fin_contrato ? now()->diffInDays($this->fecha_fin_contrato) : null,
            'contrato_por_vencer' => $this->fecha_fin_contrato ? now()->addDays(30) >= $this->fecha_fin_contrato : false,
            
            // Información salarial (solo para roles autorizados)
            $this->mergeWhen($request->user()?->can('viewSalaryInfo', $this->resource), [
                'salario_base' => $this->salario_base,
                'tipo_contrato' => $this->tipo_contrato,
                'moneda' => $this->moneda ?? 'PEN',
                'salario_formateado' => $this->salario_base ? $this->formatCurrency($this->salario_base) : null,
            ]),
            
            // Estado y flags
            'activo' => $this->activo,
            'estado' => $this->activo ? 'Activo' : 'Inactivo',
            'en_periodo_prueba' => $this->en_periodo_prueba,
            'fin_periodo_prueba' => $this->fin_periodo_prueba,
            'dias_restantes_prueba' => $this->fin_periodo_prueba && $this->en_periodo_prueba 
                ? now()->diffInDays($this->fin_periodo_prueba, false) 
                : null,
            
            // Información bancaria (solo para roles autorizados)
            $this->mergeWhen($request->user()?->can('viewBankInfo', $this->resource), [
                'banco' => $this->banco,
                'numero_cuenta' => $this->numero_cuenta,
                'tipo_cuenta' => $this->tipo_cuenta,
            ]),
            
            // Relación con usuario del sistema
            'usuario_id' => $this->usuario_id,
            'usuario' => $this->whenLoaded('usuario', function () {
                return new UsuarioResource($this->usuario);
            }),
            
            'has_system_user' => !is_null($this->usuario_id),
            'user_status' => $this->whenLoaded('usuario', function () {
                if (!$this->usuario) return 'sin_usuario';
                if (!$this->usuario->activo) return 'usuario_inactivo';
                if ($this->usuario->blocked_until && $this->usuario->blocked_until > now()) return 'usuario_bloqueado';
                return 'usuario_activo';
            }),
            
            // Documentos y archivos
            'documentos' => $this->whenLoaded('documentos', function () {
                return $this->documentos->map(function ($documento) {
                    return [
                        'id' => $documento->id,
                        'tipo' => $documento->tipo,
                        'nombre' => $documento->nombre,
                        'archivo_url' => $documento->archivo_url,
                        'fecha_vencimiento' => $documento->fecha_vencimiento,
                        'vencido' => $documento->fecha_vencimiento ? now() > $documento->fecha_vencimiento : false,
                        'por_vencer' => $documento->fecha_vencimiento ? now()->addDays(30) >= $documento->fecha_vencimiento : false,
                        'uploaded_at' => $documento->created_at
                    ];
                });
            }),
            
            // Evaluaciones y desempeño
            'evaluaciones' => $this->whenLoaded('evaluaciones', function () {
                return $this->evaluaciones->map(function ($evaluacion) {
                    return [
                        'id' => $evaluacion->id,
                        'periodo' => $evaluacion->periodo,
                        'puntuacion' => $evaluacion->puntuacion,
                        'estado' => $evaluacion->estado,
                        'fecha_evaluacion' => $evaluacion->fecha_evaluacion,
                        'evaluador' => $evaluacion->evaluador?->nombres . ' ' . $evaluacion->evaluador?->apellidos
                    ];
                });
            }),
            
            'ultima_evaluacion' => $this->whenLoaded('evaluaciones', function () {
                $ultima = $this->evaluaciones->sortByDesc('fecha_evaluacion')->first();
                return $ultima ? [
                    'fecha' => $ultima->fecha_evaluacion,
                    'puntuacion' => $ultima->puntuacion,
                    'estado' => $ultima->estado
                ] : null;
            }),
            
            // Capacitaciones
            'capacitaciones' => $this->whenLoaded('capacitaciones', function () {
                return $this->capacitaciones->map(function ($capacitacion) {
                    return [
                        'id' => $capacitacion->id,
                        'nombre' => $capacitacion->nombre,
                        'tipo' => $capacitacion->tipo,
                        'estado' => $capacitacion->pivot->estado,
                        'fecha_inicio' => $capacitacion->pivot->fecha_inicio,
                        'fecha_fin' => $capacitacion->pivot->fecha_fin,
                        'progreso' => $capacitacion->pivot->progreso,
                        'certificado' => $capacitacion->pivot->certificado_url
                    ];
                });
            }),
            
            // Vacaciones y permisos
            'vacaciones' => $this->whenLoaded('vacaciones', function () {
                return $this->vacaciones->map(function ($vacacion) {
                    return [
                        'id' => $vacacion->id,
                        'tipo' => $vacacion->tipo,
                        'fecha_inicio' => $vacacion->fecha_inicio,
                        'fecha_fin' => $vacacion->fecha_fin,
                        'dias_solicitados' => $vacacion->dias_solicitados,
                        'estado' => $vacacion->estado,
                        'aprobado_por' => $vacacion->aprobadoPor?->nombres . ' ' . $vacacion->aprobadoPor?->apellidos,
                        'motivo' => $vacacion->motivo
                    ];
                });
            }),
            
            'dias_vacaciones_disponibles' => $this->dias_vacaciones_disponibles ?? 0,
            'dias_vacaciones_tomados' => $this->dias_vacaciones_tomados ?? 0,
            'vacaciones_pendientes' => $this->whenLoaded('vacaciones', function () {
                return $this->vacaciones->where('estado', 'pendiente')->count();
            }),
            
            // Información de emergencia
            'contacto_emergencia' => [
                'nombre' => $this->contacto_emergencia_nombre,
                'relacion' => $this->contacto_emergencia_relacion,
                'telefono' => $this->contacto_emergencia_telefono,
                'email' => $this->contacto_emergencia_email
            ],
            
            // Métricas y estadísticas
            'estadisticas' => [
                'asistencia_promedio' => $this->asistencia_promedio ?? 0,
                'puntualidad_promedio' => $this->puntualidad_promedio ?? 0,
                'proyectos_completados' => $this->proyectos_completados ?? 0,
                'evaluacion_promedio' => $this->evaluacion_promedio ?? 0,
                'capacitaciones_completadas' => $this->whenLoaded('capacitaciones', function () {
                    return $this->capacitaciones->where('pivot.estado', 'completado')->count();
                })
            ],
            
            // Alertas y notificaciones
            'alertas' => [
                'contrato_por_vencer' => $this->fecha_fin_contrato ? now()->addDays(30) >= $this->fecha_fin_contrato : false,
                'periodo_prueba_terminando' => $this->fin_periodo_prueba && $this->en_periodo_prueba 
                    ? now()->addDays(7) >= $this->fin_periodo_prueba : false,
                'documentos_por_vencer' => $this->whenLoaded('documentos', function () {
                    return $this->documentos->filter(function ($doc) {
                        return $doc->fecha_vencimiento && now()->addDays(30) >= $doc->fecha_vencimiento;
                    })->count();
                }),
                'sin_evaluacion_anual' => $this->whenLoaded('evaluaciones', function () {
                    $ultimaEvaluacion = $this->evaluaciones->sortByDesc('fecha_evaluacion')->first();
                    return !$ultimaEvaluacion || $ultimaEvaluacion->fecha_evaluacion < now()->subYear();
                })
            ],
            
            // Fechas de auditoría
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at_human' => $this->updated_at->diffForHumans(),
            
            // Información del creador
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'nombre_completo' => $this->createdBy->nombres . ' ' . $this->createdBy->apellidos
                ];
            })
        ];
    }

    /**
     * Formatear moneda
     */
    private function formatCurrency($amount, $currency = 'PEN')
    {
        $symbols = [
            'PEN' => 'S/ ',
            'USD' => '$ ',
            'EUR' => '€ '
        ];
        
        $symbol = $symbols[$currency] ?? '';
        return $symbol . number_format($amount, 2);
    }
}