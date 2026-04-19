<?php

namespace App\Livewire\App\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionLog extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $itemFilter = '';

    #[Url(except: '')]
    public string $typeFilter = '';

    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    public function updatedItemFilter(): void { $this->resetPage(); }
    public function updatedTypeFilter(): void { $this->resetPage(); }
    public function updatedDateFrom(): void { $this->resetPage(); }
    public function updatedDateTo(): void { $this->resetPage(); }

    public function render()
    {
        $transactions = InventoryTransaction::with(['inventoryItem', 'user'])
            ->when($this->itemFilter, fn ($q) => $q->where('inventory_item_id', $this->itemFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderByDesc('created_at')
            ->paginate(25);

        $inventoryItems = InventoryItem::orderBy('name')->get();

        return view('livewire.app.inventory.transaction-log', compact('transactions', 'inventoryItems'))
            ->layout('layouts.app', ['heading' => 'Transaction Log']);
    }
}
