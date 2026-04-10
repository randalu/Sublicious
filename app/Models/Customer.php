<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'name', 'email', 'phone', 'notes', 'total_orders', 'total_spent',
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function defaultAddress(): ?CustomerAddress
    {
        return $this->addresses()->where('is_default', true)->first();
    }
}
