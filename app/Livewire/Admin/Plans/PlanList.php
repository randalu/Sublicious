<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;

class PlanList extends Component
{
    public function render()
    {
        return view('livewire.admin.plans.plan-list')
            ->layout('layouts.admin', ['heading' => ' Plan List']);
    }
}
