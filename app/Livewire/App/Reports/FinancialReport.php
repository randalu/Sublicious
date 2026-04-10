<?php

namespace App\Livewire\App\Reports;

use App\Models\Expense;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use League\Csv\Writer;
use SplTempFileObject;

class FinancialReport extends Component
{
    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    public function mount(): void
    {
        if (! $this->dateFrom) {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        }
        if (! $this->dateTo) {
            $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        }
    }

    #[Computed]
    public function stats(): array
    {
        $paidOrders = Order::where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo);

        $grossRevenue   = (clone $paidOrders)->sum('subtotal');
        $serviceCharges = (clone $paidOrders)->sum('service_charge');
        $deliveryFees   = (clone $paidOrders)->sum('delivery_fee');
        $discounts      = (clone $paidOrders)->sum('discount_amount');
        $netRevenue     = (clone $paidOrders)->sum('total');
        $refunds        = Order::where('payment_status', 'refunded')
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->sum('refund_amount');

        $totalOrders    = (clone $paidOrders)->count();
        $avgOrderValue  = $totalOrders > 0 ? round($netRevenue / $totalOrders, 2) : 0;

        $expenses = Expense::whereDate('date', '>=', $this->dateFrom)
            ->whereDate('date', '<=', $this->dateTo)
            ->sum('amount');

        $profit = $netRevenue - $expenses - $refunds;

        return compact(
            'grossRevenue', 'serviceCharges', 'deliveryFees', 'discounts',
            'netRevenue', 'refunds', 'totalOrders', 'avgOrderValue', 'expenses', 'profit'
        );
    }

    #[Computed]
    public function dailyRevenue(): array
    {
        return Order::where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    #[Computed]
    public function revenueByType(): array
    {
        return Order::where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->selectRaw('order_type, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('order_type')
            ->get()
            ->toArray();
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows = $this->dailyRevenue;

        return response()->streamDownload(function () use ($rows) {
            $csv = Writer::createFromFileObject(new SplTempFileObject());
            $csv->insertOne(['Date', 'Orders', 'Revenue']);
            foreach ($rows as $row) {
                $csv->insertOne([$row['date'], $row['orders'], $row['revenue']]);
            }
            echo $csv->toString();
        }, "financial-report-{$this->dateFrom}-{$this->dateTo}.csv");
    }

    public function render()
    {
        return view('livewire.app.reports.financial-report')
            ->layout('layouts.app', ['heading' => 'Financial Report']);
    }
}
