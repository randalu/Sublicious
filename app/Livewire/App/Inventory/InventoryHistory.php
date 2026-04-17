<?php

namespace App\Livewire\App\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryHistory extends Component
{
    use WithPagination;

    public InventoryItem $item;

    #[Url(except: '')]
    public string $typeFilter = '';

    public function mount(InventoryItem $inventoryItem): void
    {
        $this->item = $inventoryItem;
    }

    public function updatedTypeFilter(): void { $this->resetPage(); }

    public function render()
    {
        $transactions = InventoryTransaction::where('inventory_item_id', $this->item->id)
            ->with('user')
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('livewire.app.inventory.inventory-history', compact('transactions'))
            ->layout('layouts.app', ['heading' => $this->item->name . ' — History']);
    }
}
