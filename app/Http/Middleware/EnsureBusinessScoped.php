<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessScoped
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if (! $user->business_id || ! $user->business) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'No business associated with this account.']);
        }

        $business = $user->business;

        if ($business->isSuspended()) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your business account has been suspended. Please contact support.',
            ]);
        }

        if (! $user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been deactivated. Contact your administrator.',
            ]);
        }

        // Share business with all views
        view()->share('business', $business);

        return $next($request);
    }
}
