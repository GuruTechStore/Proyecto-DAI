<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;

class DiagnosticarBaseDatos extends Command
{
    protected $signature = 'db:diagnostico';
    protected $description = 'Diagnostica problemas de conexión y estructura de base de datos';

    public function handle()
    {
        $this->info('=== DIAGNÓSTICO DE BASE DE DATOS ===');
        $this->newLine();

        // 1. Verificar conexión a base de datos
        $this->verificarConexion();
        
        // 2. Verificar tablas importantes
        $this->verificarTablas();
        
        // 3. Verificar datos de ejemplo
        $this->verificarDatos();
        
        // 4. Verificar relaciones
        $this->verificarRelaciones();
        
        // 5. Verificar permisos
        $this->verificarPermisos();

        $this->newLine();
        $this->info('=== DIAGNÓSTICO COMPLETADO ===');
    }

    protected function verificarConexion()
    {
        $this->info('1. Verificando conexión a base de datos...');
        
        try {
            DB::connection()->getPdo();
            $this->line('   ✅ Conexión exitosa');
            
            $dbName = DB::connection()->getDatabaseName();
            $this->line("   📊 Base de datos: {$dbName}");
            
        } catch (\Exception $e) {
            $this->error('   ❌ Error de conexión: ' . $e->getMessage());
            return false;
        }
        
        $this->newLine();
        return true;
    }

    protected function verificarTablas()
    {
        $this->info('2. Verificando estructura de tablas...');
        
        $tablasImportantes = [
            'productos',
            'categorias', 
            'proveedores',
            'usuarios',
            'ventas',
            'detalle_ventas'
        ];

        foreach ($tablasImportantes as $tabla) {
            if (Schema::hasTable($tabla)) {
                $count = DB::table($tabla)->count();
                $this->line("   ✅ Tabla '{$tabla}' existe ({$count} registros)");
                
                // Verificar columnas importantes para productos
                if ($tabla === 'productos') {
                    $this->verificarColumnasProductos();
                }
                
            } else {
                $this->error("   ❌ Tabla '{$tabla}' NO existe");
            }
        }
        
        $this->newLine();
    }

    protected function verificarColumnasProductos()
    {
        $columnasRequeridas = [
            'id', 'codigo', 'nombre', 'descripcion', 'categoria_id',
            'precio_compra', 'precio_venta', 'stock', 'stock_minimo',
            'activo', 'created_at', 'updated_at'
        ];

        $columnasExistentes = Schema::getColumnListing('productos');
        
        foreach ($columnasRequeridas as $columna) {
            if (in_array($columna, $columnasExistentes)) {
                $this->line("      ✅ Columna '{$columna}' existe");
            } else {
                $this->error("      ❌ Columna '{$columna}' NO existe");
            }
        }
    }

    protected function verificarDatos()
    {
        $this->info('3. Verificando datos de ejemplo...');
        
        try {
            // Verificar categorías
            $categorias = Categoria::count();
            if ($categorias > 0) {
                $this->line("   ✅ Categorías: {$categorias} registros");
                $primera = Categoria::first();
                $this->line("      Primera categoría: {$primera->nombre}");
            } else {
                $this->warn('   ⚠️  No hay categorías registradas');
                $this->crearDatosEjemplo();
            }

            // Verificar proveedores
            $proveedores = Proveedor::count();
            if ($proveedores > 0) {
                $this->line("   ✅ Proveedores: {$proveedores} registros");
            } else {
                $this->warn('   ⚠️  No hay proveedores registrados');
            }

            // Verificar productos
            $productos = Producto::count();
            if ($productos > 0) {
                $this->line("   ✅ Productos: {$productos} registros");
                
                // Verificar productos activos
                $productosActivos = Producto::where('activo', true)->count();
                $this->line("      Productos activos: {$productosActivos}");
                
                // Verificar productos con stock
                $conStock = Producto::where('stock', '>', 0)->count();
                $this->line("      Productos con stock: {$conStock}");
                
            } else {
                $this->warn('   ⚠️  No hay productos registrados');
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error al verificar datos: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    protected function verificarRelaciones()
    {
        $this->info('4. Verificando relaciones de datos...');
        
        try {
            // Verificar productos con categorías
            $productosSinCategoria = Producto::whereNull('categoria_id')->count();
            if ($productosSinCategoria > 0) {
                $this->warn("   ⚠️  {$productosSinCategoria} productos sin categoría");
            } else {
                $this->line('   ✅ Todos los productos tienen categoría');
            }

            // Verificar integridad referencial
            $categoriasOrfanas = Producto::whereNotNull('categoria_id')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('categorias')
                          ->whereRaw('categorias.id = productos.categoria_id');
                })->count();
                
            if ($categoriasOrfanas > 0) {
                $this->error("   ❌ {$categoriasOrfanas} productos con categorías inexistentes");
            } else {
                $this->line('   ✅ Integridad referencial correcta');
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error al verificar relaciones: ' . $e->getMessage());
        }
        
        $this->newLine();
    }

    protected function verificarPermisos()
    {
        $this->info('5. Verificando permisos y configuración...');
        
        try {
            // Verificar usuario autenticado
            if (auth()->check()) {
                $user = auth()->user();
                $this->line("   ✅ Usuario autenticado: {$user->name}");
                
                // Verificar permisos de productos
                if (method_exists($user, 'can')) {
                    $permisos = [
                        'productos.ver' => 'Ver productos',
                        'productos.crear' => 'Crear productos',
                        'productos.editar' => 'Editar productos',
                        'productos.eliminar' => 'Eliminar productos'
                    ];

                    foreach ($permisos as $permiso => $descripcion) {
                        if ($user->can($permiso)) {
                            $this->line("      ✅ {$descripcion}");
                        } else {
                            $this->warn("      ⚠️  Sin permiso: {$descripcion}");
                        }
                    }
                } else {
                    $this->warn('   ⚠️  Sistema de permisos no configurado');
                }
                
            } else {
                $this->warn('   ⚠️  Usuario no autenticado');
            }

            // Verificar configuración de aplicación
            $this->line('   📋 Configuración de aplicación:');
            $this->line('      APP_ENV: ' . config('app.env'));
            $this->line('      APP_DEBUG: ' . (config('app.debug') ? 'true' : 'false'));
            $this->line('      DB_CONNECTION: ' . config('database.default'));

        } catch (\Exception $e) {
            $this->error('   ❌ Error al verificar permisos: ' . $e->getMessage());
        }
    }

    protected function crearDatosEjemplo()
    {
        $this->info('   🔧 Creando datos de ejemplo...');
        
        try {
            DB::beginTransaction();

            // Crear categorías de ejemplo
            $categorias = [
                ['nombre' => 'Pantallas', 'descripcion' => 'Pantallas y displays', 'activa' => true],
                ['nombre' => 'Baterías', 'descripcion' => 'Baterías para dispositivos', 'activa' => true],
                ['nombre' => 'Cámaras', 'descripcion' => 'Módulos de cámara', 'activa' => true],
                ['nombre' => 'Accesorios', 'descripcion' => 'Cables y accesorios', 'activa' => true],
            ];

            foreach ($categorias as $categoria) {
                Categoria::create($categoria);
            }

            // Crear proveedores de ejemplo
            $proveedores = [
                ['nombre' => 'TechParts S.A.', 'contacto' => 'ventas@techparts.com', 'telefono' => '01-234-5678', 'activo' => true],
                ['nombre' => 'Distribuidora Global', 'contacto' => 'info@global.com', 'telefono' => '01-876-5432', 'activo' => true],
            ];

            foreach ($proveedores as $proveedor) {
                Proveedor::create($proveedor);
            }

            // Crear productos de ejemplo
            $categoria1 = Categoria::first();
            $proveedor1 = Proveedor::first();

            $productos = [
                [
                    'codigo' => 'PROD-000001',
                    'nombre' => 'Pantalla iPhone 12',
                    'descripcion' => 'Pantalla LCD completa para iPhone 12',
                    'categoria_id' => $categoria1->id,
                    'proveedor_id' => $proveedor1->id,
                    'precio_compra' => 80.00,
                    'precio_venta' => 150.00,
                    'stock' => 15,
                    'stock_minimo' => 5,
                    'activo' => true
                ],
                [
                    'codigo' => 'PROD-000002',
                    'nombre' => 'Batería iPhone 12',
                    'descripcion' => 'Batería original para iPhone 12',
                    'categoria_id' => $categoria1->id,
                    'proveedor_id' => $proveedor1->id,
                    'precio_compra' => 25.00,
                    'precio_venta' => 60.00,
                    'stock' => 25,
                    'stock_minimo' => 10,
                    'activo' => true
                ]
            ];

            foreach ($productos as $producto) {
                Producto::create($producto);
            }

            DB::commit();
            $this->line('      ✅ Datos de ejemplo creados exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('      ❌ Error al crear datos de ejemplo: ' . $e->getMessage());
        }
    }

    protected function mostrarSolucionesComunes()
    {
        $this->newLine();
        $this->info('=== SOLUCIONES COMUNES ===');
        $this->newLine();

        $this->warn('Si no se muestran productos, verifica:');
        $this->line('1. Ejecutar migraciones: php artisan migrate');
        $this->line('2. Ejecutar seeders: php artisan db:seed');
        $this->line('3. Verificar permisos de usuario');
        $this->line('4. Limpiar cache: php artisan cache:clear');
        $this->line('5. Verificar archivo .env');
        $this->newLine();

        $this->warn('Si hay errores de base de datos:');
        $this->line('1. Verificar conexión en .env');
        $this->line('2. Verificar que la BD existe');
        $this->line('3. Verificar permisos de usuario de BD');
        $this->line('4. Ejecutar: php artisan config:clear');
        $this->newLine();
    }
}