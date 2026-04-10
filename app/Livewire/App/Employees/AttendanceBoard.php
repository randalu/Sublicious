<?php

namespace App\Livewire\App\Employees;

use Livewire\Component;

class AttendanceBoard extends Component
{
    public function render()
    {
        return view('livewire.app.employees.attendance-board')
            ->layout('layouts.app', ['heading' => ' Attendance Board']);
    }
}
