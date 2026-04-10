<div>
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- SMS Notifications --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="h-8 w-8 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">SMS Notifications</h2>
            </div>

            <div class="space-y-4">
                @php
                    $smsToggles = [
                        'notify_new_order_sms'           => ['label' => 'New Online Order', 'desc' => 'SMS to your business when a new online order is placed'],
                        'notify_order_accepted_sms'      => ['label' => 'Order Accepted', 'desc' => 'SMS to customer when their order is accepted'],
                        'notify_delivery_dispatched_sms' => ['label' => 'Delivery Dispatched', 'desc' => 'SMS to customer when a rider is on the way'],
                        'notify_delivery_delivered_sms'  => ['label' => 'Order Delivered', 'desc' => 'SMS to customer when delivery is complete'],
                    ];
                @endphp

                @foreach($smsToggles as $key => $item)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $item['label'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $item['desc'] }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="{{ $key }}" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary-300 rounded-full peer
                                        peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                        peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px]
                                        after:start-[2px] after:bg-white after:border-gray-300 after:border
                                        after:rounded-full after:h-5 after:w-5 after:transition-all
                                        peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Email Notifications --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Email Notifications</h2>
            </div>

            <div class="space-y-4">
                @php
                    $emailToggles = [
                        'notify_new_order_email'  => ['label' => 'New Order Email', 'desc' => 'Email notification when a new order arrives'],
                        'notify_low_stock_email'  => ['label' => 'Low Stock Alert', 'desc' => 'Email when menu item inventory drops below threshold'],
                    ];
                @endphp

                @foreach($emailToggles as $key => $item)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $item['label'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $item['desc'] }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="{{ $key }}" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary-300 rounded-full peer
                                        peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                        peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px]
                                        after:start-[2px] after:bg-white after:border-gray-300 after:border
                                        after:rounded-full after:h-5 after:w-5 after:transition-all
                                        peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="px-6 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                Save Preferences
            </button>
        </div>
    </form>
</div>
