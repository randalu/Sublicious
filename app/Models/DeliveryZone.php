<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryZone extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'name', 'polygon', 'delivery_fee',
        'minimum_order_amount', 'estimated_minutes', 'is_active',
    ];

    protected $casts = [
        'polygon' => 'array',
        'delivery_fee' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
