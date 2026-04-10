<?php

namespace App\Livewire\App\Orders;

use Livewire\Component;

class OrderBoard extends Component
{
    public function render()
    {
        return view('livewire.app.orders.order-board')
            ->layout('layouts.app', ['heading' => ' Order Board']);
    }
}
