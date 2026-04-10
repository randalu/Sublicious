<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['bill_id', 'description', 'quantity', 'unit_price', 'total', 'sort_order'];

    protected $casts = ['unit_price' => 'decimal:2', 'total' => 'decimal:2'];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
