<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reemplaza el /register público eliminado — solo un admin da de alta
     * usuarios. Password reforzada (hallazgo 6.2: el registro público
     * original solo pedía min:4).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ];
    }
}
