<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;

class Platform extends Component
{
    public function render()
    {
        return view('livewire.admin.settings.platform')
            ->layout('layouts.admin', ['heading' => ' Platform']);
    }
}
