<?php

namespace App\Livewire\App\Orders;

use Livewire\Component;

class OrderCreate extends Component
{
    public function render()
    {
        return view('livewire.app.orders.order-create')
            ->layout('layouts.app', ['heading' => ' Order Create']);
    }
}
