<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'employee_id', 'date', 'in_time', 'out_time',
        'hours_worked', 'status', 'notes', 'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
        'in_time' => 'datetime',
        'out_time' => 'datetime',
        'hours_worked' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function computeHours(): float
    {
        if ($this->in_time && $this->out_time) {
            return round($this->in_time->diffInMinutes($this->out_time) / 60, 2);
        }
        return 0;
    }
}
