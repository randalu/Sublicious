<?php

namespace App\Livewire\App\Employees;

use Livewire\Component;

class EmployeeList extends Component
{
    public function render()
    {
        return view('livewire.app.employees.employee-list')
            ->layout('layouts.app', ['heading' => ' Employee List']);
    }
}
