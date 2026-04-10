<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Delivery Riders</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $riders->total() }} rider(s) registered</p>
        </div>
        <button wire:click="openForm()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Rider
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Search --}}
    <div class="mb-5 relative max-w-md">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by name, phone or vehicle…"
               class="pl-9 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
    </div>

    @if($riders->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No riders found. Add your first delivery rider.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rider</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deliveries</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($riders as $rider)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ $rider->name }}</p>
                                <p class="text-xs text-gray-400">{{ $rider->phone }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($rider->vehicle_type)
                                    <p class="capitalize">{{ $rider->vehicle_type }}</p>
                                @endif
                                @if($rider->vehicle_number)
                                    <p class="text-xs text-gray-400 font-mono">{{ $rider->vehicle_number }}</p>
                                @endif
                                @if(! $rider->vehicle_type && ! $rider->vehicle_number)
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($rider->commission_type === 'percentage')
                                    {{ number_format($rider->commission_value, 1) }}%
                                @else
                                    {{ number_format($rider->commission_value, 2) }} / delivery
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-gray-700">{{ $rider->total_deliveries }}</td>
                            <td class="px-4 py-3 text-center">
                                <button wire:click="toggleActive({{ $rider->id }})"
                                        class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full font-medium transition-colors
                                               {{ $rider->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $rider->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    {{ $rider->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openForm({{ $rider->id }})"
                                            class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $rider->id }})"
                                            wire:confirm="Delete {{ $rider->name }}? This cannot be undone."
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
        @if($riders->hasPages())
            <div class="mt-4">{{ $riders->links() }}</div>
        @endif
    @endif

    {{-- Rider Form Modal --}}
    @if($showForm)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">{{ $editingId ? 'Edit Rider' : 'Add Rider' }}</h2>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" autofocus
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                            <input wire:model="phone" type="tel"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type</label>
                            <input wire:model="vehicleType" type="text" placeholder="e.g. motorcycle"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Number</label>
                            <input wire:model="vehicleNumber" type="text" placeholder="e.g. ABC 1234"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Commission Type</label>
                            <select wire:model="commissionType"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="per_delivery">Per Delivery (fixed)</option>
                                <option value="percentage">Percentage of fee</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Commission Value
                                <span class="text-gray-400">({{ $commissionType === 'percentage' ? '%' : 'amount' }})</span>
                            </label>
                            <input wire:model="commissionValue" type="number" step="0.01" min="0"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('commissionValue') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input wire:model="isActive" type="checkbox" id="rider-active" class="rounded border-gray-300 text-primary-600">
                        <label for="rider-active" class="text-sm text-gray-700">Active (available for assignments)</label>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">Save</button>
                        <button type="button" wire:click="closeForm" class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
