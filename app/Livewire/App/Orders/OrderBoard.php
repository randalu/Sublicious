<?php

namespace App\Livewire\App\Orders;

use App\Models\Order;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class OrderBoard extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $statusFilter = '';

    #[Url(except: '')]
    public string $typeFilter = '';

    #[Url(except: '')]
    public string $dateFilter = '';

    public function updatedSearch(): void      { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }
    public function updatedTypeFilter(): void   { $this->resetPage(); }

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

    public function cancel(int $id, string $reason = ''): void
    {
        Order::findOrFail($id)->update([
            'status'        => 'cancelled',
            'cancel_reason' => $reason ?: 'Cancelled by staff',
        ]);
    }

    public function render()
    {
        $orders = Order::with(['customer', 'table', 'items'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('order_number', 'like', "%{$this->search}%")
                      ->orWhere('customer_name', 'like', "%{$this->search}%")
                      ->orWhere('customer_phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('order_type', $this->typeFilter))
            ->when($this->dateFilter, fn ($q) => $q->whereDate('created_at', $this->dateFilter))
            ->orderByDesc('created_at')
            ->paginate(30);

        $statusCounts = Order::selectRaw('status, count(*) as count')
            ->whereDate('created_at', today())
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('livewire.app.orders.order-board', compact('orders', 'statusCounts'))
            ->layout('layouts.app', ['heading' => 'Orders']);
    }
}
