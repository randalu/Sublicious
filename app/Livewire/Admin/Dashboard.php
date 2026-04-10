<?php

namespace App\Livewire\Admin;

use App\Models\Business;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'total_businesses' => Business::count(),
            'active_businesses' => Business::where('is_active', true)->count(),
            'suspended_businesses' => Business::where('subscription_status', 'suspended')->count(),
            'total_users' => User::whereNotNull('business_id')->count(),
            'total_orders_today' => Order::whereDate('created_at', today())->count(),
            'total_plans' => Plan::where('is_active', true)->count(),
        ];

        $recentBusinesses = Business::with('plan')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $planDistribution = Plan::withCount('businesses')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.admin.dashboard', compact('stats', 'recentBusinesses', 'planDistribution'))
            ->layout('layouts.admin', ['title' => 'Dashboard — Admin', 'heading' => 'Platform Dashboard']);
    }
}
