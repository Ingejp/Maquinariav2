<?php

namespace App\Models\Catalogs;

use App\Models\Concerns\HasConfigStatus;
use App\Models\ReportStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['description', 'configuration_status_id'])]
class Status extends Model
{
    use HasConfigStatus;

    protected $table = 'status';

    public function reportStatuses(): HasMany
    {
        return $this->hasMany(ReportStatus::class, 'status_id');
    }
}
