<?php

namespace App\Livewire\Admin\Businesses;

use Livewire\Component;

class BusinessLogs extends Component
{
    public function render()
    {
        return view('livewire.admin.businesses.business-logs')
            ->layout('layouts.admin', ['heading' => ' Business Logs']);
    }
}
