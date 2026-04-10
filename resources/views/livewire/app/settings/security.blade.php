<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Change Password --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-5">Change Password</h2>
        <form wire:submit="changePassword" class="space-y-4 max-w-md">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input wire:model="current_password" type="password" autocomplete="current-password"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input wire:model="new_password" type="password" autocomplete="new-password"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('new_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input wire:model="new_password_confirmation" type="password" autocomplete="new-password"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                @error('new_password_confirmation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <button type="submit"
                        class="px-5 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    Update Password
                </button>
            </div>
        </form>
    </div>

    {{-- Two-Factor Authentication --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Two-Factor Authentication</h2>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-gray-900">2FA Status:
                    @if($user->twoFactorEnabled())
                        <span class="text-green-600 font-semibold">Enabled</span>
                    @else
                        <span class="text-gray-400">Disabled</span>
                    @endif
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    Add an extra layer of security to your account with two-factor authentication.
                </p>
            </div>
            @if($user->twoFactorEnabled())
                <button type="button"
                        onclick="alert('2FA setup requires scanning QR code — coming soon')"
                        class="px-4 py-2 text-sm font-medium text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                    Disable 2FA
                </button>
            @else
                <button type="button"
                        onclick="alert('2FA setup requires scanning QR code — coming soon')"
                        class="px-4 py-2 text-sm font-medium text-primary-600 border border-primary-300 rounded-lg hover:bg-primary-50 transition-colors">
                    Enable 2FA
                </button>
            @endif
        </div>

        <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-700">
            2FA setup requires scanning a QR code — coming soon.
        </div>
    </div>

    {{-- Session Info --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Account Info</h2>
        <dl class="space-y-3">
            <div class="flex justify-between text-sm">
                <dt class="text-gray-500">Email</dt>
                <dd class="font-medium text-gray-900">{{ $user->email }}</dd>
            </div>
            <div class="flex justify-between text-sm">
                <dt class="text-gray-500">Role</dt>
                <dd class="font-medium text-gray-900">{{ ucfirst($user->role) }}</dd>
            </div>
            <div class="flex justify-between text-sm">
                <dt class="text-gray-500">Email Verified</dt>
                <dd class="font-medium {{ $user->email_verified_at ? 'text-green-600' : 'text-red-500' }}">
                    {{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y') : 'Not verified' }}
                </dd>
            </div>
        </dl>
    </div>
</div>
