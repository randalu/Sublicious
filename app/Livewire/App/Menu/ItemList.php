<?php

namespace App\Livewire\App\Menu;

use Livewire\Component;

class ItemList extends Component
{
    public function render()
    {
        return view('livewire.app.menu.item-list')
            ->layout('layouts.app', ['heading' => ' Item List']);
    }
}
