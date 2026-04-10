<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Component;

class Smtp extends Component
{
    public function render()
    {
        return view('livewire.admin.settings.smtp')
            ->layout('layouts.admin', ['heading' => ' Smtp']);
    }
}
