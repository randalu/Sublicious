<div>
    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Items</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalItems }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Low Stock</p>
            <p class="text-2xl font-bold {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ $lowStockCount }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalValue, 2) }}</p>
        </div>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Inventory</h1>
        <button wire:click="openForm()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Item
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-5">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search inventory..."
                   class="pl-9 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
        </div>
        <select wire:model.live="stockFilter"
                class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
            <option value="">All Stock Levels</option>
            <option value="low">Low Stock</option>
            <option value="out">Out of Stock</option>
        </select>
    </div>

    {{-- Items table --}}
    @if($items->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
            <p class="text-gray-500 text-sm mt-3">No inventory items found.</p>
            <button wire:click="openForm()" class="mt-3 text-sm text-primary-600 hover:text-primary-700 font-medium">Add your first item</button>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Threshold</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost/Unit</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($items as $item)
                        @php $isLow = $item->isLowStock(); @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ $isLow ? 'bg-amber-50/50' : '' }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $item->unit }}</td>
                            <td class="px-4 py-3 text-right text-sm font-medium {{ $isLow ? 'text-amber-700' : 'text-gray-900' }}">
                                {{ number_format($item->current_stock, $item->unit === 'pcs' ? 0 : 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-500">
                                {{ number_format($item->low_stock_threshold, $item->unit === 'pcs' ? 0 : 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-600">{{ number_format($item->cost_per_unit, 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-600">{{ number_format($item->current_stock * $item->cost_per_unit, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($item->current_stock <= 0)
                                    <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700">Out</span>
                                @elseif($isLow)
                                    <span class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-700">Low</span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">OK</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openTransaction({{ $item->id }}, 'restock')"
                                            class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                            title="Restock">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </button>
                                    <button wire:click="openTransaction({{ $item->id }}, 'deduction')"
                                            class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors"
                                            title="Deduct">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                    </button>
                                    <button wire:click="openHistory({{ $item->id }})"
                                            class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="History">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                    <button wire:click="openLinkMenu({{ $item->id }})"
                                            class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                            title="Link Menu Items">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    </button>
                                    <button wire:click="openForm({{ $item->id }})"
                                            class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                                            title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $item->id }})"
                                            wire:confirm="Delete this inventory item and all its transactions?"
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="mt-4">{{ $items->links() }}</div>
        @endif
    @endif

    {{-- Create/Edit Item Modal --}}
    @if($showForm)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">{{ $editingId ? 'Edit Item' : 'Add Inventory Item' }}</h2>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" placeholder="e.g. Flour, Tomatoes, Olive Oil"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                            <select wire:model="unit"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="kg">Kilograms (kg)</option>
                                <option value="g">Grams (g)</option>
                                <option value="L">Litres (L)</option>
                                <option value="ml">Millilitres (ml)</option>
                            </select>
                            @error('unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        @if(!$editingId)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Initial Stock <span class="text-red-500">*</span></label>
                                <input wire:model="currentStock" type="number" step="0.001" min="0"
                                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                @error('currentStock') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold <span class="text-red-500">*</span></label>
                            <input wire:model="lowStockThreshold" type="number" step="0.001" min="0"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('lowStockThreshold') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cost per Unit <span class="text-red-500">*</span></label>
                            <input wire:model="costPerUnit" type="number" step="0.01" min="0"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('costPerUnit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Save</button>
                        <button type="button" wire:click="closeForm" class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Stock Transaction Modal --}}
    @if($showTransaction)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeTransaction">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">
                    {{ ucfirst($transactionType) }} Stock
                </h2>
                <p class="text-sm text-gray-500 mb-5">{{ $transactionItemName }}</p>
                <form wire:submit="saveTransaction" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select wire:model="transactionType"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="restock">Restock (add)</option>
                            <option value="deduction">Deduction (remove)</option>
                            <option value="waste">Waste (remove)</option>
                            <option value="adjustment">Adjustment (set to)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $transactionType === 'adjustment' ? 'New Stock Level' : 'Quantity' }}
                            <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="transactionQty" type="number" step="0.001" min="0"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                               placeholder="0">
                        @error('transactionQty') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="transactionNotes" rows="2"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                                  placeholder="Optional reason..."></textarea>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Update Stock</button>
                        <button type="button" wire:click="closeTransaction" class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Transaction History Modal --}}
    @if($showHistory)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeHistory">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Transaction History</h2>
                        <p class="text-sm text-gray-500">{{ $historyItemName }}</p>
                    </div>
                    <button wire:click="closeHistory" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 -mx-6 px-6">
                    @if(count($transactions) === 0)
                        <p class="text-center text-sm text-gray-400 py-8">No transactions yet.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($transactions as $tx)
                                <div class="flex items-start gap-3 text-sm border-b border-gray-100 pb-3 last:border-0">
                                    <div class="shrink-0 mt-0.5">
                                        @if($tx->type === 'restock')
                                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-green-100 text-green-600">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                            </span>
                                        @elseif($tx->type === 'deduction' || $tx->type === 'waste')
                                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-red-100 text-red-600">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-blue-100 text-blue-600">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-900 capitalize">{{ $tx->type }}</span>
                                            <span class="text-xs text-gray-400">{{ $tx->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-gray-600">
                                            {{ number_format($tx->quantity_before, 2) }} &rarr; {{ number_format($tx->quantity_after, 2) }}
                                            <span class="text-gray-400">({{ $tx->quantity >= 0 ? '+' : '' }}{{ number_format($tx->quantity, 2) }})</span>
                                        </p>
                                        @if($tx->notes)
                                            <p class="text-gray-400 text-xs mt-0.5">{{ $tx->notes }}</p>
                                        @endif
                                        @if($tx->user)
                                            <p class="text-gray-400 text-xs">by {{ $tx->user->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Link Menu Items Modal --}}
    @if($showLinkMenu)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeLinkMenu">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Linked Menu Items</h2>
                        <p class="text-sm text-gray-500">{{ $linkItemName }}</p>
                    </div>
                    <button wire:click="closeLinkMenu" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Existing links --}}
                <div class="overflow-y-auto flex-1 -mx-6 px-6 mb-4">
                    @if(count($linkedMenuItems) === 0)
                        <p class="text-center text-sm text-gray-400 py-4">No menu items linked yet.</p>
                    @else
                        <div class="space-y-2">
                            @foreach($linkedMenuItems as $mi)
                                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $mi->name }}</span>
                                        <span class="text-gray-400 ml-2">{{ $mi->pivot->quantity_used }} {{ $linkItemName ? '' : '' }} per item</span>
                                    </div>
                                    <button wire:click="unlinkMenuItem({{ $linkItemId }}, {{ $mi->id }})"
                                            wire:confirm="Unlink this menu item?"
                                            class="text-gray-400 hover:text-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Add link form --}}
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-sm font-medium text-gray-700 mb-3">Link a Menu Item</p>
                    <form wire:submit="saveLink" class="space-y-3">
                        <div>
                            <select wire:model="linkMenuItemId"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">Select menu item...</option>
                                @foreach($menuItems as $mi)
                                    <option value="{{ $mi->id }}">{{ $mi->name }}</option>
                                @endforeach
                            </select>
                            @error('linkMenuItemId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Quantity used per menu item sold</label>
                            <input wire:model="linkQuantityUsed" type="number" step="0.001" min="0.001"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('linkQuantityUsed') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="w-full py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Link</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
