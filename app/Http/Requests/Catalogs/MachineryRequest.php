<?php

namespace App\Http\Requests\Catalogs;

use Illuminate\Foundation\Http\FormRequest;

class MachineryRequest extends FormRequest
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
            'machinery_type_id' => ['required', 'integer', 'exists:machinery_type,id'],
            'field_id' => ['required', 'integer', 'exists:field,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => mb_strtoupper(trim((string) $this->input('description'))),
        ]);
    }
}
