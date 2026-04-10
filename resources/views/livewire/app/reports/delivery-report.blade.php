<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Delivery Report</h1>
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

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['Total Deliveries', $this->summary['total'], 'blue'],
            ['Delivered', $this->summary['delivered'], 'green'],
            ['Failed', $this->summary['failed'], 'red'],
            ['Avg Time (min)', $this->summary['avg_time_min'], 'purple'],
        ] as [$label, $value, $color])
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $label }}</p>
                <p class="text-2xl font-bold text-{{ $color }}-600 mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase">Delivery Fees Collected</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($this->summary['total_fees'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase">Commissions Paid</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($this->summary['commissions'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-medium text-gray-500 uppercase">Net from Delivery</p>
            <p class="text-xl font-bold text-green-600 mt-1">{{ number_format($this->summary['total_fees'] - $this->summary['commissions'], 2) }}</p>
        </div>
    </div>

    {{-- Rider performance table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Rider Performance</h2>
        </div>
        @if(empty($this->riderPerformance))
            <div class="p-8 text-center text-gray-400 text-sm">No riders yet. <a href="{{ route('app.delivery.riders') }}" wire:navigate class="text-primary-600 hover:underline">Add riders</a>.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rider</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Deliveries (period)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Commission (period)</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">All-time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->riderPerformance as $rider)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $rider['name'] }}
                                @if($rider['vehicle_type']) <span class="text-xs text-gray-400">({{ $rider['vehicle_type'] }})</span> @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2 py-1 rounded-full {{ $rider['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $rider['is_active'] ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ $rider['period_deliveries'] }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($rider['period_commission'] ?? 0, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-500">{{ $rider['total_deliveries'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
