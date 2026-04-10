<div>
    {{-- Screen header (hidden on print) --}}
    <div class="print:hidden flex items-center gap-3 mb-6">
        <a href="{{ route('app.billing') }}" wire:navigate
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Bill #{{ $bill->bill_number }}</h1>
            <p class="text-sm text-gray-500">{{ $bill->created_at->format('d M Y, H:i') }}</p>
        </div>
        <div class="ml-auto flex items-center gap-3">
            @if($bill->order)
                <a href="{{ route('app.orders.show', $bill->order) }}" wire:navigate
                   class="text-sm text-primary-600 hover:underline">
                    View Order
                </a>
            @endif
            <button onclick="window.print()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Receipt
            </button>
        </div>
    </div>

    {{-- Print styles --}}
    <style>
        @media print {
            body * { visibility: hidden; }
            #receipt-print-area, #receipt-print-area * { visibility: visible; }
            #receipt-print-area { position: fixed; left: 0; top: 0; width: 80mm; }
        }
    </style>

    {{-- Receipt area --}}
    <div id="receipt-print-area" class="bg-white rounded-xl border border-gray-200 overflow-hidden max-w-md mx-auto">
        {{-- Business info --}}
        <div class="px-6 pt-6 pb-4 text-center border-b border-dashed border-gray-300">
            @if($business?->logo_url)
                <img src="{{ $business->logo_url }}" alt="{{ $business->name }}" class="h-12 w-auto mx-auto mb-2">
            @endif
            <h2 class="text-lg font-bold text-gray-900">{{ $business?->name ?? 'Restaurant' }}</h2>
            @if($business?->address)
                <p class="text-xs text-gray-500 mt-0.5">{{ $business->address }}</p>
            @endif
            @if($business?->phone)
                <p class="text-xs text-gray-500">{{ $business->phone }}</p>
            @endif
        </div>

        {{-- Bill header --}}
        <div class="px-6 py-4 border-b border-dashed border-gray-200">
            <div class="grid grid-cols-2 gap-y-1 text-sm">
                <span class="text-gray-500">Bill #</span>
                <span class="font-mono font-semibold text-right text-gray-900">{{ $bill->bill_number }}</span>

                @if($bill->order)
                    <span class="text-gray-500">Order #</span>
                    <span class="font-mono text-right text-gray-700">{{ $bill->order->order_number }}</span>
                @endif

                <span class="text-gray-500">Date</span>
                <span class="text-right text-gray-700">{{ $bill->created_at->format('d M Y') }}</span>

                <span class="text-gray-500">Time</span>
                <span class="text-right text-gray-700">{{ $bill->created_at->format('H:i') }}</span>

                @if($bill->customer_name)
                    <span class="text-gray-500">Customer</span>
                    <span class="text-right text-gray-700">{{ $bill->customer_name }}</span>
                @endif

                @if($bill->table)
                    <span class="text-gray-500">Table</span>
                    <span class="text-right text-gray-700">{{ $bill->table->table_number }}</span>
                @endif
            </div>
        </div>

        {{-- Items --}}
        <div class="px-6 py-4">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="pb-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                        <th class="pb-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="pb-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="pb-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($bill->items as $item)
                        <tr>
                            <td class="py-1.5 text-gray-800">{{ $item->description }}</td>
                            <td class="py-1.5 text-center text-gray-600">{{ $item->quantity }}</td>
                            <td class="py-1.5 text-right text-gray-600">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-1.5 text-right font-medium text-gray-800">{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="px-6 pb-4 border-t border-dashed border-gray-200 pt-3 space-y-1.5">
            <div class="flex justify-between text-sm text-gray-600">
                <span>Subtotal</span>
                <span>{{ number_format($bill->subtotal, 2) }}</span>
            </div>
            @if($bill->service_charge > 0)
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Service Charge</span>
                    <span>{{ number_format($bill->service_charge, 2) }}</span>
                </div>
            @endif
            @if($bill->discount_amount > 0)
                <div class="flex justify-between text-sm text-green-600">
                    <span>Discount</span>
                    <span>-{{ number_format($bill->discount_amount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t border-gray-300 mt-2">
                <span>TOTAL</span>
                <span>{{ number_format($bill->total, 2) }}</span>
            </div>
        </div>

        {{-- Payment info --}}
        <div class="px-6 pb-4 border-t border-dashed border-gray-200 pt-3">
            <div class="grid grid-cols-2 gap-y-1 text-sm">
                <span class="text-gray-500">Payment</span>
                <span class="text-right font-medium text-gray-800 capitalize">{{ $bill->payment_method ?? '—' }}</span>

                @php
                    $statusColor = match($bill->payment_status) {
                        'paid' => 'green',
                        'refunded' => 'purple',
                        default => 'yellow',
                    };
                @endphp
                <span class="text-gray-500">Status</span>
                <span class="text-right">
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 font-medium capitalize">
                        {{ $bill->payment_status }}
                    </span>
                </span>

                @if($bill->paid_at)
                    <span class="text-gray-500">Paid At</span>
                    <span class="text-right text-gray-700">{{ $bill->paid_at->format('H:i, d M Y') }}</span>
                @endif

                @if($bill->amount_paid && $bill->payment_method === 'cash')
                    <span class="text-gray-500">Amount Received</span>
                    <span class="text-right text-gray-700">{{ number_format($bill->amount_paid, 2) }}</span>

                    @if($bill->change_amount > 0)
                        <span class="text-gray-500">Change</span>
                        <span class="text-right text-gray-700">{{ number_format($bill->change_amount, 2) }}</span>
                    @endif
                @endif
            </div>
        </div>

        {{-- Notes --}}
        @if($bill->notes)
            <div class="px-6 pb-4">
                <p class="text-xs text-gray-500 italic text-center">{{ $bill->notes }}</p>
            </div>
        @endif

        {{-- Footer --}}
        <div class="px-6 pb-6 text-center border-t border-dashed border-gray-200 pt-3">
            <p class="text-xs text-gray-400">Thank you for your visit!</p>
            @if($bill->printed_at)
                <p class="text-xs text-gray-300 mt-1">Printed {{ $bill->printed_at->format('d M Y H:i') }}</p>
            @endif
        </div>
    </div>
</div>
