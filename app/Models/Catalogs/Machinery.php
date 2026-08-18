<?php

namespace App\Models\Catalogs;

use App\Models\Concerns\HasConfigStatus;
use App\Models\ReportStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function reportStatuses(): HasMany
    {
        return $this->hasMany(ReportStatus::class, 'machinery_id');
    }

    public function latestReport(): HasOne
    {
        return $this->hasOne(ReportStatus::class, 'machinery_id')->latestOfMany();
    }
}
