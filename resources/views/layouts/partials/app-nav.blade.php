@php
    $nav = [
        ['route' => 'app.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'app.orders', 'label' => 'Orders', 'icon' => 'clipboard-document-list'],
        ['route' => 'app.tables', 'label' => 'Tables', 'icon' => 'squares-2x2', 'role' => ['admin','manager','cashier']],
        ['route' => 'app.orders.live', 'label' => 'Kitchen Display', 'icon' => 'fire'],
        ['route' => 'app.menu', 'label' => 'Menu', 'icon' => 'book-open', 'role' => ['admin','manager']],
        ['route' => 'app.delivery', 'label' => 'Delivery', 'icon' => 'truck', 'feature' => 'delivery'],
        ['route' => 'app.billing', 'label' => 'Billing', 'icon' => 'document-text', 'role' => ['admin','manager','cashier']],
        ['route' => 'app.customers', 'label' => 'Customers', 'icon' => 'users', 'role' => ['admin','manager']],
        ['route' => 'app.inventory', 'label' => 'Inventory', 'icon' => 'cube', 'role' => ['admin','manager'], 'feature' => 'inventory'],
        ['route' => 'app.employees', 'label' => 'Employees', 'icon' => 'identification', 'feature' => 'hr_module'],
        ['route' => 'app.expenses', 'label' => 'Expenses', 'icon' => 'banknotes', 'feature' => 'hr_module'],
        ['route' => 'app.reports.financial', 'label' => 'Reports', 'icon' => 'chart-bar', 'role' => ['admin','manager']],
        ['route' => 'app.settings.business', 'label' => 'Settings', 'icon' => 'cog-6-tooth', 'role' => ['admin']],
    ];
    $user = auth()->user();
    $business = $user->business;
@endphp

@foreach($nav as $item)
    @php
        $canSee = true;
        if (isset($item['role']) && ! in_array($user->role, array_merge(['super_admin'], $item['role']))) {
            $canSee = false;
        }
        if (isset($item['feature']) && $business && ! $business->hasFeature($item['feature'])) {
            $canSee = false;
        }
        $isActive = request()->routeIs($item['route'] . '*');
    @endphp

    @if($canSee)
        <a href="{{ route($item['route']) }}"
           class="group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors
                  {{ $isActive ? 'bg-primary-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
            @include('layouts.partials.icon', ['name' => $item['icon'], 'class' => 'h-5 w-5 shrink-0'])
            {{ $item['label'] }}
        </a>
    @endif
@endforeach
