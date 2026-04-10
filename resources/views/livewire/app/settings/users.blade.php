<div>
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">{{ $users->count() }} team members</p>
        <button wire:click="openForm()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add User
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- New password banner --}}
    @if($newPassword)
        <div class="mb-4 rounded-lg bg-yellow-50 border border-yellow-300 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-yellow-800 mb-1">User created! Share this temporary password:</p>
                    <code class="text-base font-mono font-bold text-yellow-900 bg-yellow-100 px-3 py-1 rounded">{{ $newPassword }}</code>
                    <p class="text-xs text-yellow-700 mt-2">Ask the user to change it after first login.</p>
                </div>
                <button wire:click="dismissPassword" class="text-yellow-600 hover:text-yellow-800 ml-4">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif

    @if($users->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <p class="text-gray-500 text-sm">No team members yet.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 {{ auth()->id() === $user->id ? 'bg-primary-50/30' : '' }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center shrink-0">
                                        <span class="text-xs font-bold text-primary-700">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $user->name }}
                                            @if(auth()->id() === $user->id)
                                                <span class="ml-1 text-xs text-primary-600">(you)</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                        @if($user->phone)
                                            <p class="text-xs text-gray-400">{{ $user->phone }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ $roles[$user->role] ?? ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="toggleActive({{ $user->id }})"
                                        {{ auth()->id() === $user->id ? 'disabled' : '' }}
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-colors
                                               {{ $user->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}
                                               disabled:opacity-50 disabled:cursor-not-allowed">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="openForm({{ $user->id }})"
                                        class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showForm)
        <div class="fixed inset-0 bg-black/40 z-40 flex items-center justify-center p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">{{ $editingId ? 'Edit User' : 'Add Team Member' }}</h2>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" autofocus
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @if(!$editingId)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input wire:model="email" type="email"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input wire:model="phone" type="tel"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                            <select wire:model="role"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                                @foreach($roles as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if(!$editingId)
                        <div class="rounded-lg bg-blue-50 border border-blue-200 px-3 py-2.5 text-xs text-blue-700">
                            A temporary password will be auto-generated. Share it securely with the new user.
                        </div>
                    @endif

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                            {{ $editingId ? 'Save Changes' : 'Create User' }}
                        </button>
                        <button type="button" wire:click="closeForm" class="flex-1 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
