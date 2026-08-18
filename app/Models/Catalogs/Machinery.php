<?php

namespace App\Models\Catalogs;

use App\Models\Concerns\HasConfigStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['description', 'machinery_type_id', 'field_id', 'configuration_status_id'])]
class Machinery extends Model
{
    use HasConfigStatus;

    protected $table = 'machinery';

    public function machineryType(): BelongsTo
    {
        return $this->belongsTo(MachineryType::class, 'machinery_type_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class, 'field_id');
    }
}
