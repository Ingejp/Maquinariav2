<?php

namespace App\Models\Catalogs;

use App\Models\Concerns\HasConfigStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['description', 'configuration_status_id'])]
class MachineryType extends Model
{
    use HasConfigStatus;

    protected $table = 'machinery_type';

    public function machinery(): HasMany
    {
        return $this->hasMany(Machinery::class, 'machinery_type_id');
    }
}
