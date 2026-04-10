<?php

namespace App\Livewire\App\Reports;

use App\Models\Delivery;
use App\Models\DeliveryRider;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use League\Csv\Writer;
use SplTempFileObject;

class DeliveryReport extends Component
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
        $deliveries = Delivery::whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo);

        return [
            'total'      => (clone $deliveries)->count(),
            'delivered'  => (clone $deliveries)->where('status', 'delivered')->count(),
            'failed'     => (clone $deliveries)->where('status', 'failed')->count(),
            'in_transit' => (clone $deliveries)->whereIn('status', ['assigned', 'picked_up'])->count(),
            'total_fees' => (clone $deliveries)->sum('fee'),
            'commissions'=> (clone $deliveries)->sum('commission_earned'),
            'avg_time_min' => round((clone $deliveries)
                ->where('status', 'delivered')
                ->whereNotNull('assigned_at')
                ->whereNotNull('delivered_at')
                ->get()
                ->map(fn ($d) => $d->assigned_at->diffInMinutes($d->delivered_at))
                ->avg() ?? 0, 1),
        ];
    }

    #[Computed]
    public function riderPerformance(): array
    {
        return DeliveryRider::withCount(['deliveries as period_deliveries' => fn ($q) => $q
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
        ])
        ->withSum(['deliveries as period_commission' => fn ($q) => $q
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
        ], 'commission_earned')
        ->orderByDesc('period_deliveries')
        ->get()
        ->toArray();
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $riders = $this->riderPerformance;
        return response()->streamDownload(function () use ($riders) {
            $csv = Writer::createFromFileObject(new SplTempFileObject());
            $csv->insertOne(['Rider', 'Deliveries', 'Commission Earned']);
            foreach ($riders as $r) {
                $csv->insertOne([$r['name'], $r['period_deliveries'], $r['period_commission']]);
            }
            echo $csv->toString();
        }, "delivery-report-{$this->dateFrom}-{$this->dateTo}.csv");
    }

    public function render()
    {
        return view('livewire.app.reports.delivery-report')
            ->layout('layouts.app', ['heading' => 'Delivery Report']);
    }
}
