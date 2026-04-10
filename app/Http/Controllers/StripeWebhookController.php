<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        // TODO: Implement Stripe webhook handling in Phase 10
        return response('OK', 200);
    }
}
