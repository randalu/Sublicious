<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'category_id', 'name', 'description', 'image', 'base_price',
        'is_available', 'is_delivery_available', 'is_featured',
        'preparation_time_minutes', 'track_inventory', 'sort_order',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_delivery_available' => 'boolean',
        'is_featured' => 'boolean',
        'track_inventory' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ItemVariant::class)->orderBy('sort_order');
    }

    public function addonGroups(): BelongsToMany
    {
        return $this->belongsToMany(AddonGroup::class, 'menu_item_addon_groups')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }
}
