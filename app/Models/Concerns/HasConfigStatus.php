<?php

namespace App\Models\Concerns;

use App\Models\Catalogs\ConfigStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Compartido por Field, Status, MachineryType y Machinery: todos referencian
 * config_status para su estado activo/deshabilitado (ver ConfigStatus).
 */
trait HasConfigStatus
{
    public function configStatus(): BelongsTo
    {
        return $this->belongsTo(ConfigStatus::class, 'configuration_status_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('configuration_status_id', ConfigStatus::ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->configuration_status_id === ConfigStatus::ACTIVE;
    }
}
