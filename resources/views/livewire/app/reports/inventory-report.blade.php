<div>
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Inventory Report</h1>
        <div class="flex items-center gap-3 flex-wrap">
            <input wire:model.live="dateFrom" type="date"
                   class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <span class="text-gray-400 text-sm">to</span>
            <input wire:model.live="dateTo" type="date"
                   class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
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
            ['Total Value', number_format($this->summary['totalValue'], 2), 'green'],
        ] as [$label, $value, $color])
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $label }}</p>
                <p class="text-2xl font-bold text-{{ $color }}-600 mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    {{-- Transaction summary --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        @foreach([
            ['Restocks', $this->summary['restockCount'], 'green'],
            ['Deductions', $this->summary['deductionCount'], 'blue'],
            ['Waste', $this->summary['wasteCount'], 'red'],
        ] as [$label, $value, $color])
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $label }}</p>
                <p class="text-xl font-bold text-{{ $color }}-600 mt-1">{{ $value }}</p>
                <p class="text-xs text-gray-400">in period</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        {{-- Top consumed items --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Top Consumed Items</h2>
            @if(empty($this->topConsumed))
                <p class="text-sm text-gray-400 italic">No deductions in this period.</p>
            @else
                <div class="space-y-3">
                    @foreach($this->topConsumed as $row)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-700">{{ $row['name'] }}</span>
                            <span class="font-medium text-gray-900">{{ number_format($row['total_used'], $row['unit'] === 'pcs' ? 0 : 2) }} {{ $row['unit'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Waste report --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Waste Report</h2>
            @if(empty($this->wasteReport))
                <p class="text-sm text-gray-400 italic">No waste recorded in this period.</p>
            @else
                <div class="space-y-3">
                    @foreach($this->wasteReport as $row)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-700">{{ $row['name'] }}</span>
                            <div class="text-right">
                                <span class="font-medium text-gray-900">{{ number_format($row['total_wasted'], $row['unit'] === 'pcs' ? 0 : 2) }} {{ $row['unit'] }}</span>
                                <span class="text-gray-400 ml-1">({{ number_format($row['total_wasted'] * $row['cost_per_unit'], 2) }})</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Low stock items table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Low Stock Items</h2>
        </div>
        @if(empty($this->lowStockItems))
            <div class="p-8 text-center text-gray-400 text-sm">All items are well stocked.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Current Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Threshold</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deficit</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->lowStockItems as $item)
                        @php
                            $deficit = max(0, $item['low_stock_threshold'] - $item['current_stock']);
                            $isPcs = $item['unit'] === 'pcs';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-sm font-medium text-gray-900">{{ $item['name'] }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-500">{{ $item['unit'] }}</td>
                            <td class="px-4 py-2.5 text-sm text-right font-medium {{ $item['current_stock'] <= 0 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ number_format($item['current_stock'], $isPcs ? 0 : 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-sm text-right text-gray-500">{{ number_format($item['low_stock_threshold'], $isPcs ? 0 : 2) }}</td>
                            <td class="px-4 py-2.5 text-sm text-right text-red-600">{{ number_format($deficit, $isPcs ? 0 : 2) }}</td>
                            <td class="px-4 py-2.5 text-center">
                                @if($item['current_stock'] <= 0)
                                    <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700">Out</span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-700">Low</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
