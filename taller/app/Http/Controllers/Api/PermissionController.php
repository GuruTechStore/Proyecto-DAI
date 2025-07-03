<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SecurityLog;
use App\Models\UserActivity;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Listar todos los permisos
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Permission::class);

        $query = Permission::query();

        // Filtros
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->per_page ?? 50, 100);
        $permissions = $query->orderBy('category')
            ->orderBy('name')
            ->paginate($perPage);

        // Agrupar por categoría para mejor visualización
        $permissionsByCategory = $permissions->getCollection()->groupBy('category');

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'permissions_list_viewed',
            'modulo' => 'permisos',
            'detalles' => json_encode([
                'total_permissions' => $permissions->total(),
                'filters' => $request->only(['category', 'search'])
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'permissions' => $permissions,
                'permissions_by_category' => $permissionsByCategory,
                'categories' => Permission::distinct('category')->pluck('category')->filter()
            ],
            'meta' => [
                'pagination' => [
                    'total' => $permissions->total(),
                    'per_page' => $permissions->perPage(),
                    'current_page' => $permissions->currentPage(),
                    'last_page' => $permissions->lastPage()
                ]
            ]
        ]);
    }

    /**
     * Mostrar permiso específico
     */
    public function show(Permission $permission)
    {
        $this->authorize('view', $permission);

        // Obtener roles que tienen este permiso
        $rolesWithPermission = Role::whereHas('permissions', function($q) use ($permission) {
            $q->where('permissions.id', $permission->id);
        })->get(['id', 'name', 'display_name', 'nivel_permiso']);

        // Obtener usuarios que tienen este permiso directamente
        $usersWithDirectPermission = $permission->users()
            ->with('roles')
            ->get(['id', 'username', 'nombres', 'apellidos', 'email']);

        return response()->json([
            'success' => true,
            'data' => [
                'permission' => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name,
                    'description' => $permission->description,
                    'category' => $permission->category,
                    'created_at' => $permission->created_at,
                    'updated_at' => $permission->updated_at
                ],
                'roles_with_permission' => $rolesWithPermission,
                'users_with_direct_permission' => $usersWithDirectPermission,
                'usage_stats' => [
                    'roles_count' => $rolesWithPermission->count(),
                    'direct_users_count' => $usersWithDirectPermission->count(),
                    'total_users_with_permission' => $this->getTotalUsersWithPermission($permission)
                ]
            ]
        ]);
    }

    /**
     * Crear nuevo permiso
     */
    public function store(Request $request)
    {
        $this->authorize('create', Permission::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $permission = Permission::create([
                'name' => $request->name,
                'display_name' => $request->display_name ?? $request->name,
                'description' => $request->description,
                'category' => $request->category ?? 'general',
                'guard_name' => 'web'
            ]);

            SecurityLog::create([
                'evento' => 'permission_created',
                'usuario_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'permission_name' => $permission->name,
                    'permission_id' => $permission->id,
                    'created_by' => auth()->user()->username
                ]),
                'nivel_riesgo' => 'low'
            ]);

            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'permission_created',
                'modulo' => 'permisos',
                'detalles' => json_encode([
                    'permission_name' => $permission->name,
                    'category' => $permission->category
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permiso creado correctamente',
                'data' => $permission
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el permiso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar permiso
     */
    public function update(Request $request, Permission $permission)
    {
        $this->authorize('update', $permission);

        $validator = Validator::make($request->all(), [
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldData = $permission->toArray();
        $changes = [];

        foreach (['display_name', 'description', 'category'] as $field) {
            if ($request->has($field) && $permission->$field != $request->$field) {
                $changes[$field] = [
                    'old' => $permission->$field,
                    'new' => $request->$field
                ];
                $permission->$field = $request->$field;
            }
        }

        if (empty($changes)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cambios para actualizar'
            ], 400);
        }

        $permission->save();

        SecurityLog::create([
            'evento' => 'permission_updated',
            'usuario_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'permission_name' => $permission->name,
                'permission_id' => $permission->id,
                'changes' => $changes,
                'updated_by' => auth()->user()->username
            ]),
            'nivel_riesgo' => 'low'
        ]);

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'permission_updated',
            'modulo' => 'permisos',
            'detalles' => json_encode([
                'permission_name' => $permission->name,
                'changes' => array_keys($changes)
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permiso actualizado correctamente',
            'data' => [
                'permission' => $permission,
                'changes' => $changes
            ]
        ]);
    }

    /**
     * Eliminar permiso
     */
    public function destroy(Request $request, Permission $permission)
    {
        $this->authorize('delete', $permission);

        // Verificar si el permiso está en uso
        $rolesCount = $permission->roles()->count();
        $usersCount = $permission->users()->count();

        if ($rolesCount > 0 || $usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "No se puede eliminar el permiso. Está asignado a {$rolesCount} roles y {$usersCount} usuarios.",
                'data' => [
                    'roles_count' => $rolesCount,
                    'users_count' => $usersCount
                ]
            ], 400);
        }

        $permissionName = $permission->name;
        $permissionId = $permission->id;

        $permission->delete();

        SecurityLog::create([
            'evento' => 'permission_deleted',
            'usuario_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'permission_name' => $permissionName,
                'permission_id' => $permissionId,
                'deleted_by' => auth()->user()->username
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'permission_deleted',
            'modulo' => 'permisos',
            'detalles' => json_encode([
                'permission_name' => $permissionName
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permiso eliminado correctamente'
        ]);
    }

    /**
     * Listar todos los roles
     */
    public function roles(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $query = Role::with('permissions');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        if ($request->has('nivel_permiso')) {
            $query->where('nivel_permiso', $request->nivel_permiso);
        }

        $roles = $query->orderBy('nivel_permiso', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'descripcion' => $role->descripcion,
                    'nivel_permiso' => $role->nivel_permiso,
                    'permissions_count' => $role->permissions->count(),
                    'users_count' => $role->users()->count(),
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $roles,
            'meta' => [
                'total_roles' => $roles->count(),
                'filters_applied' => $request->only(['search', 'nivel_permiso'])
            ]
        ]);
    }

    /**
     * Mostrar rol específico
     */
    public function showRole(Role $role)
    {
        $this->authorize('view', $role);

        $role->load('permissions', 'users');

        return response()->json([
            'success' => true,
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'descripcion' => $role->descripcion,
                    'nivel_permiso' => $role->nivel_permiso,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at
                ],
                'permissions' => $role->permissions->map(function($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $permission->display_name,
                        'category' => $permission->category
                    ];
                }),
                'users' => $role->users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'nombres' => $user->nombres,
                        'apellidos' => $user->apellidos,
                        'email' => $user->email,
                        'activo' => $user->activo
                    ];
                }),
                'stats' => [
                    'permissions_count' => $role->permissions->count(),
                    'users_count' => $role->users->count(),
                    'active_users_count' => $role->users->where('activo', true)->count()
                ]
            ]
        ]);
    }

    /**
     * Crear nuevo rol
     */
    public function createRole(Request $request)
    {
        $this->authorize('create', Role::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'nivel_permiso' => 'required|integer|min:1|max:10',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar jerarquía - no puede crear roles de nivel igual o superior
        $currentUserMaxLevel = auth()->user()->roles->max('nivel_permiso') ?? 0;
        if ($request->nivel_permiso >= $currentUserMaxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes crear roles de nivel igual o superior al tuyo'
            ], 403);
        }

        try {
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'descripcion' => $request->descripcion,
                'nivel_permiso' => $request->nivel_permiso,
                'guard_name' => 'web'
            ]);

            // Asignar permisos si se proporcionaron
            if ($request->has('permissions') && !empty($request->permissions)) {
                $role->givePermissionTo($request->permissions);
            }

            SecurityLog::create([
                'evento' => 'role_created',
                'usuario_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'role_name' => $role->name,
                    'role_id' => $role->id,
                    'nivel_permiso' => $role->nivel_permiso,
                    'permissions_assigned' => $request->permissions ?? [],
                    'created_by' => auth()->user()->username
                ]),
                'nivel_riesgo' => 'medium'
            ]);

            UserActivity::create([
                'usuario_id' => auth()->id(),
                'accion' => 'role_created',
                'modulo' => 'roles',
                'detalles' => json_encode([
                    'role_name' => $role->name,
                    'nivel_permiso' => $role->nivel_permiso,
                    'permissions_count' => count($request->permissions ?? [])
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rol creado correctamente',
                'data' => [
                    'role' => $role->load('permissions'),
                    'permissions_assigned' => $role->permissions->pluck('name')
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar rol
     */
    public function updateRole(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        $validator = Validator::make($request->all(), [
            'display_name' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'nivel_permiso' => 'sometimes|integer|min:1|max:10',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar jerarquía
        $currentUserMaxLevel = auth()->user()->roles->max('nivel_permiso') ?? 0;
        if ($role->nivel_permiso >= $currentUserMaxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes modificar roles de nivel igual o superior al tuyo'
            ], 403);
        }

        if ($request->has('nivel_permiso') && $request->nivel_permiso >= $currentUserMaxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes asignar un nivel igual o superior al tuyo'
            ], 403);
        }

        $oldData = $role->toArray();
        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $changes = [];

        // Actualizar campos básicos
        foreach (['display_name', 'descripcion', 'nivel_permiso'] as $field) {
            if ($request->has($field) && $role->$field != $request->$field) {
                $changes[$field] = [
                    'old' => $role->$field,
                    'new' => $request->$field
                ];
                $role->$field = $request->$field;
            }
        }

        $role->save();

        // Actualizar permisos si se proporcionaron
        if ($request->has('permissions')) {
            $newPermissions = $request->permissions ?? [];
            
            if ($oldPermissions != $newPermissions) {
                $role->syncPermissions($newPermissions);
                $changes['permissions'] = [
                    'old' => $oldPermissions,
                    'new' => $newPermissions,
                    'added' => array_diff($newPermissions, $oldPermissions),
                    'removed' => array_diff($oldPermissions, $newPermissions)
                ];
            }
        }

        if (empty($changes)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cambios para actualizar'
            ], 400);
        }

        SecurityLog::create([
            'evento' => 'role_updated',
            'usuario_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'role_name' => $role->name,
                'role_id' => $role->id,
                'changes' => $changes,
                'updated_by' => auth()->user()->username
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'role_updated',
            'modulo' => 'roles',
            'detalles' => json_encode([
                'role_name' => $role->name,
                'changes' => array_keys($changes)
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado correctamente',
            'data' => [
                'role' => $role->fresh(['permissions']),
                'changes' => $changes
            ]
        ]);
    }

    /**
     * Eliminar rol
     */
    public function deleteRole(Request $request, Role $role)
    {
        $this->authorize('delete', $role);

        // Verificar jerarquía
        $currentUserMaxLevel = auth()->user()->roles->max('nivel_permiso') ?? 0;
        if ($role->nivel_permiso >= $currentUserMaxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar roles de nivel igual o superior al tuyo'
            ], 403);
        }

        // Verificar si el rol está en uso
        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "No se puede eliminar el rol. Está asignado a {$usersCount} usuarios.",
                'data' => [
                    'users_count' => $usersCount,
                    'users_with_role' => $role->users()->pluck('username')
                ]
            ], 400);
        }

        // Verificar si es un rol del sistema
        $systemRoles = ['Super Admin', 'Gerente', 'Supervisor', 'Técnico Senior', 'Técnico', 'Vendedor Senior', 'Vendedor', 'Empleado'];
        if (in_array($role->name, $systemRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden eliminar los roles del sistema'
            ], 400);
        }

        $roleName = $role->name;
        $roleId = $role->id;

        $role->delete();

        SecurityLog::create([
            'evento' => 'role_deleted',
            'usuario_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'role_name' => $roleName,
                'role_id' => $roleId,
                'deleted_by' => auth()->user()->username
            ]),
            'nivel_riesgo' => 'high'
        ]);

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'role_deleted',
            'modulo' => 'roles',
            'detalles' => json_encode([
                'role_name' => $roleName
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rol eliminado correctamente'
        ]);
    }

    /**
     * Asignar/remover permisos a rol
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Permisos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar jerarquía
        $currentUserMaxLevel = auth()->user()->roles->max('nivel_permiso') ?? 0;
        if ($role->nivel_permiso >= $currentUserMaxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes modificar permisos de roles de nivel igual o superior al tuyo'
            ], 403);
        }

        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $newPermissions = $request->permissions;

        $role->syncPermissions($newPermissions);

        $addedPermissions = array_diff($newPermissions, $oldPermissions);
        $removedPermissions = array_diff($oldPermissions, $newPermissions);

        SecurityLog::create([
            'evento' => 'role_permissions_updated',
            'usuario_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'role_name' => $role->name,
                'role_id' => $role->id,
                'added_permissions' => $addedPermissions,
                'removed_permissions' => $removedPermissions,
                'total_permissions' => count($newPermissions),
                'updated_by' => auth()->user()->username
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        UserActivity::create([
            'usuario_id' => auth()->id(),
            'accion' => 'role_permissions_updated',
            'modulo' => 'roles',
            'detalles' => json_encode([
                'role_name' => $role->name,
                'added_count' => count($addedPermissions),
                'removed_count' => count($removedPermissions)
            ]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permisos del rol actualizados correctamente',
            'data' => [
                'role' => $role->fresh(['permissions']),
                'changes' => [
                    'added_permissions' => $addedPermissions,
                    'removed_permissions' => $removedPermissions,
                    'total_permissions' => count($newPermissions)
                ]
            ]
        ]);
    }

    /**
     * Obtener matriz de permisos (roles vs permisos)
     */
    public function permissionMatrix()
    {
        $this->authorize('viewAny', Permission::class);

        $roles = Role::with('permissions')->orderBy('nivel_permiso', 'desc')->get();
        $permissions = Permission::orderBy('category')->orderBy('name')->get();

        $matrix = [];
        foreach ($roles as $role) {
            $rolePermissions = $role->permissions->pluck('name')->toArray();
            $matrix[$role->name] = [
                'role_info' => [
                    'id' => $role->id,
                    'display_name' => $role->display_name,
                    'nivel_permiso' => $role->nivel_permiso,
                    'users_count' => $role->users()->count()
                ],
                'permissions' => []
            ];
            
            foreach ($permissions as $permission) {
                $matrix[$role->name]['permissions'][$permission->name] = in_array($permission->name, $rolePermissions);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'matrix' => $matrix,
                'roles' => $roles->map(function($role) {
                    return [
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                        'nivel_permiso' => $role->nivel_permiso
                    ];
                }),
                'permissions' => $permissions->groupBy('category'),
                'summary' => [
                    'total_roles' => $roles->count(),
                    'total_permissions' => $permissions->count(),
                    'categories' => $permissions->groupBy('category')->keys()
                ]
            ]
        ]);
    }

    /**
     * Obtener estadísticas de permisos y roles
     */
    public function stats()
    {
        $this->authorize('viewAny', Permission::class);

        $stats = [
            'permissions' => [
                'total' => Permission::count(),
                'by_category' => Permission::selectRaw('category, COUNT(*) as count')
                    ->groupBy('category')
                    ->pluck('count', 'category'),
                'unused' => Permission::whereDoesntHave('roles')
                    ->whereDoesntHave('users')
                    ->count()
            ],
            'roles' => [
                'total' => Role::count(),
                'by_level' => Role::selectRaw('nivel_permiso, COUNT(*) as count')
                    ->groupBy('nivel_permiso')
                    ->orderBy('nivel_permiso', 'desc')
                    ->pluck('count', 'nivel_permiso'),
                'with_users' => Role::whereHas('users')->count(),
                'without_users' => Role::whereDoesntHave('users')->count()
            ],
            'assignments' => [
                'total_role_permissions' => DB::table('role_has_permissions')->count(),
                'total_user_permissions' => DB::table('model_has_permissions')->count(),
                'avg_permissions_per_role' => round(Role::withCount('permissions')->avg('permissions_count'), 2)
            ],
            'usage_analysis' => [
                'most_assigned_permissions' => Permission::withCount('roles')
                    ->orderBy('roles_count', 'desc')
                    ->limit(10)
                    ->get(['name', 'display_name', 'roles_count']),
                'least_used_permissions' => Permission::withCount('roles')
                    ->having('roles_count', '<=', 1)
                    ->orderBy('roles_count', 'asc')
                    ->limit(10)
                    ->get(['name', 'display_name', 'roles_count']),
                'roles_with_most_permissions' => Role::withCount('permissions')
                    ->orderBy('permissions_count', 'desc')
                    ->limit(10)
                    ->get(['name', 'display_name', 'permissions_count']),
                'users_with_direct_permissions' => Usuario::whereHas('permissions')
                    ->with('permissions')
                    ->get()
                    ->map(function($user) {
                        return [
                            'username' => $user->username,
                            'permissions_count' => $user->permissions->count(),
                            'permissions' => $user->permissions->pluck('name')
                        ];
                    })
            ],
            'security_metrics' => [
                'admin_roles_count' => Role::where('nivel_permiso', '>=', 8)->count(),
                'users_with_admin_access' => Usuario::whereHas('roles', function($q) {
                    $q->where('nivel_permiso', '>=', 8);
                })->count(),
                'dangerous_permissions' => Permission::whereIn('name', [
                    'delete-users', 'manage-roles', 'manage-permissions', 
                    'access-admin', 'system-settings'
                ])->withCount('users')->get(),
                'permission_sprawl' => [
                    'avg_permissions_per_user' => Usuario::withCount('allPermissions')->avg('all_permissions_count'),
                    'max_permissions_user' => Usuario::withCount('allPermissions')
                        ->orderBy('all_permissions_count', 'desc')
                        ->first(['username', 'all_permissions_count']),
                    'users_with_many_permissions' => Usuario::withCount('allPermissions')
                        ->having('all_permissions_count', '>', 20)
                        ->count()
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'generated_at' => now(),
            'cache_duration' => '15 minutes'
        ]);
    }

    /**
     * Duplicar rol existente
     */
    public function duplicateRole(Request $request, Role $role)
    {
        $this->authorize('create', Role::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'nivel_permiso' => 'nullable|integer|min:1|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar jerarquía
        $currentUserMaxLevel = auth()->user()->roles->max('nivel_permiso') ?? 0;
        $newLevel = $request->nivel_permiso ?? $role->nivel_permiso;
        
        if ($newLevel >= $currentUserMaxLevel) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes crear roles de nivel igual o superior al tuyo'
            ], 403);
        }

        try {
            $newRole = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'descripcion' => $request->descripcion ?? $role->descripcion . ' (Duplicado)',
                'nivel_permiso' => $newLevel,
                'guard_name' => 'web'
            ]);

            // Copiar todos los permisos del rol original
            $permissions = $role->permissions->pluck('name')->toArray();
            $newRole->givePermissionTo($permissions);

            SecurityLog::create([
                'evento' => 'role_duplicated',
                'usuario_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'detalles' => json_encode([
                    'original_role' => $role->name,
                    'new_role' => $newRole->name,
                    'permissions_copied' => count($permissions),
                    'created_by' => auth()->user()->username
                ]),
                'nivel_riesgo' => 'medium'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rol duplicado correctamente',
                'data' => [
                    'original_role' => $role->name,
                    'new_role' => $newRole->load('permissions'),
                    'permissions_copied' => count($permissions)
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar el rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Comparar dos roles
     */
    public function compareRoles(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $validator = Validator::make($request->all(), [
            'role1_id' => 'required|exists:roles,id',
            'role2_id' => 'required|exists:roles,id|different:role1_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'IDs de roles inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $role1 = Role::with('permissions')->find($request->role1_id);
        $role2 = Role::with('permissions')->find($request->role2_id);

        $permissions1 = $role1->permissions->pluck('name')->toArray();
        $permissions2 = $role2->permissions->pluck('name')->toArray();

        $comparison = [
            'role1' => [
                'name' => $role1->name,
                'display_name' => $role1->display_name,
                'nivel_permiso' => $role1->nivel_permiso,
                'permissions_count' => count($permissions1)
            ],
            'role2' => [
                'name' => $role2->name,
                'display_name' => $role2->display_name,
                'nivel_permiso' => $role2->nivel_permiso,
                'permissions_count' => count($permissions2)
            ],
            'comparison' => [
                'common_permissions' => array_intersect($permissions1, $permissions2),
                'role1_exclusive' => array_diff($permissions1, $permissions2),
                'role2_exclusive' => array_diff($permissions2, $permissions1),
                'similarity_percentage' => count($permissions1) > 0 || count($permissions2) > 0 
                    ? round((count(array_intersect($permissions1, $permissions2)) / count(array_unique(array_merge($permissions1, $permissions2)))) * 100, 2)
                    : 0
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $comparison
        ]);
    }

    /**
     * Exportar configuración de roles y permisos
     */
    public function exportConfiguration(Request $request)
    {
        $this->authorize('viewAny', Permission::class);

        $format = $request->get('format', 'json'); // json, csv, xlsx

        $data = [
            'export_info' => [
                'generated_at' => now(),
                'generated_by' => auth()->user()->username,
                'version' => '1.0'
            ],
            'permissions' => Permission::all()->groupBy('category'),
            'roles' => Role::with('permissions')->get()->map(function($role) {
                return [
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'descripcion' => $role->descripcion,
                    'nivel_permiso' => $role->nivel_permiso,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                    'users_count' => $role->users()->count()
                ];
            }),
            'assignments' => [
                'role_permissions' => DB::table('role_has_permissions')
                    ->join('roles', 'role_has_permissions.role_id', '=', 'roles.id')
                    ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                    ->select('roles.name as role_name', 'permissions.name as permission_name')
                    ->get(),
                'user_roles' => DB::table('model_has_roles')
                    ->join('usuarios', 'model_has_roles.model_id', '=', 'usuarios.id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('model_has_roles.model_type', Usuario::class)
                    ->select('usuarios.username', 'roles.name as role_name')
                    ->get()
            ]
        ];

        // Log exportación
        SecurityLog::create([
            'evento' => 'permissions_config_exported',
            'usuario_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'detalles' => json_encode([
                'format' => $format,
                'permissions_count' => Permission::count(),
                'roles_count' => Role::count()
            ]),
            'nivel_riesgo' => 'medium'
        ]);

        if ($format === 'csv') {
            // Implementar exportación CSV
            return response()->json([
                'success' => false,
                'message' => 'Exportación CSV no implementada aún'
            ], 501);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Validar configuración de roles y permisos
     */
    public function validateConfiguration()
    {
        $this->authorize('viewAny', Permission::class);

        $issues = [];
        $warnings = [];
        $info = [];

        // Verificar roles sin usuarios
        $rolesWithoutUsers = Role::whereDoesntHave('users')->get();
        if ($rolesWithoutUsers->isNotEmpty()) {
            $warnings[] = [
                'type' => 'roles_without_users',
                'message' => 'Roles sin usuarios asignados',
                'count' => $rolesWithoutUsers->count(),
                'roles' => $rolesWithoutUsers->pluck('name')
            ];
        }

        // Verificar permisos sin usar
        $unusedPermissions = Permission::whereDoesntHave('roles')->whereDoesntHave('users')->get();
        if ($unusedPermissions->isNotEmpty()) {
            $warnings[] = [
                'type' => 'unused_permissions',
                'message' => 'Permisos no asignados a ningún rol o usuario',
                'count' => $unusedPermissions->count(),
                'permissions' => $unusedPermissions->pluck('name')
            ];
        }

        // Verificar usuarios con muchos permisos directos
        $usersWithDirectPermissions = Usuario::whereHas('permissions')->withCount('permissions')->get();
        if ($usersWithDirectPermissions->isNotEmpty()) {
            $info[] = [
                'type' => 'users_with_direct_permissions',
                'message' => 'Usuarios con permisos directos (no vía roles)',
                'count' => $usersWithDirectPermissions->count(),
                'users' => $usersWithDirectPermissions->map(function($user) {
                    return [
                        'username' => $user->username,
                        'permissions_count' => $user->permissions_count
                    ];
                })
            ];
        }

        // Verificar jerarquía de roles
        $hierarchyIssues = [];
        $roles = Role::orderBy('nivel_permiso', 'desc')->get();
        foreach ($roles as $role) {
            $lowerRoles = Role::where('nivel_permiso', '<', $role->nivel_permiso)->get();
            foreach ($lowerRoles as $lowerRole) {
                $rolePerms = $role->permissions->pluck('name')->toArray();
                $lowerPerms = $lowerRole->permissions->pluck('name')->toArray();
                $extraPerms = array_diff($lowerPerms, $rolePerms);
                
                if (!empty($extraPerms)) {
                    $hierarchyIssues[] = [
                        'higher_role' => $role->name,
                        'lower_role' => $lowerRole->name,
                        'extra_permissions' => $extraPerms
                    ];
                }
            }
        }

        if (!empty($hierarchyIssues)) {
            $issues[] = [
                'type' => 'hierarchy_violations',
                'message' => 'Roles de menor nivel con permisos que no tienen roles superiores',
                'violations' => $hierarchyIssues
            ];
        }

        // Verificar Super Admin
        $superAdmins = Usuario::whereHas('roles', function($q) {
            $q->where('name', 'Super Admin');
        })->where('activo', true)->count();

        if ($superAdmins === 0) {
            $issues[] = [
                'type' => 'no_super_admin',
                'message' => 'No hay Super Admins activos en el sistema',
                'severity' => 'critical'
            ];
        } elseif ($superAdmins === 1) {
            $warnings[] = [
                'type' => 'single_super_admin',
                'message' => 'Solo hay un Super Admin activo',
                'recommendation' => 'Considera tener al menos 2 Super Admins'
            ];
        }

        $validation = [
            'status' => empty($issues) ? 'valid' : 'issues_found',
            'summary' => [
                'issues_count' => count($issues),
                'warnings_count' => count($warnings),
                'info_count' => count($info)
            ],
            'issues' => $issues,
            'warnings' => $warnings,
            'info' => $info,
            'recommendations' => [
                'Revisar roles sin usuarios y considerar eliminarlos',
                'Asignar permisos no utilizados a roles apropiados',
                'Mantener al menos 2 Super Admins activos',
                'Evitar asignar permisos directos a usuarios, usar roles en su lugar'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $validation
        ]);
    }
    private function getTotalUsersWithPermission(Permission $permission)
    {
        // Usuarios con el permiso directo
        $directUsers = $permission->users()->count();
        
        // Usuarios con el permiso vía roles
        $viaRoles = DB::table('usuarios')
            ->join('model_has_roles', 'usuarios.id', '=', 'model_has_roles.model_id')
            ->join('role_has_permissions', 'model_has_roles.role_id', '=', 'role_has_permissions.role_id')
            ->where('model_has_roles.model_type', Usuario::class)
            ->where('role_has_permissions.permission_id', $permission->id)
            ->distinct('usuarios.id')
            ->count();

        return $directUsers + $viaRoles;
    }
}