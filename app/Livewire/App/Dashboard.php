<?php

namespace App\Livewire\App;

use App\Models\Delivery;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\Order;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $business = auth()->user()->business;

        $today = today();

        $stats = [
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'revenue_today' => Order::whereDate('created_at', $today)
                ->where('payment_status', 'paid')
                ->sum('total'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'active_deliveries' => Delivery::whereIn('status', ['assigned', 'picked_up'])->count(),
            'orders_this_month' => $business->currentMonthOrderCount(),
            'monthly_limit' => $business->plan?->max_orders_per_month ?? 0,
        ];

        $recentOrders = Order::with(['table', 'delivery.rider'])
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $pendingOrders = Order::with(['table', 'delivery.rider'])
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->take(5)
            ->get();

        $lowStockItems = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->orderBy('current_stock')
            ->take(5)
            ->get();

        return view('livewire.app.dashboard', compact('stats', 'recentOrders', 'pendingOrders', 'lowStockItems'))
            ->layout('layouts.app', ['heading' => 'Dashboard']);
    }
}
