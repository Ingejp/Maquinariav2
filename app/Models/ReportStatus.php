<?php

namespace App\Models;

use App\Models\Catalogs\Machinery;
use App\Models\Catalogs\Status;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['observation', 'machinery_id', 'status_id', 'user_id'])]
class ReportStatus extends Model
{
    use SoftDeletes;

    protected $table = 'report_status';

    public function machinery(): BelongsTo
    {
        return $this->belongsTo(Machinery::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
