<?php

namespace App\Livewire\App\Billing;

use Livewire\Component;

class BillDetail extends Component
{
    public function render()
    {
        return view('livewire.app.billing.bill-detail')
            ->layout('layouts.app', ['heading' => ' Bill Detail']);
    }
}
