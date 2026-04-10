<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'business_id', 'subscription_id', 'stripe_invoice_id',
        'amount', 'currency', 'status', 'paid_at', 'invoice_pdf_url',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount'  => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function formattedAmount(): string
    {
        return '$' . number_format($this->amount / 100, 2);
    }
}
