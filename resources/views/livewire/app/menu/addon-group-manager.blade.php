<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Add-on Groups</h1>
            <p class="text-sm text-gray-500 mt-1">Reusable option sets that you attach to menu items.</p>
        </div>
        <button wire:click="openGroupForm()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Group
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Group form modal --}}
    @if($showGroupForm)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeGroupForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">{{ $editingGroupId ? 'Edit Group' : 'New Add-on Group' }}</h2>
                <form wire:submit="saveGroup" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Group Name <span class="text-red-500">*</span></label>
                        <input wire:model="groupName" type="text" placeholder="e.g. Sauces, Toppings…" autofocus
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('groupName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selection Type</label>
                            <select wire:model="selectionType"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="multiple">Pick multiple</option>
                                <option value="single">Pick one</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 cursor-pointer pb-1">
                                <input wire:model="isRequired" type="checkbox"
                                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700">Required</span>
                            </label>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Selections</label>
                            <input wire:model="minSelections" type="number" min="0"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Selections</label>
                            <input wire:model="maxSelections" type="number" min="1"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                            Save Group
                        </button>
                        <button type="button" wire:click="closeGroupForm"
                                class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Groups list --}}
    @if($groups->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No add-on groups yet.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($groups as $group)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    {{-- Group header --}}
                    <div class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50 transition-colors"
                         wire:click="toggleExpand({{ $group->id }})">
                        <div class="flex-1">
                            <span class="font-medium text-gray-800">{{ $group->name }}</span>
                            <span class="ml-2 text-xs text-gray-400">{{ $group->items_count }} options</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">
                                    {{ $group->selection_type === 'single' ? 'Pick one' : 'Pick multiple' }}
                                </span>
                                @if($group->is_required)
                                    <span class="text-xs px-1.5 py-0.5 rounded bg-red-50 text-red-500">Required</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1" x-on:click.stop>
                            <button wire:click="openGroupForm({{ $group->id }})"
                                    class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:click="deleteGroup({{ $group->id }})"
                                    wire:confirm="Delete '{{ $group->name }}' and all its options?"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 transition-transform {{ $expandedGroupId === $group->id ? 'rotate-180' : '' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    {{-- Expanded items --}}
                    @if($expandedGroupId === $group->id)
                        <div class="border-t border-gray-100 bg-gray-50 p-4">
                            {{-- Items list --}}
                            @if($group->items->isEmpty())
                                <p class="text-sm text-gray-400 italic mb-3">No options yet.</p>
                            @else
                                <div class="space-y-2 mb-4">
                                    @foreach($group->items as $item)
                                        <div class="flex items-center gap-3 bg-white rounded-lg px-3 py-2 border border-gray-200">
                                            @if($editingItemId === $item->id)
                                                <form wire:submit="saveItem" class="flex-1 flex items-center gap-2">
                                                    <input wire:model="editItemName" type="text"
                                                           class="flex-1 rounded border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                                    <input wire:model="editItemPrice" type="number" step="0.01" min="0"
                                                           class="w-24 rounded border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                                    <button type="submit" class="text-primary-600 text-xs font-medium">Save</button>
                                                    <button type="button" wire:click="cancelEditItem" class="text-gray-400 text-xs">Cancel</button>
                                                </form>
                                            @else
                                                <span class="flex-1 text-sm text-gray-800">{{ $item->name }}</span>
                                                <span class="text-sm text-gray-500 font-medium">
                                                    {{ $item->price > 0 ? '+' . number_format($item->price, 2) : 'Free' }}
                                                </span>
                                                <button wire:click="toggleItemAvailable({{ $item->id }})"
                                                        class="text-xs px-2 py-0.5 rounded-full {{ $item->is_available ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                                                    {{ $item->is_available ? 'On' : 'Off' }}
                                                </button>
                                                <button wire:click="startEditItem({{ $item->id }})"
                                                        class="p-1 text-gray-400 hover:text-primary-600 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <button wire:click="deleteItem({{ $item->id }})"
                                                        wire:confirm="Remove '{{ $item->name }}'?"
                                                        class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Add item inline --}}
                            <form wire:submit="addItem({{ $group->id }})" class="flex items-center gap-2">
                                <input wire:model="newItemName" type="text" placeholder="Option name…"
                                       class="flex-1 rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <input wire:model="newItemPrice" type="number" step="0.01" min="0" placeholder="Price"
                                       class="w-24 rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <button type="submit"
                                        class="px-3 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                                    Add
                                </button>
                            </form>
                            @error('newItemName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
