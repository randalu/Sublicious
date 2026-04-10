<div>
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('app.orders') }}" wire:navigate
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Order #{{ $order->order_number }}</h1>
            <p class="text-sm text-gray-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
        </div>
        <div class="ml-auto flex items-center gap-2 flex-wrap justify-end">
            {{-- Status badge --}}
            <span class="text-xs px-2 py-1 rounded-full bg-{{ $order->statusColor() }}-100 text-{{ $order->statusColor() }}-700 font-medium">
                {{ $order->statusLabel() }}
            </span>
            {{-- Payment badge --}}
            @if($order->isPaid())
                <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 font-medium">Paid</span>
            @else
                <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 font-medium">Unpaid</span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main: items --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Order items --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-800">Items</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($order->items as $oi)
                        <div class="px-4 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-800">
                                        {{ $oi->name }}
                                        @if($oi->variant_name)
                                            <span class="text-gray-400">({{ $oi->variant_name }})</span>
                                        @endif
                                    </p>
                                    @foreach($oi->addons as $addon)
                                        <p class="text-xs text-gray-400">+ {{ $addon->name }}</p>
                                    @endforeach
                                    @if($oi->notes)
                                        <p class="text-xs text-gray-400 italic">{{ $oi->notes }}</p>
                                    @endif
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm text-gray-500">{{ $oi->quantity }} × {{ number_format($oi->unit_price, 2) }}</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ number_format($oi->subtotal, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center text-sm text-gray-400">No items in this order.</div>
                    @endforelse
                </div>

                {{-- Totals --}}
                <div class="px-4 py-3 border-t border-gray-100 space-y-1.5 bg-gray-50">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span>{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->service_charge > 0)
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Service Charge</span>
                            <span>{{ number_format($order->service_charge, 2) }}</span>
                        </div>
                    @endif
                    @if($order->delivery_fee > 0)
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Delivery Fee</span>
                            <span>{{ number_format($order->delivery_fee, 2) }}</span>
                        </div>
                    @endif
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-sm text-green-600">
                            <span>Discount</span>
                            <span>-{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-1.5 border-t border-gray-200">
                        <span>Total</span>
                        <span>{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Delivery info --}}
            @if($order->isDelivery() && $order->delivery)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <h2 class="font-semibold text-gray-800 mb-3">Delivery Info</h2>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-xs text-gray-500 font-medium">Status</dt>
                            <dd class="mt-0.5 text-gray-800">{{ ucfirst(str_replace('_', ' ', $order->delivery->status)) }}</dd>
                        </div>
                        @if($order->delivery->rider)
                            <div>
                                <dt class="text-xs text-gray-500 font-medium">Rider</dt>
                                <dd class="mt-0.5 text-gray-800">{{ $order->delivery->rider->name }}</dd>
                            </div>
                        @endif
                        @if($order->delivery->fee)
                            <div>
                                <dt class="text-xs text-gray-500 font-medium">Fee</dt>
                                <dd class="mt-0.5 text-gray-800">{{ number_format($order->delivery->fee, 2) }}</dd>
                            </div>
                        @endif
                        @if($order->delivery->assigned_at)
                            <div>
                                <dt class="text-xs text-gray-500 font-medium">Assigned</dt>
                                <dd class="mt-0.5 text-gray-800">{{ $order->delivery->assigned_at->format('H:i') }}</dd>
                            </div>
                        @endif
                        @if($order->delivery->picked_up_at)
                            <div>
                                <dt class="text-xs text-gray-500 font-medium">Picked Up</dt>
                                <dd class="mt-0.5 text-gray-800">{{ $order->delivery->picked_up_at->format('H:i') }}</dd>
                            </div>
                        @endif
                        @if($order->delivery->delivered_at)
                            <div>
                                <dt class="text-xs text-gray-500 font-medium">Delivered</dt>
                                <dd class="mt-0.5 text-gray-800">{{ $order->delivery->delivered_at->format('H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                    @if($order->delivery_address)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <dt class="text-xs text-gray-500 font-medium">Delivery Address</dt>
                            <dd class="mt-0.5 text-sm text-gray-800">{{ $order->delivery_address }}</dd>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Cancel reason --}}
            @if($order->status === 'cancelled' && $order->cancel_reason)
                <div class="bg-red-50 rounded-xl border border-red-200 px-4 py-3">
                    <p class="text-sm font-medium text-red-800">Cancellation Reason</p>
                    <p class="text-sm text-red-700 mt-0.5">{{ $order->cancel_reason }}</p>
                </div>
            @endif
        </div>

        {{-- Sidebar: info + actions --}}
        <div class="space-y-4">

            {{-- Order info card --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="font-semibold text-gray-800 mb-3">Order Info</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Type</dt>
                        <dd class="text-gray-800 font-medium capitalize">{{ str_replace('_', ' ', $order->order_type) }}</dd>
                    </div>
                    @if($order->table)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Table</dt>
                            <dd class="text-gray-800 font-medium">Table {{ $order->table->table_number }}</dd>
                        </div>
                    @endif
                    @if($order->customer_name)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Customer</dt>
                            <dd class="text-gray-800 font-medium">{{ $order->customer_name }}</dd>
                        </div>
                    @endif
                    @if($order->customer_phone)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Phone</dt>
                            <dd class="text-gray-800">{{ $order->customer_phone }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Source</dt>
                        <dd class="text-gray-800 uppercase text-xs font-medium">{{ $order->source }}</dd>
                    </div>
                    @if($order->payment_method && $order->payment_method !== 'pending')
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Payment</dt>
                            <dd class="text-gray-800 capitalize">{{ $order->payment_method }}</dd>
                        </div>
                    @endif
                    @if($order->notes)
                        <div class="pt-2 border-t border-gray-100">
                            <dt class="text-gray-500 mb-0.5">Notes</dt>
                            <dd class="text-gray-700 text-xs">{{ $order->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Action buttons --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                <h2 class="font-semibold text-gray-800 mb-1">Actions</h2>

                {{-- Advance status --}}
                @if($order->isActive() && $order->nextStatus())
                    <button wire:click="advanceStatus"
                            wire:loading.attr="disabled"
                            class="w-full py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition-colors">
                        Mark as {{ ucfirst(str_replace('_', ' ', $order->nextStatus())) }}
                    </button>
                @endif

                {{-- Mark paid --}}
                @if($order->isActive() && ! $order->isPaid())
                    <button wire:click="openPayModal"
                            class="w-full py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                        Collect Payment
                    </button>
                @endif

                {{-- Print bill --}}
                @if($order->bill)
                    <a href="{{ route('app.billing.show', $order->bill) }}" wire:navigate
                       class="block w-full py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors text-center">
                        View / Print Bill
                    </a>
                @endif

                {{-- Cancel --}}
                @if($order->isActive())
                    <button wire:click="openCancelModal"
                            class="w-full py-2.5 border border-red-200 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors">
                        Cancel Order
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Cancel modal --}}
    @if($showCancelModal)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="$set('showCancelModal', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Cancel Order</h2>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason for cancellation <span class="text-red-500">*</span></label>
                    <textarea wire:model="cancelReason" rows="3" placeholder="Please provide a reason…"
                              class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                    @error('cancelReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-3">
                    <button wire:click="confirmCancel"
                            wire:loading.attr="disabled"
                            class="flex-1 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-colors">
                        <span wire:loading.remove>Confirm Cancel</span>
                        <span wire:loading>Processing…</span>
                    </button>
                    <button wire:click="$set('showCancelModal', false)"
                            class="flex-1 py-2.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                        Back
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Payment modal --}}
    @if($showPayModal)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="$set('showPayModal', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">Collect Payment</h2>

                <div class="mb-4 text-sm text-gray-600 flex justify-between">
                    <span>Total Due</span>
                    <span class="font-bold text-gray-900 text-base">{{ number_format($order->total, 2) }}</span>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['cash' => 'Cash', 'card' => 'Card', 'online' => 'Online'] as $method => $label)
                                <button type="button" wire:click="$set('paymentMethod', '{{ $method }}')"
                                        class="py-2 text-sm font-medium rounded-lg border transition-colors
                                               {{ $paymentMethod === $method ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount Received</label>
                        <input wire:model="amountPaid" type="number" step="0.01" min="0"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    @if((float)$amountPaid > (float)$order->total)
                        <div class="rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-700">
                            Change: {{ number_format(max(0, (float)$amountPaid - (float)$order->total), 2) }}
                        </div>
                    @endif
                    @error('paymentMethod') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    @error('amountPaid') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="markPaid"
                            wire:loading.attr="disabled"
                            class="flex-1 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 disabled:opacity-60 transition-colors">
                        <span wire:loading.remove>Confirm Payment</span>
                        <span wire:loading>Processing…</span>
                    </button>
                    <button wire:click="$set('showPayModal', false)"
                            class="flex-1 py-2.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
