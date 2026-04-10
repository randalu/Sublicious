<?php

namespace App\Livewire\Public;

use Livewire\Component;

class QrMenuViewer extends Component
{
    public function render()
    {
        return view('livewire.public.qr-menu-viewer')
            ->layout('layouts.public', ['heading' => ' Qr Menu Viewer']);
    }
}
