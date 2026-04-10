<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'business_id', 'user_id', 'user_email', 'event',
        'auditable_type', 'auditable_id',
        'old_values', 'new_values', 'url',
        'ip_address', 'user_agent', 'tags',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public static function record(
        string $event,
        ?int $businessId = null,
        ?string $auditableType = null,
        ?int $auditableId = null,
        array $oldValues = [],
        array $newValues = [],
        ?string $tags = null
    ): void {
        $user = auth()->user();
        static::create([
            'business_id' => $businessId ?? ($user?->business_id),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'event' => $event,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'tags' => $tags,
        ]);
    }
}
