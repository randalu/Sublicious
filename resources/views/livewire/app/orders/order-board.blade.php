<div>
    {{-- Toolbar --}}
    <div class="flex flex-col gap-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1">
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search order #, customer name or phone..."
                       class="w-full sm:w-80 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
            </div>
            <div class="flex items-center gap-2">
                <select wire:model.live="typeFilter" class="rounded-md border-gray-300 text-sm shadow-sm">
                    <option value="">All Types</option>
                    <option value="dine_in">Dine In</option>
                    <option value="takeaway">Takeaway</option>
                    <option value="delivery">Delivery</option>
                    <option value="online">Online</option>
                </select>
                <input wire:model.live="dateFilter" type="date"
                       class="rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                <a href="{{ route('app.orders.create') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                    + New Order
                </a>
            </div>
        </div>

        {{-- Status filter tabs --}}
        <div class="flex items-center gap-1 overflow-x-auto pb-1">
            @php
                $statuses = [
                    '' => 'All',
                    'pending' => 'Pending',
                    'accepted' => 'Accepted',
                    'preparing' => 'Preparing',
                    'ready' => 'Ready',
                    'dispatched' => 'Dispatched',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled',
                ];
            @endphp
            @foreach($statuses as $value => $label)
                <button wire:click="$set('statusFilter', '{{ $value }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-colors
                               {{ $statusFilter === $value ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $label }}
                    @if($value === '')
                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs
                                     {{ $statusFilter === $value ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-500' }}">
                            {{ $statusCounts->sum() }}
                        </span>
                    @elseif(($statusCounts[$value] ?? 0) > 0)
                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-xs
                                     {{ $statusFilter === $value ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-500' }}">
                            {{ $statusCounts[$value] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Customer</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Type</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Payment</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Time</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($orders as $order)
                    <tr wire:key="order-{{ $order->id }}">
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500 sm:hidden">{{ $order->customer_name ?? '-' }}</p>
                        </td>
                        <td class="px-5 py-4 hidden sm:table-cell">
                            <span class="text-sm text-gray-700">{{ $order->customer_name ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-4 hidden md:table-cell">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 capitalize">
                                {{ str_replace('_', ' ', $order->order_type) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-sm font-medium text-gray-900">{{ number_format($order->total, 2) }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-{{ $order->statusColor() }}-100 text-{{ $order->statusColor() }}-700">
                                {{ $order->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-5 py-4 hidden lg:table-cell">
                            @if($order->payment_status === 'paid')
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700">Paid</span>
                            @else
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-700">{{ ucfirst($order->payment_status ?? 'Unpaid') }}</span>
                            @endif
                            @if($order->payment_method)
                                <span class="text-xs text-gray-400 ml-1 capitalize">{{ $order->payment_method }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 hidden lg:table-cell">
                            <span class="text-sm text-gray-500">{{ $order->created_at->format('H:i') }}</span>
                            <span class="text-xs text-gray-400 block">{{ $order->created_at->format('d M') }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($order->nextStatus())
                                    <button wire:click="advanceStatus({{ $order->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md bg-primary-50 text-primary-700 text-xs font-medium hover:bg-primary-100 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                        {{ ucfirst($order->nextStatus()) }}
                                    </button>
                                @endif
                                <a href="{{ route('app.orders.show', $order) }}" wire:navigate
                                   class="text-xs text-primary-600 hover:text-primary-700 font-medium">View</a>
                                @if($order->isActive() && $order->status !== 'cancelled')
                                    <button wire:click="cancel({{ $order->id }})"
                                            wire:confirm="Are you sure you want to cancel this order?"
                                            class="text-xs text-red-600 hover:text-red-700 font-medium">Cancel</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-400">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($orders->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
