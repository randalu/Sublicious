<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryRider extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'name', 'phone', 'vehicle_type', 'vehicle_number',
        'commission_type', 'commission_value', 'is_active',
        'total_deliveries', 'total_commission_earned',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'total_commission_earned' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'rider_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(RiderCommissionPayout::class, 'rider_id');
    }

    public function calculateCommission(float $deliveryFee): float
    {
        if ($this->commission_type === 'percentage') {
            return round($deliveryFee * ($this->commission_value / 100), 2);
        }
        return (float) $this->commission_value;
    }
}
