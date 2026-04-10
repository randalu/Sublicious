<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'name', 'phone', 'email', 'role', 'salary_type',
        'salary_amount', 'hire_date', 'id_number', 'emergency_contact',
        'is_active', 'notes',
    ];

    protected $casts = [
        'salary_amount' => 'decimal:2',
        'hire_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function todayAttendance(): ?Attendance
    {
        return $this->attendances()->whereDate('date', today())->first();
    }
}
