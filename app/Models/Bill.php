<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'order_id', 'table_id', 'bill_number', 'customer_name',
        'customer_phone', 'subtotal', 'service_charge', 'discount_amount', 'total',
        'payment_method', 'payment_status', 'amount_paid', 'change_amount',
        'paid_at', 'notes', 'printed_at', 'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'printed_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class)->orderBy('sort_order');
    }
}
