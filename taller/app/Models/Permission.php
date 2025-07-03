<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
        'descripcion',
        'categoria',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope para filtrar por categoría
     */
    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Scope para buscar permisos
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('name', 'LIKE', "%{$termino}%")
              ->orWhere('descripcion', 'LIKE', "%{$termino}%");
        });
    }

    /**
     * Obtener todas las categorías disponibles
     */
    public static function getCategorias()
    {
        return self::distinct('categoria')
                   ->whereNotNull('categoria')
                   ->pluck('categoria')
                   ->sort()
                   ->values();
    }

    /**
     * Obtener permisos agrupados por categoría
     */
    public static function agrupadosPorCategoria()
    {
        return self::orderBy('categoria')
                   ->orderBy('name')
                   ->get()
                   ->groupBy('categoria');
    }

    /**
     * Verificar si un permiso es crítico (para operaciones sensibles)
     */
    public function esCritico()
    {
        $permisosCriticos = [
            'usuarios.eliminar',
            'empleados.eliminar',
            'configuracion.sistema',
            'configuracion.respaldos',
            'auditoria.eliminar',
        ];

        return in_array($this->name, $permisosCriticos);
    }

    /**
     * Obtener módulo del permiso (parte antes del punto)
     */
    public function getModuloAttribute()
    {
        return explode('.', $this->name)[0] ?? 'general';
    }

    /**
     * Obtener acción del permiso (parte después del punto)
     */
    public function getAccionAttribute()
    {
        $partes = explode('.', $this->name);
        return $partes[1] ?? $this->name;
    }

    /**
     * Verificar si el permiso pertenece a un módulo específico
     */
    public function perteneceAlModulo($modulo)
    {
        return $this->modulo === $modulo;
    }

    /**
     * Obtener estadísticas de uso del permiso
     */
    public function getEstadisticasUso()
    {
        // Contar roles que tienen este permiso
        $rolesCount = $this->roles()->count();
        
        // Contar usuarios que tienen este permiso directamente
        $usuariosDirectos = $this->users()->count();
        
        // Contar usuarios que tienen este permiso a través de roles
        $usuariosIndirectos = \App\Models\Usuario::role($this->roles()->pluck('name')->toArray())->count();

        return [
            'roles_con_permiso' => $rolesCount,
            'usuarios_directos' => $usuariosDirectos,
            'usuarios_indirectos' => $usuariosIndirectos,
            'total_usuarios' => $usuariosDirectos + $usuariosIndirectos,
        ];
    }

    /**
     * Obtener descripción amigable del permiso
     */
    public function getDescripcionAmigableAttribute()
    {
        if ($this->descripcion) {
            return $this->descripcion;
        }

        // Generar descripción basada en el nombre
        $descripciones = [
            'ver' => 'Ver y consultar',
            'crear' => 'Crear nuevos registros',
            'editar' => 'Modificar registros existentes',
            'eliminar' => 'Eliminar registros',
            'exportar' => 'Exportar datos',
            'importar' => 'Importar datos',
            'gestionar' => 'Gestión completa',
            'reportes' => 'Generar reportes',
        ];

        $accion = $this->accion;
        $modulo = ucfirst($this->modulo);
        $descripcionAccion = $descripciones[$accion] ?? $accion;

        return "{$descripcionAccion} {$modulo}";
    }

    /**
     * Verificar si puede ser eliminado (no está asignado a ningún rol o usuario)
     */
    public function puedeEliminarse()
    {
        return $this->roles()->count() === 0 && $this->users()->count() === 0;
    }

    /**
     * Obtener permisos relacionados (mismo módulo)
     */
    public function getPermisosRelacionados()
    {
        return self::where('id', '!=', $this->id)
                   ->where('name', 'LIKE', $this->modulo . '.%')
                   ->orderBy('name')
                   ->get();
    }
}