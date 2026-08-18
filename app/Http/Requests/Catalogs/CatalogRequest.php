<?php

namespace App\Http\Requests\Catalogs;

use Illuminate\Foundation\Http\FormRequest;

class CatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:25'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Los catálogos legacy están casi todos en mayúsculas (BARRIOS,
        // REACH STACKER, OPERATIVA) — se normaliza al guardar para mantener
        // esa convención sin depender de que el usuario la respete a mano.
        $this->merge([
            'description' => mb_strtoupper(trim((string) $this->input('description'))),
        ]);
    }
}
