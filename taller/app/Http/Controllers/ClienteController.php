<?php
// app/Http/Controllers/ClienteController.php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:clientes.ver')->only(['index', 'show', 'apiIndex', 'getStats']);
        $this->middleware('can:clientes.crear')->only(['create', 'store']);
        $this->middleware('can:clientes.editar')->only(['edit', 'update']);
        $this->middleware('can:clientes.eliminar')->only(['destroy', 'bulkDelete']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Cliente::query();

            // Aplicar filtros si existen
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('apellido', 'like', "%{$search}%")
                      ->orWhere('documento', 'like', "%{$search}%")
                      ->orWhere('telefono', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->has('tipo_documento') && $request->tipo_documento) {
                $query->where('tipo_documento', $request->tipo_documento);
            }

            if ($request->has('status') && $request->status !== '') {
                $query->where('activo', $request->status === 'active');
            }

            // Ordenamiento
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            $allowedSorts = ['nombre', 'apellido', 'documento', 'telefono', 'email', 'created_at'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = in_array($perPage, [10, 15, 25, 50]) ? $perPage : 15;
            
            $clientes = $query->withCount(['reparaciones', 'ventas'])
                            ->paginate($perPage)
                            ->withQueryString();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $clientes,
                    'message' => 'Clientes obtenidos exitosamente'
                ]);
            }

            return view('modules.clientes.index', compact('clientes'));

        } catch (Exception $e) {
            \Log::error('Error in ClienteController@index: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al cargar los clientes'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al cargar los clientes');
        }
    }

    /**
     * API Index - Para llamadas AJAX
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $query = Cliente::query();

            // Aplicar filtros si existen
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('apellido', 'like', "%{$search}%")
                      ->orWhere('documento', 'like', "%{$search}%")
                      ->orWhere('telefono', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->has('tipo_documento') && $request->tipo_documento) {
                $query->where('tipo_documento', $request->tipo_documento);
            }

            if ($request->has('status') && $request->status !== '') {
                $query->where('activo', $request->status === 'active');
            }

            // Ordenamiento
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            $allowedSorts = ['nombre', 'apellido', 'documento', 'telefono', 'email', 'created_at'];
            if (in_array($sortField, $allowedSorts)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Paginación
            $perPage = $request->get('per_page', 15);
            $perPage = in_array($perPage, [10, 15, 25, 50]) ? $perPage : 15;
            
            $clientes = $query->withCount(['reparaciones', 'ventas'])
                            ->paginate($perPage)
                            ->withQueryString();

            return response()->json([
                'success' => true,
                'data' => $clientes->items(),
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage(),
                'per_page' => $clientes->perPage(),
                'total' => $clientes->total(),
                'from' => $clientes->firstItem(),
                'to' => $clientes->lastItem()
            ]);

        } catch (Exception $e) {
            \Log::error('Error in ClienteController@apiIndex: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los clientes'
            ], 500);
        }
    }

    /**
     * Get Stats - Para estadísticas del dashboard
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $stats = [
                'total_clientes' => Cliente::count(),
                'clientes_activos' => Cliente::where('activo', true)->count(),
                'nuevos_mes' => Cliente::whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)
                                      ->count(),
                'con_reparaciones' => Cliente::has('reparaciones')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            \Log::error('Error in ClienteController@getStats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('modules.clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100',
                'apellido' => 'nullable|string|max:100',
                'tipo_documento' => 'required|in:DNI,RUC,Pasaporte,Carnet',
                'documento' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('clientes')->whereNull('deleted_at')
                ],
                'telefono' => 'required|string|max:20',
                'email' => [
                    'nullable',
                    'email',
                    'max:100',
                    Rule::unique('clientes')->whereNull('deleted_at')
                ],
                'direccion' => 'nullable|string|max:255'
            ]);

            // Validaciones adicionales del documento
            $validator->after(function ($validator) use ($request) {
                $tipoDoc = $request->get('tipo_documento', '');
                $documento = $request->get('documento', '');
                
                if ($documento && $tipoDoc === 'DNI' && strlen($documento) !== 8) {
                    $validator->errors()->add('documento', 'El DNI debe tener exactamente 8 dígitos');
                } elseif ($documento && $tipoDoc === 'RUC' && strlen($documento) !== 11) {
                    $validator->errors()->add('documento', 'El RUC debe tener exactamente 11 dígitos');
                } elseif ($documento && $tipoDoc === 'Pasaporte' && strlen($documento) < 6) {
                    $validator->errors()->add('documento', 'El pasaporte debe tener al menos 6 caracteres');
                }
            });

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Datos de validación incorrectos',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();

            // Crear el cliente
            $cliente = Cliente::create([
                'nombre' => trim($request->nombre),
                'apellido' => $request->apellido ? trim($request->apellido) : null,
                'tipo_documento' => $request->tipo_documento,
                'documento' => $request->documento ? trim($request->documento) : null,
                'telefono' => trim($request->telefono),
                'email' => $request->email ? trim($request->email) : null,
                'direccion' => $request->direccion ? trim($request->direccion) : null,
                'activo' => true
            ]);

            DB::commit();

            // Log de actividad
            \Log::info('Cliente creado exitosamente', [
                'cliente_id' => $cliente->id,
                'usuario_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente creado exitosamente',
                    'data' => $cliente
                ], 201);
            }

            return redirect()->route('clientes.index')->with('success', 'Cliente creado exitosamente');

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error in ClienteController@store: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error interno del servidor al crear el cliente'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al crear el cliente')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        try {
            $cliente->load(['reparaciones', 'ventas', 'equipos']);
            
            return view('modules.clientes.show', compact('cliente'));

        } catch (Exception $e) {
            \Log::error('Error in ClienteController@show: ' . $e->getMessage());
            return redirect()->route('clientes.index')->with('error', 'Error al mostrar el cliente');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        return view('modules.clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100',
                'apellido' => 'nullable|string|max:100',
                'tipo_documento' => 'required|in:DNI,RUC,Pasaporte,Carnet',
                'documento' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('clientes')->ignore($cliente->id)->whereNull('deleted_at')
                ],
                'telefono' => 'required|string|max:20',
                'email' => [
                    'nullable',
                    'email',
                    'max:100',
                    Rule::unique('clientes')->ignore($cliente->id)->whereNull('deleted_at')
                ],
                'direccion' => 'nullable|string|max:255',
                'activo' => 'boolean'
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Datos de validación incorrectos',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();

            $cliente->update([
                'nombre' => trim($request->nombre),
                'apellido' => $request->apellido ? trim($request->apellido) : null,
                'tipo_documento' => $request->tipo_documento,
                'documento' => $request->documento ? trim($request->documento) : null,
                'telefono' => trim($request->telefono),
                'email' => $request->email ? trim($request->email) : null,
                'direccion' => $request->direccion ? trim($request->direccion) : null,
                'activo' => $request->has('activo') ? (bool)$request->activo : $cliente->activo
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente actualizado exitosamente',
                    'data' => $cliente
                ]);
            }

            return redirect()->route('clientes.index')->with('success', 'Cliente actualizado exitosamente');

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error in ClienteController@update: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el cliente'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al actualizar el cliente')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        try {
            DB::beginTransaction();

            // Verificar si tiene relaciones
            if ($cliente->reparaciones()->count() > 0 || $cliente->ventas()->count() > 0) {
                // Solo desactivar si tiene relaciones
                $cliente->update(['activo' => false]);
                $message = 'Cliente desactivado exitosamente (tiene reparaciones/ventas asociadas)';
            } else {
                // Eliminar completamente si no tiene relaciones
                $cliente->delete();
                $message = 'Cliente eliminado exitosamente';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error in ClienteController@destroy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el cliente'
            ], 500);
        }
    }

    /**
     * Bulk delete clients.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->get('ids', []);
            
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se proporcionaron IDs para eliminar'
                ], 400);
            }

            DB::beginTransaction();

            $clientes = Cliente::whereIn('id', $ids)->get();
            $eliminados = 0;
            $desactivados = 0;

            foreach ($clientes as $cliente) {
                if ($cliente->reparaciones()->count() > 0 || $cliente->ventas()->count() > 0) {
                    $cliente->update(['activo' => false]);
                    $desactivados++;
                } else {
                    $cliente->delete();
                    $eliminados++;
                }
            }

            DB::commit();

            $message = "Procesados: {$eliminados} eliminados, {$desactivados} desactivados";

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (Exception $e) {
            DB::rollback();
            \Log::error('Error in ClienteController@bulkDelete: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la eliminación masiva'
            ], 500);
        }
    }

    /**
     * Export clients to Excel.
     */
    public function export(Request $request)
    {
        try {
            $query = Cliente::query();

            // Aplicar filtros si se especifican IDs
            if ($request->has('ids') && $request->ids) {
                $ids = explode(',', $request->ids);
                $query->whereIn('id', $ids);
            }

            $clientes = $query->orderBy('nombre')->get();

            // Aquí iría la lógica de exportación a Excel
            // Por ahora retornamos JSON como ejemplo
            return response()->json([
                'success' => true,
                'message' => 'Exportación iniciada',
                'data' => $clientes
            ]);

        } catch (Exception $e) {
            \Log::error('Error in ClienteController@export: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar los clientes'
            ], 500);
        }
    }

    /**
     * Search clients for autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $clientes = Cliente::where('activo', true)
                ->where(function ($q) use ($query) {
                    $q->where('nombre', 'like', "%{$query}%")
                      ->orWhere('apellido', 'like', "%{$query}%")
                      ->orWhere('documento', 'like', "%{$query}%")
                      ->orWhere('telefono', 'like', "%{$query}%");
                })
                ->select('id', 'nombre', 'apellido', 'documento', 'telefono')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $clientes
            ]);

        } catch (Exception $e) {
            \Log::error('Error in ClienteController@search: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda'
            ], 500);
        }
    }

    /**
     * Get client data for API
     */
    public function getData(Cliente $cliente): JsonResponse
    {
        try {
            $cliente->load(['reparaciones', 'ventas', 'equipos']);
            
            return response()->json([
                'success' => true,
                'data' => $cliente
            ]);

        } catch (Exception $e) {
            \Log::error('Error in ClienteController@getData: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del cliente'
            ], 500);
        }
    }
}