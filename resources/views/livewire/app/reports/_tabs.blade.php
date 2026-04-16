@php
    $tabs = [
        ['route' => 'app.reports.financial', 'label' => 'Financial'],
        ['route' => 'app.reports.orders', 'label' => 'Orders'],
        ['route' => 'app.reports.delivery', 'label' => 'Delivery', 'feature' => 'delivery'],
        ['route' => 'app.reports.employees', 'label' => 'Employees', 'feature' => 'hr_module'],
        ['route' => 'app.reports.inventory', 'label' => 'Inventory', 'feature' => 'inventory'],
    ];
    $business = auth()->user()->business;
@endphp
<div class="flex gap-1 bg-gray-100 rounded-lg p-1 mb-6 overflow-x-auto">
    @foreach($tabs as $tab)
        @if(!isset($tab['feature']) || $business?->hasFeature($tab['feature']))
            <a href="{{ route($tab['route']) }}" wire:navigate
               class="px-4 py-2 text-sm font-medium rounded-md whitespace-nowrap transition-colors
                      {{ request()->routeIs($tab['route']) ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                {{ $tab['label'] }}
            </a>
        @endif
    @endforeach
</div>
