<?php

namespace App\Livewire\App;

use App\Models\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationList extends Component
{
    use WithPagination;

    public function markAsRead(string $id): void
    {
        Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllRead(): void
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function delete(string $id): void
    {
        Notification::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();
    }

    public function render()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderByRaw('read_at IS NOT NULL')
            ->orderByDesc('created_at')
            ->paginate(30);

        $unreadCount = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return view('livewire.app.notification-list', compact('notifications', 'unreadCount'))
            ->layout('layouts.app', ['heading' => 'Notifications']);
    }
}
