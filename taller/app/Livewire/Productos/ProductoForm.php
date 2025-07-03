<?php

namespace App\Livewire\Productos;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\MovimientoStock;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\DB;

class ProductoForm extends Component
{
    public ?Producto $producto = null;
    public bool $isEditing = false;

    #[Rule('required|string|max:50', message: 'El código es obligatorio y debe tener máximo 50 caracteres')]
    public string $codigo = '';
    
    #[Rule('required|string|max:200', message: 'El nombre es obligatorio y debe tener máximo 200 caracteres')]
    public string $nombre = '';
    
    #[Rule('nullable|string', message: 'La descripción debe ser texto válido')]
    public string $descripcion = '';
    
    #[Rule('required|exists:categorias,id', message: 'Seleccione una categoría válida')]
    public string $categoria_id = '';
    
    #[Rule('required|numeric|min:0', message: 'El precio de compra debe ser un número mayor o igual a 0')]
    public string $precio_compra = '';
    
    #[Rule('required|numeric|min:0', message: 'El precio de venta debe ser un número mayor o igual a 0')]
    public string $precio_venta = '';
    
    #[Rule('required|integer|min:0', message: 'El stock mínimo debe ser un número entero mayor o igual a 0')]
    public string $stock_minimo = '';
    
    #[Rule('required|integer|min:0', message: 'El stock actual debe ser un número entero mayor o igual a 0')]
    public string $stock_actual = '';
    
    #[Rule('nullable|exists:proveedores,id', message: 'Seleccione un proveedor válido')]
    public string $proveedor_id = '';
    
    #[Rule('nullable|string|max:100', message: 'La ubicación debe tener máximo 100 caracteres')]
    public string $ubicacion = '';
    
    public bool $activo = true;

    public function mount(?Producto $producto = null)
    {
        if ($producto && $producto->exists) {
            $this->isEditing = true;
            $this->producto = $producto;
            $this->codigo = $producto->codigo;
            $this->nombre = $producto->nombre;
            $this->descripcion = $producto->descripcion ?? '';
            $this->categoria_id = $producto->categoria_id;
            $this->precio_compra = $producto->precio_compra;
            $this->precio_venta = $producto->precio_venta;
            $this->stock_minimo = $producto->stock_minimo;
            $this->stock_actual = $producto->stock_actual;
            $this->proveedor_id = $producto->proveedor_id ?? '';
            $this->ubicacion = $producto->ubicacion ?? '';
            $this->activo = $producto->activo;
        }
        
        // Verificar permisos
        if ($this->isEditing) {
            abort_unless(auth()->user()->can('productos.editar'), 403);
        } else {
            abort_unless(auth()->user()->can('productos.crear'), 403);
        }
    }

    public function getCategoriasProperty()
    {
        return Categoria::where('activa', true)->orderBy('nombre')->get();
    }

    public function getProveedoresProperty()
    {
        return Proveedor::where('activo', true)->orderBy('nombre')->get();
    }

    public function save()
    {
        // Validar datos básicos
        $this->validate();
        
        // Validación adicional para código único
        $query = Producto::where('codigo', $this->codigo);
        if ($this->isEditing) {
            $query->where('id', '!=', $this->producto->id);
        }
        
        if ($query->exists()) {
            $this->addError('codigo', 'Este código ya está registrado por otro producto.');
            return;
        }

        $data = [
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
            'categoria_id' => $this->categoria_id,
            'precio_compra' => (float) $this->precio_compra,
            'precio_venta' => (float) $this->precio_venta,
            'stock_minimo' => (int) $this->stock_minimo,
            'proveedor_id' => $this->proveedor_id ?: null,
            'ubicacion' => $this->ubicacion ?: null,
            'activo' => $this->activo,
        ];

        if ($this->isEditing) {
            // No actualizar stock_actual en edición, solo en creación
            $this->producto->update($data);
            
            // Log de actividad
            activity()
                ->performedOn($this->producto)
                ->causedBy(auth()->user())
                ->withProperties(['modulo' => 'productos'])
                ->log('Producto actualizado: ' . $this->producto->nombre);
            
            $this->dispatch('producto-updated');
            session()->flash('success', 'Producto actualizado correctamente');
            return redirect()->route('productos.index');
            
        } else {
            // En creación, incluir stock_actual
            $data['stock_actual'] = (int) $this->stock_actual;
            
            DB::transaction(function () use ($data) {
                $producto = Producto::create($data);
                
                // Registrar movimiento inicial de stock si hay stock_actual
                if ($data['stock_actual'] > 0) {
                    MovimientoStock::create([
                        'producto_id' => $producto->id,
                        'tipo_movimiento' => 'entrada',
                        'cantidad' => $data['stock_actual'],
                        'stock_anterior' => 0,
                        'stock_nuevo' => $data['stock_actual'],
                        'motivo' => 'Stock inicial',
                        'usuario_id' => auth()->id(),
                        'fecha' => now(),
                    ]);
                }
                
                // Log de actividad
                activity()
                    ->performedOn($producto)
                    ->causedBy(auth()->user())
                    ->withProperties(['modulo' => 'productos'])
                    ->log('Producto creado: ' . $producto->nombre);
            });
            
            $this->dispatch('producto-created');
            session()->flash('success', 'Producto creado correctamente');
            return redirect()->route('productos.index');
        }
    }

    public function cancel()
    {
        return redirect()->route('productos.index');
    }

    public function updatedPrecioCompra()
    {
        // Auto-calcular precio de venta con margen del 30% si no se ha definido
        if ($this->precio_compra && !$this->precio_venta) {
            $margen = 1.3; // 30% de margen
            $this->precio_venta = number_format((float) $this->precio_compra * $margen, 2, '.', '');
        }
    }

    public function render()
    {
        return view('livewire.productos.producto-form', [
            'categorias' => $this->categorias,
            'proveedores' => $this->proveedores,
        ]);
    }
}