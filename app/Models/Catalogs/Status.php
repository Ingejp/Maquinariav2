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

    /**
     * No hay columna de severidad en el esquema legacy — se infiere del
     * texto para poder colorear el Dashboard (good/warn/critical/neutral).
     * "NO OPERATIVA" se revisa antes que "OPERATIVA" porque la contiene.
     */
    public function semanticClass(): string
    {
        $upper = mb_strtoupper($this->description);

        return match (true) {
            str_contains($upper, 'NO OPERATIVA') => 'critical',
            str_contains($upper, 'LIMITAC') => 'warn',
            str_contains($upper, 'OPERATIVA') => 'good',
            default => 'neutral',
        };
    }
}
