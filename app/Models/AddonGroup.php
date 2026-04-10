<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AddonGroup extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'name', 'selection_type', 'is_required',
        'min_selections', 'max_selections',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AddonGroupItem::class)->orderBy('sort_order');
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_addon_groups');
    }
}
