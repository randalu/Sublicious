<div
    x-data="{
        showItemModal: false,
        selectedItem: null,
        selectedVariantId: null,
        selectedAddons: {},
        itemQuantity: 1,
        itemNotes: '',
        showCart: false,
        activeCategory: null,

        openItemModal(item) {
            this.selectedItem = item;
            this.selectedVariantId = item.variants && item.variants.length > 0 ? item.variants[0].id : null;
            this.selectedAddons = {};
            this.itemQuantity = 1;
            this.itemNotes = '';
            if (item.addon_groups) {
                item.addon_groups.forEach(group => {
                    if (group.selection_type === 'single') {
                        this.selectedAddons[group.id] = null;
                    } else {
                        this.selectedAddons[group.id] = [];
                    }
                });
            }
            this.showItemModal = true;
        },

        closeItemModal() {
            this.showItemModal = false;
            this.selectedItem = null;
        },

        toggleAddon(groupId, itemId, selectionType) {
            if (selectionType === 'single') {
                this.selectedAddons[groupId] = this.selectedAddons[groupId] === itemId ? null : itemId;
            } else {
                if (!Array.isArray(this.selectedAddons[groupId])) {
                    this.selectedAddons[groupId] = [];
                }
                const idx = this.selectedAddons[groupId].indexOf(itemId);
                if (idx > -1) {
                    this.selectedAddons[groupId].splice(idx, 1);
                } else {
                    this.selectedAddons[groupId].push(itemId);
                }
            }
        },

        isAddonSelected(groupId, itemId, selectionType) {
            if (selectionType === 'single') {
                return this.selectedAddons[groupId] === itemId;
            }
            return Array.isArray(this.selectedAddons[groupId]) && this.selectedAddons[groupId].includes(itemId);
        },

        getSelectedAddonIds() {
            let ids = [];
            Object.keys(this.selectedAddons).forEach(groupId => {
                const val = this.selectedAddons[groupId];
                if (Array.isArray(val)) {
                    ids = ids.concat(val);
                } else if (val) {
                    ids.push(val);
                }
            });
            return ids;
        },

        calculateItemPrice() {
            if (!this.selectedItem) return 0;
            let price = parseFloat(this.selectedItem.base_price);

            if (this.selectedVariantId && this.selectedItem.variants) {
                const variant = this.selectedItem.variants.find(v => v.id === this.selectedVariantId);
                if (variant) {
                    if (variant.price_type === 'replace') {
                        price = parseFloat(variant.price_adjustment);
                    } else if (variant.price_type === 'add') {
                        price = price + parseFloat(variant.price_adjustment);
                    }
                }
            }

            const addonIds = this.getSelectedAddonIds();
            if (this.selectedItem.addon_groups) {
                this.selectedItem.addon_groups.forEach(group => {
                    group.items.forEach(addonItem => {
                        if (addonIds.includes(addonItem.id)) {
                            price += parseFloat(addonItem.price);
                        }
                    });
                });
            }

            return (price * this.itemQuantity).toFixed(2);
        },

        addToCart() {
            if (!this.selectedItem) return;
            const addonIds = this.getSelectedAddonIds();
            $wire.addToCart(
                this.selectedItem.id,
                this.selectedVariantId,
                addonIds,
                this.itemQuantity,
                this.itemNotes
            );
            this.closeItemModal();
        },

        scrollToCategory(categoryId) {
            this.activeCategory = categoryId;
            const el = document.getElementById('category-' + categoryId);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }"
    class="min-h-screen bg-gray-50"
>
    {{-- Order Success --}}
    @if($orderPlaced)
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Order Placed!</h2>
                <p class="text-gray-600 mb-4">Your order has been submitted successfully.</p>
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-500">Order Number</p>
                    <p class="text-xl font-bold text-gray-900">{{ $orderNumber }}</p>
                </div>
                <p class="text-sm text-gray-500 mb-6">You will receive a confirmation soon. Please keep your order number for reference.</p>
                <button wire:click="startNewOrder" class="w-full bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                    Place Another Order
                </button>
            </div>
        </div>
    @else
        {{-- Header --}}
        <header class="bg-white shadow-sm sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center gap-4">
                    @if($business->logo)
                        <img src="{{ Storage::url($business->logo) }}" alt="{{ $business->name }}" class="h-12 w-12 rounded-full object-cover">
                    @endif
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl font-bold text-gray-900 truncate">{{ $business->name }}</h1>
                        @if($business->description)
                            <p class="text-sm text-gray-500 truncate">{{ $business->description }}</p>
                        @endif
                    </div>
                    <button
                        @click="showCart = !showCart"
                        class="relative inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 transition-colors lg:hidden"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-5.98.572m5.98-.572h9m-9 0a3 3 0 01-5.98.572M17.25 14.25a3 3 0 005.98.572m-5.98-.572h-9m9 0a3 3 0 015.98.572M3.75 5.272l.633 2.377m0 0h13.49a1.5 1.5 0 011.446 1.902l-1.67 6.01a1.5 1.5 0 01-1.446 1.098H7.012a1.5 1.5 0 01-1.446-1.098L3.383 7.649z" />
                        </svg>
                        Cart
                        @if(count($cartItems) > 0)
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                {{ count($cartItems) }}
                            </span>
                        @endif
                    </button>
                </div>
            </div>
        </header>

        {{-- Category Navigation --}}
        @if($categories->count() > 1)
            <div class="bg-white border-b sticky top-[73px] z-20">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <nav class="flex gap-1 overflow-x-auto py-2 scrollbar-hide" aria-label="Menu categories">
                        @foreach($categories as $category)
                            @if($category->items->count() > 0)
                                <button
                                    @click="scrollToCategory({{ $category->id }})"
                                    :class="activeCategory === {{ $category->id }} ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100'"
                                    class="whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-colors"
                                >
                                    {{ $category->name }}
                                </button>
                            @endif
                        @endforeach
                    </nav>
                </div>
            </div>
        @endif

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @if($errors->has('order'))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <p class="text-sm text-red-700">{{ $errors->first('order') }}</p>
                    </div>
                </div>
            @endif

            <div class="lg:grid lg:grid-cols-3 lg:gap-8">
                {{-- Menu Section --}}
                <div class="lg:col-span-2 space-y-8">
                    @foreach($categories as $category)
                        @if($category->items->count() > 0)
                            <section id="category-{{ $category->id }}">
                                <h2 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">{{ $category->name }}</h2>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    @foreach($category->items as $item)
                                        <button
                                            type="button"
                                            @click="openItemModal({{ Js::from([
                                                'id' => $item->id,
                                                'name' => $item->name,
                                                'description' => $item->description,
                                                'base_price' => $item->base_price,
                                                'image' => $item->image ? Storage::url($item->image) : null,
                                                'preparation_time_minutes' => $item->preparation_time_minutes,
                                                'variants' => $item->variants->map(fn($v) => [
                                                    'id' => $v->id,
                                                    'name' => $v->name,
                                                    'price_adjustment' => $v->price_adjustment,
                                                    'price_type' => $v->price_type,
                                                ]),
                                                'addon_groups' => $item->addonGroups->map(fn($g) => [
                                                    'id' => $g->id,
                                                    'name' => $g->name,
                                                    'selection_type' => $g->selection_type,
                                                    'is_required' => $g->is_required,
                                                    'min_selections' => $g->min_selections,
                                                    'max_selections' => $g->max_selections,
                                                    'items' => $g->items->map(fn($i) => [
                                                        'id' => $i->id,
                                                        'name' => $i->name,
                                                        'price' => $i->price,
                                                    ]),
                                                ]),
                                            ]) }})"
                                            class="flex bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow overflow-hidden text-left w-full"
                                        >
                                            @if($item->image)
                                                <div class="flex-shrink-0 w-28 h-28">
                                                    <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                                </div>
                                            @endif
                                            <div class="flex-1 p-4 min-w-0">
                                                <h3 class="font-semibold text-gray-900 truncate">{{ $item->name }}</h3>
                                                @if($item->description)
                                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $item->description }}</p>
                                                @endif
                                                <div class="flex items-center justify-between mt-2">
                                                    <span class="text-indigo-600 font-bold">${{ number_format($item->base_price, 2) }}</span>
                                                    @if($item->preparation_time_minutes)
                                                        <span class="text-xs text-gray-400 flex items-center gap-1">
                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            {{ $item->preparation_time_minutes }} min
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    @endforeach

                    @if($categories->every(fn($c) => $c->items->count() === 0))
                        <div class="text-center py-16">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">No items available</h3>
                            <p class="mt-1 text-sm text-gray-500">This restaurant has no menu items available for online ordering right now.</p>
                        </div>
                    @endif
                </div>

                {{-- Cart Sidebar (Desktop) --}}
                <div class="hidden lg:block">
                    <div class="sticky top-[130px]">
                        @include('livewire.public.partials.order-cart')
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Cart Drawer --}}
        <div
            x-show="showCart"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showCart = false"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"
            x-cloak
        ></div>
        <div
            x-show="showCart"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 w-full max-w-md bg-white shadow-2xl z-50 overflow-y-auto lg:hidden"
            x-cloak
        >
            <div class="p-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Your Cart</h2>
                    <button @click="showCart = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @include('livewire.public.partials.order-cart')
            </div>
        </div>

        {{-- Item Detail Modal --}}
        <div
            x-show="showItemModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
            @click.self="closeItemModal()"
            x-cloak
        >
            <div
                x-show="showItemModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <template x-if="selectedItem">
                    <div>
                        {{-- Item Image --}}
                        <template x-if="selectedItem.image">
                            <div class="relative h-48 w-full">
                                <img :src="selectedItem.image" :alt="selectedItem.name" class="w-full h-full object-cover rounded-t-2xl">
                                <button
                                    @click="closeItemModal()"
                                    class="absolute top-3 right-3 bg-white/80 backdrop-blur-sm rounded-full p-1.5 hover:bg-white transition-colors"
                                >
                                    <svg class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <div class="p-6">
                            {{-- Close button (no image) --}}
                            <template x-if="!selectedItem.image">
                                <div class="flex justify-end -mt-2 -mr-2 mb-2">
                                    <button @click="closeItemModal()" class="text-gray-400 hover:text-gray-600">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            {{-- Name and Description --}}
                            <h3 class="text-xl font-bold text-gray-900" x-text="selectedItem.name"></h3>
                            <template x-if="selectedItem.description">
                                <p class="text-sm text-gray-500 mt-1" x-text="selectedItem.description"></p>
                            </template>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="text-lg font-bold text-indigo-600">$<span x-text="parseFloat(selectedItem.base_price).toFixed(2)"></span></span>
                                <template x-if="selectedItem.preparation_time_minutes">
                                    <span class="text-xs text-gray-400 flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span x-text="selectedItem.preparation_time_minutes + ' min'"></span>
                                    </span>
                                </template>
                            </div>

                            {{-- Variants --}}
                            <template x-if="selectedItem.variants && selectedItem.variants.length > 0">
                                <div class="mt-5">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Choose a variant</h4>
                                    <div class="space-y-2">
                                        <template x-for="variant in selectedItem.variants" :key="variant.id">
                                            <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-colors"
                                                   :class="selectedVariantId === variant.id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                                                <input type="radio" name="variant" :value="variant.id"
                                                       x-model.number="selectedVariantId"
                                                       class="text-indigo-600 focus:ring-indigo-500">
                                                <span class="flex-1 text-sm font-medium text-gray-900" x-text="variant.name"></span>
                                                <span class="text-sm text-gray-600">
                                                    <template x-if="variant.price_type === 'replace'">
                                                        <span x-text="'$' + parseFloat(variant.price_adjustment).toFixed(2)"></span>
                                                    </template>
                                                    <template x-if="variant.price_type === 'add'">
                                                        <span x-text="'+$' + parseFloat(variant.price_adjustment).toFixed(2)"></span>
                                                    </template>
                                                </span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Addon Groups --}}
                            <template x-if="selectedItem.addon_groups && selectedItem.addon_groups.length > 0">
                                <div>
                                    <template x-for="group in selectedItem.addon_groups" :key="group.id">
                                        <div class="mt-5">
                                            <div class="flex items-center gap-2 mb-2">
                                                <h4 class="text-sm font-semibold text-gray-900" x-text="group.name"></h4>
                                                <template x-if="group.is_required">
                                                    <span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-medium">Required</span>
                                                </template>
                                                <template x-if="!group.is_required">
                                                    <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded font-medium">Optional</span>
                                                </template>
                                            </div>
                                            <template x-if="group.selection_type === 'single' && group.min_selections">
                                                <p class="text-xs text-gray-400 mb-2">Choose one</p>
                                            </template>
                                            <template x-if="group.selection_type === 'multiple' && group.max_selections">
                                                <p class="text-xs text-gray-400 mb-2" x-text="'Select up to ' + group.max_selections"></p>
                                            </template>
                                            <div class="space-y-2">
                                                <template x-for="addonItem in group.items" :key="addonItem.id">
                                                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-colors"
                                                           :class="isAddonSelected(group.id, addonItem.id, group.selection_type) ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'">
                                                        <template x-if="group.selection_type === 'single'">
                                                            <input type="radio"
                                                                   :name="'addon-group-' + group.id"
                                                                   :value="addonItem.id"
                                                                   :checked="selectedAddons[group.id] === addonItem.id"
                                                                   @change="toggleAddon(group.id, addonItem.id, 'single')"
                                                                   class="text-indigo-600 focus:ring-indigo-500">
                                                        </template>
                                                        <template x-if="group.selection_type === 'multiple'">
                                                            <input type="checkbox"
                                                                   :checked="isAddonSelected(group.id, addonItem.id, 'multiple')"
                                                                   @change="toggleAddon(group.id, addonItem.id, 'multiple')"
                                                                   class="text-indigo-600 focus:ring-indigo-500 rounded">
                                                        </template>
                                                        <span class="flex-1 text-sm font-medium text-gray-900" x-text="addonItem.name"></span>
                                                        <template x-if="parseFloat(addonItem.price) > 0">
                                                            <span class="text-sm text-gray-600" x-text="'+$' + parseFloat(addonItem.price).toFixed(2)"></span>
                                                        </template>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- Special Notes --}}
                            <div class="mt-5">
                                <label class="text-sm font-semibold text-gray-900">Special Notes</label>
                                <textarea
                                    x-model="itemNotes"
                                    rows="2"
                                    placeholder="Any special requests..."
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                ></textarea>
                            </div>

                            {{-- Quantity + Add to Cart --}}
                            <div class="mt-6 flex items-center gap-4">
                                <div class="flex items-center border border-gray-300 rounded-lg">
                                    <button
                                        @click="if (itemQuantity > 1) itemQuantity--"
                                        class="px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-l-lg transition-colors"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                        </svg>
                                    </button>
                                    <span class="px-4 py-2 text-sm font-semibold text-gray-900 min-w-[2.5rem] text-center" x-text="itemQuantity"></span>
                                    <button
                                        @click="itemQuantity++"
                                        class="px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-r-lg transition-colors"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </button>
                                </div>
                                <button
                                    @click="addToCart()"
                                    class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2"
                                >
                                    <span>Add to Cart</span>
                                    <span class="text-indigo-200">-</span>
                                    <span>$<span x-text="calculateItemPrice()"></span></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    @endif
</div>
