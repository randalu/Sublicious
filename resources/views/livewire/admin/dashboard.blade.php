<div>
    {{-- Stats grid --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 mb-8">
        @php
            $cards = [
                ['label' => 'Total Businesses', 'value' => $stats['total_businesses'], 'color' => 'blue'],
                ['label' => 'Active', 'value' => $stats['active_businesses'], 'color' => 'green'],
                ['label' => 'Suspended', 'value' => $stats['suspended_businesses'], 'color' => 'red'],
                ['label' => 'Total Users', 'value' => $stats['total_users'], 'color' => 'purple'],
                ['label' => 'Orders Today', 'value' => $stats['total_orders_today'], 'color' => 'orange'],
                ['label' => 'Active Plans', 'value' => $stats['total_plans'], 'color' => 'indigo'],
            ];
        @endphp

        @foreach($cards as $card)
            <div class="bg-white rounded-xl p-4 shadow-sm ring-1 ring-gray-900/5">
                <p class="text-xs font-medium text-gray-500">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($card['value']) }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Recent Businesses --}}
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Recent Businesses</h3>
                <a href="{{ route('admin.businesses') }}" class="text-xs text-primary-600 hover:text-primary-700">View all →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentBusinesses as $biz)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $biz->name }}</p>
                            <p class="text-xs text-gray-500">{{ $biz->email }} · {{ $biz->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                         {{ $biz->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $biz->is_active ? 'Active' : 'Suspended' }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $biz->plan?->name ?? 'No Plan' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-400">No businesses yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Plan Distribution --}}
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Plan Distribution</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($planDistribution as $plan)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $plan->name }}</p>
                            <p class="text-xs text-gray-500">{{ $plan->formattedPrice() }}/month</p>
                        </div>
                        <span class="text-lg font-bold text-gray-900">{{ $plan->businesses_count }}</span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-gray-400">No plans configured.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
