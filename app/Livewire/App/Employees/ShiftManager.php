<?php

namespace App\Livewire\App\Employees;

use Livewire\Component;

class ShiftManager extends Component
{
    public function render()
    {
        return view('livewire.app.employees.shift-manager')
            ->layout('layouts.app', ['heading' => ' Shift Manager']);
    }
}
