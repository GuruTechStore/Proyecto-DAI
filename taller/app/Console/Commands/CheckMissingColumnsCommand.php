<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckMissingColumnsCommand extends Command
{
    protected $signature = 'db:check-columns';
    protected $description = 'Verificar qué columnas faltan en las tablas';

    public function handle()
    {
        $this->info('🔍 Verificando columnas en las tablas...');
        $this->newLine();

        $tables = [
            'proveedores' => [
                'required' => ['id', 'nombre', 'activo', 'created_at', 'updated_at'],
                'optional' => ['ruc', 'contacto', 'telefono', 'email', 'direccion', 'banco', 'numero_cuenta', 'tipo_cuenta', 'observaciones', 'deleted_at']
            ],
            'productos' => [
                'required' => ['id', 'nombre', 'precio', 'stock', 'activo', 'created_at', 'updated_at'],
                'optional' => ['codigo', 'descripcion', 'categoria_id', 'proveedor_id', 'precio_compra', 'stock_minimo', 'unidad_medida', 'ubicacion', 'imagen', 'deleted_at']
            ],
            'empleados' => [
                'required' => ['id', 'nombres', 'apellidos', 'activo', 'created_at', 'updated_at'],
                'optional' => ['dni', 'telefono', 'email', 'especialidad', 'fecha_contratacion', 'salario', 'direccion', 'deleted_at']
            ],
            'categorias' => [
                'required' => ['id', 'nombre', 'activo', 'created_at', 'updated_at'],
                'optional' => ['descripcion', 'color', 'icono', 'deleted_at']
            ]
        ];

        foreach ($tables as $tableName => $columns) {
            $this->checkTable($tableName, $columns);
        }

        $this->newLine();
        $this->info('✅ Verificación completada!');
    }

    private function checkTable($tableName, $columns)
    {
        if (!Schema::hasTable($tableName)) {
            $this->error("❌ Tabla '{$tableName}' NO EXISTE");
            return;
        }

        $this->info("📋 Tabla: {$tableName}");
        
        $existingColumns = Schema::getColumnListing($tableName);
        $allColumns = array_merge($columns['required'], $columns['optional']);
        
        $missing = [];
        $existing = [];

        foreach ($allColumns as $column) {
            if (in_array($column, $existingColumns)) {
                $existing[] = $column;
            } else {
                $missing[] = $column;
            }
        }

        // Mostrar columnas existentes
        if (!empty($existing)) {
            $this->line("   ✅ Existentes: " . implode(', ', $existing));
        }

        // Mostrar columnas faltantes
        if (!empty($missing)) {
            $this->warn("   ⚠️  Faltan: " . implode(', ', $missing));
        } else {
            $this->info("   ✅ Todas las columnas están presentes");
        }

        $this->newLine();
    }
}