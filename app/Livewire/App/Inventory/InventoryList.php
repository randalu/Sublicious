<?php

namespace App\Livewire\App\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;
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

    #[Url(except: '')]
    public string $unitFilter = '';

    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $unit = 'pcs';
    public string $currentStock = '0';
    public string $lowStockThreshold = '0';
    public string $costPerUnit = '0.00';

    public bool $showAdjustModal = false;
    public ?int $adjustingItemId = null;
    public string $adjustingItemName = '';
    public string $adjustType = 'restock';
    public string $adjustQuantity = '';
    public string $adjustNotes = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStockFilter(): void { $this->resetPage(); }
    public function updatedUnitFilter(): void { $this->resetPage(); }

    protected function formRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'unit' => 'required|in:pcs,kg,g,L,ml',
            'currentStock' => 'required|numeric|min:0',
            'lowStockThreshold' => 'required|numeric|min:0',
            'costPerUnit' => 'required|numeric|min:0',
        ];
    }

    protected function adjustRules(): array
    {
        return [
            'adjustType' => 'required|in:restock,deduction,adjustment,waste',
            'adjustQuantity' => 'required|numeric|min:0.001',
            'adjustNotes' => 'nullable|string|max:500',
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingId = $id;
        if ($id) {
            $item = InventoryItem::findOrFail($id);
            $this->name = $item->name;
            $this->unit = $item->unit;
            $this->currentStock = (string) $item->current_stock;
            $this->lowStockThreshold = (string) $item->low_stock_threshold;
            $this->costPerUnit = (string) $item->cost_per_unit;
        }
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->unit = 'pcs';
        $this->currentStock = '0';
        $this->lowStockThreshold = '0';
        $this->costPerUnit = '0.00';
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate($this->formRules());

        $data = [
            'name' => trim($this->name),
            'unit' => $this->unit,
            'low_stock_threshold' => $this->lowStockThreshold,
            'cost_per_unit' => $this->costPerUnit,
        ];

        DB::transaction(function () use ($data) {
            if ($this->editingId) {
                $item = InventoryItem::findOrFail($this->editingId);
                $item->update($data);
            } else {
                $data['current_stock'] = $this->currentStock;
                $item = InventoryItem::create($data);

                if ((float) $this->currentStock > 0) {
                    InventoryTransaction::create([
                        'business_id' => $item->business_id,
                        'inventory_item_id' => $item->id,
                        'type' => 'restock',
                        'quantity' => $this->currentStock,
                        'quantity_before' => 0,
                        'quantity_after' => $this->currentStock,
                        'notes' => 'Initial stock',
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        });

        $this->closeForm();
        session()->flash('success', $this->editingId ? 'Item updated.' : 'Item created.');
    }

    public function openAdjust(int $id): void
    {
        $item = InventoryItem::findOrFail($id);
        $this->adjustingItemId = $item->id;
        $this->adjustingItemName = $item->name;
        $this->adjustType = 'restock';
        $this->adjustQuantity = '';
        $this->adjustNotes = '';
        $this->showAdjustModal = true;
        $this->resetValidation();
    }

    public function closeAdjust(): void
    {
        $this->showAdjustModal = false;
        $this->adjustingItemId = null;
    }

    public function saveAdjust(): void
    {
        $this->validate($this->adjustRules());

        $item = InventoryItem::findOrFail($this->adjustingItemId);
        $before = (float) $item->current_stock;
        $qty = (float) $this->adjustQuantity;

        $after = match ($this->adjustType) {
            'restock' => $before + $qty,
            'deduction', 'waste' => max(0, $before - $qty),
            'adjustment' => $qty,
        };

        DB::transaction(function () use ($item, $before, $qty, $after) {
            $item->update(['current_stock' => $after]);

            InventoryTransaction::create([
                'business_id' => $item->business_id,
                'inventory_item_id' => $item->id,
                'type' => $this->adjustType,
                'quantity' => $this->adjustType === 'adjustment' ? abs($after - $before) : $qty,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'notes' => trim($this->adjustNotes) ?: null,
                'user_id' => auth()->id(),
            ]);
        });

        $this->closeAdjust();
        session()->flash('success', 'Stock updated.');
    }

    public function delete(int $id): void
    {
        InventoryItem::findOrFail($id)->delete();
        session()->flash('success', 'Item deleted.');
    }

    public function render()
    {
        $query = InventoryItem::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->unitFilter, fn ($q) => $q->where('unit', $this->unitFilter))
            ->when($this->stockFilter === 'low', fn ($q) => $q->whereColumn('current_stock', '<=', 'low_stock_threshold')->where('current_stock', '>', 0))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('current_stock', '<=', 0))
            ->orderBy('name');

        $items = $query->paginate(20);

        $lowStockCount = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')->where('current_stock', '>', 0)->count();
        $outOfStockCount = InventoryItem::where('current_stock', '<=', 0)->count();
        $totalValue = InventoryItem::selectRaw('SUM(current_stock * cost_per_unit) as total')->value('total') ?? 0;

        return view('livewire.app.inventory.inventory-list', compact('items', 'lowStockCount', 'outOfStockCount', 'totalValue'))
            ->layout('layouts.app', ['heading' => 'Inventory']);
    }
}
