<?php

namespace App\Livewire\App\Reports;

use Livewire\Component;

class OrderReport extends Component
{
    public function render()
    {
        return view('livewire.app.reports.order-report')
            ->layout('layouts.app', ['heading' => ' Order Report']);
    }
}
