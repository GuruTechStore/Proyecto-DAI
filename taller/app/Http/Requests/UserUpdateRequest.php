<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user') ?? $this->route('usuario');
        
        // Los usuarios pueden actualizar su propio perfil
        if (auth()->id() === $user->id) {
            return true;
        }
        
        // Los admin/supervisor pueden actualizar otros usuarios
        return auth()->user()->hasRole(['admin', 'supervisor']);
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('usuario')?->id;
        $isOwnProfile = auth()->id() === $userId;
        
        $rules = [
            'nombre' => 'sometimes|required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('usuarios', 'email')->ignore($userId)
            ],
            'telefono' => 'nullable|string|regex:/^[0-9+\-\s()]+$/|max:20'
        ];

        // Solo admins/supervisores pueden cambiar estos campos
        if (!$isOwnProfile && auth()->user()->hasRole(['admin', 'supervisor'])) {
            $rules = array_merge($rules, [
                'rol' => 'sometimes|required|in:admin,supervisor,empleado,cliente',
                'activo' => 'sometimes|boolean',
                'bloqueado' => 'sometimes|boolean',
                'debe_cambiar_password' => 'sometimes|boolean',
                'empleado_id' => [
                    'nullable',
                    'exists:empleados,id',
                    Rule::unique('usuarios', 'empleado_id')->ignore($userId)
                ]
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'Debe ser un email válido',
            'email.unique' => 'Este email ya está registrado',
            'telefono.regex' => 'El teléfono tiene un formato inválido',
            'rol.required' => 'Debe seleccionar un rol',
            'rol.in' => 'El rol seleccionado no es válido',
            'empleado_id.exists' => 'El empleado seleccionado no existe',
            'empleado_id.unique' => 'Este empleado ya tiene un usuario asignado'
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];
        
        if ($this->has('nombre')) {
            $data['nombre'] = trim($this->nombre);
        }
        
        if ($this->has('email')) {
            $data['email'] = strtolower(trim($this->email));
        }
        
        if ($this->has('telefono')) {
            $data['telefono'] = $this->telefono ? 
                               preg_replace('/[^\d+\-\s()]/', '', $this->telefono) : null;
        }

        $this->merge($data);
    }

    public function getValidatedData(): array
    {
        $validated = parent::validated();
        
        // Agregar información de auditoría
        $validated['updated_by'] = auth()->id();
        
        return $validated;
    }

    protected function failedAuthorization()
    {
        abort(403, 'No tienes permisos para actualizar este usuario');
    }

    // Método para verificar si se están actualizando campos sensibles
    public function hasSecuritySensitiveChanges(): bool
    {
        $sensitiveFields = ['rol', 'activo', 'bloqueado', 'debe_cambiar_password'];
        
        foreach ($sensitiveFields as $field) {
            if ($this->has($field)) {
                return true;
            }
        }
        
        return false;
    }
}