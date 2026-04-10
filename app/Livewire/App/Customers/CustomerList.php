<?php

namespace App\Livewire\App\Customers;

use Livewire\Component;

class CustomerList extends Component
{
    public function render()
    {
        return view('livewire.app.customers.customer-list')
            ->layout('layouts.app', ['heading' => ' Customer List']);
    }
}
