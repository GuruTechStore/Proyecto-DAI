<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    /**
     * Obtener empleado_id del usuario autenticado
     * 
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     * @return int
     */
    protected function getEmpleadoId()
    {
        $user = auth()->user();
        if (!$user || !$user->empleado_id) {
            abort(403, 'Usuario no asociado a un empleado');
        }
        return $user->empleado_id;
    }
    
    /**
     * Verificar que el usuario tenga empleado asociado
     * 
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse|null
     */
    protected function requireEmpleado()
    {
        $user = auth()->user();
        if (!$user || !$user->empleado_id) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no asociado a un empleado'
                ], 403);
            }
            
            return redirect()->route('dashboard')
                ->with('error', 'Usuario no asociado a un empleado. Contacta al administrador.');
        }
        return null;
    }
    
    /**
     * Obtener datos del empleado del usuario actual
     * 
     * @return \App\Models\Empleado|null
     */
    protected function getCurrentEmpleado()
    {
        $user = auth()->user();
        return $user ? $user->empleado : null;
    }
    
    /**
     * Verificar si el usuario tiene empleado (sin abortar)
     * 
     * @return bool
     */
    protected function hasEmpleado()
    {
        $user = auth()->user();
        return $user && $user->empleado_id;
    }
    
    /**
     * Log actividad del usuario
     * 
     * @param string $action
     * @param string $module
     * @param string $description
     * @param array $additionalData
     * @return void
     */
    protected function logActivity($action, $module, $description, $additionalData = [])
    {
        if (class_exists(\App\Models\UserActivity::class)) {
            \App\Models\UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => $action,
                'modulo' => $module,
                'descripcion' => $description,
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'datos_adicionales' => $additionalData
            ]);
        }
    }
    
    /**
     * Obtener nombre del usuario para mostrar
     * 
     * @return string
     */
    protected function getUserDisplayName()
    {
        $user = auth()->user();
        if (!$user) return 'Usuario desconocido';
        
        if ($user->empleado) {
            return $user->empleado->nombres . ' ' . $user->empleado->apellidos;
        }
        
        return $user->username;
    }
    
    /**
     * Preparar datos de venta/reparación con empleado
     * 
     * @param array $baseData
     * @return array
     */
    protected function prepareOperationData($baseData = [])
    {
        $user = auth()->user();
        if (!$user->empleado_id) {
            throw new \Exception('Usuario no asociado a un empleado');
        }
        
        return array_merge($baseData, [
            'empleado_id' => $user->empleado_id,
            'creado_por' => $user->id,
        ]);
    }
    
    /**
     * Generar código único para operaciones
     * 
     * @param string $prefix (V para ventas, R para reparaciones)
     * @param string $table
     * @return string
     */
    protected function generateOperationCode($prefix, $table)
    {
        $year = date('Y');
        $count = \DB::table($table)
            ->whereYear('created_at', $year)
            ->count();
        
        return $prefix . '-' . $year . '-' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
    }
}