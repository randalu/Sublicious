<?php

namespace App\Livewire\App\Reports;

use App\Models\Attendance;
use App\Models\Employee;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use League\Csv\Writer;
use SplTempFileObject;

class EmployeeReport extends Component
{
    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = $this->dateFrom ?: now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = $this->dateTo   ?: now()->endOfMonth()->format('Y-m-d');
    }

    #[Computed]
    public function summary(): array
    {
        $total   = Employee::where('is_active', true)->count();
        $present = Attendance::whereDate('date', today())->where('status', 'present')->count();
        $absent  = Attendance::whereDate('date', today())->where('status', 'absent')->count();
        $totalHours = Attendance::whereBetween('date', [$this->dateFrom, $this->dateTo])->sum('hours_worked');

        return compact('total', 'present', 'absent', 'totalHours');
    }

    #[Computed]
    public function employeeData(): array
    {
        $employees   = Employee::where('is_active', true)->orderBy('name')->get();
        $attendances = Attendance::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->get()
            ->groupBy('employee_id');

        return $employees->map(function ($emp) use ($attendances) {
            $atts = $attendances->get($emp->id, collect());
            return [
                'employee'     => $emp,
                'present_days' => $atts->whereIn('status', ['present', 'late'])->count(),
                'absent_days'  => $atts->where('status', 'absent')->count(),
                'total_hours'  => round($atts->sum('hours_worked'), 2),
            ];
        })->toArray();
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $data = $this->employeeData;
        return response()->streamDownload(function () use ($data) {
            $csv = Writer::createFromFileObject(new SplTempFileObject());
            $csv->insertOne(['Employee', 'Role', 'Present Days', 'Absent Days', 'Total Hours']);
            foreach ($data as $row) {
                $csv->insertOne([
                    $row['employee']['name'],
                    $row['employee']['role'],
                    $row['present_days'],
                    $row['absent_days'],
                    $row['total_hours'],
                ]);
            }
            echo $csv->toString();
        }, "employee-report-{$this->dateFrom}-{$this->dateTo}.csv");
    }

    public function render()
    {
        return view('livewire.app.reports.employee-report')
            ->layout('layouts.app', ['heading' => 'Employee Report']);
    }
}
