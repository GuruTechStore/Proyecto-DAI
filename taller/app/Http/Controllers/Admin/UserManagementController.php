<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'active']);
    }

    /**
     * Mostrar lista de usuarios
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('usuarios.ver'), 403);

        $query = Usuario::with(['empleado', 'roles', 'permissions']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('status')) {
            $query->where('activo', $request->status === 'active');
        }

        if ($request->filled('verified')) {
            if ($request->verified === 'true') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(15)->withQueryString();

        // Datos adicionales
        $roles = Role::all();
        $stats = [
            'total' => Usuario::count(),
            'activos' => Usuario::where('activo', true)->count(),
            'verificados' => Usuario::whereNotNull('email_verified_at')->count(),
            'bloqueados' => Usuario::whereNotNull('blocked_until')->where('blocked_until', '>', now())->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'users' => $users,
                'roles' => $roles,
                'stats' => $stats
            ]);
        }

        return view('admin.users.index', compact('users', 'roles', 'stats'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        abort_unless(auth()->user()->can('usuarios.crear'), 403);
        
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('category');
        
        return view('admin.users.create', compact('roles', 'permissions'));
    }
    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request,Usuario $user)
    {
        abort_unless(auth()->user()->can('usuarios.crear'), 403);

        $validated = $request->validate([
            'username' => 'sometimes|string|max:50|unique:usuarios,username,' . $user->id,
            'email' => 'sometimes|email|max:100|unique:usuarios,email,' . $user->id,
            'tipo_usuario' => 'sometimes|string|in:empleado,gerente,supervisor,admin',
            'activo' => 'boolean',
            'force_password_change' => 'boolean'        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['activo'] = $request->boolean('activo', true);
        
        if ($request->boolean('email_verified')) {
            $validated['email_verified_at'] = now();
        }

        $user = Usuario::create($validated);

        // Asignar roles
        if ($request->filled('roles')) {
            $user->assignRole($request->roles);
        }

        // Asignar permisos adicionales
        if ($request->filled('permissions')) {
            $user->givePermissionTo($request->permissions);
        }

        // Log de creación
        SecurityLog::create([
            'tipo' => 'user_created',
            'descripcion' => 'Usuario creado por administrador',
            'usuario_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'severity' => 'info',
            'datos_adicionales' => [
                'created_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'roles' => $request->roles ?? []
            ]
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'user' => $user->load(['roles', 'permissions'])
            ], 201);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado exitosamente');
    }

    /**
     * Mostrar detalles del usuario
     */
    public function show(Usuario $user)
    {
        abort_unless(auth()->user()->can('usuarios.ver'), 403);

        $user->load(['roles', 'permissions']);

        // Estadísticas del usuario
        $stats = [
            'last_login' => $user->last_login_at,
            'login_count' => $user->login_count ?? 0,
            'failed_logins' => SecurityLog::where('usuario_id', $user->id)
                ->where('tipo', 'failed_login')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'password_age' => $user->password_changed_at 
                ? now()->diffInDays($user->password_changed_at)
                : null,
            'account_age' => now()->diffInDays($user->created_at),
        ];

        // Actividad reciente
        $recentActivity = SecurityLog::where('usuario_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.users.show', compact('user', 'stats', 'recentActivity'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Usuario $user)
    {
        abort_unless(auth()->user()->can('usuarios.editar'), 403);
        
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('category');
        $user->load(['roles', 'permissions']);
        
        return view('admin.users.edit', compact('user', 'roles', 'permissions'));
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, Usuario $user)
    {
        abort_unless(auth()->user()->can('usuarios.editar'), 403);

        $validated = $request->validate([
            'username' => 'sometimes|string|max:50|unique:usuarios,username,' . $user->id,
            'email' => 'sometimes|email|max:100|unique:usuarios,email,' . $user->id,
            'tipo_usuario' => 'sometimes|string|in:empleado,gerente,supervisor,admin',
            'activo' => 'boolean',
            'force_password_change' => 'boolean'
        ]);

        $validated['activo'] = $request->boolean('activo', $user->activo);
        
        if ($request->boolean('email_verified') && !$user->email_verified_at) {
            $validated['email_verified_at'] = now();
        } elseif (!$request->boolean('email_verified')) {
            $validated['email_verified_at'] = null;
        }

        $user->update($validated);

        // Sincronizar roles
        if ($request->has('roles')) {
            $user->syncRoles($request->roles ?? []);
        }

        // Sincronizar permisos
        if ($request->has('permissions')) {
            $user->syncPermissions($request->permissions ?? []);
        }

        // Log de actualización
        SecurityLog::create([
            'tipo' => 'user_updated',
            'descripcion' => 'Usuario actualizado por administrador',
            'usuario_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'severity' => 'info',
            'datos_adicionales' => [
                'updated_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'changes' => array_keys($validated)
            ]
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'user' => $user->fresh(['roles', 'permissions'])
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Eliminar usuario
     */
    public function destroy(Usuario $user)
    {
        abort_unless(auth()->user()->can('usuarios.eliminar'), 403);

        // Verificar que no se elimine a sí mismo
        if ($user->id === auth()->id()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propia cuenta'
                ], 422);
            }

            return back()->with('error', 'No puedes eliminar tu propia cuenta');
        }

        // Verificar si es el único Super Admin
        if ($user->hasRole('Super Admin')) {
            $superAdminCount = Usuario::whereHas('roles', function($q) {
                $q->where('name', 'Super Admin');
            })->where('activo', true)->count();

            if ($superAdminCount <= 1) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar el último Super Admin'
                    ], 422);
                }

                return back()->with('error', 'No se puede eliminar el último Super Admin');
            }
        }

        // Log antes de eliminar
        SecurityLog::create([
            'tipo' => 'user_deleted',
            'descripcion' => 'Usuario eliminado por administrador',
            'usuario_id' => $user->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'severity' => 'high',
            'datos_adicionales' => [
                'deleted_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'user_data' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')
                ]
            ]
        ]);

        $user->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente');
    }

    /**
     * Asignar rol a usuario
     */
    public function assignRole(Request $request, Usuario $user)
    {
        abort_unless(auth()->user()->can('usuarios.editar'), 403);

        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user->assignRole($request->role);

        SecurityLog::create([
            'tipo' => 'role_assigned',
            'descripcion' => 'Rol asignado a usuario',
            'usuario_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'severity' => 'medium',
            'datos_adicionales' => [
                'assigned_by' => auth()->user()->username,
                'role' => $request->role
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => "Rol '{$request->role}' asignado exitosamente"
        ]);
    }

    /**
     * Cambiar estado del usuario
     */
    public function toggleStatus(Usuario $user)
    {
        abort_unless(auth()->user()->can('usuarios.editar'), 403);

        // Verificar que no se desactive a sí mismo
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes cambiar el estado de tu propia cuenta'
            ], 422);
        }

        $user->update(['activo' => !$user->activo]);
        
        $status = $user->activo ? 'activado' : 'desactivado';

        SecurityLog::create([
            'tipo' => 'user_status_changed',
            'descripcion' => "Usuario {$status}",
            'usuario_id' => $user->id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'severity' => 'medium',
            'datos_adicionales' => [
                'changed_by' => auth()->user()->username,
                'new_status' => $user->activo
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => "Usuario {$status} exitosamente",
            'activo' => $user->activo
        ]);
    }

    /**
     * Resetear contraseña
     */
    public function resetPassword(Request $request, Usuario $user)
    {
        abort_unless(auth()->user()->can('usuarios.editar'), 403);

        $request->validate([
            'new_password' => ['required', 'string', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
            'force_change' => 'boolean'
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
            'force_password_change' => $request->boolean('force_change', true)
        ]);

        // Revocar todos los tokens del usuario
        $user->tokens()->delete();

        SecurityLog::create([
            'tipo' => 'password_reset_admin',
            'descripcion' => 'Contraseña reseteada por administrador',
            'usuario_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'severity' => 'high',
            'datos_adicionales' => [
                'reset_by' => auth()->user()->username,
                'admin_id' => auth()->id(),
                'force_change' => $request->boolean('force_change')
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña reseteada exitosamente. El usuario deberá iniciar sesión nuevamente.'
        ]);
    }
}