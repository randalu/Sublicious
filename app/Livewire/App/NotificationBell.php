<?php

namespace App\Livewire\App;

use Livewire\Component;

class NotificationBell extends Component
{
    public function render()
    {
        $unread = 0;
        if (auth()->check() && auth()->user()->business_id) {
            $unread = \App\Models\Notification::where('user_id', auth()->id())
                ->whereNull('read_at')
                ->count();
        }
        return view('livewire.app.notification-bell', compact('unread'));
    }
}
