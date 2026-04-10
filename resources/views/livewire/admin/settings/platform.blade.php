<div>
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- General --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="h-10 w-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                    <svg class="h-5 w-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">General</h2>
                    <p class="text-xs text-gray-400">Platform identity and branding</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Platform Name <span class="text-red-500">*</span></label>
                    <input wire:model="platform_name" type="text" placeholder="Sublicious"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('platform_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Platform Logo URL</label>
                    <input wire:model="platform_logo" type="url" placeholder="https://example.com/logo.png"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('platform_logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Defaults --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Defaults</h2>
                    <p class="text-xs text-gray-400">Default currency and timezone for new businesses</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Currency <span class="text-red-500">*</span></label>
                    <select wire:model="default_currency"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="USD">USD - US Dollar</option>
                        <option value="LKR">LKR - Sri Lankan Rupee</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                    </select>
                    @error('default_currency') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Timezone <span class="text-red-500">*</span></label>
                    <input wire:model="default_timezone" type="text" placeholder="UTC"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('default_timezone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Feature Toggles --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="h-10 w-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Feature Toggles</h2>
                    <p class="text-xs text-gray-400">Control platform-wide features</p>
                </div>
            </div>

            <div class="space-y-4">
                <label class="flex items-center justify-between p-4 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Allow New Registrations</p>
                        <p class="text-xs text-gray-400 mt-0.5">When disabled, new businesses cannot sign up</p>
                    </div>
                    <input type="checkbox" wire:model="allow_registrations"
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </label>

                <label class="flex items-center justify-between p-4 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Maintenance Mode</p>
                        <p class="text-xs text-gray-400 mt-0.5">When enabled, all tenant apps will show a maintenance page</p>
                    </div>
                    <input type="checkbox" wire:model="maintenance_mode"
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                </label>
            </div>
        </div>

        {{-- Support --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="h-10 w-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Support Contact</h2>
                    <p class="text-xs text-gray-400">Displayed to businesses and their customers</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                    <input wire:model="support_email" type="email" placeholder="support@sublicious.app"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('support_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
                    <input wire:model="support_phone" type="text" placeholder="+1 234 567 8900"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                    @error('support_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                Save Platform Settings
            </button>
        </div>
    </form>
</div>
