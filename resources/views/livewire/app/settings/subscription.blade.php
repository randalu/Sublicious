<div class="space-y-6">
    @if(request('checkout') === 'success')
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Subscription activated successfully! Welcome to your new plan.
        </div>
    @endif
    @if(request('checkout') === 'cancelled')
        <div class="rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-700">
            Checkout was cancelled. You were not charged.
        </div>
    @endif
    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Current Plan + Usage --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Current Plan</h2>
            @if($currentPlan)
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $currentPlan->name }}</h3>
                        @if($currentPlan->description)
                            <p class="text-sm text-gray-500 mt-1">{{ $currentPlan->description }}</p>
                        @endif
                        <div class="mt-3 flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-primary-600">{{ $currentPlan->formattedPrice('monthly') }}</span>
                            @if($currentPlan->price_monthly > 0)
                                <span class="text-sm text-gray-400">/ month</span>
                            @endif
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                 {{ in_array($business->subscription_status, ['active', null]) ? 'bg-green-100 text-green-700' : ($business->subscription_status === 'past_due' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ ucfirst($business->subscription_status ?? 'active') }}
                    </span>
                </div>
                @if($business->stripe_id)
                    <button wire:click="portal" wire:loading.attr="disabled"
                            class="mt-4 w-full py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Manage Billing &amp; Invoices →
                    </button>
                @endif
            @else
                <p class="text-sm text-gray-500">No plan selected.</p>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">This Month's Usage</h2>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="font-medium text-gray-700">Orders</span>
                        <span class="text-gray-500">{{ $ordersThisMonth }} / {{ $orderLimit > 0 ? number_format($orderLimit) : '∞' }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all
                                    {{ $usagePercent >= 90 ? 'bg-red-500' : ($usagePercent >= 70 ? 'bg-yellow-500' : 'bg-primary-500') }}"
                             style="width: {{ min($usagePercent, 100) }}%"></div>
                    </div>
                    @if($usagePercent >= 90)
                        <p class="mt-1 text-xs text-red-600 font-medium">You are approaching your plan limit. Consider upgrading.</p>
                    @endif
                </div>

                @if($currentPlan)
                    <div class="grid grid-cols-3 gap-3 pt-1">
                        <div class="text-center p-2.5 bg-gray-50 rounded-lg">
                            <p class="text-base font-bold text-gray-900">{{ $currentPlan->max_staff }}</p>
                            <p class="text-xs text-gray-500">Staff Slots</p>
                        </div>
                        <div class="text-center p-2.5 bg-gray-50 rounded-lg">
                            <p class="text-base font-bold text-gray-900">{{ $currentPlan->max_menu_items }}</p>
                            <p class="text-xs text-gray-500">Menu Items</p>
                        </div>
                        <div class="text-center p-2.5 bg-gray-50 rounded-lg">
                            <p class="text-base font-bold text-gray-900">{{ $currentPlan->max_delivery_zones ?? '—' }}</p>
                            <p class="text-xs text-gray-500">Zones</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Billing Cycle Toggle --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Available Plans</h2>
            <div class="inline-flex items-center rounded-lg border border-gray-200 p-0.5 bg-gray-50">
                <button wire:click="$set('billingCycle', 'monthly')"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors
                               {{ $billingCycle === 'monthly' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Monthly
                </button>
                <button wire:click="$set('billingCycle', 'yearly')"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors
                               {{ $billingCycle === 'yearly' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Yearly <span class="text-xs text-green-600 font-semibold ml-1">Save 20%</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($allPlans as $plan)
                @php
                    $isCurrent = $currentPlan?->id === $plan->id;
                    $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
                @endphp
                <div class="relative border rounded-xl p-5 flex flex-col
                            {{ $isCurrent ? 'border-primary-500 ring-2 ring-primary-100' : 'border-gray-200' }}">
                    @if($isCurrent)
                        <span class="absolute -top-2.5 left-4 bg-primary-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">Current</span>
                    @endif

                    <h3 class="font-bold text-gray-900 text-lg">{{ $plan->name }}</h3>

                    <div class="mt-2 flex items-baseline gap-1">
                        <span class="text-2xl font-extrabold text-gray-900">
                            {{ $plan->isFree() ? 'Free' : ('$' . number_format($price / 100, 2)) }}
                        </span>
                        @if(! $plan->isFree())
                            <span class="text-sm text-gray-400">/ {{ $billingCycle === 'yearly' ? 'year' : 'month' }}</span>
                        @endif
                    </div>

                    <ul class="mt-4 space-y-2 text-sm text-gray-600 flex-1">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ number_format($plan->max_orders_per_month) }} orders/month
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $plan->max_staff }} staff members
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $plan->max_menu_items }} menu items
                        </li>
                        @if($plan->features)
                            @foreach($plan->features as $feature => $enabled)
                                <li class="flex items-center gap-2 {{ $enabled ? '' : 'opacity-40 line-through' }}">
                                    @if($enabled)
                                        <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg class="h-4 w-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @endif
                                    {{ ucwords(str_replace('_', ' ', $feature)) }}
                                </li>
                            @endforeach
                        @endif
                    </ul>

                    @if(! $isCurrent)
                        <button wire:click="checkout({{ $plan->id }})" wire:loading.attr="disabled"
                                class="mt-5 w-full py-2.5 text-sm font-semibold rounded-lg transition-colors
                                       {{ $plan->isFree() ? 'border border-gray-300 text-gray-700 hover:bg-gray-50' : 'bg-primary-600 text-white hover:bg-primary-700' }}">
                            <span wire:loading.remove wire:target="checkout({{ $plan->id }})">
                                {{ $plan->isFree() ? 'Downgrade to Free' : 'Upgrade to ' . $plan->name }}
                            </span>
                            <span wire:loading wire:target="checkout({{ $plan->id }})">Processing…</span>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Invoice History --}}
    @if($invoices->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">Invoice History</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Invoice</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $invoice->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $invoice->formattedAmount() }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                             {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                @if($invoice->invoice_pdf_url)
                                    <a href="{{ $invoice->invoice_pdf_url }}" target="_blank"
                                       class="text-primary-600 hover:text-primary-700 font-medium">Download PDF</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
