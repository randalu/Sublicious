<?php

namespace App\Livewire\App\Settings;

use App\Models\Plan;
use App\Models\SubscriptionInvoice;
use Livewire\Component;

class Subscription extends Component
{
    public string $billingCycle = 'monthly';

    public function checkout(int $planId): void
    {
        $business = auth()->user()->business;
        $plan = Plan::findOrFail($planId);

        if ($plan->isFree()) {
            $this->cancelStripeSubscription($business);
            $business->plan_id = $plan->id;
            $business->subscription_status = 'active';
            $business->save();
            session()->flash('success', 'Switched to the Free plan.');
            return;
        }

        $priceId = $this->billingCycle === 'yearly'
            ? $plan->stripe_price_id_yearly
            : $plan->stripe_price_id_monthly;

        if (! $priceId || ! config('cashier.secret')) {
            session()->flash('error', 'Stripe is not configured. Please contact support.');
            return;
        }

        if (! $business->stripe_id) {
            $business->createAsStripeCustomer([
                'name'     => $business->name,
                'email'    => $business->email,
                'metadata' => ['business_id' => $business->id],
            ]);
        }

        $checkoutSession = $business->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('app.settings.subscription') . '?checkout=success',
                'cancel_url'  => route('app.settings.subscription') . '?checkout=cancelled',
                'metadata'    => [
                    'plan_slug'     => $plan->slug,
                    'billing_cycle' => $this->billingCycle,
                    'business_id'   => $business->id,
                ],
            ]);

        $this->redirect($checkoutSession->url);
    }

    public function portal(): void
    {
        $business = auth()->user()->business;

        if (! $business->stripe_id || ! config('cashier.secret')) {
            session()->flash('error', 'No active Stripe subscription found.');
            return;
        }

        $url = $business->billingPortalUrl(route('app.settings.subscription'));
        $this->redirect($url);
    }

    private function cancelStripeSubscription($business): void
    {
        if (! $business->stripe_id || ! config('cashier.secret')) {
            return;
        }
        try {
            $sub = $business->subscription('default');
            if ($sub) {
                $sub->cancelNow();
            }
        } catch (\Throwable) {
            // Ignore errors during free plan switch
        }
    }

    public function render()
    {
        $business    = auth()->user()->business->load('plan');
        $currentPlan = $business->plan;
        $allPlans    = Plan::where('is_active', true)->orderBy('sort_order')->orderBy('price_monthly')->get();
        $invoices    = SubscriptionInvoice::where('business_id', $business->id)
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $ordersThisMonth = $business->currentMonthOrderCount();
        $orderLimit      = $currentPlan?->max_orders_per_month ?? 0;
        $usagePercent    = $business->orderUsagePercent();

        return view('livewire.app.settings.subscription', compact(
            'business', 'currentPlan', 'allPlans', 'invoices',
            'ordersThisMonth', 'orderLimit', 'usagePercent'
        ))->layout('layouts.app', ['heading' => 'Subscription & Billing']);
    }
}
