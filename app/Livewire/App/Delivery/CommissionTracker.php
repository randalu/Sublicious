<?php

namespace App\Livewire\App\Delivery;

use Livewire\Component;

class CommissionTracker extends Component
{
    public function render()
    {
        return view('livewire.app.delivery.commission-tracker')
            ->layout('layouts.app', ['heading' => ' Commission Tracker']);
    }
}
