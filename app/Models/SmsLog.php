<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'order_id', 'recipient_phone', 'message',
        'status', 'provider_response', 'provider', 'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
