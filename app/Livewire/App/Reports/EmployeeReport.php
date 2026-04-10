<?php

namespace App\Livewire\App\Reports;

use Livewire\Component;

class EmployeeReport extends Component
{
    public function render()
    {
        return view('livewire.app.reports.employee-report')
            ->layout('layouts.app', ['heading' => ' Employee Report']);
    }
}
