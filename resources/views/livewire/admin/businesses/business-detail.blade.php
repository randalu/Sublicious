<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.businesses') }}" wire:navigate class="text-sm text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $business->name }}</h1>
                <p class="text-sm text-gray-500">{{ $business->email }} · {{ $business->slug }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($business->is_active)
                <button wire:click="$set('showSuspendModal', true)"
                        class="px-3 py-2 text-sm font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                    Suspend
                </button>
            @else
                <button wire:click="activate"
                        class="px-3 py-2 text-sm font-medium text-green-600 border border-green-200 rounded-lg hover:bg-green-50 transition-colors">
                    Activate
                </button>
            @endif
            @if(!$business->is_verified)
                <button wire:click="verify"
                        class="px-3 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors">
                    Verify
                </button>
            @endif
            <a href="{{ route('admin.businesses.logs', $business) }}" wire:navigate
               class="px-3 py-2 text-sm font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                View Logs
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Info Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 md:col-span-2">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Business Info</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div><dt class="text-gray-500">Status</dt>
                    <dd class="font-medium">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                     {{ $business->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $business->is_active ? 'Active' : 'Suspended' }}
                        </span>
                        @if($business->is_verified)
                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">Verified</span>
                        @endif
                    </dd>
                </div>
                <div><dt class="text-gray-500">Plan</dt><dd class="font-medium">{{ $business->plan?->name ?? 'None' }}</dd></div>
                <div><dt class="text-gray-500">Subscription</dt><dd>{{ ucfirst($business->subscription_status ?? 'none') }}</dd></div>
                <div><dt class="text-gray-500">Currency</dt><dd>{{ $business->currency }}</dd></div>
                <div><dt class="text-gray-500">Timezone</dt><dd>{{ $business->timezone }}</dd></div>
                <div><dt class="text-gray-500">Phone</dt><dd>{{ $business->phone ?? '—' }}</dd></div>
                <div class="col-span-2"><dt class="text-gray-500">Address</dt><dd>{{ $business->address ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Owner</dt><dd>{{ $business->owner()?->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Registered</dt><dd>{{ $business->created_at->format('d M Y') }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Statistics</h2>
            <div class="space-y-3">
                @foreach([
                    ['Total Orders', number_format($stats['total_orders'])],
                    ['Total Revenue', '$' . number_format($stats['total_revenue'], 2)],
                    ['Employees', $stats['total_employees']],
                    ['Riders', $stats['total_riders']],
                    ['Menu Items', $stats['menu_items']],
                    ['Customers', $stats['customers']],
                ] as [$label, $value])
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ $label }}</span>
                        <span class="font-semibold text-gray-900">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Change Plan --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Change Plan</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($plans as $plan)
                <button wire:click="changePlan({{ $plan->id }})"
                        class="px-3 py-2 text-sm rounded-lg border transition-colors
                               {{ $business->plan_id === $plan->id ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                    {{ $plan->name }} — {{ $plan->formattedPrice('monthly') }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Recent Audit Logs --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Recent Activity</h2>
            <a href="{{ route('admin.businesses.logs', $business) }}" wire:navigate class="text-xs text-primary-600 hover:underline">View all →</a>
        </div>
        @if($recentLogs->isEmpty())
            <div class="p-8 text-center text-gray-400 text-sm">No audit logs yet.</div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d M, H:i') }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-700">{{ $log->user_email ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-xs font-medium text-gray-900">{{ $log->event }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-500">
                                {{ $log->auditable_type ? class_basename($log->auditable_type) : '—' }}
                                @if($log->auditable_id) #{{ $log->auditable_id }} @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Danger Zone --}}
    <div class="bg-white rounded-xl border border-red-200 p-5">
        <h2 class="text-xs font-semibold text-red-500 uppercase tracking-wide mb-3">Danger Zone</h2>
        <button wire:click="$set('showDeleteModal', true)"
                class="px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
            Delete Business
        </button>
    </div>

    {{-- Suspend modal --}}
    @if($showSuspendModal)
        <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Suspend Business?</h3>
                <p class="text-sm text-gray-600 mb-5">This will disable login for all staff. The business owner will be notified.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="$set('showSuspendModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button wire:click="suspend" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Suspend</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Business?</h3>
                <p class="text-sm text-gray-600 mb-4">This action is irreversible. Type <strong>{{ $business->name }}</strong> to confirm.</p>
                <input wire:model="deleteConfirm" type="text" placeholder="Business name"
                       class="w-full mb-4 rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                @error('deleteConfirm') <p class="mb-3 text-xs text-red-600">{{ $message }}</p> @enderror
                <div class="flex gap-3 justify-end">
                    <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button wire:click="confirmDelete" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Delete Permanently</button>
                </div>
            </div>
        </div>
    @endif
</div>
