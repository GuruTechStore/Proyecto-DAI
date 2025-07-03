<?php

namespace App\Traits;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            static::auditEvent($model, 'created', null, $model->getAuditableAttributes());
        });

        static::updated(function ($model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getDirty();
            
            if (!empty($newValues)) {
                static::auditEvent($model, 'updated', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            static::auditEvent($model, 'deleted', $model->getAuditableAttributes(), null);
        });
    }

    protected static function auditEvent($model, string $operation, $oldValues, $newValues)
    {
        try {
            if (!config('app.audit_enabled', true)) {
                return;
            }

            Auditoria::create([
                'usuario_id' => Auth::id(),
                'operacion' => strtoupper($operation),
                'tabla' => $model->getTable(),
                'registro_id' => $model->getKey(),
                'datos_anteriores' => $oldValues ? $model->serializeAuditData($oldValues) : null,
                'datos_nuevos' => $newValues ? $model->serializeAuditData($newValues) : null,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Audit trait error: ' . $e->getMessage());
        }
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'registro_id')
                    ->where('tabla', $this->getTable())
                    ->orderBy('created_at', 'desc');
    }

    public function getAuditTrail(int $limit = 50)
    {
        return $this->auditorias()
                    ->with('usuario:id,nombre,email')
                    ->limit($limit)
                    ->get()
                    ->map(function ($audit) {
                        return [
                            'id' => $audit->id,
                            'operacion' => $audit->operacion,
                            'usuario' => $audit->usuario ? $audit->usuario->nombre : 'Sistema',
                            'fecha' => $audit->created_at->format('d/m/Y H:i:s'),
                            'ip' => $audit->ip,
                            'cambios' => $this->formatAuditChanges($audit)
                        ];
                    });
    }

    protected function formatAuditChanges($audit): array
    {
        $changes = [];

        if ($audit->datos_anteriores && $audit->datos_nuevos) {
            $old = is_string($audit->datos_anteriores) ? 
                   json_decode($audit->datos_anteriores, true) : $audit->datos_anteriores;
            $new = is_string($audit->datos_nuevos) ? 
                   json_decode($audit->datos_nuevos, true) : $audit->datos_nuevos;

            foreach ($new as $field => $newValue) {
                $oldValue = $old[$field] ?? null;
                if ($oldValue != $newValue) {
                    $changes[$field] = [
                        'anterior' => $oldValue,
                        'nuevo' => $newValue
                    ];
                }
            }
        }

        return $changes;
    }

    public function getAuditableAttributes(): array
    {
        $attributes = $this->getAttributes();
        
        // Excluir campos sensibles
        $excluded = ['password', 'remember_token', 'email_verified_at'];
        
        return array_diff_key($attributes, array_flip($excluded));
    }

    public function serializeAuditData($data): array
    {
        if (is_array($data)) {
            return array_map(function ($value) {
                if ($value instanceof \Carbon\Carbon) {
                    return $value->toISOString();
                }
                return $value;
            }, $data);
        }

        return $data;
    }

    // Método para obtener historial de cambios específico
    public function getFieldHistory(string $field, int $limit = 10): array
    {
        return $this->auditorias()
                    ->whereRaw("JSON_EXTRACT(datos_nuevos, '$.{$field}') IS NOT NULL")
                    ->limit($limit)
                    ->get()
                    ->map(function ($audit) use ($field) {
                        $data = is_string($audit->datos_nuevos) ? 
                               json_decode($audit->datos_nuevos, true) : $audit->datos_nuevos;
                        
                        return [
                            'valor' => $data[$field] ?? null,
                            'fecha' => $audit->created_at->format('d/m/Y H:i:s'),
                            'usuario' => $audit->usuario ? $audit->usuario->nombre : 'Sistema'
                        ];
                    })
                    ->toArray();
    }
}