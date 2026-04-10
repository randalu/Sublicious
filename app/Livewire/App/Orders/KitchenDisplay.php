<?php

namespace App\Livewire\App\Orders;

use Livewire\Component;

class KitchenDisplay extends Component
{
    public function render()
    {
        return view('livewire.app.orders.kitchen-display')
            ->layout('layouts.app', ['heading' => ' Kitchen Display']);
    }
}
