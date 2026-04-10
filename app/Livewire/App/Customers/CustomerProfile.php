<?php

namespace App\Livewire\App\Customers;

use Livewire\Component;

class CustomerProfile extends Component
{
    public function render()
    {
        return view('livewire.app.customers.customer-profile')
            ->layout('layouts.app', ['heading' => ' Customer Profile']);
    }
}
