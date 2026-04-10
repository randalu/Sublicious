<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('cashier.webhook.secret', env('STRIPE_WEBHOOK_SECRET'));

        if ($secret) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $secret);
            } catch (SignatureVerificationException $e) {
                return response('Webhook signature verification failed.', 403);
            }
        } else {
            $event = json_decode($payload, true);
            $event = (object) ['type' => $event['type'] ?? '', 'data' => (object) ['object' => (object) ($event['data']['object'] ?? [])]];
        }

        match ($event->type) {
            'checkout.session.completed'       => $this->handleCheckoutCompleted($event->data->object),
            'customer.subscription.updated'    => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted'    => $this->handleSubscriptionDeleted($event->data->object),
            'invoice.payment_succeeded'        => $this->handleInvoicePaid($event->data->object),
            'invoice.payment_failed'           => $this->handleInvoicePaymentFailed($event->data->object),
            default                            => null,
        };

        return response('Webhook handled.', 200);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        if (($session->mode ?? '') !== 'subscription') {
            return;
        }

        $customerId = $session->customer ?? null;
        $subscriptionId = $session->subscription ?? null;
        $metadata = (array) ($session->metadata ?? []);

        if (! $customerId) {
            return;
        }

        $business = Business::where('stripe_id', $customerId)->first();
        if (! $business) {
            return;
        }

        $planSlug = $metadata['plan_slug'] ?? null;
        $cycle = $metadata['billing_cycle'] ?? 'monthly';

        if ($planSlug) {
            $plan = Plan::where('slug', $planSlug)->first();
            if ($plan) {
                $business->plan_id = $plan->id;
                $business->subscription_status = 'active';
                $business->save();
            }
        }

        Log::info('Stripe checkout completed', ['business_id' => $business->id, 'subscription' => $subscriptionId]);
    }

    private function handleSubscriptionUpdated(object $stripeSubscription): void
    {
        $customerId = $stripeSubscription->customer ?? null;
        if (! $customerId) {
            return;
        }

        $business = Business::where('stripe_id', $customerId)->first();
        if (! $business) {
            return;
        }

        $status = $stripeSubscription->status ?? 'active';
        $business->subscription_status = match($status) {
            'active', 'trialing' => 'active',
            'past_due'           => 'past_due',
            'canceled'           => 'cancelled',
            default              => $status,
        };
        $business->save();
    }

    private function handleSubscriptionDeleted(object $stripeSubscription): void
    {
        $customerId = $stripeSubscription->customer ?? null;
        if (! $customerId) {
            return;
        }

        $business = Business::where('stripe_id', $customerId)->first();
        if (! $business) {
            return;
        }

        // Downgrade to free plan on cancellation
        $freePlan = Plan::where('is_default', true)->orWhere('price_monthly', 0)->first();
        if ($freePlan) {
            $business->plan_id = $freePlan->id;
        }
        $business->subscription_status = 'cancelled';
        $business->save();
    }

    private function handleInvoicePaid(object $invoice): void
    {
        $customerId = $invoice->customer ?? null;
        if (! $customerId) {
            return;
        }

        $business = Business::where('stripe_id', $customerId)->first();
        if (! $business) {
            return;
        }

        \App\Models\SubscriptionInvoice::updateOrCreate(
            ['stripe_invoice_id' => $invoice->id ?? null],
            [
                'business_id'      => $business->id,
                'amount'           => $invoice->amount_paid ?? 0,
                'currency'         => strtoupper($invoice->currency ?? 'USD'),
                'status'           => 'paid',
                'paid_at'          => now(),
                'invoice_pdf_url'  => $invoice->invoice_pdf ?? null,
            ]
        );
    }

    private function handleInvoicePaymentFailed(object $invoice): void
    {
        $customerId = $invoice->customer ?? null;
        if (! $customerId) {
            return;
        }

        $business = Business::where('stripe_id', $customerId)->first();
        if ($business) {
            $business->subscription_status = 'past_due';
            $business->save();
        }
    }
}
