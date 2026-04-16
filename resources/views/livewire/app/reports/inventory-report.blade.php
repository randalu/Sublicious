<div>
    @include('livewire.app.reports._tabs')

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Inventory Report</h1>
        <div class="flex items-center gap-3 flex-wrap">
            <input wire:model.live="dateFrom" type="date" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <span class="text-gray-400 text-sm">to</span>
            <input wire:model.live="dateTo" type="date" class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <button wire:click="exportCsv"
                    class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </button>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['Total Items', $this->summary['totalItems'], 'blue'],
            ['Low Stock', $this->summary['lowStock'], $this->summary['lowStock'] > 0 ? 'amber' : 'green'],
            ['Out of Stock', $this->summary['outOfStock'], $this->summary['outOfStock'] > 0 ? 'red' : 'green'],
            ['Total Value', number_format($this->summary['totalValue'], 2), 'purple'],
        ] as [$label, $value, $color])
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $label }}</p>
                <p class="text-2xl font-bold text-{{ $color }}-600 mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    {{-- Period Movement --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Restocked</p>
            <p class="text-xl font-bold text-green-600 mt-1">+{{ number_format($this->summary['restocked'], 1) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Used (Orders)</p>
            <p class="text-xl font-bold text-blue-600 mt-1">-{{ number_format($this->summary['deducted'], 1) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Wasted</p>
            <p class="text-xl font-bold text-red-600 mt-1">-{{ number_format($this->summary['wasted'], 1) }}</p>
        </div>
    </div>

    {{-- Item Breakdown --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Item Breakdown</h2>
        </div>
        @if(empty($this->itemBreakdown))
            <div class="p-8 text-center text-gray-400 text-sm">No inventory items yet.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Value</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Restocked</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Used</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wasted</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->itemBreakdown as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-sm font-medium text-gray-900">
                                {{ $row['name'] }}
                                <span class="text-gray-400 text-xs ml-1">{{ $row['unit'] }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-sm text-right {{ $row['current_stock'] <= 0 ? 'text-red-600 font-bold' : ($row['is_low'] ? 'text-amber-600 font-medium' : 'text-gray-700') }}">
                                {{ number_format($row['current_stock'], 1) }}
                            </td>
                            <td class="px-4 py-2.5 text-sm text-right text-gray-700">{{ number_format($row['value'], 2) }}</td>
                            <td class="px-4 py-2.5 text-sm text-right text-green-600">{{ $row['restocked'] > 0 ? '+' . number_format($row['restocked'], 1) : '—' }}</td>
                            <td class="px-4 py-2.5 text-sm text-right text-blue-600">{{ $row['deducted'] > 0 ? '-' . number_format($row['deducted'], 1) : '—' }}</td>
                            <td class="px-4 py-2.5 text-sm text-right text-red-600">{{ $row['wasted'] > 0 ? '-' . number_format($row['wasted'], 1) : '—' }}</td>
                            <td class="px-4 py-2.5 text-center">
                                @if($row['current_stock'] <= 0)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">Out</span>
                                @elseif($row['is_low'])
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Low</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">OK</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
