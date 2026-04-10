<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountCode extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'code', 'name', 'description', 'type', 'value',
        'min_order_amount', 'max_discount_amount', 'usage_limit', 'usage_count',
        'is_delivery_only', 'is_active', 'valid_from', 'valid_until',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'is_delivery_only' => 'boolean',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function isValid(float $orderTotal = 0): bool
    {
        if (! $this->is_active) return false;
        if ($this->valid_from && now()->lt($this->valid_from)) return false;
        if ($this->valid_until && now()->gt($this->valid_until)) return false;
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) return false;
        if ($orderTotal < $this->min_order_amount) return false;
        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            $discount = $subtotal * ($this->value / 100);
            if ($this->max_discount_amount) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
            return round($discount, 2);
        }
        return min((float) $this->value, $subtotal);
    }
}
