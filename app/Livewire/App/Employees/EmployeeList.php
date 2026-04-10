<?php

namespace App\Livewire\App\Employees;

use App\Models\Employee;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $roleFilter = '';

    public bool   $showForm     = false;
    public ?int   $editingId    = null;
    public string $name         = '';
    public string $phone        = '';
    public string $email        = '';
    public string $role         = 'cashier';
    public string $salaryType   = 'monthly';
    public string $salaryAmount = '0.00';
    public string $hireDate     = '';
    public string $notes        = '';

    public function updatedSearch(): void     { $this->resetPage(); }
    public function updatedRoleFilter(): void { $this->resetPage(); }

    protected function formRules(): array
    {
        return [
            'name'         => 'required|string|max:150',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:200',
            'role'         => 'required|in:admin,manager,cashier,kitchen,rider',
            'salaryType'   => 'required|in:monthly,hourly,commission_only',
            'salaryAmount' => 'required|numeric|min:0',
            'hireDate'     => 'nullable|date',
            'notes'        => 'nullable|string|max:500',
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        $this->showForm  = true;
        $this->editingId = $id;
        if ($id) {
            $e = Employee::findOrFail($id);
            $this->name         = $e->name;
            $this->phone        = $e->phone ?? '';
            $this->email        = $e->email ?? '';
            $this->role         = $e->role;
            $this->salaryType   = $e->salary_type;
            $this->salaryAmount = (string) $e->salary_amount;
            $this->hireDate     = $e->hire_date?->format('Y-m-d') ?? '';
            $this->notes        = $e->notes ?? '';
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
        $this->name = $this->phone = $this->email = $this->notes = $this->hireDate = '';
        $this->role         = 'cashier';
        $this->salaryType   = 'monthly';
        $this->salaryAmount = '0.00';
    }

    public function save(): void
    {
        $this->validate($this->formRules());
        $data = [
            'name'          => trim($this->name),
            'phone'         => trim($this->phone) ?: null,
            'email'         => trim($this->email) ?: null,
            'role'          => $this->role,
            'salary_type'   => $this->salaryType,
            'salary_amount' => $this->salaryAmount,
            'hire_date'     => $this->hireDate ?: null,
            'notes'         => trim($this->notes) ?: null,
        ];

        if ($this->editingId) {
            Employee::findOrFail($this->editingId)->update($data);
        } else {
            Employee::create($data);
        }
        $this->closeForm();
        session()->flash('success', 'Employee saved.');
    }

    public function toggleActive(int $id): void
    {
        $e = Employee::findOrFail($id);
        $e->update(['is_active' => ! $e->is_active]);
    }

    public function delete(int $id): void
    {
        Employee::findOrFail($id)->delete();
        session()->flash('success', 'Employee deleted.');
    }

    public function render()
    {
        $employees = Employee::when($this->search, fn ($q) => $q->where(
                fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                              ->orWhere('phone', 'like', "%{$this->search}%")
                              ->orWhere('email', 'like', "%{$this->search}%")
            ))
            ->when($this->roleFilter, fn ($q) => $q->where('role', $this->roleFilter))
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.app.employees.employee-list', compact('employees'))
            ->layout('layouts.app', ['heading' => 'Employees']);
    }
}
