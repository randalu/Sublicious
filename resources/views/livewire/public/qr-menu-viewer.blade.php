<div
    x-data="{
        activeCategory: null,
        showItemModal: false,
        selectedItem: null,
        init() {
            if (document.querySelectorAll('[data-category-id]').length) {
                this.activeCategory = document.querySelectorAll('[data-category-id]')[0]?.dataset.categoryId;
            }
        },
        scrollToCategory(id) {
            this.activeCategory = id;
            const el = document.getElementById('category-' + id);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        openItem(item) {
            this.selectedItem = item;
            this.showItemModal = true;
        }
    }"
    class="min-h-screen bg-gray-50"
>
    {{-- Header --}}
    <div class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-2xl mx-auto px-4 py-3 flex items-center gap-3">
            @if($business->logo)
                <img src="{{ Storage::url($business->logo) }}" alt="{{ $business->name }}"
                     class="w-10 h-10 rounded-full object-cover border border-gray-200">
            @else
                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold text-lg">
                    {{ substr($business->name, 0, 1) }}
                </div>
            @endif
            <div>
                <h1 class="text-base font-bold text-gray-900 leading-tight">{{ $business->name }}</h1>
                <p class="text-xs text-gray-500">Table {{ $table->displayName() }}</p>
            </div>
            <span class="ml-auto text-xs bg-green-100 text-green-700 font-medium px-2.5 py-1 rounded-full">Menu</span>
        </div>

        {{-- Category tabs --}}
        @if($categories->isNotEmpty())
            <div class="max-w-2xl mx-auto overflow-x-auto flex gap-2 px-4 pb-3 scrollbar-hide">
                @foreach($categories as $cat)
                    <button
                        @click="scrollToCategory({{ $cat->id }})"
                        :class="activeCategory == {{ $cat->id }} ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-colors"
                        data-category-id="{{ $cat->id }}"
                    >
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Menu content --}}
    <div class="max-w-2xl mx-auto px-4 py-5 space-y-8">
        @forelse($categories as $category)
            @if($category->items->isNotEmpty())
                <div id="category-{{ $category->id }}" x-intersect="activeCategory = '{{ $category->id }}'">
                    <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                        {{ $category->name }}
                        <span class="text-xs font-normal text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                            {{ $category->items->count() }} items
                        </span>
                    </h2>
                    <div class="space-y-3">
                        @foreach($category->items as $item)
                            <div
                                @click="openItem({{ json_encode([
                                    'id' => $item->id,
                                    'name' => $item->name,
                                    'description' => $item->description,
                                    'price' => $item->base_price,
                                    'image' => $item->image ? Storage::url($item->image) : null,
                                    'prep_time' => $item->preparation_time_minutes,
                                    'variants' => $item->variants->map(fn($v) => ['name' => $v->name, 'price_adjustment' => $v->price_adjustment, 'price_type' => $v->price_type])->values()->all(),
                                ]) }})"
                                class="bg-white rounded-xl border border-gray-200 p-4 flex items-start gap-4 cursor-pointer hover:shadow-sm hover:border-gray-300 transition-all active:scale-[0.99]"
                            >
                                @if($item->image)
                                    <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}"
                                         class="w-20 h-20 rounded-lg object-cover shrink-0 border border-gray-100">
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="font-semibold text-gray-900 text-sm leading-snug">{{ $item->name }}</h3>
                                        <span class="font-bold text-primary-600 text-sm shrink-0">
                                            {{ $business->currency }} {{ number_format($item->base_price, 2) }}
                                        </span>
                                    </div>
                                    @if($item->description)
                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $item->description }}</p>
                                    @endif
                                    <div class="flex items-center gap-3 mt-2">
                                        @if($item->preparation_time_minutes)
                                            <span class="flex items-center gap-1 text-xs text-gray-400">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $item->preparation_time_minutes }} min
                                            </span>
                                        @endif
                                        @if($item->variants->isNotEmpty())
                                            <span class="text-xs text-primary-600 font-medium">Customisable</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @empty
            <div class="text-center py-16">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-400 text-sm">Menu not available yet.</p>
            </div>
        @endforelse

        <p class="text-center text-xs text-gray-400 pb-4">
            Scan the QR code again or ask a staff member to order.
        </p>
    </div>

    {{-- Item detail modal --}}
    <div x-show="showItemModal" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center"
         @keydown.escape.window="showItemModal = false">
        <div class="absolute inset-0 bg-black/60" @click="showItemModal = false"></div>
        <div class="relative bg-white w-full max-w-lg rounded-t-2xl sm:rounded-2xl shadow-2xl max-h-[85vh] overflow-y-auto"
             x-show="showItemModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="translate-y-full sm:translate-y-4 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100">
            <template x-if="selectedItem">
                <div>
                    <template x-if="selectedItem.image">
                        <img :src="selectedItem.image" :alt="selectedItem.name"
                             class="w-full h-48 object-cover rounded-t-2xl sm:rounded-t-2xl">
                    </template>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <h3 class="text-lg font-bold text-gray-900" x-text="selectedItem.name"></h3>
                            <button @click="showItemModal = false" class="text-gray-400 hover:text-gray-600 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-2xl font-extrabold text-primary-600 mb-3">
                            {{ $business->currency }} <span x-text="selectedItem.price.toFixed(2)"></span>
                        </p>
                        <p class="text-sm text-gray-600 mb-4" x-text="selectedItem.description || ''"></p>

                        <template x-if="selectedItem.variants && selectedItem.variants.length > 0">
                            <div class="mb-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Options</p>
                                <template x-for="v in selectedItem.variants" :key="v.name">
                                    <div class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0">
                                        <span class="text-sm text-gray-700" x-text="v.name"></span>
                                        <span class="text-sm text-gray-500"
                                              x-text="v.price_type === 'replace' ? '{{ $business->currency }} ' + parseFloat(v.price_adjustment).toFixed(2) : (v.price_adjustment > 0 ? '+{{ $business->currency }} ' + parseFloat(v.price_adjustment).toFixed(2) : '')">
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="selectedItem.prep_time">
                            <p class="text-xs text-gray-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Prep time: <span x-text="selectedItem.prep_time"></span> min
                            </p>
                        </template>

                        <div class="mt-5 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                            <p class="text-xs text-amber-700 font-medium text-center">
                                To order, please ask a staff member or use the POS.
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
