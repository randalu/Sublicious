<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSetting extends Model
{
    protected $fillable = ['business_id', 'key', 'value', 'group'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
