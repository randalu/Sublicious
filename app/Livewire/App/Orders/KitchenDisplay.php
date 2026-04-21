<?php

namespace App\Livewire\App\Orders;

use App\Models\Order;
use Livewire\Attributes\On;
use Livewire\Component;

class KitchenDisplay extends Component
{
    public function advanceStatus(int $id): void
    {
        $order = Order::findOrFail($id);
        $next  = $order->nextStatus();
        if ($next) {
            $order->update(['status' => $next]);
            if ($next === 'preparing') {
                $order->deductInventory();
            }
        }
    }

    public function render()
    {
        $statuses = ['pending', 'accepted', 'preparing', 'ready'];

        $orders = Order::with(['items.addons', 'table'])
            ->whereIn('status', $statuses)
            ->orderBy('created_at')
            ->get()
            ->groupBy('status');

        return view('livewire.app.orders.kitchen-display', compact('orders', 'statuses'))
            ->layout('layouts.app', ['heading' => 'Kitchen Display']);
    }
}
