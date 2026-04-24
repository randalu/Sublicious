<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Stock Transactions</h1>
            <p class="text-sm text-gray-500 mt-1">History of all inventory movements</p>
        </div>
        <a href="{{ route('app.inventory') }}"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Inventory
        </a>
    </div>

    <div class="flex flex-wrap gap-3 mb-5">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by item, user, or notes…"
                   class="pl-9 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        </div>
        <select wire:model.live="typeFilter"
                class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All Types</option>
            <option value="restock">Restock</option>
            <option value="deduction">Deduction</option>
            <option value="waste">Waste</option>
            <option value="adjustment">Adjustment</option>
        </select>
        <select wire:model.live="itemFilter"
                class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All Items</option>
            @foreach($inventoryItems as $invItem)
                <option value="{{ $invItem->id }}">{{ $invItem->name }}</option>
            @endforeach
        </select>
        <input wire:model.live="dateFrom" type="date"
               class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        <input wire:model.live="dateTo" type="date"
               class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
    </div>

    @if($transactions->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No transactions found.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Before</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">After</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($transactions as $txn)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $txn->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $txn->inventoryItem?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $colors = [
                                        'restock' => 'bg-green-100 text-green-700',
                                        'deduction' => 'bg-orange-100 text-orange-700',
                                        'waste' => 'bg-red-100 text-red-700',
                                        'adjustment' => 'bg-blue-100 text-blue-700',
                                    ];
                                @endphp
                                <span class="text-xs px-2 py-1 rounded-full {{ $colors[$txn->type] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($txn->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium {{ $txn->type === 'restock' ? 'text-green-600' : ($txn->type === 'adjustment' ? 'text-blue-600' : 'text-red-600') }}">
                                {{ $txn->type === 'restock' ? '+' : ($txn->type === 'adjustment' ? '' : '-') }}{{ number_format(abs($txn->quantity), 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($txn->quantity_before, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900">{{ number_format($txn->quantity_after, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $txn->user?->name ?? 'System' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 max-w-[200px] truncate">{{ $txn->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="mt-4">{{ $transactions->links() }}</div>
        @endif
    @endif
</div>
