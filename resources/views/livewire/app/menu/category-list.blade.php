<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Menu Categories</h1>
            <p class="text-sm text-gray-500 mt-1">Drag to reorder. Categories group your menu items.</p>
        </div>
        <button wire:click="$toggle('showForm')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Category
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Add form --}}
    @if($showForm)
        <div class="mb-6 bg-white rounded-xl border border-gray-200 p-4">
            <form wire:submit="save" class="flex items-center gap-3">
                <input wire:model="name" type="text" placeholder="Category name…" autofocus
                       class="flex-1 rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                <button type="submit"
                        class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                    Save
                </button>
                <button type="button" wire:click="$set('showForm', false)"
                        class="px-3 py-2 text-gray-500 text-sm rounded-lg hover:bg-gray-100">
                    Cancel
                </button>
            </form>
        </div>
    @endif

    {{-- Category list --}}
    @if($categories->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-gray-500 text-sm">No categories yet. Add one to get started.</p>
        </div>
    @else
        <div id="category-sortable" class="space-y-2"
             x-data="{}"
             x-init="
                const el = document.getElementById('category-sortable');
                new Sortable(el, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'opacity-40',
                    onEnd(evt) {
                        const order = [...el.querySelectorAll('[data-id]')].map(el => parseInt(el.dataset.id));
                        $wire.reorder(order);
                    }
                });
             ">
            @foreach($categories as $cat)
                <div data-id="{{ $cat->id }}"
                     class="bg-white rounded-xl border border-gray-200 px-4 py-3 flex items-center gap-3 group">
                    {{-- Drag handle --}}
                    <div class="drag-handle cursor-grab text-gray-300 hover:text-gray-500">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                        </svg>
                    </div>

                    {{-- Name / edit --}}
                    @if($editingId === $cat->id)
                        <form wire:submit="saveEdit" class="flex-1 flex items-center gap-2">
                            <input wire:model="editName" type="text" autofocus
                                   class="flex-1 rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <button type="submit" class="text-primary-600 text-sm font-medium hover:text-primary-700">Save</button>
                            <button type="button" wire:click="cancelEdit" class="text-gray-400 text-sm hover:text-gray-600">Cancel</button>
                        </form>
                    @else
                        <div class="flex-1 flex items-center gap-2">
                            <span class="font-medium text-gray-800">{{ $cat->name }}</span>
                            <span class="text-xs text-gray-400">{{ $cat->items_count }} items</span>
                        </div>

                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            {{-- Toggle active --}}
                            <button wire:click="toggleActive({{ $cat->id }})"
                                    class="text-xs px-2 py-1 rounded-full font-medium transition-colors
                                           {{ $cat->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                {{ $cat->is_active ? 'Active' : 'Hidden' }}
                            </button>
                            {{-- Edit --}}
                            <button wire:click="startEdit({{ $cat->id }})"
                                    class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            {{-- Delete --}}
                            <button wire:click="delete({{ $cat->id }})"
                                    wire:confirm="Delete '{{ $cat->name }}'? This cannot be undone."
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
