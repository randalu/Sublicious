<?php

namespace App\Livewire\App\Reports;

use Livewire\Component;

class FinancialReport extends Component
{
    public function render()
    {
        return view('livewire.app.reports.financial-report')
            ->layout('layouts.app', ['heading' => ' Financial Report']);
    }
}
