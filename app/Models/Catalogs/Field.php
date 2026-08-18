<?php

namespace App\Models\Catalogs;

use App\Models\Concerns\HasConfigStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['description', 'configuration_status_id'])]
class Field extends Model
{
    use HasConfigStatus;

    protected $table = 'field';

    public function machinery(): HasMany
    {
        return $this->hasMany(Machinery::class, 'field_id');
    }
}
