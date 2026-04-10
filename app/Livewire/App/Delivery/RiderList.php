<?php

namespace App\Livewire\App\Delivery;

use Livewire\Component;

class RiderList extends Component
{
    public function render()
    {
        return view('livewire.app.delivery.rider-list')
            ->layout('layouts.app', ['heading' => ' Rider List']);
    }
}
