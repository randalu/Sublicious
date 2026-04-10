<div>
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Financial Report</h1>
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
            ['Net Revenue', number_format($this->stats['netRevenue'], 2), 'green'],
            ['Total Orders', $this->stats['totalOrders'], 'blue'],
            ['Avg Order Value', number_format($this->stats['avgOrderValue'], 2), 'purple'],
            ['Net Profit', number_format($this->stats['profit'], 2), $this->stats['profit'] >= 0 ? 'green' : 'red'],
        ] as [$label, $value, $color])
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $label }}</p>
                <p class="text-2xl font-bold text-{{ $color }}-600 mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    {{-- Breakdown cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Revenue Breakdown</h2>
            <div class="space-y-3">
                @foreach([
                    ['Gross Sales', $this->stats['grossRevenue']],
                    ['Service Charges', $this->stats['serviceCharges']],
                    ['Delivery Fees', $this->stats['deliveryFees']],
                    ['Discounts', -$this->stats['discounts']],
                    ['Refunds', -$this->stats['refunds']],
                ] as [$label, $value])
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">{{ $label }}</span>
                        <span class="{{ $value < 0 ? 'text-red-600' : 'text-gray-900' }} font-medium">
                            {{ $value < 0 ? '-' : '' }}{{ number_format(abs($value), 2) }}
                        </span>
                    </div>
                @endforeach
                <div class="border-t border-gray-100 pt-3 flex items-center justify-between text-sm font-bold">
                    <span class="text-gray-800">Net Revenue</span>
                    <span class="text-gray-900">{{ number_format($this->stats['netRevenue'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Revenue by Order Type</h2>
            <div class="space-y-3">
                @forelse($this->revenueByType as $row)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $row['order_type']) }}</span>
                        <div class="text-right">
                            <span class="font-medium text-gray-900">{{ number_format($row['revenue'], 2) }}</span>
                            <span class="text-gray-400 ml-1">({{ $row['orders'] }})</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">No data</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Daily revenue table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Daily Revenue</h2>
        </div>
        @if(empty($this->dailyRevenue))
            <div class="p-8 text-center text-gray-400 text-sm">No data for this period.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Orders</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->dailyRevenue as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-sm text-gray-700">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y, D') }}</td>
                            <td class="px-4 py-2.5 text-sm text-right text-gray-600">{{ $row['orders'] }}</td>
                            <td class="px-4 py-2.5 text-sm text-right font-medium text-gray-900">{{ number_format($row['revenue'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
