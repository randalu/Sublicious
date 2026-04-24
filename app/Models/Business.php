<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

class Business extends Model
{
    use SoftDeletes, Billable;

    protected $fillable = [
        'plan_id', 'name', 'slug', 'email', 'phone', 'address', 'city',
        'state', 'country', 'postal_code', 'logo', 'cover_image', 'description',
        'currency', 'timezone', 'website', 'minimum_delivery_order',
        'subscription_status', 'trial_ends_at', 'is_active', 'is_verified',
        'stripe_id', 'pm_type', 'pm_last_four',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'minimum_delivery_order' => 'decimal:2',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function owner(): ?User
    {
        return $this->users()->where('role', 'admin')->first();
    }

    public function settings(): HasMany
    {
        return $this->hasMany(BusinessSetting::class);
    }

    public function operatingHours(): HasMany
    {
        return $this->hasMany(BusinessOperatingHour::class);
    }

    public function menuCategories(): HasMany
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function riders(): HasMany
    {
        return $this->hasMany(DeliveryRider::class);
    }

    public function deliveryZones(): HasMany
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function setSetting(string $key, mixed $value, string $group = 'general'): void
    {
        $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    public function isSuspended(): bool
    {
        return $this->subscription_status === 'suspended' || ! $this->is_active;
    }

    public function currentMonthOrderCount(): int
    {
        return $this->orders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereNotIn('status', ['cancelled'])
            ->count();
    }

    public function canCreateOrder(): bool
    {
        if (! $this->plan) {
            return false;
        }
        return $this->currentMonthOrderCount() < $this->plan->max_orders_per_month;
    }

    public function remainingOrdersThisMonth(): int
    {
        if (! $this->plan) {
            return 0;
        }
        return max(0, $this->plan->max_orders_per_month - $this->currentMonthOrderCount());
    }

    public function orderUsagePercent(): float
    {
        if (! $this->plan || $this->plan->max_orders_per_month === 0) {
            return 0;
        }
        return round(($this->currentMonthOrderCount() / $this->plan->max_orders_per_month) * 100, 1);
    }

    public function hasFeature(string $feature): bool
    {
        return $this->plan ? $this->plan->hasFeature($feature) : false;
    }
}
