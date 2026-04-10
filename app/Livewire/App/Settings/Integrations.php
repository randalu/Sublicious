<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;

class Integrations extends Component
{
    public function render()
    {
        return view('livewire.app.settings.integrations')
            ->layout('layouts.app', ['heading' => ' Integrations']);
    }
}
