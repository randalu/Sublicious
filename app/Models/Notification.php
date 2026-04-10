<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Notification extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'business_id', 'user_id', 'type', 'title', 'body', 'data', 'read_at'];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
            $model->created_at = $model->freshTimestamp();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function isRead(): bool
    {
        return ! is_null($this->read_at);
    }
}
