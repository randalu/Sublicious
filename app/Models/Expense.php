<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'employee_id', 'category', 'amount', 'description',
        'date', 'receipt_image', 'approved_by', 'is_approved',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'is_approved' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
