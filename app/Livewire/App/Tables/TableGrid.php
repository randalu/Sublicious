<?php

namespace App\Livewire\App\Tables;

use Livewire\Component;

class TableGrid extends Component
{
    public function render()
    {
        return view('livewire.app.tables.table-grid')
            ->layout('layouts.app', ['heading' => ' Table Grid']);
    }
}
