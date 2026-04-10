<?php

namespace App\Livewire\App\Billing;

use Livewire\Component;

class BillList extends Component
{
    public function render()
    {
        return view('livewire.app.billing.bill-list')
            ->layout('layouts.app', ['heading' => ' Bill List']);
    }
}
