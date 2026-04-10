<?php

namespace App\Livewire\App\Orders;

use Livewire\Component;

class OrderDetail extends Component
{
    public function render()
    {
        return view('livewire.app.orders.order-detail')
            ->layout('layouts.app', ['heading' => ' Order Detail']);
    }
}
