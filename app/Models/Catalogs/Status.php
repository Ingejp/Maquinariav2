<?php

namespace App\Models\Catalogs;

use App\Models\Concerns\HasConfigStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['description', 'configuration_status_id'])]
class Status extends Model
{
    use HasConfigStatus;

    protected $table = 'status';
}
