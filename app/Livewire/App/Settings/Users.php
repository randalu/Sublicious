<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;

class Users extends Component
{
    public function render()
    {
        return view('livewire.app.settings.users')
            ->layout('layouts.app', ['heading' => ' Users']);
    }
}
