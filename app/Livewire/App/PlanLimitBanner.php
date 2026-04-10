<?php

namespace App\Livewire\App;

use Livewire\Component;

class PlanLimitBanner extends Component
{
    public function render()
    {
        $user = auth()->user();
        if (! $user || $user->isSuperAdmin() || ! $user->business) {
            return view('livewire.app.plan-limit-banner', ['show' => false]);
        }

        $business = $user->business;
        $percent = $business->orderUsagePercent();
        $remaining = $business->remainingOrdersThisMonth();

        $show = $percent >= 80;

        return view('livewire.app.plan-limit-banner', [
            'show' => $show,
            'percent' => $percent,
            'remaining' => $remaining,
            'isWarning' => $percent >= 80 && $percent < 95,
            'isCritical' => $percent >= 95,
        ]);
    }
}
