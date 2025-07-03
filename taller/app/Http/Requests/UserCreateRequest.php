<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'supervisor']);
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'email' => 'required|email|unique:usuarios,email|max:255',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'telefono' => 'nullable|string|regex:/^[0-9+\-\s()]+$/|max:20',
            'rol' => 'required|in:admin,supervisor,empleado,cliente',
            'activo' => 'boolean',
            'debe_cambiar_password' => 'boolean',
            'empleado_id' => 'nullable|exists:empleados,id|unique:usuarios,empleado_id'
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'Debe ser un email válido',
            'email.unique' => 'Este email ya está registrado',
            'password.required' => 'La contraseña es obligatoria',
            'password.confirmed' => 'La confirmación de contraseña no coincide',
            'telefono.regex' => 'El teléfono tiene un formato inválido',
            'rol.required' => 'Debe seleccionar un rol',
            'rol.in' => 'El rol seleccionado no es válido',
            'empleado_id.exists' => 'El empleado seleccionado no existe',
            'empleado_id.unique' => 'Este empleado ya tiene un usuario asignado'
        ];
    }

    protected function prepareForValidation()
    {
        // Limpiar y formatear datos
        $this->merge([
            'nombre' => trim($this->nombre),
            'email' => strtolower(trim($this->email)),
            'telefono' => $this->telefono ? preg_replace('/[^\d+\-\s()]/', '', $this->telefono) : null,
            'activo' => $this->boolean('activo', true),
            'debe_cambiar_password' => $this->boolean('debe_cambiar_password', true)
        ]);
    }

    public function getValidatedData(): array
    {
        $validated = parent::validated();
        
        // Agregar campos adicionales
        $validated['password_changed_at'] = now();
        $validated['created_by'] = auth()->id();
        
        return $validated;
    }

    protected function failedAuthorization()
    {
        abort(403, 'No tienes permisos para crear usuarios');
    }
}