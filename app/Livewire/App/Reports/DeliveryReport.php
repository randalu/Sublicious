<?php

namespace App\Livewire\App\Reports;

use Livewire\Component;

class DeliveryReport extends Component
{
    public function render()
    {
        return view('livewire.app.reports.delivery-report')
            ->layout('layouts.app', ['heading' => ' Delivery Report']);
    }
}
