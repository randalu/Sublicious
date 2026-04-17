<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-sm text-gray-500 mt-1">
                @if($unreadCount > 0)
                    <span class="text-primary-600 font-medium">{{ $unreadCount }} unread</span>
                @else
                    All caught up
                @endif
            </p>
        </div>
        @if($unreadCount > 0)
            <button wire:click="markAllRead"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-primary-600 border border-primary-300 rounded-lg hover:bg-primary-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Mark All Read
            </button>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-gray-500 text-sm mt-3">No notifications yet.</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($notifications as $notification)
                <div class="bg-white rounded-xl border {{ $notification->read_at ? 'border-gray-200' : 'border-primary-200 bg-primary-50/30' }} p-4 flex items-start gap-4 group">
                    <div class="flex-shrink-0 mt-0.5">
                        @if($notification->read_at)
                            <div class="w-2.5 h-2.5 rounded-full bg-gray-300"></div>
                        @else
                            <div class="w-2.5 h-2.5 rounded-full bg-primary-500"></div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                        @if($notification->body)
                            <p class="text-sm text-gray-600 mt-0.5">{{ $notification->body }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        @if(!$notification->read_at)
                            <button wire:click="markAsRead('{{ $notification->id }}')"
                                    class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                    title="Mark as read">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        @endif
                        <button wire:click="delete('{{ $notification->id }}')"
                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        @if($notifications->hasPages())
            <div class="mt-4">{{ $notifications->links() }}</div>
        @endif
    @endif
</div>
