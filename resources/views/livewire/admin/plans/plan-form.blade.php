<div>
    <div class="mb-6">
        <a href="{{ route('admin.plans') }}" wire:navigate
           class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Plans
        </a>
    </div>

    @if($saveError)
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $saveError }}</div>
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- Basic Info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-5">Plan Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plan Name <span class="text-red-500">*</span></label>
                    <input wire:model.live="name" type="text" placeholder="Starter, Growth, Pro…"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
                    <input wire:model="slug" type="text" placeholder="starter"
                           class="w-full rounded-lg border-gray-300 text-sm font-mono focus:border-primary-500 focus:ring-primary-500">
                    @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="description" rows="2" placeholder="Short description of this plan…"
                              class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-5">Pricing (in cents)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Price (cents) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">¢</span>
                        <input wire:model="price_monthly" type="number" min="0" step="100"
                               class="pl-8 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">= ${{ number_format($price_monthly / 100, 2) }}/month</p>
                    @error('price_monthly') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Yearly Price (cents) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">¢</span>
                        <input wire:model="price_yearly" type="number" min="0" step="100"
                               class="pl-8 w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">= ${{ number_format($price_yearly / 100, 2) }}/year</p>
                    @error('price_yearly') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Limits --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-5">Usage Limits</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Orders / Month <span class="text-red-500">*</span></label>
                    <input wire:model="max_orders_per_month" type="number" min="1"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('max_orders_per_month') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Staff <span class="text-red-500">*</span></label>
                    <input wire:model="max_staff" type="number" min="1"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('max_staff') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Menu Items <span class="text-red-500">*</span></label>
                    <input wire:model="max_menu_items" type="number" min="1"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('max_menu_items') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Features --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-5">Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @php
                    $featureOptions = [
                        'feature_delivery'          => ['label' => 'Delivery Module', 'desc' => 'Rider tracking, delivery zones'],
                        'feature_hr_module'         => ['label' => 'HR Module', 'desc' => 'Attendance, payroll, shifts'],
                        'feature_sms_notifications' => ['label' => 'SMS Notifications', 'desc' => 'Order SMS to customers'],
                        'feature_export'            => ['label' => 'Data Export', 'desc' => 'CSV/Excel exports'],
                        'feature_api_integrations'  => ['label' => 'API Integrations', 'desc' => 'Third-party integrations'],
                    ];
                @endphp
                @foreach($featureOptions as $key => $item)
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50
                                  {{ $$key ? 'border-primary-300 bg-primary-50' : '' }}">
                        <input type="checkbox" wire:model="{{ $key }}"
                               class="mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $item['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ $item['desc'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Options --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Options</h2>
            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="is_active"
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm font-medium text-gray-700">Active (visible to users)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="is_default"
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm font-medium text-gray-700">Set as default plan</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.plans') }}" wire:navigate
               class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                {{ $planId ? 'Update Plan' : 'Create Plan' }}
            </button>
        </div>
    </form>
</div>
