<div>
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('app.orders') }}" wire:navigate
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">New POS Order</h1>
            <p class="text-sm text-gray-500">Create a new order from the point of sale</p>
        </div>
    </div>

    @if($errors->has('limit'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $errors->first('limit') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- LEFT: Order setup + item picker --}}
        <div class="lg:col-span-3 space-y-4">

            {{-- Order Type & Meta --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-4">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Order Details</h2>

                {{-- Order type tabs --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-2">Order Type</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['dine_in' => 'Dine In', 'takeaway' => 'Takeaway', 'delivery' => 'Delivery'] as $type => $label)
                            <button type="button" wire:click="$set('orderType', '{{ $type }}')"
                                    class="py-2 text-sm font-medium rounded-lg border transition-colors
                                           {{ $orderType === $type ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Table selector (dine_in only) --}}
                @if($orderType === 'dine_in')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Table <span class="text-red-500">*</span></label>
                        <select wire:model="tableId"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">— Select a table —</option>
                            @foreach($this->availableTables as $table)
                                <option value="{{ $table->id }}">
                                    Table {{ $table->table_number }}{{ $table->name ? ' — ' . $table->name : '' }}
                                    ({{ $table->capacity }} seats)
                                </option>
                            @endforeach
                        </select>
                        @error('tableId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- Customer info --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">
                            Customer Name
                            @if($orderType === 'delivery') <span class="text-red-500">*</span> @endif
                        </label>
                        <input wire:model="customerName" type="text" placeholder="Optional"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('customerName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">
                            Phone
                            @if($orderType === 'delivery') <span class="text-red-500">*</span> @endif
                        </label>
                        <input wire:model="customerPhone" type="tel" placeholder="Optional"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('customerPhone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Delivery address --}}
                @if($orderType === 'delivery')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Delivery Address <span class="text-red-500">*</span></label>
                        <textarea wire:model="deliveryAddress" rows="2" placeholder="Full delivery address"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                        @error('deliveryAddress') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- Order notes --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Order Notes</label>
                    <input wire:model="orderNotes" type="text" placeholder="Any special instructions…"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
            </div>

            {{-- Item search --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.live.debounce.200ms="itemSearch" type="search"
                           placeholder="Search menu items…"
                           class="pl-9 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>

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

                    <button wire:click="addToCart"
                            wire:loading.attr="disabled"
                            class="w-full py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-60 transition-colors">
                        <span wire:loading.remove>Add to Order</span>
                        <span wire:loading>Adding…</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- RIGHT: Cart + totals --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 sticky top-6">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800">Order Summary</h2>
                    @if(count($cartItems) > 0)
                        <span class="text-xs text-gray-400">{{ count($cartItems) }} item(s)</span>
                    @endif
                </div>

                {{-- Cart items --}}
                <div class="divide-y divide-gray-50 max-h-[400px] overflow-y-auto">
                    @if(empty($cartItems))
                        <div class="px-4 py-8 text-center text-gray-400 text-sm">
                            No items yet. Browse the menu to add items.
                        </div>
                    @else
                        @foreach($cartItems as $cartItem)
                            <div class="px-4 py-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">
                                            {{ $cartItem['name'] }}
                                            @if($cartItem['variant_name'])
                                                <span class="text-gray-400">({{ $cartItem['variant_name'] }})</span>
                                            @endif
                                        </p>
                                        @foreach($cartItem['addons'] as $addon)
                                            <p class="text-xs text-gray-400">+ {{ $addon['name'] }}</p>
                                        @endforeach
                                        @if($cartItem['notes'])
                                            <p class="text-xs text-gray-400 italic">{{ $cartItem['notes'] }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <div class="flex items-center gap-1">
                                            <button wire:click="decrementCartItem('{{ $cartItem['id'] }}')"
                                                    class="w-6 h-6 rounded-full border border-gray-200 text-gray-500 hover:border-gray-300 flex items-center justify-center text-xs">−</button>
                                            <span class="w-6 text-center text-xs font-semibold">{{ $cartItem['quantity'] }}</span>
                                            <button wire:click="incrementCartItem('{{ $cartItem['id'] }}')"
                                                    class="w-6 h-6 rounded-full border border-gray-200 text-gray-500 hover:border-gray-300 flex items-center justify-center text-xs">+</button>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700 w-16 text-right">{{ number_format($cartItem['subtotal'], 2) }}</span>
                                        <button wire:click="removeFromCart('{{ $cartItem['id'] }}')"
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
                        <span>{{ number_format($subtotal, 2) }}</span>
                    </div>
                    @if($serviceCharge > 0)
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Service Charge</span>
                            <span>{{ number_format($serviceCharge, 2) }}</span>
                        </div>
                    @endif
                    @if($deliveryFee > 0)
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Delivery Fee</span>
                            <span>{{ number_format($deliveryFee, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-1.5 border-t border-gray-100">
                        <span>Total</span>
                        <span>{{ number_format($total, 2) }}</span>
                    </div>
                </div>

                {{-- Place order --}}
                <div class="px-4 pb-4">
                    <button wire:click="placeOrder"
                            wire:loading.attr="disabled"
                            @if(empty($cartItems)) disabled @endif
                            class="w-full py-3 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        <span wire:loading.remove wire:target="placeOrder">Place Order</span>
                        <span wire:loading wire:target="placeOrder">Placing Order…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
