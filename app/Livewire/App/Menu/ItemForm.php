<?php

namespace App\Livewire\App\Menu;

use Livewire\Component;

class ItemForm extends Component
{
    public function render()
    {
        return view('livewire.app.menu.item-form')
            ->layout('layouts.app', ['heading' => ' Item Form']);
    }
}
