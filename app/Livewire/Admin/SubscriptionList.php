<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class SubscriptionList extends Component
{
    public function render()
    {
        return view('livewire.admin.subscription-list')
            ->layout('layouts.admin', ['heading' => ' Subscription List']);
    }
}
