<div class="space-y-5">
    {{-- Filters --}}
    <div class="flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-52">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search"
                   placeholder="Search by business name or email…"
                   class="pl-9 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        </div>
        <select wire:model.live="statusFilter"
                class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="trialing">Trialing</option>
            <option value="past_due">Past Due</option>
            <option value="canceled">Canceled</option>
            <option value="incomplete">Incomplete</option>
            <option value="incomplete_expired">Incomplete Expired</option>
        </select>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <p class="text-sm text-gray-500">{{ $subscriptions->total() }} subscriptions</p>
        </div>

        @if($subscriptions->isEmpty())
            <div class="p-12 text-center text-gray-400 text-sm">No subscriptions found.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing Cycle</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Period Ends</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($subscriptions as $sub)
                        @php
                            $statusColors = [
                                'active'             => 'bg-green-100 text-green-700',
                                'trialing'           => 'bg-blue-100 text-blue-700',
                                'past_due'           => 'bg-red-100 text-red-700',
                                'canceled'           => 'bg-gray-100 text-gray-500',
                                'incomplete'         => 'bg-yellow-100 text-yellow-700',
                                'incomplete_expired' => 'bg-red-100 text-red-600',
                            ];
                            $color = $statusColors[$sub->stripe_status ?? ''] ?? 'bg-gray-100 text-gray-500';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $sub->business_name }}</p>
                                <p class="text-xs text-gray-400">{{ $sub->business_email }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $sub->plan_name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                    {{ ucwords(str_replace('_', ' ', $sub->stripe_status ?? 'unknown')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 capitalize">
                                {{ $sub->billing_cycle ?? 'monthly' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($sub->current_period_end)
                                    {{ \Carbon\Carbon::parse($sub->current_period_end)->format('d M Y') }}
                                    @if(\Carbon\Carbon::parse($sub->current_period_end)->isPast())
                                        <span class="ml-1 text-xs text-red-500">(expired)</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($sub->created_at)->format('d M Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($subscriptions->hasPages())
                <div class="px-5 py-3 border-t border-gray-100">
                    {{ $subscriptions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
