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

    // Item form
    public bool   $showForm    = false;
    public ?int   $editingId   = null;
    public string $name        = '';
    public string $unit        = 'pcs';
    public string $currentStock      = '0';
    public string $lowStockThreshold = '0';
    public string $costPerUnit       = '0.00';

    // Transaction form
    public bool   $showTransaction = false;
    public ?int   $transactionItemId = null;
    public string $transactionItemName = '';
    public string $transactionType  = 'restock';
    public string $transactionQty   = '';
    public string $transactionNotes = '';

    // Transaction history
    public bool $showHistory   = false;
    public ?int $historyItemId = null;
    public string $historyItemName = '';

    // Menu item linking
    public bool  $showLinkMenu     = false;
    public ?int  $linkItemId       = null;
    public string $linkItemName    = '';
    public ?int  $linkMenuItemId   = null;
    public string $linkQuantityUsed = '1';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStockFilter(): void { $this->resetPage(); }

    protected function itemRules(): array
    {
        return [
            'name'              => 'required|string|max:200',
            'unit'              => 'required|in:kg,g,L,ml,pcs',
            'currentStock'      => 'required|numeric|min:0',
            'lowStockThreshold' => 'required|numeric|min:0',
            'costPerUnit'       => 'required|numeric|min:0',
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetItemForm();
        $this->showForm = true;
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
        $this->showForm = false;
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

    public function save(): void
    {
        $this->validate($this->itemRules());

        $data = [
            'name'                => trim($this->name),
            'unit'                => $this->unit,
            'low_stock_threshold' => $this->lowStockThreshold,
            'cost_per_unit'       => $this->costPerUnit,
        ];

        if ($this->editingId) {
            InventoryItem::findOrFail($this->editingId)->update($data);
        } else {
            $data['current_stock'] = $this->currentStock;
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

    // --- Stock transactions ---

    public function openTransaction(int $id, string $type = 'restock'): void
    {
        $item = InventoryItem::findOrFail($id);
        $this->transactionItemId   = $id;
        $this->transactionItemName = $item->name;
        $this->transactionType     = $type;
        $this->transactionQty      = '';
        $this->transactionNotes    = '';
        $this->showTransaction     = true;
    }

    public function closeTransaction(): void
    {
        $this->showTransaction = false;
        $this->transactionItemId = null;
    }

    public function saveTransaction(): void
    {
        $this->validate([
            'transactionQty'   => 'required|numeric|min:0.001',
            'transactionType'  => 'required|in:restock,deduction,adjustment,waste',
            'transactionNotes' => 'nullable|string|max:500',
        ]);

        $item = InventoryItem::findOrFail($this->transactionItemId);
        $qty = (float) $this->transactionQty;
        $before = (float) $item->current_stock;

        $after = match ($this->transactionType) {
            'restock'    => $before + $qty,
            'deduction', 'waste' => max(0, $before - $qty),
            'adjustment' => $qty,
        };

        DB::transaction(function () use ($item, $qty, $before, $after) {
            $item->update(['current_stock' => $after]);

            InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'type'              => $this->transactionType,
                'quantity'          => $this->transactionType === 'adjustment' ? $after - $before : $qty,
                'quantity_before'   => $before,
                'quantity_after'    => $after,
                'notes'             => trim($this->transactionNotes) ?: null,
                'user_id'           => auth()->id(),
            ]);
        });

        $this->closeTransaction();
        session()->flash('success', 'Stock updated.');
    }

    // --- Transaction history ---

    public function openHistory(int $id): void
    {
        $item = InventoryItem::findOrFail($id);
        $this->historyItemId   = $id;
        $this->historyItemName = $item->name;
        $this->showHistory     = true;
    }

    public function closeHistory(): void
    {
        $this->showHistory = false;
        $this->historyItemId = null;
    }

    // --- Menu item linking ---

    public function openLinkMenu(int $id): void
    {
        $item = InventoryItem::findOrFail($id);
        $this->linkItemId       = $id;
        $this->linkItemName     = $item->name;
        $this->linkMenuItemId   = null;
        $this->linkQuantityUsed = '1';
        $this->showLinkMenu     = true;
    }

    public function closeLinkMenu(): void
    {
        $this->showLinkMenu = false;
        $this->linkItemId = null;
    }

    public function saveLink(): void
    {
        $this->validate([
            'linkMenuItemId'   => 'required|exists:menu_items,id',
            'linkQuantityUsed' => 'required|numeric|min:0.001',
        ]);

        $item = InventoryItem::findOrFail($this->linkItemId);
        $item->menuItems()->syncWithoutDetaching([
            $this->linkMenuItemId => ['quantity_used' => $this->linkQuantityUsed],
        ]);

        $this->closeLinkMenu();
        session()->flash('success', 'Menu item linked.');
    }

    public function unlinkMenuItem(int $inventoryItemId, int $menuItemId): void
    {
        $item = InventoryItem::findOrFail($inventoryItemId);
        $item->menuItems()->detach($menuItemId);
        session()->flash('success', 'Menu item unlinked.');
    }

    public function render()
    {
        $query = InventoryItem::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->stockFilter === 'low', fn ($q) => $q->whereColumn('current_stock', '<=', 'low_stock_threshold'))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('current_stock', '<=', 0))
            ->orderBy('name');

        $items = $query->paginate(20);

        $lowStockCount = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')->count();
        $totalItems    = InventoryItem::count();
        $totalValue    = InventoryItem::selectRaw('SUM(current_stock * cost_per_unit) as total')->value('total') ?? 0;

        $transactions = [];
        if ($this->showHistory && $this->historyItemId) {
            $transactions = InventoryTransaction::where('inventory_item_id', $this->historyItemId)
                ->with('user')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();
        }

        $menuItems = [];
        $linkedMenuItems = [];
        if ($this->showLinkMenu && $this->linkItemId) {
            $menuItems = MenuItem::orderBy('name')->get(['id', 'name']);
            $linkedMenuItems = InventoryItem::findOrFail($this->linkItemId)
                ->menuItems()
                ->get(['menu_items.id', 'menu_items.name', 'quantity_used']);
        }

        return view('livewire.app.inventory.inventory-list', compact(
            'items', 'lowStockCount', 'totalItems', 'totalValue',
            'transactions', 'menuItems', 'linkedMenuItems'
        ))->layout('layouts.app', ['heading' => 'Inventory']);
    }
}
