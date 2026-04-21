<div
    x-data="{
        showInstall: $persist(false).as('pwa_install_seen_count') < 2 && false,
        installCount: $persist(0).as('pwa_install_seen_count'),
        canInstall: false,
        init() {
            window.addEventListener('pwa-installable', () => {
                this.canInstall = true;
                if (this.installCount < 2) {
                    this.showInstall = true;
                    this.installCount++;
                }
            });
        },
        async install() {
            if (!window.deferredInstallPrompt) return;
            window.deferredInstallPrompt.prompt();
            const { outcome } = await window.deferredInstallPrompt.userChoice;
            window.deferredInstallPrompt = null;
            this.showInstall = false;
        },
        dismiss() { this.showInstall = false; }
    }"
>
    {{-- PWA Install Prompt --}}
    <div x-show="showInstall && canInstall" x-transition
         class="mb-4 flex items-center justify-between gap-4 bg-primary-600 text-white rounded-xl px-5 py-3.5 shadow">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <div>
                <p class="font-semibold text-sm">Install Sublicious</p>
                <p class="text-xs text-primary-200">Add to your home screen for quick access</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button @click="install()" class="px-3 py-1.5 bg-white text-primary-700 text-sm font-semibold rounded-lg hover:bg-primary-50 transition-colors">Install</button>
            <button @click="dismiss()" class="text-primary-200 hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

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

    {{-- Low Stock Alerts --}}
    @if($lowStockItems->isNotEmpty())
        <div class="mt-6 bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-amber-100">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </span>
                    <h3 class="text-sm font-semibold text-gray-900">Low Stock Alerts</h3>
                </div>
                <a href="{{ route('app.inventory') }}" class="text-xs text-primary-600 hover:text-primary-700">View inventory →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($lowStockItems as $item)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $item->name }}</p>
                            <p class="text-xs text-gray-500">Threshold: {{ number_format($item->low_stock_threshold, $item->unit === 'pcs' ? 0 : 2) }} {{ $item->unit }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium {{ $item->current_stock <= 0 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ number_format($item->current_stock, $item->unit === 'pcs' ? 0 : 2) }} {{ $item->unit }}
                            </span>
                            @if($item->current_stock <= 0)
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Out</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Low</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
