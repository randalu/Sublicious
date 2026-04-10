<?php

namespace App\Livewire\App\Billing;

use App\Models\Bill;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BillList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $dateFilter = '';

    #[Url(except: '')]
    public string $paymentStatusFilter = '';

    public function updatedSearch(): void             { $this->resetPage(); }
    public function updatedDateFilter(): void          { $this->resetPage(); }
    public function updatedPaymentStatusFilter(): void { $this->resetPage(); }

    public function render()
    {
        $bills = Bill::with(['order', 'table'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('bill_number', 'like', "%{$this->search}%")
                      ->orWhere('customer_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->dateFilter, fn ($q) => $q->whereDate('created_at', $this->dateFilter))
            ->when($this->paymentStatusFilter, fn ($q) => $q->where('payment_status', $this->paymentStatusFilter))
            ->orderByDesc('created_at')
            ->paginate(25);

        $todayTotal = Bill::whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->sum('total');

        return view('livewire.app.billing.bill-list', compact('bills', 'todayTotal'))
            ->layout('layouts.app', ['heading' => 'Bills']);
    }
}
