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

    public bool   $showForm      = false;
    public ?int   $editingId     = null;
    public string $name          = '';
    public string $unit          = 'pcs';
    public string $currentStock  = '0';
    public string $lowStockThreshold = '0';
    public string $costPerUnit   = '0.00';

    public bool   $showAdjustment  = false;
    public ?int   $adjustingItemId = null;
    public string $adjustmentType  = 'restock';
    public string $adjustmentQty   = '';
    public string $adjustmentNotes = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStockFilter(): void { $this->resetPage(); }

    protected function formRules(): array
    {
        return [
            'name'              => 'required|string|max:200',
            'unit'              => 'required|string|max:20',
            'currentStock'      => 'required|numeric|min:0',
            'lowStockThreshold' => 'required|numeric|min:0',
            'costPerUnit'       => 'required|numeric|min:0',
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
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
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = $this->adjustmentNotes = '';
        $this->unit = 'pcs';
        $this->currentStock = '0';
        $this->lowStockThreshold = '0';
        $this->costPerUnit = '0.00';
    }

    public function save(): void
    {
        $this->validate($this->formRules());

        $data = [
            'name'                => trim($this->name),
            'unit'                => trim($this->unit),
            'current_stock'       => $this->currentStock,
            'low_stock_threshold' => $this->lowStockThreshold,
            'cost_per_unit'       => $this->costPerUnit,
        ];

        if ($this->editingId) {
            InventoryItem::findOrFail($this->editingId)->update($data);
        } else {
            InventoryItem::create($data);
        }

        $this->closeForm();
        session()->flash('success', 'Inventory item saved.');
    }

    public function delete(int $id): void
    {
        InventoryItem::findOrFail($id)->delete();
        session()->flash('success', 'Inventory item deleted.');
    }

    public function openAdjustment(int $id): void
    {
        $this->adjustingItemId = $id;
        $this->adjustmentType  = 'restock';
        $this->adjustmentQty   = '';
        $this->adjustmentNotes = '';
        $this->showAdjustment  = true;
    }

    public function closeAdjustment(): void
    {
        $this->showAdjustment  = false;
        $this->adjustingItemId = null;
    }

    public function saveAdjustment(): void
    {
        $this->validate([
            'adjustmentType'  => 'required|in:restock,deduction,adjustment,waste',
            'adjustmentQty'   => 'required|numeric|min:0.001',
            'adjustmentNotes' => 'nullable|string|max:500',
        ]);

        $item = InventoryItem::findOrFail($this->adjustingItemId);
        $before = (float) $item->current_stock;
        $qty = (float) $this->adjustmentQty;

        $after = match ($this->adjustmentType) {
            'restock'    => $before + $qty,
            'deduction', 'waste' => max(0, $before - $qty),
            'adjustment' => $qty,
        };

        $item->update(['current_stock' => $after]);

        InventoryTransaction::create([
            'business_id'       => $item->business_id,
            'inventory_item_id' => $item->id,
            'type'              => $this->adjustmentType,
            'quantity'          => $qty,
            'quantity_before'   => $before,
            'quantity_after'    => $after,
            'notes'             => trim($this->adjustmentNotes) ?: null,
            'user_id'           => auth()->id(),
        ]);

        $this->closeAdjustment();
        session()->flash('success', 'Stock updated.');
    }

    public function render()
    {
        $items = InventoryItem::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->stockFilter === 'low', fn ($q) => $q->whereColumn('current_stock', '<=', 'low_stock_threshold'))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('current_stock', '<=', 0))
            ->orderBy('name')
            ->paginate(25);

        $lowStockCount = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')
            ->where('low_stock_threshold', '>', 0)
            ->count();

        $totalItems = InventoryItem::count();
        $totalValue = InventoryItem::selectRaw('SUM(current_stock * cost_per_unit) as value')->value('value') ?? 0;

        return view('livewire.app.inventory.inventory-list', compact('items', 'lowStockCount', 'totalItems', 'totalValue'))
            ->layout('layouts.app', ['heading' => 'Inventory']);
    }
}
