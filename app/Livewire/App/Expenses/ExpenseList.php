<?php

namespace App\Livewire\App\Expenses;

use App\Models\Employee;
use App\Models\Expense;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $categoryFilter = '';

    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    public bool   $showForm      = false;
    public ?int   $editingId     = null;
    public ?int   $employeeId    = null;
    public string $category      = '';
    public string $amount        = '0.00';
    public string $description   = '';
    public string $date          = '';

    public function updatedSearch(): void { $this->resetPage(); }

    protected function formRules(): array
    {
        return [
            'category'    => 'required|string|max:100',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
            'date'        => 'required|date',
            'employeeId'  => 'nullable|integer|exists:employees,id',
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        $this->showForm  = true;
        $this->editingId = $id;
        if ($id) {
            $e = Expense::findOrFail($id);
            $this->employeeId  = $e->employee_id;
            $this->category    = $e->category;
            $this->amount      = (string) $e->amount;
            $this->description = $e->description ?? '';
            $this->date        = $e->date->format('Y-m-d');
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
        $this->category = $this->description = '';
        $this->amount = '0.00';
        $this->date = today()->format('Y-m-d');
    }

    public function save(): void
    {
        $this->validate($this->formRules());
        $data = [
            'employee_id' => $this->employeeId,
            'category'    => trim($this->category),
            'amount'      => $this->amount,
            'description' => trim($this->description) ?: null,
            'date'        => $this->date,
        ];

        if ($this->editingId) {
            Expense::findOrFail($this->editingId)->update($data);
        } else {
            Expense::create($data);
        }
        $this->closeForm();
        session()->flash('success', 'Expense saved.');
    }

    public function approve(int $id): void
    {
        Expense::findOrFail($id)->update(['is_approved' => true, 'approved_by' => auth()->id()]);
    }

    public function delete(int $id): void
    {
        Expense::findOrFail($id)->delete();
        session()->flash('success', 'Expense deleted.');
    }

    public function render()
    {
        $expenses = Expense::with('employee')
            ->when($this->search, fn ($q) => $q->where(
                fn ($q) => $q->where('description', 'like', "%{$this->search}%")
                              ->orWhere('category', 'like', "%{$this->search}%")
            ))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->orderByDesc('date')
            ->paginate(20);

        $totalShown   = $expenses->sum('amount');
        $categories   = Expense::select('category')->distinct()->orderBy('category')->pluck('category');
        $employees    = Employee::where('is_active', true)->orderBy('name')->get();
        $monthlyTotal = Expense::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');

        return view('livewire.app.expenses.expense-list', compact('expenses', 'totalShown', 'categories', 'employees', 'monthlyTotal'))
            ->layout('layouts.app', ['heading' => 'Expenses']);
    }
}
