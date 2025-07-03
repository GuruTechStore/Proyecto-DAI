<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use App\Models\SecurityLog;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Listar usuarios (con filtros y paginación)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Usuario::class);

        $query = Usuario::with(['roles', 'permissions']);

        // Aplicar filtros
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('activo', true);
            } elseif ($request->status === 'inactive') {
                $query->where('activo', false);
            } elseif ($request->status === 'blocked') {
                $query->where('blocked_until', '>', now());
            }
        }

        if ($request->has('verified')) {
            if ($request->verified === 'true') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($request->has('has_2fa')) {
            if ($request->has_2fa === 'true') {
                $query->whereNotNull('two_factor_secret');
            } else {
                $query->whereNull('two_factor_secret');
            }
        }

        if ($request->has('created_from')) {
            $query->where('created_at', '>=', $request->created_from);
        }

        if ($request->has('created_to')) {
            $query->where('created_at', '<=', $request->created_to);
        }

        // Ordenamiento
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        
        $allowedSorts = ['id', 'username', 'email', 'nombres', 'apellidos', 'created_at', 'last_login_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($request->per_page ?? 15, 50); // Máximo 50 por página
        $users = $query->paginate($perPage);

        // Log de consulta para auditoría
        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'users_list_viewed',
            'modulo' => 'usuarios',
            'detalles' => json_encode([
                'filters' => $request->only(['search', 'role', 'status', 'verified']),
                'sort' => ['by' => $sortBy, 'order' => $sortOrder],
                'results_count' => $users->total()
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'data' => UsuarioResource::collection($users),
            'meta' => [
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem()
                ],
                'filters_applied' => $request->only(['search', 'role', 'status', 'verified', 'has_2fa']),
                'available_roles' => Role::pluck('display_name', 'name')
            ]
        ]);
    }

    /**
     * Mostrar usuario específico
     */
    public function show(Usuario $user)
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'permissions', 'createdBy']);

        // Estadísticas del usuario
        $stats = [
            'last_login' => SecurityLog::where('usuario_id', $user->id)
                ->where('evento', 'login_success')
                ->latest()
                ->first()?->created_at,
            'total_logins' => SecurityLog::where('usuario_id', $user->id)
                ->where('evento', 'login_success')
                ->count(),
            'failed_logins_last_30_days' => SecurityLog::where('usuario_id', $user->id)
                ->where('evento', 'login_failed')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'activities_last_30_days' => UserActivity::where('usuario_id', $user->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'security_events_high_risk' => SecurityLog::where('usuario_id', $user->id)
                ->where('nivel_riesgo', 'high')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'active_sessions' => $user->tokens()->count(),
            'password_age_days' => $user->password_changed_at 
                ? now()->diffInDays($user->password_changed_at) 
                : null,
        ];

        // Información de seguridad
        $security = [
            'two_factor_enabled' => !is_null($user->two_factor_secret),
            'email_verified' => !is_null($user->email_verified_at),
            'is_blocked' => $user->blocked_until && $user->blocked_until > now(),
            'blocked_until' => $user->blocked_until,
            'blocked_reason' => $user->blocked_reason,
            'password_expires_soon' => $user->password_changed_at && 
                now()->diffInDays($user->password_changed_at) > 80,
            'force_password_change' => $user->force_password_change
        ];

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'user_profile_viewed',
            'modulo' => 'usuarios',
            'detalles' => json_encode([
                'viewed_user_id' => $user->id,
                'viewed_username' => $user->username
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UsuarioResource($user),
                'stats' => $stats,
                'security' => $security
            ]
        ]);
    }

    /**
     * Crear nuevo usuario
     */
    public function store(Request $request)
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
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'direccion' => 'nullable|string|max:255',
            'role' => 'required|string|exists:roles,name',
            'activo' => 'boolean',
            'force_password_change' => 'boolean',
            'email_verified' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar jerarquía de roles
        $role = Role::where('name', $request->role)->first();
        $currentUser = auth()->user();
        $currentUserMaxLevel = $currentUser->roles->max('nivel_permiso') ?? 0;
        
        if ($role->nivel_permiso >= $currentUserMaxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para asignar este rol'
            ], 403);
        }

        try {
            $user = Usuario::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'direccion' => $request->direccion,
                'activo' => $request->activo ?? true,
                'force_password_change' => $request->force_password_change ?? true,
                'password_changed_at' => now(),
                'email_verified_at' => $request->email_verified ? now() : null,
                'created_by' => auth()->id()
            ]);

            $user->assignRole($role);

            SecurityLog::create([
                'evento' => 'user_created_api',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'created_by' => $currentUser->username,
                    'admin_id' => auth()->id(),
                    'assigned_role' => $role->name,
                    'via_api' => true
                ]),
                'nivel_riesgo' => 'low'
            ]);

            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'user_created',
                'modulo' => 'usuarios',
                'detalles' => json_encode([
                    'new_user_id' => $user->id,
                    'new_user_username' => $user->username,
                    'assigned_role' => $role->name,
                    'via' => 'api'
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data' => new UsuarioResource($user->fresh(['roles', 'permissions']))
            ], 201);

        } catch (\Exception $e) {
            SecurityLog::create([
                'evento' => 'user_creation_failed_api',
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
                'message' => 'Error interno al crear el usuario'
            ], 500);
        }
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, Usuario $user)
    {
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string|max:50|unique:usuarios,username,' . $user->id,
            'email' => 'sometimes|email|max:100|unique:usuarios,email,' . $user->id,
            'nombres' => 'sometimes|string|max:100',
            'apellidos' => 'sometimes|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'direccion' => 'nullable|string|max:255',
            'activo' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldData = $user->toArray();
        $changes = [];

        foreach ($request->only(['username', 'email', 'nombres', 'apellidos', 'telefono', 'fecha_nacimiento', 'direccion', 'activo']) as $field => $value) {
            if ($user->$field != $value) {
                $changes[$field] = [
                    'old' => $user->$field,
                    'new' => $value
                ];
                $user->$field = $value;
            }
        }

        if (empty($changes)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cambios para actualizar'
            ], 400);
        }

        if (isset($changes['email'])) {
            $user->email_verified_at = null;
        }

        $user->save();

        SecurityLog::create([
            'evento' => 'user_updated_api',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'updated_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'changes' => $changes,
                'via_api' => true
            ]),
            'nivel_riesgo' => 'low'
        ]);

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'user_updated',
            'modulo' => 'usuarios',
            'detalles' => json_encode([
                'target_user_id' => $user->id,
                'target_username' => $user->username,
                'changes' => array_keys($changes)
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'data' => [
                'user' => new UsuarioResource($user->fresh(['roles', 'permissions'])),
                'changes' => $changes
            ]
        ]);
    }

    /**
     * Eliminar usuario (desactivar)
     */
    public function destroy(Request $request, Usuario $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar tu propia cuenta'
            ], 400);
        }

        // Verificar si es el último Super Admin
        if ($user->hasRole('Super Admin')) {
            $superAdminCount = Usuario::whereHas('roles', function($q) {
                $q->where('name', 'Super Admin');
            })->where('activo', true)->count();

            if ($superAdminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el último Super Admin del sistema'
                ], 400);
            }
        }

        $user->update(['activo' => false]);
        $user->tokens()->delete();

        SecurityLog::create([
            'evento' => 'user_deleted_api',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'deleted_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'user_roles' => $user->roles->pluck('name'),
                'via_api' => true
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'user_deleted',
            'modulo' => 'usuarios',
            'detalles' => json_encode([
                'target_user_id' => $user->id,
                'target_username' => $user->username
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario desactivado correctamente'
        ]);
    }

    /**
     * Asignar rol a usuario
     */
    public function assignRole(Request $request, Usuario $user)
    {
        $this->authorize('assignRole', $user);

        $validator = Validator::make($request->all(), [
            'role' => 'required|string|exists:roles,name'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Rol inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        $role = Role::where('name', $request->role)->first();
        $currentUser = auth()->user();
        $currentUserMaxLevel = $currentUser->roles->max('nivel_permiso') ?? 0;

        if ($role->nivel_permiso >= $currentUserMaxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para asignar este rol'
            ], 403);
        }

        if ($user->hasRole($role)) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario ya tiene este rol asignado'
            ], 400);
        }

        $user->assignRole($role);

        SecurityLog::create([
            'evento' => 'role_assigned_api',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'assigned_by' => $currentUser->username,
                'admin_id' => auth()->id(),
                'role_name' => $role->name,
                'role_level' => $role->nivel_permiso,
                'via_api' => true
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'role_assigned',
            'modulo' => 'usuarios',
            'detalles' => json_encode([
                'target_user_id' => $user->id,
                'target_username' => $user->username,
                'role_assigned' => $role->name
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => "Rol '{$role->display_name}' asignado correctamente",
            'data' => new UsuarioResource($user->fresh(['roles', 'permissions']))
        ]);
    }

    /**
     * Remover rol de usuario
     */
    public function removeRole(Request $request, Usuario $user)
    {
        $this->authorize('removeRole', $user);

        $validator = Validator::make($request->all(), [
            'role' => 'required|string|exists:roles,name'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Rol inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        $role = Role::where('name', $request->role)->first();

        if (!$user->hasRole($role)) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no tiene este rol asignado'
            ], 400);
        }

        // Verificar si es el último Super Admin
        if ($role->name === 'Super Admin') {
            $superAdminCount = Usuario::whereHas('roles', function($q) {
                $q->where('name', 'Super Admin');
            })->where('activo', true)->count();

            if ($superAdminCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede remover el rol del último Super Admin'
                ], 400);
            }
        }

        $user->removeRole($role);

        SecurityLog::create([
            'evento' => 'role_removed_api',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'removed_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'role_name' => $role->name,
                'role_level' => $role->nivel_permiso,
                'via_api' => true
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        return response()->json([
            'success' => true,
            'message' => "Rol '{$role->display_name}' removido correctamente",
            'data' => new UsuarioResource($user->fresh(['roles', 'permissions']))
        ]);
    }

    /**
     * Obtener permisos de usuario
     */
    public function permissions(Usuario $user)
    {
        $this->authorize('view', $user);

        $directPermissions = $user->permissions;
        $rolePermissions = $user->getPermissionsViaRoles();
        $allPermissions = $user->getAllPermissions();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'roles' => $user->roles->map(function($role) {
                        return [
                            'name' => $role->name,
                            'display_name' => $role->display_name,
                            'nivel_permiso' => $role->nivel_permiso
                        ];
                    })
                ],
                'permissions' => [
                    'direct' => $directPermissions->map(function($perm) {
                        return [
                            'name' => $perm->name,
                            'display_name' => $perm->display_name ?? $perm->name,
                            'category' => $perm->category ?? 'general'
                        ];
                    }),
                    'via_roles' => $rolePermissions->map(function($perm) {
                        return [
                            'name' => $perm->name,
                            'display_name' => $perm->display_name ?? $perm->name,
                            'category' => $perm->category ?? 'general'
                        ];
                    }),
                    'all' => $allPermissions->map(function($perm) {
                        return [
                            'name' => $perm->name,
                            'display_name' => $perm->display_name ?? $perm->name,
                            'category' => $perm->category ?? 'general'
                        ];
                    })
                ],
                'summary' => [
                    'direct_count' => $directPermissions->count(),
                    'via_roles_count' => $rolePermissions->count(),
                    'total_unique' => $allPermissions->count()
                ]
            ]
        ]);
    }

    /**
     * Obtener actividad del usuario
     */
    public function activity(Request $request, Usuario $user)
    {
        $this->authorize('view', $user);

        $query = UserActivity::where('usuario_id', $user->id);

        // Filtros
        if ($request->has('module')) {
            $query->where('modulo', $request->module);
        }

        if ($request->has('action')) {
            $query->where('accion', $request->action);
        }

        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $perPage = min($request->per_page ?? 20, 100);
        $activities = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Estadísticas de actividad
        $stats = [
            'total_activities' => UserActivity::where('usuario_id', $user->id)->count(),
            'activities_last_7_days' => UserActivity::where('usuario_id', $user->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
            'activities_last_30_days' => UserActivity::where('usuario_id', $user->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'most_used_modules' => UserActivity::where('usuario_id', $user->id)
                ->selectRaw('modulo, COUNT(*) as count')
                ->groupBy('modulo')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->pluck('count', 'modulo'),
            'most_common_actions' => UserActivity::where('usuario_id', $user->id)
                ->selectRaw('accion, COUNT(*) as count')
                ->groupBy('accion')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->pluck('count', 'accion')
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'activities' => $activities,
                'stats' => $stats
            ]
        ]);
    }

    /**
     * Bloquear/desbloquear usuario
     */
    public function toggleBlock(Request $request, Usuario $user)
    {
        $this->authorize('lock', $user);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes bloquear tu propia cuenta'
            ], 400);
        }

        $isCurrentlyBlocked = $user->blocked_until && $user->blocked_until > now();

        if ($isCurrentlyBlocked) {
            // Desbloquear
            $user->update([
                'blocked_until' => null,
                'blocked_reason' => null
            ]);

            SecurityLog::create([
                'evento' => 'user_unblocked_api',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'unblocked_by' => auth()->user()->username,
                    'admin_id' => auth()->id(),
                    'via_api' => true
                ]),
                'nivel_riesgo' => 'medium'
            ]);

            $message = 'Usuario desbloqueado correctamente';
        } else {
            // Bloquear
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|max:255',
                'duration_hours' => 'nullable|integer|min:1|max:8760' // máximo 1 año
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos para el bloqueo',
                    'errors' => $validator->errors()
                ], 422);
            }

            $blockUntil = $request->duration_hours 
                ? now()->addHours($request->duration_hours)
                : now()->addDay();

            $user->update([
                'blocked_until' => $blockUntil,
                'blocked_reason' => $request->reason,
                'failed_login_attempts' => 0
            ]);

            // Revocar tokens activos
            $user->tokens()->delete();

            SecurityLog::create([
                'evento' => 'user_blocked_api',
                'usuario_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'blocked_by' => auth()->user()->username,
                    'admin_id' => auth()->id(),
                    'reason' => $request->reason,
                    'blocked_until' => $blockUntil,
                    'duration_hours' => $request->duration_hours ?? 24,
                    'via_api' => true
                ]),
                'nivel_riesgo' => 'high'
            ]);

            $message = 'Usuario bloqueado correctamente';
        }

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => $isCurrentlyBlocked ? 'user_unblocked' : 'user_blocked',
            'modulo' => 'usuarios',
            'detalles' => json_encode([
                'target_user_id' => $user->id,
                'target_username' => $user->username,
                'reason' => $request->reason ?? null
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'user' => new UsuarioResource($user->fresh(['roles', 'permissions'])),
                'is_blocked' => !$isCurrentlyBlocked,
                'blocked_until' => $user->blocked_until,
                'blocked_reason' => $user->blocked_reason
            ]
        ]);
    }

    /**
     * Forzar cambio de contraseña
     */
    public function forcePasswordChange(Request $request, Usuario $user)
    {
        $this->authorize('forcePasswordChange', $user);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes forzar el cambio de tu propia contraseña'
            ], 400);
        }

        $user->update([
            'force_password_change' => true
        ]);

        // Revocar todos los tokens del usuario
        $user->tokens()->delete();

        SecurityLog::create([
            'evento' => 'password_change_forced_api',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'forced_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'via_api' => true
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'password_change_forced',
            'modulo' => 'usuarios',
            'detalles' => json_encode([
                'target_user_id' => $user->id,
                'target_username' => $user->username
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Se ha forzado el cambio de contraseña. El usuario deberá cambiarla en su próximo login.',
            'data' => new UsuarioResource($user->fresh(['roles', 'permissions']))
        ]);
    }

    /**
     * Obtener usuarios disponibles para asignar tareas/proyectos
     */
    public function available(Request $request)
    {
        $this->authorize('viewAny', Usuario::class);

        $query = Usuario::where('activo', true)
            ->whereNull('blocked_until')
            ->orWhere('blocked_until', '<=', now());

        if ($request->has('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->has('exclude')) {
            $excludeIds = is_array($request->exclude) ? $request->exclude : [$request->exclude];
            $query->whereNotIn('id', $excludeIds);
        }

        $users = $query->with('roles')
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->nombres . ' ' . $user->apellidos,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('display_name'),
                    'nivel_permiso' => $user->roles->max('nivel_permiso') ?? 0
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $users,
            'meta' => [
                'total' => $users->count(),
                'filters_applied' => $request->only(['role', 'exclude'])
            ]
        ]);
    }
}