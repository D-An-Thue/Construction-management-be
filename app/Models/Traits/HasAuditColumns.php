<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasAuditColumns
{
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('IsDeleted', false);
    }
}
