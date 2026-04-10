<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Delivery Board</h1>
            <p class="text-sm text-gray-500 mt-1">Manage delivery orders and rider assignments</p>
        </div>
        <a href="{{ route('app.delivery.riders') }}" wire:navigate
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors">
            Manage Riders
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search order, customer, address…"
                   class="pl-9 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        </div>
        <select wire:model.live="statusFilter"
                class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All Statuses</option>
            @foreach(['pending','accepted','preparing','ready','dispatched','delivered','completed','cancelled'] as $s)
                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>

    @if($deliveryOrders->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No delivery orders found.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer / Address</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rider</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Fee</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($deliveryOrders as $order)
                        @php
                            $statusColors = [
                                'pending' => 'yellow', 'accepted' => 'blue', 'preparing' => 'orange',
                                'ready' => 'purple', 'dispatched' => 'indigo', 'delivered' => 'green',
                                'completed' => 'green', 'cancelled' => 'red',
                            ];
                            $color = $statusColors[$order->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <a href="{{ route('app.orders.show', $order) }}" wire:navigate
                                   class="text-sm font-mono font-semibold text-primary-600 hover:underline">
                                    {{ $order->order_number }}
                                </a>
                                <p class="text-xs text-gray-400">{{ $order->created_at->format('d M, H:i') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $order->customer_name ?? '—' }}</p>
                                @if($order->customer_phone)
                                    <p class="text-xs text-gray-400">{{ $order->customer_phone }}</p>
                                @endif
                                @if($order->delivery_address)
                                    <p class="text-xs text-gray-500 max-w-xs truncate">{{ $order->delivery_address }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($order->delivery && $order->delivery->rider)
                                    <p class="text-sm font-medium text-gray-800">{{ $order->delivery->rider->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $order->delivery->rider->phone }}</p>
                                @else
                                    <span class="text-xs text-gray-400 italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 font-medium">
                                    {{ ucfirst($order->status) }}
                                </span>
                                @if($order->delivery)
                                    <p class="mt-1 text-xs text-gray-400">Delivery: {{ ucfirst(str_replace('_', ' ', $order->delivery->status)) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700 font-medium">
                                {{ number_format($order->delivery_fee, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 flex-wrap">
                                    {{-- Assign/reassign rider --}}
                                    @if($order->isActive())
                                        <button wire:click="openAssignModal({{ $order->id }})"
                                                class="text-xs px-2.5 py-1 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                            {{ $order->delivery ? 'Reassign' : 'Assign Rider' }}
                                        </button>
                                    @endif

                                    {{-- Advance delivery status --}}
                                    @if($order->delivery && in_array($order->delivery->status, ['assigned', 'picked_up']))
                                        @php
                                            $nextLabel = $order->delivery->status === 'assigned' ? 'Picked Up' : 'Delivered';
                                        @endphp
                                        <button wire:click="advanceDeliveryStatus({{ $order->delivery->id }})"
                                                class="text-xs px-2.5 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                            {{ $nextLabel }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($deliveryOrders->hasPages())
            <div class="mt-4">{{ $deliveryOrders->links() }}</div>
        @endif
    @endif

    {{-- Assign Rider Modal --}}
    @if($showAssignModal)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="$set('showAssignModal', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Assign Rider</h2>

                @if($activeRiders->isEmpty())
                    <div class="rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-700 mb-4">
                        No active riders available. Please activate a rider first.
                    </div>
                @else
                    <div class="space-y-2 max-h-64 overflow-y-auto mb-4">
                        @foreach($activeRiders as $rider)
                            <button type="button" wire:click="$set('selectedRiderId', {{ $rider->id }})"
                                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg border transition-colors
                                           {{ $selectedRiderId == $rider->id ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <div class="text-left">
                                    <p class="text-sm font-medium text-gray-800">{{ $rider->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $rider->phone }}</p>
                                </div>
                                <div class="text-right text-xs text-gray-500">
                                    @if($rider->vehicle_type)
                                        <p class="capitalize">{{ $rider->vehicle_type }}</p>
                                    @endif
                                    <p>{{ $rider->total_deliveries }} trips</p>
                                </div>
                            </button>
                        @endforeach
                    </div>
                    @error('selectedRiderId') <p class="mb-3 text-xs text-red-600">{{ $message }}</p> @enderror
                @endif

                <div class="flex gap-3">
                    <button wire:click="assignRider"
                            wire:loading.attr="disabled"
                            @if($activeRiders->isEmpty()) disabled @endif
                            class="flex-1 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 disabled:opacity-50 transition-colors">
                        <span wire:loading.remove>Assign</span>
                        <span wire:loading>Assigning…</span>
                    </button>
                    <button wire:click="$set('showAssignModal', false)"
                            class="flex-1 py-2.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
