<?php

namespace App\Livewire\Public;

use Livewire\Component;

class OnlineOrderForm extends Component
{
    public function render()
    {
        return view('livewire.public.online-order-form')
            ->layout('layouts.public', ['heading' => ' Online Order Form']);
    }
}
