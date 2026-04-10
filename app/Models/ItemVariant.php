<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemVariant extends Model
{
    protected $fillable = [
        'business_id', 'menu_item_id', 'name', 'price_adjustment',
        'price_type', 'is_available', 'sort_order',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function getFinalPrice(float $basePrice): float
    {
        return match ($this->price_type) {
            'replace' => (float) $this->price_adjustment,
            'add' => $basePrice + (float) $this->price_adjustment,
            'subtract' => max(0, $basePrice - (float) $this->price_adjustment),
            default => $basePrice,
        };
    }
}
