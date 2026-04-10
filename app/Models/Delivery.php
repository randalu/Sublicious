<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'order_id', 'rider_id', 'zone_id',
        'pickup_address', 'delivery_address', 'delivery_lat', 'delivery_lng',
        'status', 'fee', 'commission_earned',
        'assigned_at', 'picked_up_at', 'delivered_at', 'notes',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'commission_earned' => 'decimal:2',
        'delivery_lat' => 'decimal:7',
        'delivery_lng' => 'decimal:7',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(DeliveryRider::class, 'rider_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'zone_id');
    }
}
