<div>
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('app.tables') }}" wire:navigate
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                Table {{ $table->table_number }}
                @if($table->name) · {{ $table->name }} @endif
            </h1>
            <p class="text-sm text-gray-500">{{ $table->capacity }} seats · Open Session</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <span class="text-sm text-gray-500 font-mono">{{ $this->order->order_number }}</span>
        </div>
    </div>

    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- LEFT: Menu item selector --}}
        <div class="lg:col-span-3 space-y-4">

            {{-- Search bar --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.live.debounce.200ms="itemSearch" type="search"
                           placeholder="Search for items to add…"
                           class="pl-9 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>

                {{-- Search results --}}
                @if(strlen($itemSearch) > 0)
                    <div class="mt-2 space-y-1">
                        @forelse($this->searchResults as $item)
                            <button wire:click="selectItem({{ $item->id }})"
                                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-primary-50 hover:text-primary-700 transition-colors text-left">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $item->name }}</p>
                                    @if($item->variants->count())
                                        <p class="text-xs text-gray-400">{{ $item->variants->count() }} variants</p>
                                    @endif
                                </div>
                                <span class="text-sm font-semibold text-gray-700">{{ number_format($item->base_price, 2) }}</span>
                            </button>
                        @empty
                            <p class="px-3 py-2 text-sm text-gray-400">No items found for "{{ $itemSearch }}"</p>
                        @endforelse
                    </div>
                @endif
            </div>

            {{-- Category browse --}}
            @if(! $showItemPicker)
                @foreach($categories as $cat)
                    @if($cat->items->count())
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                            <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                                <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ $cat->name }}</h3>
                            </div>
                            <div class="divide-y divide-gray-50">
                                @foreach($cat->items as $item)
                                    <button wire:click="selectItem({{ $item->id }})"
                                            class="w-full flex items-center justify-between px-4 py-3 hover:bg-primary-50 transition-colors text-left">
                                        <p class="text-sm font-medium text-gray-800">{{ $item->name }}</p>
                                        <span class="text-sm font-semibold text-gray-700 ml-3 shrink-0">{{ number_format($item->base_price, 2) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

            {{-- Item picker panel --}}
            @if($showItemPicker && $this->selectedItem)
                @php $item = $this->selectedItem; @endphp
                <div class="bg-white rounded-xl border border-primary-200 shadow-sm p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $item->name }}</h3>
                            <p class="text-sm text-gray-500">Base price: {{ number_format($item->base_price, 2) }}</p>
                        </div>
                        <button wire:click="$set('showItemPicker', false)" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Variants --}}
                    @if($item->variants->count())
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Choose Variant</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($item->variants as $variant)
                                    <button type="button"
                                            wire:click="$set('selectedVariantId', {{ $variant->id }})"
                                            class="px-3 py-1.5 text-sm rounded-lg border transition-colors
                                                   {{ $selectedVariantId == $variant->id ? 'border-primary-500 bg-primary-50 text-primary-700 font-medium' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                        {{ $variant->name }}
                                        @if($variant->price_type === 'replace')
                                            — {{ number_format($variant->price_adjustment, 2) }}
                                        @elseif($variant->price_adjustment > 0)
                                            +{{ number_format($variant->price_adjustment, 2) }}
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Addon groups --}}
                    @foreach($item->addonGroups as $group)
                        <div class="mb-4">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">
                                {{ $group->name }}
                                @if($group->is_required) <span class="text-red-500">*</span> @endif
                            </p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($group->items->where('is_available', true) as $addonItem)
                                    <button type="button"
                                            wire:click="toggleAddon({{ $addonItem->id }})"
                                            class="px-3 py-1.5 text-sm rounded-lg border transition-colors
                                                   {{ in_array($addonItem->id, $selectedAddons) ? 'border-primary-500 bg-primary-50 text-primary-700 font-medium' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                        {{ $addonItem->name }}
                                        @if($addonItem->price > 0) +{{ number_format($addonItem->price, 2) }} @else Free @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    {{-- Quantity + Notes --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Quantity</label>
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="$set('quantity', max(1, quantity - 1))"
                                        class="w-8 h-8 rounded-full border border-gray-300 text-gray-600 hover:border-gray-400 flex items-center justify-center">−</button>
                                <span class="w-8 text-center font-semibold">{{ $quantity }}</span>
                                <button type="button" wire:click="$set('quantity', quantity + 1)"
                                        class="w-8 h-8 rounded-full border border-gray-300 text-gray-600 hover:border-gray-400 flex items-center justify-center">+</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Notes</label>
                            <input wire:model="itemNotes" type="text" placeholder="e.g. no onions"
                                   class="w-full rounded-lg border-gray-300 text-xs focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>

                    <button wire:click="addToOrder"
                            wire:loading.attr="disabled"
                            class="w-full py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-60 transition-colors">
                        <span wire:loading.remove>Add to Order</span>
                        <span wire:loading>Adding…</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- RIGHT: Current order --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 sticky top-6">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-800">Current Order</h2>
                </div>

                {{-- Order items --}}
                <div class="divide-y divide-gray-50 max-h-[400px] overflow-y-auto">
                    @if($this->order->items->isEmpty())
                        <div class="px-4 py-8 text-center text-gray-400 text-sm">
                            No items yet. Select from the menu.
                        </div>
                    @else
                        @foreach($this->order->items as $oi)
                            <div class="px-4 py-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">
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
                                    <div class="flex items-center gap-2 shrink-0">
                                        <div class="flex items-center gap-1">
                                            <button wire:click="decrementItem({{ $oi->id }})"
                                                    class="w-6 h-6 rounded-full border border-gray-200 text-gray-500 hover:border-gray-300 flex items-center justify-center text-xs">−</button>
                                            <span class="w-6 text-center text-xs font-semibold">{{ $oi->quantity }}</span>
                                            <button wire:click="incrementItem({{ $oi->id }})"
                                                    class="w-6 h-6 rounded-full border border-gray-200 text-gray-500 hover:border-gray-300 flex items-center justify-center text-xs">+</button>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700 w-16 text-right">{{ number_format($oi->subtotal, 2) }}</span>
                                        <button wire:click="removeItem({{ $oi->id }})"
                                                class="text-gray-300 hover:text-red-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Totals --}}
                <div class="px-4 py-3 border-t border-gray-100 space-y-1.5">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span>{{ number_format($this->order->subtotal, 2) }}</span>
                    </div>
                    @if($this->order->service_charge > 0)
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Service Charge</span>
                            <span>{{ number_format($this->order->service_charge, 2) }}</span>
                        </div>
                    @endif
                    @if($this->order->discount_amount > 0)
                        <div class="flex justify-between text-sm text-green-600">
                            <span>Discount</span>
                            <span>-{{ number_format($this->order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-1.5 border-t border-gray-100">
                        <span>Total</span>
                        <span>{{ number_format($this->order->total, 2) }}</span>
                    </div>
                </div>

                {{-- Close button --}}
                <div class="px-4 pb-4">
                    <button wire:click="openPayModal"
                            @if($this->order->items->isEmpty()) disabled @endif
                            class="w-full py-3 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        Close & Pay
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment modal --}}
    @if($showPayModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">Close Table & Collect Payment</h2>

                <div class="mb-5 space-y-3">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Total Due</span>
                        <span class="font-bold text-gray-900 text-base">{{ number_format($this->order->total, 2) }}</span>
                    </div>
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
                    @if((float)$amountPaid > (float)$this->order->total)
                        <div class="rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-700">
                            Change: {{ number_format(max(0, (float)$amountPaid - (float)$this->order->total), 2) }}
                        </div>
                    @endif
                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="closeTable"
                            wire:loading.attr="disabled"
                            class="flex-1 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 disabled:opacity-60 transition-colors">
                        <span wire:loading.remove>Confirm & Close</span>
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
