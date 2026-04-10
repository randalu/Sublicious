<?php

namespace App\Livewire\App\Customers;

use App\Models\Customer;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public bool   $showForm      = false;
    public ?int   $editingId     = null;
    public string $name          = '';
    public string $email         = '';
    public string $phone         = '';
    public string $notes         = '';

    public function updatedSearch(): void { $this->resetPage(); }

    protected function formRules(): array
    {
        return [
            'name'  => 'required|string|max:150',
            'email' => 'nullable|email|max:200',
            'phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        $this->showForm   = true;
        $this->editingId  = $id;

        if ($id) {
            $c = Customer::findOrFail($id);
            $this->name  = $c->name;
            $this->email = $c->email ?? '';
            $this->phone = $c->phone ?? '';
            $this->notes = $c->notes ?? '';
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
        $this->name = $this->email = $this->phone = $this->notes = '';
    }

    public function save(): void
    {
        $this->validate($this->formRules());
        $data = [
            'name'  => trim($this->name),
            'email' => trim($this->email) ?: null,
            'phone' => trim($this->phone) ?: null,
            'notes' => trim($this->notes) ?: null,
        ];

        if ($this->editingId) {
            Customer::findOrFail($this->editingId)->update($data);
        } else {
            Customer::create($data);
        }
        $this->closeForm();
        session()->flash('success', 'Customer saved.');
    }

    public function delete(int $id): void
    {
        Customer::findOrFail($id)->delete();
        session()->flash('success', 'Customer deleted.');
    }

    public function render()
    {
        $customers = Customer::when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('total_orders')
            ->paginate(20);

        return view('livewire.app.customers.customer-list', compact('customers'))
            ->layout('layouts.app', ['heading' => 'Customers']);
    }
}
