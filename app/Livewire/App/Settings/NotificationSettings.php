<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;

class NotificationSettings extends Component
{
    public function render()
    {
        return view('livewire.app.settings.notification-settings')
            ->layout('layouts.app', ['heading' => ' Notification Settings']);
    }
}
