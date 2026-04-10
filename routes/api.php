<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Push notification subscription
Route::post('/push/subscribe', function (Request $request) {
    $request->validate([
        'endpoint' => 'required|string',
        'keys.p256dh' => 'required|string',
        'keys.auth' => 'required|string',
    ]);

    \App\Models\PushSubscription::updateOrCreate(
        ['endpoint' => $request->endpoint],
        [
            'business_id' => auth()->user()?->business_id,
            'user_id' => auth()->id(),
            'p256dh' => $request->input('keys.p256dh'),
            'auth' => $request->input('keys.auth'),
        ]
    );

    return response()->json(['status' => 'subscribed']);
})->middleware('auth:sanctum');

// Online order submission (public)
Route::post('/orders/online', [\App\Http\Controllers\OnlineOrderController::class, 'store'])
    ->name('api.orders.online');
