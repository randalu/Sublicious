<?php

namespace App\Livewire\App\Delivery;

use Livewire\Component;

class ZoneManager extends Component
{
    public function render()
    {
        return view('livewire.app.delivery.zone-manager')
            ->layout('layouts.app', ['heading' => ' Zone Manager']);
    }
}
