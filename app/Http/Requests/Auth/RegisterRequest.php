<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo superadmin y propietario pueden registrar usuarios
        $rol = $this->user()?->rol?->nombre;
        return in_array($rol, ['superadmin', 'propietario']);
    }

    public function rules(): array
    {
        return [
            'restaurante_id' => ['nullable', 'uuid', 'exists:restaurante,id'],
            'rol_id'         => ['required', 'uuid', 'exists:rol,id'],
            'nombre'         => ['required', 'string', 'max:150'],
            'email'          => ['required', 'email', 'unique:usuario,email'],
            'password'       => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'restaurante_id.exists' => 'El restaurante seleccionado no existe.',
            'rol_id.required'       => 'El rol es obligatorio.',
            'rol_id.exists'         => 'El rol seleccionado no existe.',
            'nombre.required'       => 'El nombre es obligatorio.',
            'email.required'        => 'El correo electrónico es obligatorio.',
            'email.unique'          => 'Este correo ya está registrado.',
            'password.required'     => 'La contraseña es obligatoria.',
            'password.min'          => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'    => 'Las contraseñas no coinciden.',
        ];
    }
}
