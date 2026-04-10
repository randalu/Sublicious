<?php

namespace App\Livewire\App\Reports;

use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use League\Csv\Writer;
use SplTempFileObject;

class OrderReport extends Component
{
    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = $this->dateFrom ?: now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = $this->dateTo   ?: now()->endOfMonth()->format('Y-m-d');
    }

    #[Computed]
    public function summary(): array
    {
        $orders = Order::whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo);

        return [
            'total'     => (clone $orders)->count(),
            'completed' => (clone $orders)->where('status', 'completed')->count(),
            'cancelled' => (clone $orders)->where('status', 'cancelled')->count(),
            'refunded'  => (clone $orders)->where('status', 'refunded')->count(),
            'dine_in'   => (clone $orders)->where('order_type', 'dine_in')->count(),
            'delivery'  => (clone $orders)->whereIn('order_type', ['delivery', 'online'])->count(),
            'takeaway'  => (clone $orders)->where('order_type', 'takeaway')->count(),
            'avg_items' => round((clone $orders)->withCount('items')->get()->avg('items_count') ?? 0, 1),
        ];
    }

    #[Computed]
    public function byStatus(): array
    {
        return Order::whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->selectRaw('status, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }

    #[Computed]
    public function byType(): array
    {
        return Order::whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->selectRaw('order_type, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('order_type')
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }

    #[Computed]
    public function topItems(): array
    {
        return \App\Models\OrderItem::whereHas('order', fn ($q) => $q
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->whereNotIn('status', ['cancelled', 'refunded'])
        )
        ->selectRaw('name, SUM(quantity) as total_qty, SUM(subtotal) as revenue')
        ->groupBy('name')
        ->orderByDesc('total_qty')
        ->limit(10)
        ->get()
        ->toArray();
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $orders = Order::with(['items', 'customer'])
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->orderByDesc('created_at')
            ->get();

        return response()->streamDownload(function () use ($orders) {
            $csv = Writer::createFromFileObject(new SplTempFileObject());
            $csv->insertOne(['Order #', 'Date', 'Type', 'Status', 'Customer', 'Items', 'Total', 'Payment']);
            foreach ($orders as $o) {
                $csv->insertOne([
                    $o->order_number,
                    $o->created_at->format('Y-m-d H:i'),
                    $o->order_type,
                    $o->status,
                    $o->customer?->name ?? $o->customer_name ?? '',
                    $o->items->count(),
                    $o->total,
                    $o->payment_method,
                ]);
            }
            echo $csv->toString();
        }, "orders-{$this->dateFrom}-{$this->dateTo}.csv");
    }

    public function render()
    {
        return view('livewire.app.reports.order-report')
            ->layout('layouts.app', ['heading' => 'Order Report']);
    }
}
