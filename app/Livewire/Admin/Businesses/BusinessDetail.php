<?php

namespace App\Livewire\Admin\Businesses;

use Livewire\Component;

class BusinessDetail extends Component
{
    public function render()
    {
        return view('livewire.admin.businesses.business-detail')
            ->layout('layouts.admin', ['heading' => ' Business Detail']);
    }
}
