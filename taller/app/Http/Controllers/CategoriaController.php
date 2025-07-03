<?php

// ===== CategoriaController.php =====
namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
    }

    public function index()
    {
        $categorias = Categoria::withCount('productos')->paginate(10);
        return view('modules.categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('modules.categorias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:categorias,nombre'
        ]);

        Categoria::create($request->only('nombre'));

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada exitosamente');
    }

    public function show(Categoria $categoria)
    {
        $categoria->load(['productos' => function($query) {
            $query->latest()->take(10);
        }]);
        
        return view('modules.categorias.show', compact('categoria'));
    }

    public function edit(Categoria $categoria)
    {
        return view('modules.categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:categorias,nombre,' . $categoria->id
        ]);

        $categoria->update($request->only('nombre'));

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada exitosamente');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->exists()) {
            return back()->with('error', 'No se puede eliminar la categoría porque tiene productos asociados');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada exitosamente');
    }

    public function productos(Categoria $categoria)
    {
        $productos = $categoria->productos()->paginate(15);
        return view('modules.categorias.productos', compact('categoria', 'productos'));
    }
}

