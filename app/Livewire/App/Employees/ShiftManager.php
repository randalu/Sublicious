<?php

namespace App\Livewire\App\Employees;

use App\Models\Employee;
use App\Models\Shift;
use Livewire\Component;

class ShiftManager extends Component
{
    public string $weekStart = '';
    public bool   $showForm  = false;
    public ?int   $editingId = null;

    // Form fields
    public ?int   $employeeId = null;
    public string $date       = '';
    public string $startTime  = '09:00';
    public string $endTime    = '17:00';
    public string $notes      = '';

    public function mount(): void
    {
        $this->weekStart = now()->startOfWeek()->format('Y-m-d');
    }

    public function prevWeek(): void
    {
        $this->weekStart = \Carbon\Carbon::parse($this->weekStart)->subWeek()->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $this->weekStart = \Carbon\Carbon::parse($this->weekStart)->addWeek()->format('Y-m-d');
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        $this->showForm  = true;
        $this->editingId = $id;
        if ($id) {
            $s = Shift::findOrFail($id);
            $this->employeeId = $s->employee_id;
            $this->date       = $s->date->format('Y-m-d');
            $this->startTime  = $s->start_time;
            $this->endTime    = $s->end_time;
            $this->notes      = $s->notes ?? '';
        }
    }

    public function closeForm(): void
    {
        $this->showForm  = false;
        $this->editingId = null;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->employeeId = null;
        $this->date = $this->notes = '';
        $this->startTime = '09:00';
        $this->endTime   = '17:00';
    }

    public function save(): void
    {
        $this->validate([
            'employeeId' => 'required|integer|exists:employees,id',
            'date'       => 'required|date',
            'startTime'  => 'required',
            'endTime'    => 'required',
        ]);

        $data = [
            'employee_id' => $this->employeeId,
            'date'        => $this->date,
            'start_time'  => $this->startTime,
            'end_time'    => $this->endTime,
            'notes'       => trim($this->notes) ?: null,
        ];

        if ($this->editingId) {
            Shift::findOrFail($this->editingId)->update($data);
        } else {
            Shift::create($data);
        }

        $this->closeForm();
        session()->flash('success', 'Shift saved.');
    }

    public function delete(int $id): void
    {
        Shift::findOrFail($id)->delete();
    }

    public function render()
    {
        $weekEnd  = \Carbon\Carbon::parse($this->weekStart)->endOfWeek();
        $weekDays = [];
        for ($d = \Carbon\Carbon::parse($this->weekStart); $d->lte($weekEnd); $d->addDay()) {
            $weekDays[] = $d->format('Y-m-d');
        }

        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $shifts = Shift::with('employee')
            ->whereBetween('date', [$this->weekStart, $weekEnd->format('Y-m-d')])
            ->get()
            ->groupBy(fn ($s) => $s->employee_id);

        return view('livewire.app.employees.shift-manager', compact('employees', 'shifts', 'weekDays'))
            ->layout('layouts.app', ['heading' => 'Shift Manager']);
    }
}
