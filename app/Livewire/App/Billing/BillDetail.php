<?php

namespace App\Livewire\App\Billing;

use App\Models\Bill;
use Livewire\Component;

class BillDetail extends Component
{
    public Bill $bill;

    public function mount(Bill $bill): void
    {
        $this->bill = $bill;
        $this->bill->load(['items', 'order', 'table']);

        // Record print time on first view after paid
        if ($this->bill->payment_status === 'paid' && ! $this->bill->printed_at) {
            $this->bill->update(['printed_at' => now()]);
            $this->bill->refresh();
        }
    }

    public function render()
    {
        $business = auth()->user()->business;

        return view('livewire.app.billing.bill-detail', compact('business'))
            ->layout('layouts.app', ['heading' => 'Bill ' . $this->bill->bill_number]);
    }
}
