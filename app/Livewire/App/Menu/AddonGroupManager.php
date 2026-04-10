<?php

namespace App\Livewire\App\Menu;

use Livewire\Component;

class AddonGroupManager extends Component
{
    public function render()
    {
        return view('livewire.app.menu.addon-group-manager')
            ->layout('layouts.app', ['heading' => ' Addon Group Manager']);
    }
}
