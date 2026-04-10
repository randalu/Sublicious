<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Commission Tracker</h1>
            <p class="text-sm text-gray-500 mt-1">Track rider earnings and manage payouts</p>
        </div>
        <input wire:model.live="month" type="month"
               class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Rider earnings table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Monthly Earnings — {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</h2>
        </div>
        @if($riders->isEmpty())
            <div class="px-4 py-8 text-center text-sm text-gray-400">No riders found.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rider</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deliveries (month)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Commission (month)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">All-time Deliveries</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">All-time Earned</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($riders as $rider)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $rider->name }}</p>
                                <p class="text-xs text-gray-400">{{ $rider->phone }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $rider->month_deliveries }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">{{ number_format($rider->month_commission ?? 0, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-500">{{ $rider->total_deliveries }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-500">{{ number_format($rider->total_commission_earned, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2 py-1 rounded-full font-medium
                                             {{ $rider->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $rider->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($rider->month_deliveries > 0)
                                    <button wire:click="openPayoutModal({{ $rider->id }})"
                                            class="text-xs px-3 py-1.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                        Create Payout
                                    </button>
                                @else
                                    <span class="text-xs text-gray-300">No deliveries</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Payout records --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Payout Records</h2>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input wire:model.live="showAllPayouts" type="checkbox" class="rounded border-gray-300 text-primary-600">
                Show paid
            </label>
        </div>
        @if($payouts->isEmpty())
            <div class="px-4 py-8 text-center text-sm text-gray-400">No {{ $showAllPayouts ? '' : 'unpaid ' }}payout records.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rider</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deliveries</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($payouts as $payout)
                        <tr class="hover:bg-gray-50 transition-colors {{ $payout->is_paid ? 'opacity-70' : '' }}">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $payout->rider?->name ?? '—' }}</p>
                                @if($payout->notes)
                                    <p class="text-xs text-gray-400">{{ $payout->notes }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $payout->period_start->format('d M Y') }}
                                <span class="text-gray-400">→</span>
                                {{ $payout->period_end->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $payout->total_deliveries }}</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">{{ number_format($payout->total_commission, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($payout->is_paid)
                                    <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 font-medium">
                                        Paid {{ $payout->paid_at?->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 font-medium">Unpaid</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(! $payout->is_paid)
                                    <button wire:click="markPaid({{ $payout->id }})"
                                            wire:confirm="Mark this payout as paid?"
                                            class="text-xs px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                        Mark Paid
                                    </button>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Create Payout Modal --}}
    @if($showPayoutModal)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="$set('showPayoutModal', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Create Payout</h2>
                <form wire:submit="createPayout" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Period Start</label>
                        <input wire:model="periodStart" type="date"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('periodStart') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Period End</label>
                        <input wire:model="periodEnd" type="date"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('periodEnd') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <input wire:model="notes" type="text" placeholder="Optional note"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-60 transition-colors">
                            <span wire:loading.remove>Create Payout</span>
                            <span wire:loading>Creating…</span>
                        </button>
                        <button type="button" wire:click="$set('showPayoutModal', false)"
                                class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
