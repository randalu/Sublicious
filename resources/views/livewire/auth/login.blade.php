<div>
    @if($challengingTwoFactor)
        {{-- 2FA Challenge --}}
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Two-Factor Authentication</h2>
        <p class="text-sm text-gray-500 mb-6">
            @if($useRecoveryCode)
                Enter one of your emergency recovery codes.
            @else
                Enter the 6-digit code from your authenticator app.
            @endif
        </p>

        <form wire:submit="verifyTwoFactor" class="space-y-5">
            @if($useRecoveryCode)
                <div>
                    <label for="recoveryCode" class="block text-sm font-medium text-gray-700">Recovery Code</label>
                    <input wire:model="recoveryCode" id="recoveryCode" type="text" autofocus
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm font-mono"
                           placeholder="Enter recovery code">
                    @error('recoveryCode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @else
                <div>
                    <label for="twoFactorCode" class="block text-sm font-medium text-gray-700">Authentication Code</label>
                    <input wire:model="twoFactorCode" id="twoFactorCode" type="text" inputmode="numeric"
                           pattern="[0-9]*" maxlength="6" autofocus
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm text-center tracking-[0.5em] font-mono"
                           placeholder="000000">
                    @error('twoFactorCode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>Verify</span>
                <span wire:loading>Verifying...</span>
            </button>
        </form>

        <div class="mt-4 flex items-center justify-between">
            <button type="button" wire:click="toggleRecoveryMode"
                    class="text-sm font-medium text-primary-600 hover:text-primary-500">
                {{ $useRecoveryCode ? 'Use authenticator code' : 'Use a recovery code' }}
            </button>
            <button type="button" wire:click="cancelTwoFactor"
                    class="text-sm text-gray-500 hover:text-gray-700">
                Cancel
            </button>
        </div>
    @else
        {{-- Normal Login --}}
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Sign in to your account</h2>

        @if($error)
            <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200">
                <p class="text-sm text-red-700">{{ $error }}</p>
            </div>
        @endif

        <form wire:submit="login" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input wire:model="email" id="email" type="email" autocomplete="email" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                       placeholder="you@example.com">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input wire:model="password" id="password" type="password" autocomplete="current-password" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input wire:model="remember" id="remember" type="checkbox"
                           class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                </div>
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500">
                    Forgot password?
                </a>
            </div>

            <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>Sign in</span>
                <span wire:loading>Signing in...</span>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-medium text-primary-600 hover:text-primary-500">Register your business</a>
        </p>
    @endif
</div>
