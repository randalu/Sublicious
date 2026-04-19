<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Transaction Log</h1>
            <p class="text-sm text-gray-500 mt-1">Stock movement history</p>
        </div>
        <a href="{{ route('app.inventory') }}" wire:navigate
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Inventory
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-5">
        <select wire:model.live="itemFilter"
                class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All Items</option>
            @foreach($inventoryItems as $inv)
                <option value="{{ $inv->id }}">{{ $inv->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="typeFilter"
                class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All Types</option>
            <option value="restock">Restock</option>
            <option value="deduction">Deduction</option>
            <option value="adjustment">Adjustment</option>
            <option value="waste">Waste</option>
        </select>
        <input wire:model.live="dateFrom" type="date" placeholder="From"
               class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        <input wire:model.live="dateTo" type="date" placeholder="To"
               class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
    </div>

    @if($transactions->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
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
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Before</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Change</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">After</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">By</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($transactions as $tx)
                        @php
                            $change = $tx->quantity_after - $tx->quantity_before;
                            $typeColors = [
                                'restock' => 'bg-green-100 text-green-700',
                                'deduction' => 'bg-blue-100 text-blue-700',
                                'adjustment' => 'bg-gray-100 text-gray-700',
                                'waste' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                {{ $tx->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $tx->inventoryItem?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $typeColors[$tx->type] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 text-right tabular-nums">
                                {{ rtrim(rtrim(number_format($tx->quantity_before, 3), '0'), '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-right tabular-nums {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $change >= 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($change, 3), '0'), '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium tabular-nums">
                                {{ rtrim(rtrim(number_format($tx->quantity_after, 3), '0'), '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $tx->user?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 max-w-[200px] truncate" title="{{ $tx->notes }}">
                                {{ $tx->notes ?? '—' }}
                            </td>
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
