<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use App\Models\Usuario;
use App\Models\UserActivity;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar perfil del usuario
     */
    public function show()
    {
        $user = auth()->user();
        $user->load(['empleado', 'roles', 'permissions']);
        
        // Verificar que tenga empleado asociado
        if (!$user->empleado) {
            return redirect()->route('dashboard')
                ->with('error', 'Tu usuario no está asociado a un empleado. Contacta al administrador.');
        }
        
        // Últimas actividades
        $recentActivity = UserActivity::where('usuario_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('profile.show', compact('user', 'recentActivity'));
    }
    /**
     * Mostrar formulario de edición
     */
    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Actualizar perfil
     */
    public function update(Request $request)
    {
        $user = auth()->user();

    if (!$user->empleado_id) {
            return back()->with('error', 'Usuario no asociado a un empleado');
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:usuarios,email,' . $user->id,
        ]);

        // Los datos personales se actualizan en el empleado
        if ($user->empleado) {
            $empleadoData = $request->validate([
                'nombres' => 'required|string|max:100',
                'apellidos' => 'required|string|max:100',
                'telefono' => 'nullable|string|max:20',
                'direccion' => 'nullable|string|max:255',
            ]);
            
            $user->empleado->update($empleadoData);
        }

    // Solo actualizar email del usuario
    $user->update($validated);
        // Registrar actividad
        activity()
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties(['modulo' => 'perfil'])
            ->log('Perfil actualizado');

        return redirect()->route('profile.show')
            ->with('success', 'Perfil actualizado correctamente');
    }

    /**
     * Eliminar cuenta
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();

        // No permitir eliminar Super Admin
        if ($user->hasRole('Super Admin')) {
            return back()->withErrors(['error' => 'No se puede eliminar la cuenta de Super Admin']);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Cuenta eliminada correctamente');
    }

    /**
     * Subir avatar
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();

        // Eliminar avatar anterior
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Subir nuevo avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        
        $user->update(['avatar' => $path]);

        return response()->json([
            'success' => true,
            'avatar_url' => Storage::url($path),
            'message' => 'Avatar actualizado correctamente'
        ]);
    }

    /**
     * Remover avatar
     */
    public function removeAvatar()
    {
        $user = auth()->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Avatar eliminado correctamente'
        ]);
    }

    /**
     * Mostrar configuración de seguridad
     */
    public function security()
    {
        $user = auth()->user();
        
        // Sesiones activas (simplificado)
        $activeSessions = collect([
            [
                'id' => session()->getId(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'last_activity' => now(),
                'is_current' => true
            ]
        ]);

        return view('profile.security', compact('user', 'activeSessions'));
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = auth()->user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Registrar actividad de seguridad
        activity()
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'modulo' => 'seguridad',
                'accion' => 'cambio_contraseña',
                'ip' => request()->ip()
            ])
            ->log('Contraseña cambiada');

        return back()->with('success', 'Contraseña actualizada correctamente');
    }

    /**
     * Toggle 2FA (placeholder)
     */
    public function toggleTwoFactor(Request $request)
    {
        $user = auth()->user();
        $enabled = $request->boolean('enabled');

        // Aquí implementarías la lógica de 2FA
        // Por ahora solo simulamos
        $user->update(['two_factor_enabled' => $enabled]);

        $message = $enabled ? 'Autenticación de dos factores activada' : 'Autenticación de dos factores desactivada';

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}