<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Usuario;

class VentaSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = Cliente::all();
        $productos = Producto::where('stock', '>', 0)->get();
        $vendedores = Usuario::whereHas('roles', function($q) {
            $q->whereIn('name', ['Vendedor', 'Gerente', 'Super Admin']);
        })->get();

        if ($clientes->isEmpty() || $productos->isEmpty() || $vendedores->isEmpty()) {
            $this->command->warn('⚠️ No hay datos suficientes para crear ventas');
            return;
        }

        // Crear 5 ventas de ejemplo
        for ($i = 1; $i <= 5; $i++) {
            $subtotal = 0;
            $cliente = $clientes->random();
            $vendedor = $vendedores->random();

            $venta = Venta::create([
                'codigo_venta' => 'VTA-20250100' . $i,
                'cliente_id' => $cliente->id,
                'empleado_id' => $vendedor->empleado_id,
                'fecha' => now()->subDays(rand(0, 30)),
                'tipo_documento' => 'boleta',
                'numero_boleta' => 'B001-0000' . $i,
                'subtotal' => 0, // Se actualizará después
                'descuento' => 0,
                'total' => 0, // Se actualizará después
                'metodo_pago' => ['efectivo', 'tarjeta', 'transferencia'][rand(0, 2)],
                'estado' => 'completada',
                'creado_por' => $vendedor->id,
            ]);

            // Agregar 1-3 productos a la venta
            $numProductos = rand(1, 3);
            $productosUsados = [];

            for ($j = 0; $j < $numProductos; $j++) {
                $producto = $productos->whereNotIn('id', $productosUsados)->random();
                $productosUsados[] = $producto->id;
                $cantidad = rand(1, min(3, $producto->stock));

                $detalle = DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                    'descuento' => 0,
                    'garantia_dias' => 30,
                    'subtotal' => $cantidad * $producto->precio,
                ]);

                $subtotal += $detalle->subtotal;

                // Actualizar stock
                $producto->decrement('stock', $cantidad);
            }

            // Actualizar totales de la venta
            $venta->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);
        }

        $this->command->info('✅ 5 ventas de prueba creadas');
    }
}