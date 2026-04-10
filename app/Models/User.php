<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'business_id', 'name', 'email', 'password', 'phone', 'avatar',
        'role', 'is_active', 'two_factor_secret', 'two_factor_recovery_codes',
        'two_factor_confirmed_at', 'email_verified_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function canManageStaff(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'manager']);
    }

    public function canAccessKitchen(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'manager', 'kitchen']);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function twoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at);
    }
}
