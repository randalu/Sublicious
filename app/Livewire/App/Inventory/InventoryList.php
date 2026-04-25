<?php

namespace App\Livewire\App\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $stockFilter = '';

    // Item form
    public bool   $showForm    = false;
    public ?int   $editingId   = null;
    public string $name        = '';
    public string $unit        = 'pcs';
    public string $currentStock      = '0';
    public string $lowStockThreshold = '0';
    public string $costPerUnit       = '0.00';

    // Stock adjustment form
    public bool   $showAdjustForm    = false;
    public ?int   $adjustingItemId   = null;
    public string $adjustType        = 'restock';
    public string $adjustQuantity    = '';
    public string $adjustNotes       = '';

    // Transaction history
    public bool $showHistory   = false;
    public ?int $historyItemId = null;

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStockFilter(): void { $this->resetPage(); }

    protected function itemRules(): array
    {
        return [
            'name'              => 'required|string|max:150',
            'unit'              => 'required|string|in:kg,g,L,ml,pcs',
            'currentStock'      => 'required|numeric|min:0',
            'lowStockThreshold' => 'required|numeric|min:0',
            'costPerUnit'       => 'required|numeric|min:0',
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetItemForm();
        $this->showForm  = true;
        $this->editingId = $id;

        if ($id) {
            $item = InventoryItem::findOrFail($id);
            $this->name              = $item->name;
            $this->unit              = $item->unit;
            $this->currentStock      = (string) $item->current_stock;
            $this->lowStockThreshold = (string) $item->low_stock_threshold;
            $this->costPerUnit       = (string) $item->cost_per_unit;
        }
    }

    public function closeForm(): void
    {
        $this->showForm  = false;
        $this->editingId = null;
        $this->resetItemForm();
    }

    private function resetItemForm(): void
    {
        $this->name = '';
        $this->unit = 'pcs';
        $this->currentStock = '0';
        $this->lowStockThreshold = '0';
        $this->costPerUnit = '0.00';
    }

    public function saveItem(): void
    {
        $this->validate($this->itemRules());

        $data = [
            'name'                => trim($this->name),
            'unit'                => $this->unit,
            'low_stock_threshold' => $this->lowStockThreshold,
            'cost_per_unit'       => $this->costPerUnit,
        ];

        if ($this->editingId) {
            $item = InventoryItem::findOrFail($this->editingId);
            $oldStock = (float) $item->current_stock;
            $newStock = (float) $this->currentStock;

            $item->update(array_merge($data, ['current_stock' => $newStock]));

            if ($oldStock !== $newStock) {
                InventoryTransaction::create([
                    'inventory_item_id' => $item->id,
                    'type'              => 'adjustment',
                    'quantity'          => $newStock - $oldStock,
                    'quantity_before'   => $oldStock,
                    'quantity_after'    => $newStock,
                    'notes'             => 'Manual edit adjustment',
                    'user_id'           => auth()->id(),
                ]);
            }

            session()->flash('success', 'Item updated.');
        } else {
            $item = InventoryItem::create(array_merge($data, [
                'current_stock' => $this->currentStock,
            ]));

            if ((float) $this->currentStock > 0) {
                InventoryTransaction::create([
                    'inventory_item_id' => $item->id,
                    'type'              => 'restock',
                    'quantity'          => $this->currentStock,
                    'quantity_before'   => 0,
                    'quantity_after'    => $this->currentStock,
                    'notes'             => 'Initial stock',
                    'user_id'           => auth()->id(),
                ]);
            }

            session()->flash('success', 'Item created.');
        }

        $this->closeForm();
    }

    public function openAdjustForm(int $id): void
    {
        $this->adjustingItemId = $id;
        $this->adjustType      = 'restock';
        $this->adjustQuantity  = '';
        $this->adjustNotes     = '';
        $this->showAdjustForm  = true;
    }

    public function closeAdjustForm(): void
    {
        $this->showAdjustForm  = false;
        $this->adjustingItemId = null;
    }

    public function saveAdjustment(): void
    {
        $this->validate([
            'adjustType'     => 'required|in:restock,deduction,adjustment,waste',
            'adjustQuantity' => 'required|numeric|min:0.001',
            'adjustNotes'    => 'nullable|string|max:500',
        ]);

        $item = InventoryItem::findOrFail($this->adjustingItemId);
        $before = (float) $item->current_stock;
        $qty    = (float) $this->adjustQuantity;

        $after = in_array($this->adjustType, ['deduction', 'waste'])
            ? max(0, $before - $qty)
            : $before + $qty;

        $item->update(['current_stock' => $after]);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'type'              => $this->adjustType,
            'quantity'          => in_array($this->adjustType, ['deduction', 'waste']) ? -$qty : $qty,
            'quantity_before'   => $before,
            'quantity_after'    => $after,
            'notes'             => trim($this->adjustNotes) ?: null,
            'user_id'           => auth()->id(),
        ]);

        $this->closeAdjustForm();
        session()->flash('success', ucfirst($this->adjustType) . ' recorded. Stock updated.');
    }

    public function openHistory(int $id): void
    {
        $this->historyItemId = $id;
        $this->showHistory   = true;
    }

    public function closeHistory(): void
    {
        $this->showHistory   = false;
        $this->historyItemId = null;
    }

    public function delete(int $id): void
    {
        InventoryItem::findOrFail($id)->delete();
        session()->flash('success', 'Item deleted.');
    }

    public function render()
    {
        $items = InventoryItem::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->stockFilter === 'low', fn ($q) => $q->whereColumn('current_stock', '<=', 'low_stock_threshold'))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('current_stock', '<=', 0))
            ->orderBy('name')
            ->paginate(20);

        $lowStockCount = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')->count();
        $totalItems    = InventoryItem::count();
        $totalValue    = InventoryItem::selectRaw('SUM(current_stock * cost_per_unit) as val')->value('val') ?? 0;

        $historyTransactions = [];
        $historyItem = null;
        if ($this->showHistory && $this->historyItemId) {
            $historyItem = InventoryItem::find($this->historyItemId);
            $historyTransactions = InventoryTransaction::with('user')
                ->where('inventory_item_id', $this->historyItemId)
                ->orderByDesc('created_at')
                ->take(50)
                ->get();
        }

        return view('livewire.app.inventory.inventory-list', compact(
            'items', 'lowStockCount', 'totalItems', 'totalValue',
            'historyTransactions', 'historyItem'
        ))->layout('layouts.app', ['heading' => 'Inventory']);
    }
}
