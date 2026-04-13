<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('app.menu.items') }}" wire:navigate class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $item ? 'Edit: ' . $item->name : 'New Menu Item' }}</h1>
    </div>

    @if($saveError)
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $saveError }}</div>
    @endif

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Main info --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Basic Info --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Basic Info</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" placeholder="e.g. Classic Cheeseburger"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="3" placeholder="Optional description…"
                              class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                    @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select wire:model="categoryId"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">— None —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Base Price <span class="text-red-500">*</span></label>
                        <input wire:model="basePrice" type="number" step="0.01" min="0"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('basePrice') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prep Time (minutes)</label>
                    <input wire:model="preparationTimeMinutes" type="number" min="1" max="300"
                           class="w-32 rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                </div>

                {{-- Toggles --}}
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input wire:model="isAvailable" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-gray-700">Available on menu</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input wire:model="isDeliveryAvailable" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-gray-700">Available for delivery</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input wire:model="isFeatured" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-gray-700">Featured item</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input wire:model="trackInventory" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm text-gray-700">Track inventory</span>
                    </label>
                </div>
            </div>

            {{-- Variants --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Variants</h2>
                        <p class="text-xs text-gray-400 mt-0.5">e.g. Small, Medium, Large with different prices</p>
                    </div>
                    <button type="button" wire:click="addVariant"
                            class="text-sm text-primary-600 font-medium hover:text-primary-700">+ Add Variant</button>
                </div>

                @if(empty($variants))
                    <p class="text-sm text-gray-400 italic">No variants — single price item.</p>
                @else
                    <div class="space-y-3">
                        @foreach($variants as $i => $variant)
                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1 grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Name</label>
                                        <input wire:model="variants.{{ $i }}.name" type="text" placeholder="e.g. Large"
                                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                        @error("variants.{$i}.name") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Price Type</label>
                                        <select wire:model="variants.{{ $i }}.price_type"
                                                class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="replace">Fixed Price</option>
                                            <option value="add">+ Add</option>
                                            <option value="subtract">− Subtract</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Amount</label>
                                        <input wire:model="variants.{{ $i }}.price_adjustment" type="number" step="0.01" min="0"
                                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                        @error("variants.{$i}.price_adjustment") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 pt-5">
                                    <label class="flex items-center gap-1 cursor-pointer" title="Available">
                                        <input wire:model="variants.{{ $i }}.is_available" type="checkbox"
                                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <span class="text-xs text-gray-500">On</span>
                                    </label>
                                    <button type="button" wire:click="removeVariant({{ $i }})"
                                            class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Addon Groups --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Add-on Groups</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Extras customers can choose (e.g. Sauces, Toppings)</p>
                    </div>
                    <a href="{{ route('app.menu.addons') }}" wire:navigate class="text-xs text-primary-600 hover:underline">Manage Groups</a>
                </div>

                @if($addonGroups->isEmpty())
                    <p class="text-sm text-gray-400 italic">No addon groups yet. <a href="{{ route('app.menu.addons') }}" wire:navigate class="text-primary-600 hover:underline">Create one</a>.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($addonGroups as $group)
                            <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                          {{ in_array($group->id, $attachedAddonGroupIds) ? 'border-primary-300 bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="checkbox"
                                       wire:click="toggleAddonGroup({{ $group->id }})"
                                       @checked(in_array($group->id, $attachedAddonGroupIds))
                                       class="mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $group->name }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ $group->selection_type === 'single' ? 'Pick one' : 'Pick multiple' }}
                                        @if($group->is_required) · Required @endif
                                        · {{ $group->items->count() }} options
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT: Image + Actions --}}
        <div class="space-y-5">

            {{-- Image upload --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Item Image</h2>

                @if($photo)
                    <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="w-full h-48 object-cover rounded-lg mb-3">
                @elseif($item?->image)
                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-48 object-cover rounded-lg mb-3">
                @else
                    <div class="w-full h-48 bg-gray-100 rounded-lg flex items-center justify-center text-gray-300 mb-3">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                @endif

                <label class="block w-full text-center cursor-pointer py-2 px-4 rounded-lg border border-gray-300 text-sm text-gray-600 hover:border-primary-400 hover:text-primary-600 transition-colors">
                    <span>{{ $photo ? 'Change Image' : 'Upload Image' }}</span>
                    <input type="file" wire:model="photo" accept="image/*" class="sr-only">
                </label>
                @error('photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-400 mt-1 text-center">JPG, PNG, WebP — max 2 MB</p>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="w-full py-2.5 px-4 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-60 transition-colors">
                    <span wire:loading.remove>{{ $item ? 'Update Item' : 'Create Item' }}</span>
                    <span wire:loading>Saving…</span>
                </button>
                <a href="{{ route('app.menu.items') }}" wire:navigate
                   class="block w-full text-center py-2.5 px-4 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>
