<?php

namespace App\Livewire\Admin\Businesses;

use Livewire\Component;

class BusinessForm extends Component
{
    public function render()
    {
        return view('livewire.admin.businesses.business-form')
            ->layout('layouts.admin', ['heading' => ' Business Form']);
    }
}
