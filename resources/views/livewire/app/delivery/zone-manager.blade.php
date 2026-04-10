<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Delivery Zones</h1>
            <p class="text-sm text-gray-500 mt-1">Configure zones, fees and delivery estimates</p>
        </div>
        <button wire:click="openForm()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Zone
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if($zones->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No delivery zones configured. Add your first zone.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($zones as $zone)
                <div class="bg-white rounded-xl border border-gray-200 p-4 {{ ! $zone->is_active ? 'opacity-60' : '' }}">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $zone->name }}</h3>
                            @if($zone->polygon && isset($zone->polygon['description']) && $zone->polygon['description'])
                                <p class="text-xs text-gray-400 mt-0.5">{{ $zone->polygon['description'] }}</p>
                            @endif
                        </div>
                        <button wire:click="toggleActive({{ $zone->id }})"
                                class="text-xs px-2 py-1 rounded-full font-medium transition-colors
                                       {{ $zone->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                            {{ $zone->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </div>

                    <dl class="grid grid-cols-2 gap-2 text-sm mb-4">
                        <div>
                            <dt class="text-xs text-gray-500">Delivery Fee</dt>
                            <dd class="font-semibold text-gray-900">{{ number_format($zone->delivery_fee, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Min. Order</dt>
                            <dd class="font-semibold text-gray-900">{{ number_format($zone->minimum_order_amount, 2) }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs text-gray-500">Estimated Time</dt>
                            <dd class="font-medium text-gray-700">{{ $zone->estimated_minutes }} min</dd>
                        </div>
                    </dl>

                    <div class="flex gap-2">
                        <button wire:click="openForm({{ $zone->id }})"
                                class="flex-1 py-1.5 text-xs font-medium border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Edit
                        </button>
                        <button wire:click="delete({{ $zone->id }})"
                                wire:confirm="Delete zone '{{ $zone->name }}'?"
                                class="flex-1 py-1.5 text-xs font-medium border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Zone Form Modal --}}
    @if($showForm)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">{{ $editingId ? 'Edit Zone' : 'New Delivery Zone' }}</h2>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Zone Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" autofocus placeholder="e.g. City Centre"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Area Description</label>
                        <textarea wire:model="areaDescription" rows="2"
                                  placeholder="Describe the coverage area (streets, landmarks, radius…)"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Fee <span class="text-red-500">*</span></label>
                            <input wire:model="deliveryFee" type="number" step="0.01" min="0"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('deliveryFee') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Order</label>
                            <input wire:model="minimumOrder" type="number" step="0.01" min="0"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Minutes</label>
                            <input wire:model="estimatedMinutes" type="number" min="1" max="999"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input wire:model="isActive" type="checkbox" id="zone-active" class="rounded border-gray-300 text-primary-600">
                        <label for="zone-active" class="text-sm text-gray-700">Active (available for orders)</label>
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
