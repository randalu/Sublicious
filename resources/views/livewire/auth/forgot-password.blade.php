<div>
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Reset your password</h2>
    <p class="text-sm text-gray-500 mb-6">Enter your email and we'll send you a reset link.</p>

    @if($sent)
        <div class="rounded-md bg-green-50 p-4 border border-green-200">
            <p class="text-sm text-green-800">A password reset link has been sent to <strong>{{ $email }}</strong>. Check your inbox.</p>
        </div>
    @else
        @if($error)
            <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200">
                <p class="text-sm text-red-700">{{ $error }}</p>
            </div>
        @endif
        <form wire:submit="sendLink" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Email address</label>
                <input wire:model="email" type="email" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit"
                    class="w-full py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 disabled:opacity-50"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>Send reset link</span>
                <span wire:loading>Sending...</span>
            </button>
        </form>
    @endif

    <p class="mt-6 text-center text-sm text-gray-500">
        <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:text-primary-500">← Back to sign in</a>
    </p>
</div>
