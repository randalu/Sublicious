<?php

namespace App\Livewire\App\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\MenuItem;
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

    public bool   $showForm       = false;
    public ?int   $editingId      = null;
    public string $name           = '';
    public string $unit           = 'pcs';
    public string $current_stock  = '0';
    public string $low_stock_threshold = '0';
    public string $cost_per_unit  = '0.00';
    public array  $linkedMenuItems = [];

    public bool   $showAdjustment   = false;
    public ?int   $adjustingItemId  = null;
    public string $adjustmentType   = 'restock';
    public string $adjustmentQty    = '';
    public string $adjustmentNotes  = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStockFilter(): void { $this->resetPage(); }

    protected function formRules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'unit'                => 'required|string|max:20',
            'current_stock'       => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'cost_per_unit'       => 'required|numeric|min:0',
            'linkedMenuItems'     => 'array',
            'linkedMenuItems.*'   => 'integer|exists:menu_items,id',
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        $this->showForm  = true;
        $this->editingId = $id;

        if ($id) {
            $item = InventoryItem::with('menuItems')->findOrFail($id);
            $this->name                = $item->name;
            $this->unit                = $item->unit;
            $this->current_stock       = (string) $item->current_stock;
            $this->low_stock_threshold = (string) $item->low_stock_threshold;
            $this->cost_per_unit       = (string) $item->cost_per_unit;
            $this->linkedMenuItems     = $item->menuItems->pluck('id')->toArray();
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
        $this->name = '';
        $this->unit = 'pcs';
        $this->current_stock = '0';
        $this->low_stock_threshold = '0';
        $this->cost_per_unit = '0.00';
        $this->linkedMenuItems = [];
    }

    public function save(): void
    {
        $this->validate($this->formRules());

        $data = [
            'name'                => trim($this->name),
            'unit'                => trim($this->unit),
            'current_stock'       => $this->current_stock,
            'low_stock_threshold' => $this->low_stock_threshold,
            'cost_per_unit'       => $this->cost_per_unit,
        ];

        DB::transaction(function () use ($data) {
            if ($this->editingId) {
                $item = InventoryItem::findOrFail($this->editingId);
                $item->update($data);
            } else {
                $item = InventoryItem::create($data);
            }

            $syncData = [];
            foreach ($this->linkedMenuItems as $menuItemId) {
                $syncData[$menuItemId] = ['quantity_used' => 1];
            }
            $item->menuItems()->sync($syncData);
        });

        $this->closeForm();
        session()->flash('success', 'Inventory item saved.');
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

        DB::transaction(function () use ($item, $before, $after, $qty) {
            $item->update(['current_stock' => $after]);

            InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'type'              => $this->adjustmentType,
                'quantity'          => $this->adjustmentType === 'adjustment' ? $after - $before : $qty,
                'quantity_before'   => $before,
                'quantity_after'    => $after,
                'notes'             => trim($this->adjustmentNotes) ?: null,
                'user_id'           => auth()->id(),
            ]);
        });

        $this->closeAdjustment();
        session()->flash('success', 'Stock updated.');
    }

    public function delete(int $id): void
    {
        $item = InventoryItem::findOrFail($id);
        $item->menuItems()->detach();
        $item->delete();
        session()->flash('success', 'Inventory item deleted.');
    }

    public function render()
    {
        $items = InventoryItem::with('menuItems')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->stockFilter === 'low', fn ($q) => $q->whereColumn('current_stock', '<=', 'low_stock_threshold'))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('current_stock', '<=', 0))
            ->orderBy('name')
            ->paginate(20);

        $lowStockCount = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')->count();
        $totalValue    = InventoryItem::selectRaw('SUM(current_stock * cost_per_unit) as total')->value('total') ?? 0;
        $menuItems     = MenuItem::orderBy('name')->get(['id', 'name']);

        return view('livewire.app.inventory.inventory-list', compact('items', 'lowStockCount', 'totalValue', 'menuItems'))
            ->layout('layouts.app', ['heading' => 'Inventory']);
    }
}
