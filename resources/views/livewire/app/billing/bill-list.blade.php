<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bills</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $bills->total() }} bill(s) ·
                Today's revenue: <span class="font-semibold text-gray-700">{{ number_format($todayTotal, 2) }}</span>
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by bill # or customer…"
                   class="pl-9 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        </div>
        <input wire:model.live="dateFilter" type="date"
               class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        <select wire:model.live="paymentStatusFilter"
                class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All Statuses</option>
            <option value="paid">Paid</option>
            <option value="unpaid">Unpaid</option>
            <option value="refunded">Refunded</option>
        </select>
        @if($dateFilter || $paymentStatusFilter || $search)
            <button wire:click="$set('dateFilter', ''); $set('paymentStatusFilter', ''); $set('search', '')"
                    class="text-xs text-gray-500 hover:text-gray-700 underline whitespace-nowrap">
                Clear filters
            </button>
        @endif
    </div>

    @if($bills->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No bills found.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bill #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer / Table</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($bills as $bill)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <a href="{{ route('app.billing.show', $bill) }}" wire:navigate
                                   class="text-sm font-mono font-semibold text-primary-600 hover:underline">
                                    {{ $bill->bill_number }}
                                </a>
                                @if($bill->order)
                                    <p class="text-xs text-gray-400">Order: {{ $bill->order->order_number }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $bill->customer_name ?? '—' }}</p>
                                @if($bill->table)
                                    <p class="text-xs text-gray-400">Table {{ $bill->table->table_number }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <p>{{ $bill->created_at->format('d M Y') }}</p>
                                @if($bill->paid_at)
                                    <p class="text-xs text-gray-400">Paid {{ $bill->paid_at->format('H:i') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                                {{ number_format($bill->total, 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 capitalize">
                                {{ $bill->payment_method ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColor = match($bill->payment_status) {
                                        'paid' => 'green',
                                        'refunded' => 'purple',
                                        default => 'yellow',
                                    };
                                @endphp
                                <span class="text-xs px-2 py-1 rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 font-medium capitalize">
                                    {{ $bill->payment_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('app.billing.show', $bill) }}" wire:navigate
                                   class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors inline-flex">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($bills->hasPages())
            <div class="mt-4">{{ $bills->links() }}</div>
        @endif
    @endif
</div>
