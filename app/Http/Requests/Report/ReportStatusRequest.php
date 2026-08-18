<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class ReportStatusRequest extends FormRequest
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
            'machinery_id' => ['required', 'integer', 'exists:machinery,id'],
            'status_id' => ['required', 'integer', 'exists:status,id'],
            'observation' => ['nullable', 'string', 'max:250'],
        ];
    }
}
