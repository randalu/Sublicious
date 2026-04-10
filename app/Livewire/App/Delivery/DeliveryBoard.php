<?php

namespace App\Livewire\App\Delivery;

use Livewire\Component;

class DeliveryBoard extends Component
{
    public function render()
    {
        return view('livewire.app.delivery.delivery-board')
            ->layout('layouts.app', ['heading' => ' Delivery Board']);
    }
}
