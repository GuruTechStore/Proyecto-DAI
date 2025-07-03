<?php

namespace App\Http\Controllers;

use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use App\Models\SecurityLog;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Listar usuarios con filtros avanzados
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Usuario::class);

        $query = Usuario::with(['empleado', 'roles', 'permissions']);
        // Filtros
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhereHas('empleado', function($eq) use ($search) {
                    $eq->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%");
                });
            });
        }
        if ($request->has('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->has('status')) {
            $query->where('activo', $request->status === 'active');
        }

        if ($request->has('verified')) {
            if ($request->verified === 'true') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
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
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => UsuarioResource::collection($users),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage()
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
            'login_count' => SecurityLog::where('usuario_id', $user->id)
                ->where('evento', 'login_success')
                ->count(),
            'failed_logins' => SecurityLog::where('usuario_id', $user->id)
                ->where('evento', 'login_failed')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'activities_count' => UserActivity::where('usuario_id', $user->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'security_events' => SecurityLog::where('usuario_id', $user->id)
                ->where('nivel_riesgo', 'high')
                ->where('created_at', '>=', now()->subDays(30))
                ->count()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UsuarioResource($user),
                'stats' => $stats
            ]
        ]);
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

        // Actualizar solo campos enviados
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

        // Verificar cambio de email
        if (isset($changes['email'])) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Log cambios
        SecurityLog::create([
            'evento' => 'user_updated',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'updated_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'changes' => $changes
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
     * Eliminar usuario (soft delete)
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
        $user->tokens()->delete(); // Revocar tokens

        SecurityLog::create([
            'evento' => 'user_deleted',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'deleted_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'user_roles' => $user->roles->pluck('name')
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
     * Restaurar usuario
     */
    public function restore(Request $request, Usuario $user)
    {
        $this->authorize('restore', $user);

        $user->update(['activo' => true]);

        SecurityLog::create([
            'evento' => 'user_restored',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'restored_by' => auth()->user()->username,
                'admin_id' => auth()->id()
            ]),
            'nivel_riesgo' => 'low'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario restaurado correctamente',
            'data' => new UsuarioResource($user->fresh(['roles', 'permissions']))
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

        // Verificar jerarquía
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
            'evento' => 'role_assigned',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'assigned_by' => $currentUser->username,
                'admin_id' => auth()->id(),
                'role_name' => $role->name,
                'role_level' => $role->nivel_permiso
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
            'data' => [
                'user' => new UsuarioResource($user->fresh(['roles', 'permissions'])),
                'assigned_role' => [
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'nivel_permiso' => $role->nivel_permiso
                ]
            ]
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
            'evento' => 'role_removed',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'removed_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'role_name' => $role->name,
                'role_level' => $role->nivel_permiso
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
                    'roles' => $user->roles->pluck('name')
                ],
                'direct_permissions' => $directPermissions->pluck('name'),
                'role_permissions' => $rolePermissions->pluck('name'),
                'all_permissions' => $allPermissions->pluck('name'),
                'permissions_count' => $allPermissions->count()
            ]
        ]);
    }

    /**
     * Obtener actividad de usuario
     */
    public function activity(Request $request, Usuario $user)
    {
        $this->authorize('view', $user);

        $activities = UserActivity::where('usuario_id', $user->id)
            ->when($request->module, function($q) use ($request) {
                $q->where('modulo', $request->module);
            })
            ->when($request->action, function($q) use ($request) {
                $q->where('accion', $request->action);
            })
            ->when($request->from_date, function($q) use ($request) {
                $q->where('created_at', '>=', $request->from_date);
            })
            ->when($request->to_date, function($q) use ($request) {
                $q->where('created_at', '<=', $request->to_date);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    /**
     * Bloquear usuario temporalmente
     */
    public function lockUser(Request $request, Usuario $user)
    {
        $this->authorize('lock', $user);

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
            'duration_minutes' => 'nullable|integer|min:1|max:10080' // máximo 1 semana
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $lockUntil = $request->duration_minutes 
            ? now()->addMinutes($request->duration_minutes)
            : now()->addDay(); // 24 horas por defecto

        $user->update([
            'blocked_until' => $lockUntil,
            'blocked_reason' => $request->reason
        ]);

        // Revocar tokens activos
        $user->tokens()->delete();

        SecurityLog::create([
            'evento' => 'user_locked',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'locked_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'reason' => $request->reason,
                'locked_until' => $lockUntil,
                'duration_minutes' => $request->duration_minutes ?? 1440
            ]),
            'nivel_riesgo' => 'high'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario bloqueado correctamente',
            'data' => [
                'locked_until' => $lockUntil,
                'reason' => $request->reason
            ]
        ]);
    }

    /**
     * Desbloquear usuario
     */
    public function unlockUser(Request $request, Usuario $user)
    {
        $this->authorize('unlock', $user);

        if (!$user->blocked_until || $user->blocked_until <= now()) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no está bloqueado'
            ], 400);
        }

        $user->update([
            'blocked_until' => null,
            'blocked_reason' => null
        ]);

        SecurityLog::create([
            'evento' => 'user_unlocked',
            'usuario_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'unlocked_by' => auth()->user()->username,
                'admin_id' => auth()->id()
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario desbloqueado correctamente'
        ]);
    }

    /**
     * Estadísticas generales de usuarios
     */
    public function stats()
    {
        $this->authorize('viewAny', Usuario::class);

        $stats = [
            'total_users' => Usuario::count(),
            'active_users' => Usuario::where('activo', true)->count(),
            'inactive_users' => Usuario::where('activo', false)->count(),
            'verified_users' => Usuario::whereNotNull('email_verified_at')->count(),
            'unverified_users' => Usuario::whereNull('email_verified_at')->count(),
            'locked_users' => Usuario::where('blocked_until', '>', now())->count(),
            'users_with_2fa' => Usuario::whereNotNull('two_factor_secret')->count(),
            'recent_registrations' => Usuario::where('created_at', '>=', now()->subDays(7))->count(),
            'users_by_role' => Usuario::select('roles.name', \DB::raw('count(*) as count'))
                ->join('model_has_roles', 'usuarios.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_type', Usuario::class)
                ->groupBy('roles.name')
                ->pluck('count', 'roles.name')
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}