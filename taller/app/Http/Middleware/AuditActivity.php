<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Auditoria;

class AuditActivity
{
    protected $excludedRoutes = [
        'horizon*',
        '_ignition*',
        'api/heartbeat',
        'assets/*',
        '*.css',
        '*.js',
        '*.png',
        '*.jpg',
        '*.gif',
        '*.svg'
    ];

    public function handle(Request $request, Closure $next)
    {
        // Ejecutar la request primero
        $response = $next($request);

        // Solo auditar si está habilitado
        if (!config('app.audit_enabled', true)) {
            return $response;
        }

        // Excluir rutas no importantes
        if ($this->shouldExclude($request)) {
            return $response;
        }

        // Solo auditar operaciones que modifican datos
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    protected function shouldExclude(Request $request): bool
    {
        $path = $request->path();
        
        foreach ($this->excludedRoutes as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    protected function logActivity(Request $request, $response)
    {
        try {
            $routeAction = $request->route() ? $request->route()->getActionName() : null;
            $tabla = $this->extractTableFromRoute($request);
            
            Auditoria::create([
                'usuario_id' => Auth::id(),
                'operacion' => $request->method(),
                'tabla' => $tabla,
                'datos_anteriores' => $this->getPreviousData($request),
                'datos_nuevos' => $this->getNewData($request, $response),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'ruta' => $request->fullUrl(),
                'controlador' => $routeAction,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::error('Audit middleware error: ' . $e->getMessage());
        }
    }

    protected function extractTableFromRoute(Request $request): ?string
    {
        $route = $request->route();
        if (!$route) return null;

        // Intentar extraer de la URI
        $uri = $route->uri();
        $segments = explode('/', $uri);
        
        // Buscar segmentos que parezcan nombres de tablas
        $possibleTables = ['usuarios', 'clientes', 'productos', 'reparaciones', 'ventas', 'empleados'];
        
        foreach ($segments as $segment) {
            if (in_array($segment, $possibleTables)) {
                return $segment;
            }
        }

        return null;
    }

    protected function getPreviousData(Request $request): ?array
    {
        // Para operaciones UPDATE, intentar obtener datos anteriores
        if ($request->method() === 'PUT' || $request->method() === 'PATCH') {
            $id = $request->route('id') ?? $request->route('user') ?? $request->route('cliente');
            if ($id) {
                $tabla = $this->extractTableFromRoute($request);
                if ($tabla) {
                    try {
                        $model = $this->getModelClass($tabla);
                        if ($model) {
                            return $model::find($id)?->toArray();
                        }
                    } catch (\Exception $e) {
                        // Silently fail
                    }
                }
            }
        }

        return null;
    }

    protected function getNewData(Request $request, $response): ?array
    {
        $data = [];
        
        // Obtener datos del request (excluyendo passwords)
        $requestData = $request->except(['password', 'password_confirmation', '_token', '_method']);
        if (!empty($requestData)) {
            $data['request'] = $requestData;
        }

        // Intentar obtener datos de la respuesta si es JSON
        if ($response->headers->get('content-type') === 'application/json') {
            try {
                $responseData = json_decode($response->getContent(), true);
                if (is_array($responseData)) {
                    $data['response'] = $responseData;
                }
            } catch (\Exception $e) {
                // Ignore JSON decode errors
            }
        }

        return !empty($data) ? $data : null;
    }

    protected function getModelClass(string $tabla): ?string
    {
        $modelMap = [
            'usuarios' => \App\Models\Usuario::class,
            'clientes' => \App\Models\Cliente::class,
            'productos' => \App\Models\Producto::class,
            'reparaciones' => \App\Models\Reparacion::class,
            'ventas' => \App\Models\Venta::class,
            'empleados' => \App\Models\Empleado::class,
        ];

        return $modelMap[$tabla] ?? null;
    }
}