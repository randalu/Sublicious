<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500">{{ $discounts->total() }} codes total</p>
        </div>
        <button wire:click="openForm()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Code
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if($discounts->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <svg class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <p class="text-gray-500 text-sm">No discount codes yet. Create your first one!</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code / Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validity</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($discounts as $discount)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-mono font-bold text-gray-900 text-sm">{{ $discount->code }}</p>
                                <p class="text-xs text-gray-500">{{ $discount->name }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($discount->type === 'percentage')
                                    {{ $discount->value }}%
                                @else
                                    ${{ $discount->value }}
                                @endif
                                @if($discount->min_order_amount)
                                    <p class="text-xs text-gray-400">Min: ${{ $discount->min_order_amount }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $discount->usage_count }}
                                @if($discount->usage_limit)
                                    / {{ $discount->usage_limit }}
                                @else
                                    / ∞
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                @if($discount->valid_from)
                                    <p>From: {{ $discount->valid_from->format('M d, Y') }}</p>
                                @endif
                                @if($discount->valid_until)
                                    <p>Until: {{ $discount->valid_until->format('M d, Y') }}</p>
                                @endif
                                @if(!$discount->valid_from && !$discount->valid_until)
                                    <span class="text-gray-400">No expiry</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="toggleActive({{ $discount->id }})"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                               {{ $discount->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $discount->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openForm({{ $discount->id }})"
                                            class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $discount->id }})"
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($discounts->hasPages())
            <div class="mt-4">{{ $discounts->links() }}</div>
        @endif
    @endif

    {{-- Create/Edit Modal --}}
    @if($showForm)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-bold text-gray-900 mb-5">{{ $editingId ? 'Edit Discount Code' : 'New Discount Code' }}</h2>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                            <input wire:model="code" type="text" placeholder="SUMMER20"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 uppercase">
                            @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Display Name <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" placeholder="Summer Sale"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                            <select wire:model="type"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Value <span class="text-red-500">*</span></label>
                            <input wire:model="value" type="number" step="0.01" min="0"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Order Amount</label>
                            <input wire:model="min_order_amount" type="number" step="0.01" min="0" placeholder="0.00"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usage Limit</label>
                            <input wire:model="usage_limit" type="number" min="1" placeholder="Unlimited"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valid From</label>
                            <input wire:model="valid_from" type="date"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valid Until</label>
                            <input wire:model="valid_until" type="date"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input wire:model="is_active" type="checkbox" id="is_active"
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <label for="is_active" class="text-sm text-gray-700">Active</label>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">Save</button>
                        <button type="button" wire:click="closeForm" class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirm Modal --}}
    @if($confirmDelete)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
                <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Discount Code?</h3>
                <p class="text-sm text-gray-500 mb-6">This action cannot be undone.</p>
                <div class="flex gap-3">
                    <button wire:click="delete" class="flex-1 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">Delete</button>
                    <button wire:click="cancelDelete" class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                </div>
            </div>
        </div>
    @endif
</div>
