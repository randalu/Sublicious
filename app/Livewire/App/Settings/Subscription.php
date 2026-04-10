<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;

class Subscription extends Component
{
    public function render()
    {
        return view('livewire.app.settings.subscription')
            ->layout('layouts.app', ['heading' => ' Subscription']);
    }
}
