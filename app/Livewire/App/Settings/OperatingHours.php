<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;

class OperatingHours extends Component
{
    public function render()
    {
        return view('livewire.app.settings.operating-hours')
            ->layout('layouts.app', ['heading' => ' Operating Hours']);
    }
}
