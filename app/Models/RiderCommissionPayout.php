<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderCommissionPayout extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'rider_id', 'period_start', 'period_end',
        'total_deliveries', 'total_commission', 'is_paid', 'paid_at', 'notes',
    ];

    protected $casts = [
        'total_commission' => 'decimal:2',
        'is_paid' => 'boolean',
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
    ];

    public function rider(): BelongsTo
    {
        return $this->belongsTo(DeliveryRider::class, 'rider_id');
    }
}
