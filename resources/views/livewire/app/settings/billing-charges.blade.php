<div>
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-5">Service Charge Configuration</h2>

            {{-- Enable toggle --}}
            <div class="flex items-center justify-between pb-5 border-b border-gray-100">
                <div>
                    <p class="text-sm font-medium text-gray-700">Enable Service Charge</p>
                    <p class="text-xs text-gray-400 mt-0.5">Automatically add a service charge to orders</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="service_charge_enabled" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary-300 rounded-full peer
                                peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px]
                                after:start-[2px] after:bg-white after:border-gray-300 after:border
                                after:rounded-full after:h-5 after:w-5 after:transition-all
                                peer-checked:bg-primary-600"></div>
                </label>
            </div>

            @if($service_charge_enabled)
                <div class="pt-5 space-y-5">
                    {{-- Type --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Charge Type</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model.live="service_charge_type" value="percentage"
                                           class="text-primary-600 focus:ring-primary-500">
                                    <span class="text-sm text-gray-700">Percentage (%)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model.live="service_charge_type" value="fixed"
                                           class="text-primary-600 focus:ring-primary-500">
                                    <span class="text-sm text-gray-700">Fixed Amount</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $service_charge_type === 'percentage' ? 'Percentage (%)' : 'Fixed Amount' }}
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">
                                    {{ $service_charge_type === 'percentage' ? '%' : '$' }}
                                </span>
                                <input wire:model.live="service_charge_value" type="number" step="0.01" min="0"
                                       class="pl-8 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            @error('service_charge_value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Applies to --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Applies To</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="service_charge_applies_to" value="all"
                                       class="text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700">All Orders</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="service_charge_applies_to" value="dine_in_only"
                                       class="text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700">Dine-In Only</span>
                            </label>
                        </div>
                    </div>

                    {{-- Live preview --}}
                    <div class="rounded-lg bg-primary-50 border border-primary-200 px-4 py-3">
                        <p class="text-xs font-medium text-primary-700 uppercase tracking-wide mb-1">Preview</p>
                        <p class="text-sm text-primary-800">{{ $this->preview }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                Save Changes
            </button>
        </div>
    </form>
</div>
