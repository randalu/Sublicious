<div>
    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 mb-6">
        @php
            $currency = auth()->user()->business->currency ?? 'USD';
            $cards = [
                ['label' => 'Orders Today', 'value' => number_format($stats['orders_today']), 'color' => 'blue'],
                ['label' => 'Revenue Today', 'value' => $currency . ' ' . number_format($stats['revenue_today'], 2), 'color' => 'green'],
                ['label' => 'Pending Orders', 'value' => $stats['pending_orders'], 'color' => 'yellow'],
                ['label' => 'Active Deliveries', 'value' => $stats['active_deliveries'], 'color' => 'purple'],
                ['label' => 'Orders This Month', 'value' => $stats['orders_this_month'], 'color' => 'indigo'],
                ['label' => 'Monthly Limit', 'value' => number_format($stats['monthly_limit']), 'color' => 'gray'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="bg-white rounded-xl p-4 shadow-sm ring-1 ring-gray-900/5">
                <p class="text-xs font-medium text-gray-500">{{ $card['label'] }}</p>
                <p class="text-xl font-bold text-gray-900 mt-1 truncate">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Pending Orders --}}
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Pending Orders</h3>
                <a href="{{ route('app.orders') }}" class="text-xs text-primary-600 hover:text-primary-700">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($pendingOrders as $order)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $order->customer_name ?? 'Walk-in' }} ·
                                {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                                @if($order->table) · {{ $order->table->displayName() }} @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900">{{ auth()->user()->business->currency }} {{ number_format($order->total, 2) }}</span>
                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700">Pending</span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-400">No pending orders. 🎉</div>
                @endforelse
            </div>
        </div>

        {{-- Today's orders --}}
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Today's Orders</h3>
                <a href="{{ route('app.orders') }}" class="text-xs text-primary-600 hover:text-primary-700">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentOrders as $order)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500">{{ $order->created_at->format('h:i A') }} · {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900">{{ auth()->user()->business->currency }} {{ number_format($order->total, 2) }}</span>
                            @php $color = $order->statusColor(); @endphp
                            <span class="inline-flex items-center rounded-full bg-{{ $color }}-100 px-2 py-0.5 text-xs font-medium text-{{ $color }}-700">
                                {{ $order->statusLabel() }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-400">No orders today yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
