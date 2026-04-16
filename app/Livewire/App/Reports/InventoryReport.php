<?php

namespace App\Livewire\App\Reports;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use League\Csv\Writer;
use SplTempFileObject;

class InventoryReport extends Component
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
        $totalItems   = InventoryItem::count();
        $lowStock     = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')->count();
        $outOfStock   = InventoryItem::where('current_stock', '<=', 0)->count();
        $totalValue   = InventoryItem::selectRaw('SUM(current_stock * cost_per_unit) as total')->value('total') ?? 0;

        $periodTransactions = InventoryTransaction::whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo);

        $restocked = (clone $periodTransactions)->where('type', 'restock')->sum('quantity');
        $deducted  = (clone $periodTransactions)->where('type', 'deduction')->sum('quantity');
        $wasted    = (clone $periodTransactions)->where('type', 'waste')->sum('quantity');

        return compact('totalItems', 'lowStock', 'outOfStock', 'totalValue', 'restocked', 'deducted', 'wasted');
    }

    #[Computed]
    public function itemBreakdown(): array
    {
        $items = InventoryItem::orderBy('name')->get();

        return $items->map(function ($item) {
            $txs = InventoryTransaction::where('inventory_item_id', $item->id)
                ->whereDate('created_at', '>=', $this->dateFrom)
                ->whereDate('created_at', '<=', $this->dateTo);

            return [
                'name'          => $item->name,
                'unit'          => $item->unit,
                'current_stock' => (float) $item->current_stock,
                'threshold'     => (float) $item->low_stock_threshold,
                'cost_per_unit' => (float) $item->cost_per_unit,
                'value'         => (float) ($item->current_stock * $item->cost_per_unit),
                'restocked'     => (float) (clone $txs)->where('type', 'restock')->sum('quantity'),
                'deducted'      => (float) (clone $txs)->where('type', 'deduction')->sum('quantity'),
                'wasted'        => (float) (clone $txs)->where('type', 'waste')->sum('quantity'),
                'is_low'        => $item->isLowStock(),
            ];
        })->toArray();
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $data = $this->itemBreakdown;
        return response()->streamDownload(function () use ($data) {
            $csv = Writer::createFromFileObject(new SplTempFileObject());
            $csv->insertOne(['Item', 'Unit', 'Current Stock', 'Threshold', 'Cost/Unit', 'Value', 'Restocked', 'Used', 'Wasted', 'Status']);
            foreach ($data as $row) {
                $csv->insertOne([
                    $row['name'], $row['unit'], $row['current_stock'], $row['threshold'],
                    $row['cost_per_unit'], $row['value'], $row['restocked'], $row['deducted'],
                    $row['wasted'], $row['is_low'] ? 'LOW' : 'OK',
                ]);
            }
            echo $csv->toString();
        }, "inventory-report-{$this->dateFrom}-{$this->dateTo}.csv");
    }

    public function render()
    {
        return view('livewire.app.reports.inventory-report')
            ->layout('layouts.app', ['heading' => 'Inventory Report']);
    }
}
