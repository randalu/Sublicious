<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;

class BillingCharges extends Component
{
    public function render()
    {
        return view('livewire.app.settings.billing-charges')
            ->layout('layouts.app', ['heading' => ' Billing Charges']);
    }
}
