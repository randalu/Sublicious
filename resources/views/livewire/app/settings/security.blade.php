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
                <div class="flex gap-2">
                    <button type="button" wire:click="regenerateRecoveryCodes"
                            wire:confirm="Generate new recovery codes? Old codes will stop working."
                            class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Recovery Codes
                    </button>
                    <button type="button" wire:click="disable2FA"
                            wire:confirm="Are you sure you want to disable two-factor authentication?"
                            class="px-4 py-2 text-sm font-medium text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                        Disable 2FA
                    </button>
                </div>
            @else
                <button type="button" wire:click="startEnable2FA"
                        class="px-4 py-2 text-sm font-medium text-primary-600 border border-primary-300 rounded-lg hover:bg-primary-50 transition-colors">
                    Enable 2FA
                </button>
            @endif
        </div>
    </div>

    {{-- 2FA Setup Modal --}}
    @if($showSetup2FA)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="cancelSetup">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-2">Set Up 2FA</h2>
                <p class="text-sm text-gray-500 mb-4">
                    Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.), then enter the 6-digit code to confirm.
                </p>

                <div class="flex justify-center mb-4">
                    {!! $twoFactorQrSvg !!}
                </div>

                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Or enter this key manually:</p>
                    <p class="text-sm font-mono font-medium text-gray-900 select-all break-all">{{ $twoFactorSecret }}</p>
                </div>

                <form wire:submit="confirmEnable2FA" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                        <input wire:model="confirmCode" type="text" inputmode="numeric" pattern="[0-9]*"
                               maxlength="6" autofocus placeholder="000000"
                               class="w-full rounded-lg border-gray-300 text-sm text-center tracking-[0.5em] font-mono focus:border-primary-500 focus:ring-primary-500">
                        @error('confirmCode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">Verify & Enable</button>
                        <button type="button" wire:click="cancelSetup" class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Recovery Codes Modal --}}
    @if($showRecoveryCodes)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeRecoveryCodes">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-2">Recovery Codes</h2>
                <p class="text-sm text-gray-500 mb-4">
                    Save these codes in a safe place. Each code can be used once to sign in if you lose access to your authenticator app.
                </p>

                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($recoveryCodes as $code)
                            <p class="font-mono text-sm text-gray-900 select-all">{{ $code }}</p>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-700 mb-4">
                    These codes will not be shown again. Store them securely.
                </div>

                <button type="button" wire:click="closeRecoveryCodes"
                        class="w-full py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    I've Saved My Codes
                </button>
            </div>
        </div>
    @endif

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
