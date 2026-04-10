<?php

namespace App\Livewire\App\Delivery;

use App\Models\Delivery;
use App\Models\DeliveryRider;
use App\Models\Order;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DeliveryBoard extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $statusFilter = '';

    #[Url(except: '')]
    public string $search = '';

    // Assign rider modal
    public bool   $showAssignModal  = false;
    public ?int   $assigningOrderId = null;
    public ?int   $selectedRiderId  = null;

    public function updatedSearch(): void      { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    public function openAssignModal(int $orderId): void
    {
        $this->assigningOrderId = $orderId;
        $this->selectedRiderId  = null;
        $this->showAssignModal  = true;
    }

    public function assignRider(): void
    {
        $this->validate([
            'selectedRiderId' => 'required|exists:delivery_riders,id',
        ]);

        $order = Order::findOrFail($this->assigningOrderId);
        $rider = DeliveryRider::findOrFail($this->selectedRiderId);

        $fee        = (float) $order->delivery_fee;
        $commission = $rider->calculateCommission($fee);

        if ($order->delivery) {
            // Re-assign
            $order->delivery->update([
                'rider_id'        => $rider->id,
                'status'          => 'assigned',
                'commission_earned' => $commission,
                'assigned_at'     => now(),
                'picked_up_at'    => null,
                'delivered_at'    => null,
            ]);
        } else {
            Delivery::create([
                'order_id'          => $order->id,
                'rider_id'          => $rider->id,
                'delivery_address'  => $order->delivery_address,
                'status'            => 'assigned',
                'fee'               => $fee,
                'commission_earned' => $commission,
                'assigned_at'       => now(),
            ]);
        }

        $this->showAssignModal  = false;
        $this->assigningOrderId = null;
        $this->selectedRiderId  = null;
        session()->flash('success', 'Rider assigned successfully.');
    }

    public function advanceDeliveryStatus(int $deliveryId): void
    {
        $delivery = Delivery::findOrFail($deliveryId);

        $next = match ($delivery->status) {
            'assigned'  => 'picked_up',
            'picked_up' => 'delivered',
            default     => null,
        };

        if ($next) {
            $timestamps = [
                'picked_up' => ['picked_up_at' => now()],
                'delivered' => ['delivered_at' => now()],
            ];
            $delivery->update(array_merge(['status' => $next], $timestamps[$next] ?? []));

            // When delivered, advance order status too
            if ($next === 'delivered') {
                $delivery->order->update(['status' => 'delivered']);
                // Update rider stats
                $delivery->rider?->increment('total_deliveries');
                $delivery->rider?->increment('total_commission_earned', $delivery->commission_earned);
            }
        }
    }

    public function render()
    {
        $deliveryOrders = Order::with(['delivery.rider', 'customer'])
            ->whereIn('order_type', ['delivery', 'online'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('order_number', 'like', "%{$this->search}%")
                      ->orWhere('customer_name', 'like', "%{$this->search}%")
                      ->orWhere('delivery_address', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(20);

        $activeRiders = DeliveryRider::where('is_active', true)->orderBy('name')->get();

        return view('livewire.app.delivery.delivery-board', compact('deliveryOrders', 'activeRiders'))
            ->layout('layouts.app', ['heading' => 'Delivery Board']);
    }
}
