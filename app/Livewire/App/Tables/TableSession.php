<?php

namespace App\Livewire\App\Tables;

use Livewire\Component;

class TableSession extends Component
{
    public function render()
    {
        return view('livewire.app.tables.table-session')
            ->layout('layouts.app', ['heading' => ' Table Session']);
    }
}
