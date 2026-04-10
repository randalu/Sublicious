<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;

class DiscountCodes extends Component
{
    public function render()
    {
        return view('livewire.app.settings.discount-codes')
            ->layout('layouts.app', ['heading' => ' Discount Codes']);
    }
}
