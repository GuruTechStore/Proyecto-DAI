<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\SecurityLog;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    /**
     * Registrar nuevo usuario (solo para administradores)
     */
    public function register(Request $request)
    {
        $this->authorize('create', Usuario::class);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:usuarios,username',
            'email' => 'required|string|email|max:100|unique:usuarios,email',
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
            'empleado_id' => 'required|exists:empleados,id',
            'tipo_usuario' => 'required|string|in:empleado,gerente,supervisor,admin',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'direccion' => 'nullable|string|max:255',
            'role' => 'required|string|exists:roles,name',
            'activo' => 'boolean',
            'force_password_change' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el rol existe y el usuario tiene permisos para asignarlo
        $role = Role::where('name', $request->role)->first();
        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'El rol especificado no existe'
            ], 400);
        }

        // Verificar jerarquía de roles
        $currentUser = auth()->user();
        $currentUserMaxLevel = $currentUser->roles->max('nivel_permiso') ?? 0;
        
        if ($role->nivel_permiso >= $currentUserMaxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para asignar este rol'
            ], 403);
        }

        try {
            // Crear usuario
// Crear usuario
        $user = Usuario::create([
            'empleado_id' => $request->empleado_id,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password, // El mutator se encarga del hash
            'tipo_usuario' => $request->tipo_usuario,
            'activo' => $request->activo ?? true,
            'force_password_change' => $request->force_password_change ?? true,
        ]);
            // Asignar rol
            $user->assignRole($role);

            // Log creación de usuario
            SecurityLog::create([
                'evento' => 'user_created',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'created_by' => $currentUser->username,
                    'assigned_role' => $role->name,
                    'admin_id' => auth()->id()
                ]),
                'nivel_riesgo' => 'low'
            ]);

            // Log actividad del administrador
            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'user_created',
                'modulo' => 'usuarios',
                'detalles' => json_encode([
                    'new_user_id' => $user->id,
                    'new_user_username' => $user->username,
                    'assigned_role' => $role->name
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'nombres' => $user->nombres,
                        'apellidos' => $user->apellidos,
                        'roles' => $user->roles->pluck('name'),
                        'activo' => $user->activo,
                        'created_at' => $user->created_at
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            // Log error
            SecurityLog::create([
                'evento' => 'user_creation_failed',
                'usuario_id' => null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'error' => $e->getMessage(),
                    'attempted_by' => $currentUser->username,
                    'attempted_username' => $request->username
                ]),
                'nivel_riesgo' => 'medium'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario'
            ], 500);
        }
    }

    /**
     * Validar disponibilidad de username
     */
    public function checkUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Username requerido',
                'errors' => $validator->errors()
            ], 422);
        }

        $exists = Usuario::where('username', $request->username)->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'username' => $request->username,
                'available' => !$exists
            ]
        ]);
    }

    /**
     * Validar disponibilidad de email
     */
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email requerido',
                'errors' => $validator->errors()
            ], 422);
        }

        $exists = Usuario::where('email', $request->email)->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'email' => $request->email,
                'available' => !$exists
            ]
        ]);
    }

    /**
     * Obtener roles disponibles para asignar
     */
    public function getAvailableRoles()
    {
        $currentUser = auth()->user();
        $currentUserMaxLevel = $currentUser->roles->max('nivel_permiso') ?? 0;

        // Solo puede asignar roles de nivel inferior
        $availableRoles = Role::where('nivel_permiso', '<', $currentUserMaxLevel)
            ->orderBy('nivel_permiso', 'desc')
            ->get(['id', 'name', 'display_name', 'nivel_permiso', 'descripcion']);

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $availableRoles,
                'current_user_level' => $currentUserMaxLevel
            ]
        ]);
    }

    /**
     * Registro masivo de usuarios (CSV)
     */
    public function bulkRegister(Request $request)
    {
        $this->authorize('create', Usuario::class);

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:2048',
            'default_role' => 'required|string|exists:roles,name',
            'send_credentials' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo o datos incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $csvData = array_map('str_getcsv', file($file->getPathname()));
            $headers = array_shift($csvData);

            $created = 0;
            $errors = [];
            $defaultRole = Role::where('name', $request->default_role)->first();

            foreach ($csvData as $index => $row) {
                if (count($row) != count($headers)) {
                    $errors[] = "Fila " . ($index + 2) . ": Número de columnas incorrecto";
                    continue;
                }

                $userData = array_combine($headers, $row);
                
                // Validar datos básicos
                if (empty($userData['username']) || empty($userData['email'])) {
                    $errors[] = "Fila " . ($index + 2) . ": Username y email son requeridos";
                    continue;
                }

                // Verificar si ya existe
                if (Usuario::where('username', $userData['username'])->exists() ||
                    Usuario::where('email', $userData['email'])->exists()) {
                    $errors[] = "Fila " . ($index + 2) . ": Usuario ya existe";
                    continue;
                }

                // Crear usuario
                $tempPassword = $userData['password'] ?? str()->random(12);
                
                $user = Usuario::create([
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'password' => Hash::make($tempPassword),
                    'nombres' => $userData['nombres'] ?? '',
                    'apellidos' => $userData['apellidos'] ?? '',
                    'telefono' => $userData['telefono'] ?? null,
                    'activo' => true,
                    'force_password_change' => true,
                    'password_changed_at' => now(),
                    'created_by' => auth()->id()
                ]);

                $user->assignRole($defaultRole);
                $created++;
            }

            // Log registro masivo
            SecurityLog::create([
                'evento' => 'bulk_user_creation',
                'usuario_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'created_count' => $created,
                    'errors_count' => count($errors),
                    'default_role' => $defaultRole->name
                ]),
                'nivel_riesgo' => 'medium'
            ]);

            return response()->json([
                'success' => true,
                'message' => "Registro masivo completado: {$created} usuarios creados",
                'data' => [
                    'created' => $created,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el registro masivo: ' . $e->getMessage()
            ], 500);
        }
    }
}