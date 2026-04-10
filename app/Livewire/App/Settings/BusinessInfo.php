<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;

class BusinessInfo extends Component
{
    public function render()
    {
        return view('livewire.app.settings.business-info')
            ->layout('layouts.app', ['heading' => ' Business Info']);
    }
}
