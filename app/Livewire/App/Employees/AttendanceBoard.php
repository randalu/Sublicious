<?php

namespace App\Livewire\App\Employees;

use App\Models\Attendance;
use App\Models\Employee;
use Livewire\Component;

class AttendanceBoard extends Component
{
    public string $date = '';

    public function mount(): void
    {
        $this->date = today()->format('Y-m-d');
    }

    public function clockIn(int $employeeId): void
    {
        $existing = Attendance::where('employee_id', $employeeId)
            ->whereDate('date', $this->date)
            ->first();

        if ($existing) {
            session()->flash('error', 'Employee already clocked in today.');
            return;
        }

        Attendance::create([
            'employee_id' => $employeeId,
            'date'        => $this->date,
            'in_time'     => now(),
            'status'      => 'present',
            'marked_by'   => auth()->id(),
        ]);
    }

    public function clockOut(int $attendanceId): void
    {
        $att = Attendance::findOrFail($attendanceId);
        if ($att->out_time) {
            return; // already clocked out
        }
        $att->update([
            'out_time'     => now(),
            'hours_worked' => $att->computeHours(),
        ]);
    }

    public function markAbsent(int $employeeId): void
    {
        Attendance::updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $this->date],
            ['status' => 'absent', 'marked_by' => auth()->id()]
        );
    }

    public function markStatus(int $attendanceId, string $status): void
    {
        Attendance::findOrFail($attendanceId)->update(['status' => $status]);
    }

    public function render()
    {
        $employees   = Employee::where('is_active', true)->orderBy('name')->get();
        $attendances = Attendance::with('employee')
            ->whereDate('date', $this->date)
            ->get()
            ->keyBy('employee_id');

        $summary = [
            'present'  => $attendances->where('status', 'present')->count(),
            'absent'   => $attendances->where('status', 'absent')->count(),
            'late'     => $attendances->where('status', 'late')->count(),
            'not_marked' => $employees->count() - $attendances->count(),
        ];

        return view('livewire.app.employees.attendance-board', compact('employees', 'attendances', 'summary'))
            ->layout('layouts.app', ['heading' => 'Attendance']);
    }
}
