<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.businesses.show', $business) }}" wire:navigate class="text-sm text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Audit Logs</h1>
                <p class="text-sm text-gray-500">{{ $business->name }}</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-48">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by event, user, model…"
                   class="pl-9 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        </div>
        <select wire:model.live="event" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All Events</option>
            @foreach($events as $evt)
                <option value="{{ $evt }}">{{ $evt }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($logs->isEmpty())
            <div class="p-8 text-center text-gray-400 text-sm">No audit logs match the current filters.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-700">{{ $log->user_email ?? 'System' }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ $log->event }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-500">
                                {{ $log->auditable_type ? class_basename($log->auditable_type) : '—' }}
                                @if($log->auditable_id) #{{ $log->auditable_id }} @endif
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-400">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
