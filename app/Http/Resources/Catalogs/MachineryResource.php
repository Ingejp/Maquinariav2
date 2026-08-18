<?php

namespace App\Http\Resources\Catalogs;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $description
 */
class MachineryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'active' => $this->isActive(),
            'machinery_type' => [
                'id' => $this->machineryType->id,
                'description' => $this->machineryType->description,
            ],
            'field' => [
                'id' => $this->field->id,
                'description' => $this->field->description,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
