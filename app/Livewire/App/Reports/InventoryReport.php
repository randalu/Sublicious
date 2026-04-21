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
        $totalItems  = InventoryItem::count();
        $lowStock    = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')->count();
        $outOfStock  = InventoryItem::where('current_stock', '<=', 0)->count();
        $totalValue  = InventoryItem::selectRaw('SUM(current_stock * cost_per_unit) as total')->value('total') ?? 0;

        $transactions = InventoryTransaction::whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo);

        $restockCount   = (clone $transactions)->where('type', 'restock')->count();
        $deductionCount = (clone $transactions)->where('type', 'deduction')->count();
        $wasteCount     = (clone $transactions)->where('type', 'waste')->count();

        return compact('totalItems', 'lowStock', 'outOfStock', 'totalValue', 'restockCount', 'deductionCount', 'wasteCount');
    }

    #[Computed]
    public function lowStockItems(): array
    {
        return InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->orderBy('current_stock')
            ->get()
            ->toArray();
    }

    #[Computed]
    public function topConsumed(): array
    {
        return InventoryTransaction::where('type', 'deduction')
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->join('inventory_items', 'inventory_transactions.inventory_item_id', '=', 'inventory_items.id')
            ->selectRaw('inventory_items.name, inventory_items.unit, SUM(inventory_transactions.quantity) as total_used')
            ->groupBy('inventory_items.name', 'inventory_items.unit')
            ->orderByDesc('total_used')
            ->limit(10)
            ->get()
            ->toArray();
    }

    #[Computed]
    public function wasteReport(): array
    {
        return InventoryTransaction::where('type', 'waste')
            ->whereDate('created_at', '>=', $this->dateFrom)
            ->whereDate('created_at', '<=', $this->dateTo)
            ->join('inventory_items', 'inventory_transactions.inventory_item_id', '=', 'inventory_items.id')
            ->selectRaw('inventory_items.name, inventory_items.unit, inventory_items.cost_per_unit, SUM(inventory_transactions.quantity) as total_wasted')
            ->groupBy('inventory_items.name', 'inventory_items.unit', 'inventory_items.cost_per_unit')
            ->orderByDesc('total_wasted')
            ->get()
            ->toArray();
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $items = InventoryItem::orderBy('name')->get();

        return response()->streamDownload(function () use ($items) {
            $csv = Writer::createFromFileObject(new SplTempFileObject());
            $csv->insertOne(['Name', 'Unit', 'Current Stock', 'Low Threshold', 'Cost/Unit', 'Total Value', 'Status']);
            foreach ($items as $item) {
                $csv->insertOne([
                    $item->name,
                    $item->unit,
                    $item->current_stock,
                    $item->low_stock_threshold,
                    $item->cost_per_unit,
                    round($item->current_stock * $item->cost_per_unit, 2),
                    $item->current_stock <= 0 ? 'Out of Stock' : ($item->isLowStock() ? 'Low' : 'OK'),
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
