<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;

class ApiKeys extends Component
{
    public function render()
    {
        return view('livewire.admin.settings.api-keys')
            ->layout('layouts.admin', ['heading' => ' Api Keys']);
    }
}
