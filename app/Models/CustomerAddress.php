<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'business_id', 'customer_id', 'label', 'address_line_1',
        'address_line_2', 'city', 'state', 'postal_code', 'lat', 'lng', 'is_default',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
