<?php

namespace App\Traits;

use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;

/**
 * Global scope that automatically filters all queries by the authenticated
 * user's business_id. Boot this trait on any tenant-scoped model.
 */
trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        // Apply scope when querying
        static::addGlobalScope('business', function (Builder $builder) {
            if (auth()->check() && ! auth()->user()->isSuperAdmin()) {
                $builder->where(
                    (new static)->getTable() . '.business_id',
                    auth()->user()->business_id
                );
            }
        });

        // Auto-set business_id on create
        static::creating(function ($model) {
            if (
                auth()->check() &&
                ! auth()->user()->isSuperAdmin() &&
                empty($model->business_id)
            ) {
                $model->business_id = auth()->user()->business_id;
            }
        });
    }

    public function scopeForBusiness(Builder $query, int|Business $business): Builder
    {
        $id = $business instanceof Business ? $business->id : $business;
        return $query->withoutGlobalScope('business')->where('business_id', $id);
    }
}
