<div wire:poll.30s>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kitchen Display</h1>
            <p class="text-sm text-gray-500">Auto-refreshes every 30 seconds · {{ now()->format('H:i:s') }}</p>
        </div>
        <a href="{{ route('app.orders') }}" wire:navigate
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
            Orders Board
        </a>
    </div>

    @php
        $columnConfig = [
            'pending'   => ['label' => 'Pending',   'color' => 'yellow', 'next' => 'Accept'],
            'accepted'  => ['label' => 'Accepted',  'color' => 'blue',   'next' => 'Start Prep'],
            'preparing' => ['label' => 'Preparing', 'color' => 'orange', 'next' => 'Mark Ready'],
            'ready'     => ['label' => 'Ready',     'color' => 'green',  'next' => 'Dispatch'],
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($statuses as $status)
            @php $cfg = $columnConfig[$status]; $statusOrders = $orders->get($status, collect()); @endphp
            <div class="space-y-3">
                {{-- Column header --}}
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-{{ $cfg['color'] }}-400"></span>
                        <h2 class="text-sm font-semibold text-gray-700">{{ $cfg['label'] }}</h2>
                    </div>
                    <span class="text-xs font-medium text-gray-400 bg-gray-100 rounded-full px-2 py-0.5">{{ $statusOrders->count() }}</span>
                </div>

                {{-- Cards --}}
                @if($statusOrders->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center">
                        <p class="text-xs text-gray-400">No orders</p>
                    </div>
                @else
                    @foreach($statusOrders as $order)
                        <div class="bg-white rounded-xl border border-{{ $cfg['color'] }}-200 shadow-sm overflow-hidden">
                            {{-- Card header --}}
                            <div class="px-3 py-2 bg-{{ $cfg['color'] }}-50 border-b border-{{ $cfg['color'] }}-100 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 font-mono">{{ $order->order_number }}</p>
                                    <p class="text-xs text-gray-500 capitalize">
                                        {{ str_replace('_', ' ', $order->order_type) }}
                                        @if($order->table) · Table {{ $order->table->table_number }} @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-semibold text-{{ $cfg['color'] }}-700">
                                        {{ $order->created_at->diffForHumans(null, true) }}
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $order->created_at->format('H:i') }}</p>
                                </div>
                            </div>

                            {{-- Items list --}}
                            <div class="px-3 py-2 divide-y divide-gray-50">
                                @foreach($order->items as $oi)
                                    <div class="py-1.5">
                                        <div class="flex items-baseline justify-between gap-1">
                                            <span class="text-sm font-medium text-gray-800">
                                                <span class="font-bold text-gray-600 mr-1">{{ $oi->quantity }}×</span>{{ $oi->name }}
                                                @if($oi->variant_name)
                                                    <span class="text-gray-400 text-xs">({{ $oi->variant_name }})</span>
                                                @endif
                                            </span>
                                        </div>
                                        @foreach($oi->addons as $addon)
                                            <p class="text-xs text-gray-400 ml-5">+ {{ $addon->name }}</p>
                                        @endforeach
                                        @if($oi->notes)
                                            <p class="text-xs text-amber-600 ml-5 italic">{{ $oi->notes }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Notes --}}
                            @if($order->notes)
                                <div class="px-3 pb-2">
                                    <p class="text-xs text-amber-600 bg-amber-50 rounded px-2 py-1">{{ $order->notes }}</p>
                                </div>
                            @endif

                            {{-- Action button --}}
                            @if($order->nextStatus())
                                <div class="px-3 pb-3">
                                    <button wire:click="advanceStatus({{ $order->id }})"
                                            wire:loading.attr="disabled"
                                            class="w-full py-1.5 text-xs font-semibold rounded-lg bg-{{ $cfg['color'] }}-600 text-white hover:bg-{{ $cfg['color'] }}-700 transition-colors">
                                        {{ $cfg['next'] }} →
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
    </div>
</div>
