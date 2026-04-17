<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('app.inventory') }}" class="text-sm text-primary-600 hover:underline">&larr; Back to Inventory</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $this->item->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                Current stock: <span class="font-medium text-gray-900">{{ number_format($this->item->current_stock, $this->item->unit === 'pcs' ? 0 : 2) }} {{ $this->item->unit }}</span>
                &middot; Cost: {{ number_format($this->item->cost_per_unit, 2) }}/{{ $this->item->unit }}
            </p>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-5">
        <select wire:model.live="typeFilter"
                class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All Types</option>
            <option value="restock">Restock</option>
            <option value="deduction">Deduction</option>
            <option value="waste">Waste</option>
            <option value="adjustment">Adjustment</option>
        </select>
    </div>

    @if($transactions->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No transaction history for this item.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Before</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">After</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($transactions as $tx)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $tx->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $badge = match($tx->type) {
                                        'restock'    => 'bg-green-100 text-green-700',
                                        'deduction'  => 'bg-blue-100 text-blue-700',
                                        'waste'      => 'bg-red-100 text-red-700',
                                        'adjustment' => 'bg-purple-100 text-purple-700',
                                        default      => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="text-xs px-2 py-1 rounded-full {{ $badge }}">{{ ucfirst($tx->type) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium {{ in_array($tx->type, ['deduction', 'waste']) ? 'text-red-600' : 'text-green-600' }}">
                                {{ in_array($tx->type, ['deduction', 'waste']) ? '-' : ($tx->type === 'adjustment' ? '=' : '+') }}{{ number_format($tx->quantity, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($tx->quantity_before, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 font-medium">{{ number_format($tx->quantity_after, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-[200px] truncate">{{ $tx->notes ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $tx->user?->name ?? '—' }}</td>
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
