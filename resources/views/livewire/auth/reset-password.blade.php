<div>
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Set new password</h2>

    @if($error)
        <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200">
            <p class="text-sm text-red-700">{{ $error }}</p>
        </div>
    @endif

    <form wire:submit="resetPassword" class="space-y-4">
        <input type="hidden" wire:model="token">
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input wire:model="email" type="email" required readonly
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">New Password</label>
            <input wire:model="password" type="password" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input wire:model="passwordConfirmation" type="password" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
        </div>
        <button type="submit"
                class="w-full py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 disabled:opacity-50"
                wire:loading.attr="disabled">
            <span wire:loading.remove>Reset Password</span>
            <span wire:loading>Resetting...</span>
        </button>
    </form>
</div>
