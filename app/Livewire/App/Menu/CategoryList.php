<?php

namespace App\Livewire\App\Menu;

use Livewire\Component;

class CategoryList extends Component
{
    public function render()
    {
        return view('livewire.app.menu.category-list')
            ->layout('layouts.app', ['heading' => ' Category List']);
    }
}
