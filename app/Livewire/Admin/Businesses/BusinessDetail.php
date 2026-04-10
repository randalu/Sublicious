<?php

namespace App\Livewire\Admin\Businesses;

use App\Models\Business;
use App\Models\Plan;
use Livewire\Component;

class BusinessDetail extends Component
{
    public Business $business;

    public bool $showSuspendModal = false;
    public bool $showDeleteModal  = false;
    public string $deleteConfirm  = '';

    public function mount(Business $business): void
    {
        $this->business = $business->load('plan', 'owner');
    }

    public function activate(): void
    {
        $this->business->update(['is_active' => true, 'subscription_status' => 'active']);
        session()->flash('success', 'Business activated.');
    }

    public function suspend(): void
    {
        $this->business->update(['is_active' => false, 'subscription_status' => 'suspended']);
        $this->showSuspendModal = false;
        session()->flash('success', 'Business suspended.');
    }

    public function verify(): void
    {
        $this->business->update(['is_verified' => true]);
        session()->flash('success', 'Business verified.');
    }

    public function confirmDelete(): void
    {
        if ($this->deleteConfirm !== $this->business->name) {
            $this->addError('deleteConfirm', 'Business name does not match.');
            return;
        }
        $this->business->delete();
        session()->flash('success', 'Business deleted.');
        $this->redirect(route('admin.businesses'), navigate: true);
    }

    public function changePlan(int $planId): void
    {
        $plan = Plan::findOrFail($planId);
        $this->business->update(['plan_id' => $plan->id]);
        $this->business->refresh()->load('plan');
        session()->flash('success', "Plan changed to {$plan->name}.");
    }

    public function render()
    {
        $plans   = Plan::where('is_active', true)->orderBy('price_monthly')->get();
        $stats   = [
            'total_orders'    => $this->business->orders()->count(),
            'total_revenue'   => $this->business->orders()->where('payment_status', 'paid')->sum('total'),
            'total_employees' => $this->business->employees()->count(),
            'total_riders'    => $this->business->riders()->count(),
            'menu_items'      => $this->business->menuItems()->count(),
            'customers'       => $this->business->customers()->count(),
        ];
        $recentLogs = $this->business->auditLogs()
            ->with('user')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return view('livewire.admin.businesses.business-detail', compact('plans', 'stats', 'recentLogs'))
            ->layout('layouts.admin', ['heading' => $this->business->name]);
    }
}
