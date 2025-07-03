<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Pantallas', 'descripcion' => 'Pantallas y displays para dispositivos móviles'],
            ['nombre' => 'Baterías', 'descripcion' => 'Baterías de reemplazo para dispositivos'],
            ['nombre' => 'Cámaras', 'descripcion' => 'Módulos de cámara frontal y trasera'],
            ['nombre' => 'Placas Base', 'descripcion' => 'Tarjetas madre y placas lógicas'],
            ['nombre' => 'Flexores', 'descripcion' => 'Cables flex y conectores'],
            ['nombre' => 'Botones', 'descripcion' => 'Botones de encendido, volumen y home'],
            ['nombre' => 'Altavoces', 'descripcion' => 'Altavoces y auriculares'],
            ['nombre' => 'Micrófonos', 'descripcion' => 'Micrófonos y componentes de audio'],
            ['nombre' => 'Carcasas', 'descripcion' => 'Carcasas, tapas y cubiertas'],
            ['nombre' => 'Herramientas', 'descripcion' => 'Herramientas para reparación'],
            ['nombre' => 'Accesorios', 'descripcion' => 'Accesorios y complementos'],
            ['nombre' => 'Consumibles', 'descripcion' => 'Pegamentos, cintas y consumibles'],
        ];

        foreach ($categorias as $categoria) {
            Categoria::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                $categoria
            );
        }

        $this->command->info('✅ ' . count($categorias) . ' categorías creadas');
    }
}