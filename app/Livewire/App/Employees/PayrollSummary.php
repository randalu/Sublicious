<?php

namespace App\Livewire\App\Employees;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\RiderCommissionPayout;
use Livewire\Component;

class PayrollSummary extends Component
{
    public string $month = '';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function render()
    {
        [$year, $mon] = explode('-', $this->month);
        $startDate = \Carbon\Carbon::create($year, $mon, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        $employees = Employee::where('is_active', true)->orderBy('name')->get();

        $payroll = $employees->map(function ($emp) use ($startDate, $endDate) {
            $attendances = Attendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $totalHours   = $attendances->sum('hours_worked');
            $presentDays  = $attendances->whereIn('status', ['present', 'late'])->count();
            $absentDays   = $attendances->where('status', 'absent')->count();

            $basePay = match ($emp->salary_type) {
                'monthly' => (float) $emp->salary_amount,
                'hourly'  => round($totalHours * (float) $emp->salary_amount, 2),
                default   => 0,
            };

            // Rider commission
            $commission = 0;
            if ($emp->role === 'rider') {
                $commission = RiderCommissionPayout::whereHas(
                    'rider',
                    fn ($q) => $q->where('phone', $emp->phone)
                )
                ->whereBetween('period_start', [$startDate, $endDate])
                ->sum('total_commission');
            }

            return [
                'employee'     => $emp,
                'present_days' => $presentDays,
                'absent_days'  => $absentDays,
                'total_hours'  => $totalHours,
                'base_pay'     => $basePay,
                'commission'   => $commission,
                'total'        => $basePay + $commission,
            ];
        });

        $grandTotal = $payroll->sum('total');

        return view('livewire.app.employees.payroll-summary', compact('payroll', 'grandTotal'))
            ->layout('layouts.app', ['heading' => 'Payroll Summary']);
    }
}
