<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price_monthly', 'price_yearly',
        'max_orders_per_month', 'max_staff', 'max_menu_items', 'max_delivery_zones',
        'features', 'stripe_price_id_monthly', 'stripe_price_id_yearly',
        'is_active', 'is_default', 'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'price_monthly' => 'integer',
        'price_yearly' => 'integer',
    ];

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function isFree(): bool
    {
        return $this->price_monthly === 0;
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) ($this->features[$feature] ?? false);
    }

    public function formattedPrice(string $cycle = 'monthly'): string
    {
        $amount = $cycle === 'yearly' ? $this->price_yearly : $this->price_monthly;
        if ($amount === 0) {
            return 'Free';
        }
        return '$' . number_format($amount / 100, 2);
    }

    public static function getDefault(): self
    {
        return static::where('is_default', true)->first()
            ?? static::orderBy('price_monthly')->first();
    }
}
