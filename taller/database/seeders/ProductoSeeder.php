<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener categorías y proveedores
        $categorias = Categoria::all();
        $proveedores = Proveedor::all();

        if ($categorias->isEmpty() || $proveedores->isEmpty()) {
            $this->command->warn('⚠️ No hay categorías o proveedores. Ejecute sus seeders primero.');
            return;
        }

        $productos = [
            // Pantallas
            [
                'codigo' => 'PANT-IPH-12',
                'nombre' => 'Pantalla iPhone 12 Original',
                'descripcion' => 'Pantalla OLED original para iPhone 12',
                'categoria_id' => $categorias->where('nombre', 'Pantallas')->first()->id ?? 1,
                'proveedor_id' => $proveedores->first()->id,
                'precio_venta' => 250.00,  // Cambiado de 'precio' a 'precio_venta'
                'precio_compra' => 150.00,
                'stock' => 10,
                'stock_minimo' => 3,
                'unidad_medida' => 'unidad',
                'garantia_dias' => 30,
                'activo' => true
            ],
            [
                'codigo' => 'PANT-SAM-A50',
                'nombre' => 'Pantalla Samsung A50',
                'descripcion' => 'Pantalla AMOLED para Samsung Galaxy A50',
                'categoria_id' => $categorias->where('nombre', 'Pantallas')->first()->id ?? 1,
                'proveedor_id' => $proveedores->first()->id,
                'precio_venta' => 180.00,  // Cambiado de 'precio' a 'precio_venta'
                'precio_compra' => 100.00,
                'stock' => 15,
                'stock_minimo' => 5,
                'unidad_medida' => 'unidad',
                'garantia_dias' => 30,
                'activo' => true
            ],
            // Baterías
            [
                'codigo' => 'BAT-IPH-12',
                'nombre' => 'Batería iPhone 12',
                'descripcion' => 'Batería de reemplazo para iPhone 12 - 2815mAh',
                'categoria_id' => $categorias->where('nombre', 'Baterías')->first()->id ?? 2,
                'proveedor_id' => $proveedores->first()->id,
                'precio_venta' => 80.00,  // Cambiado de 'precio' a 'precio_venta'
                'precio_compra' => 40.00,
                'stock' => 20,
                'stock_minimo' => 8,
                'unidad_medida' => 'unidad',
                'garantia_dias' => 90,
                'activo' => true
            ],
            [
                'codigo' => 'BAT-SAM-A50',
                'nombre' => 'Batería Samsung A50',
                'descripcion' => 'Batería de reemplazo para Samsung A50 - 4000mAh',
                'categoria_id' => $categorias->where('nombre', 'Baterías')->first()->id ?? 2,
                'proveedor_id' => $proveedores->first()->id,
                'precio_venta' => 60.00,  // Cambiado de 'precio' a 'precio_venta'
                'precio_compra' => 30.00,
                'stock' => 25,
                'stock_minimo' => 10,
                'unidad_medida' => 'unidad',
                'garantia_dias' => 90,
                'activo' => true
            ],
            // Cámaras
            [
                'codigo' => 'CAM-IPH-12-TRAS',
                'nombre' => 'Cámara Trasera iPhone 12',
                'descripcion' => 'Módulo de cámara trasera para iPhone 12',
                'categoria_id' => $categorias->where('nombre', 'Cámaras')->first()->id ?? 3,
                'proveedor_id' => $proveedores->first()->id,
                'precio_venta' => 120.00,  // Cambiado de 'precio' a 'precio_venta'
                'precio_compra' => 70.00,
                'stock' => 8,
                'stock_minimo' => 3,
                'unidad_medida' => 'unidad',
                'garantia_dias' => 60,
                'activo' => true
            ],
            // Accesorios
            [
                'codigo' => 'ACC-CABLE-USB-C',
                'nombre' => 'Cable USB-C a Lightning',
                'descripcion' => 'Cable de carga rápida USB-C a Lightning 1m',
                'categoria_id' => $categorias->where('nombre', 'Accesorios')->first()->id ?? 4,
                'proveedor_id' => $proveedores->first()->id,
                'precio_venta' => 25.00,  // Cambiado de 'precio' a 'precio_venta'
                'precio_compra' => 10.00,
                'stock' => 50,
                'stock_minimo' => 20,
                'unidad_medida' => 'unidad',
                'garantia_dias' => 30,
                'activo' => true
            ],
            [
                'codigo' => 'ACC-CARG-20W',
                'nombre' => 'Cargador 20W USB-C',
                'descripcion' => 'Cargador rápido 20W con puerto USB-C',
                'categoria_id' => $categorias->where('nombre', 'Accesorios')->first()->id ?? 4,
                'proveedor_id' => $proveedores->first()->id,
                'precio_venta' => 35.00,  // Cambiado de 'precio' a 'precio_venta'
                'precio_compra' => 15.00,
                'stock' => 30,
                'stock_minimo' => 10,
                'unidad_medida' => 'unidad',
                'garantia_dias' => 30,
                'activo' => true
            ],
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }

        $this->command->info('✅ ' . count($productos) . ' productos creados');
    }
}