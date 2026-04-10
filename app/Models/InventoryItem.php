<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'name', 'unit', 'current_stock', 'low_stock_threshold', 'cost_per_unit',
    ];

    protected $casts = [
        'current_stock' => 'decimal:3',
        'low_stock_threshold' => 'decimal:3',
        'cost_per_unit' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->low_stock_threshold;
    }
}
