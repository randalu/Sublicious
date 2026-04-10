<?php

namespace App\Livewire\App\Delivery;

use App\Models\Delivery;
use App\Models\DeliveryRider;
use App\Models\RiderCommissionPayout;
use Illuminate\Support\Carbon;
use Livewire\Component;

class CommissionTracker extends Component
{
    // Month filter
    public string $month = '';

    // Payout creation modal
    public bool   $showPayoutModal = false;
    public ?int   $payoutRiderId   = null;
    public string $periodStart     = '';
    public string $periodEnd       = '';
    public string $notes           = '';

    // Payout list filter
    public bool $showAllPayouts = false;

    public function mount(): void
    {
        $this->month      = now()->format('Y-m');
        $this->periodStart = now()->startOfMonth()->toDateString();
        $this->periodEnd   = now()->endOfMonth()->toDateString();
    }

    public function updatedMonth(): void
    {
        if ($this->month) {
            $date = Carbon::createFromFormat('Y-m', $this->month);
            $this->periodStart = $date->startOfMonth()->toDateString();
            $this->periodEnd   = $date->endOfMonth()->toDateString();
        }
    }

    public function openPayoutModal(int $riderId): void
    {
        $this->payoutRiderId = $riderId;
        $this->notes         = '';
        $this->showPayoutModal = true;
    }

    public function createPayout(): void
    {
        $this->validate([
            'payoutRiderId' => 'required|exists:delivery_riders,id',
            'periodStart'   => 'required|date',
            'periodEnd'     => 'required|date|after_or_equal:periodStart',
        ]);

        $rider = DeliveryRider::findOrFail($this->payoutRiderId);

        // Calculate commission for the period
        $deliveries = Delivery::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$this->periodStart . ' 00:00:00', $this->periodEnd . ' 23:59:59'])
            ->get();

        $totalDeliveries  = $deliveries->count();
        $totalCommission  = $deliveries->sum('commission_earned');

        RiderCommissionPayout::create([
            'rider_id'         => $rider->id,
            'period_start'     => $this->periodStart,
            'period_end'       => $this->periodEnd,
            'total_deliveries' => $totalDeliveries,
            'total_commission' => $totalCommission,
            'is_paid'          => false,
            'notes'            => $this->notes ?: null,
        ]);

        $this->showPayoutModal = false;
        session()->flash('success', 'Payout record created for ' . $rider->name . '.');
    }

    public function markPaid(int $payoutId): void
    {
        RiderCommissionPayout::findOrFail($payoutId)->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);
        session()->flash('success', 'Payout marked as paid.');
    }

    public function render()
    {
        $start = $this->month
            ? Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()
            : now()->startOfMonth();
        $end = $start->copy()->endOfMonth();

        // Riders with their month stats
        $riders = DeliveryRider::withCount(['deliveries as month_deliveries' => function ($q) use ($start, $end) {
                $q->where('status', 'delivered')
                  ->whereBetween('delivered_at', [$start, $end]);
            }])
            ->withSum(['deliveries as month_commission' => function ($q) use ($start, $end) {
                $q->where('status', 'delivered')
                  ->whereBetween('delivered_at', [$start, $end]);
            }], 'commission_earned')
            ->orderByDesc('month_deliveries')
            ->get();

        // Payout records
        $payouts = RiderCommissionPayout::with('rider')
            ->when(! $this->showAllPayouts, fn ($q) => $q->where('is_paid', false))
            ->orderByDesc('period_end')
            ->limit(50)
            ->get();

        return view('livewire.app.delivery.commission-tracker', compact('riders', 'payouts'))
            ->layout('layouts.app', ['heading' => 'Commission Tracker']);
    }
}
