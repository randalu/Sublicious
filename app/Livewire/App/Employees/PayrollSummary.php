<?php

namespace App\Livewire\App\Employees;

use Livewire\Component;

class PayrollSummary extends Component
{
    public function render()
    {
        return view('livewire.app.employees.payroll-summary')
            ->layout('layouts.app', ['heading' => ' Payroll Summary']);
    }
}
