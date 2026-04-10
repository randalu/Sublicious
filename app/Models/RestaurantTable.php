<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RestaurantTable extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'section_id', 'table_number', 'name',
        'capacity', 'qr_code_token', 'status',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(TableSection::class, 'section_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function activeOrder(): ?Order
    {
        return $this->orders()
            ->whereIn('status', ['pending', 'accepted', 'preparing', 'ready'])
            ->where('payment_status', 'unpaid')
            ->latest()
            ->first();
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function generateQrToken(): void
    {
        $this->qr_code_token = Str::random(32);
        $this->save();
    }

    public function displayName(): string
    {
        return $this->name ?: 'Table ' . $this->table_number;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'available' => 'green',
            'occupied' => 'red',
            'reserved' => 'yellow',
            'cleaning' => 'blue',
            default => 'gray',
        };
    }
}
