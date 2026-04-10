<?php

namespace App\Livewire\Admin\Plans;

use Livewire\Component;

class PlanForm extends Component
{
    public function render()
    {
        return view('livewire.admin.plans.plan-form')
            ->layout('layouts.admin', ['heading' => ' Plan Form']);
    }
}
