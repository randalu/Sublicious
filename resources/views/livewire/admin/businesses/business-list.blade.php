<div>
    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex-1">
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by name or email..."
                   class="w-full sm:w-80 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="statusFilter" class="rounded-md border-gray-300 text-sm shadow-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
            <a href="{{ route('admin.businesses.create') }}"
               class="inline-flex items-center gap-1.5 rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                + New Business
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 border border-green-200">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Plan</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Registered</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($businesses as $biz)
                    <tr>
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium text-gray-900">{{ $biz->name }}</p>
                            <p class="text-xs text-gray-500">{{ $biz->email }}</p>
                        </td>
                        <td class="px-5 py-4 hidden sm:table-cell">
                            <span class="text-sm text-gray-700">{{ $biz->plan?->name ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-4 hidden md:table-cell">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                         {{ $biz->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $biz->is_active ? 'Active' : 'Suspended' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 hidden lg:table-cell">
                            <span class="text-sm text-gray-500">{{ $biz->created_at->format('M d, Y') }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.businesses.show', $biz) }}"
                                   class="text-xs text-primary-600 hover:text-primary-700 font-medium">View</a>
                                @if($biz->is_active)
                                    <button wire:click="$set('confirmSuspendId', {{ $biz->id }})"
                                            class="text-xs text-yellow-600 hover:text-yellow-700 font-medium">Suspend</button>
                                @else
                                    <button wire:click="restore({{ $biz->id }})"
                                            class="text-xs text-green-600 hover:text-green-700 font-medium">Restore</button>
                                @endif
                                <button wire:click="$set('confirmDeleteId', {{ $biz->id }})"
                                        class="text-xs text-red-600 hover:text-red-700 font-medium">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-400">No businesses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($businesses->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">
                {{ $businesses->links() }}
            </div>
        @endif
    </div>

    {{-- Suspend confirm --}}
    @if($confirmSuspendId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
            <div class="bg-white rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
                <h3 class="text-base font-semibold text-gray-900">Suspend Business?</h3>
                <p class="mt-2 text-sm text-gray-500">This will immediately prevent all users of this business from logging in.</p>
                <div class="mt-5 flex gap-3 justify-end">
                    <button wire:click="$set('confirmSuspendId', null)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button wire:click="suspend({{ $confirmSuspendId }})" class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700">Suspend</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete confirm --}}
    @if($confirmDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50">
            <div class="bg-white rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
                <h3 class="text-base font-semibold text-gray-900">Delete Business?</h3>
                <p class="mt-2 text-sm text-gray-500">This will soft-delete the business. All data will be preserved but inaccessible.</p>
                <div class="mt-5 flex gap-3 justify-end">
                    <button wire:click="$set('confirmDeleteId', null)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button wire:click="delete({{ $confirmDeleteId }})" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    @endif
</div>
