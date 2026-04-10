<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('app.customers') }}" wire:navigate
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Info card --}}
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Contact</h2>
                <div class="space-y-2 text-sm">
                    @if($customer->phone)
                        <div class="flex items-center gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $customer->phone }}
                        </div>
                    @endif
                    @if($customer->email)
                        <div class="flex items-center gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $customer->email }}
                        </div>
                    @endif
                    @if($customer->notes)
                        <p class="text-gray-500 italic">{{ $customer->notes }}</p>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Summary</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-900">{{ $customer->total_orders }}</p>
                        <p class="text-xs text-gray-500 mt-1">Total Orders</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-lg font-bold text-gray-900">{{ number_format($customer->total_spent, 2) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Total Spent</p>
                    </div>
                </div>
            </div>

            {{-- Addresses --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Addresses</h2>
                    <button wire:click="openAddressForm()" class="text-xs text-primary-600 hover:underline">+ Add</button>
                </div>

                @if($customer->addresses->isEmpty())
                    <p class="text-sm text-gray-400 italic">No saved addresses.</p>
                @else
                    <div class="space-y-2">
                        @foreach($customer->addresses as $addr)
                            <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                                <div class="flex items-start justify-between">
                                    <div>
                                        @if($addr->label)
                                            <p class="text-xs font-medium text-gray-500 uppercase">{{ $addr->label }}</p>
                                        @endif
                                        <p class="text-sm text-gray-800">{{ $addr->address_line_1 }}</p>
                                        @if($addr->city) <p class="text-xs text-gray-500">{{ $addr->city }}</p> @endif
                                        @if($addr->is_default)
                                            <span class="inline-block mt-1 text-xs px-1.5 py-0.5 bg-primary-100 text-primary-700 rounded">Default</span>
                                        @endif
                                    </div>
                                    <div class="flex gap-1">
                                        @if(!$addr->is_default)
                                            <button wire:click="setDefault({{ $addr->id }})" class="p-1 text-gray-300 hover:text-primary-500" title="Set default">★</button>
                                        @endif
                                        <button wire:click="openAddressForm({{ $addr->id }})" class="p-1 text-gray-300 hover:text-primary-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button wire:click="deleteAddress({{ $addr->id }})"
                                                wire:confirm="Remove this address?"
                                                class="p-1 text-gray-300 hover:text-red-500">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Order history --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-800">Order History</h2>
                </div>
                @if($customer->orders->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-sm">No orders yet.</div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach($customer->orders as $order)
                            <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('app.orders.show', $order) }}" wire:navigate
                                           class="text-sm font-medium text-gray-900 hover:text-primary-600">
                                            {{ $order->order_number }}
                                        </a>
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $order->statusColor() }}-100 text-{{ $order->statusColor() }}-700">
                                            {{ $order->statusLabel() }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $order->created_at->format('d M Y, H:i') }} ·
                                        {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">{{ number_format($order->total, 2) }}</p>
                                    <p class="text-xs text-gray-400">{{ $order->items->count() }} items</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Address Form Modal --}}
    @if($showAddressForm)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeAddressForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">{{ $editingAddressId ? 'Edit Address' : 'Add Address' }}</h2>
                <form wire:submit="saveAddress" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Label (optional)</label>
                        <input wire:model="label" type="text" placeholder="e.g. Home, Office"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
                        <input wire:model="addressLine1" type="text"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('addressLine1') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input wire:model="city" type="text"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input wire:model="isDefault" type="checkbox"
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-gray-700">Set as default address</span>
                    </label>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Save</button>
                        <button type="button" wire:click="closeAddressForm" class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
