<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;

class Security extends Component
{
    public function render()
    {
        return view('livewire.app.settings.security')
            ->layout('layouts.app', ['heading' => ' Security']);
    }
}
