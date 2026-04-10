<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableSection extends Model
{
    use BelongsToBusiness;

    protected $fillable = ['business_id', 'name', 'sort_order'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class, 'section_id');
    }
}
