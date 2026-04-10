<?php

namespace App\Livewire\App\Menu;

use App\Models\AddonGroup;
use App\Models\AddonGroupItem;
use Livewire\Component;

class AddonGroupManager extends Component
{
    // Group form
    public bool    $showGroupForm     = false;
    public ?int    $editingGroupId    = null;
    public string  $groupName         = '';
    public string  $selectionType     = 'multiple';
    public bool    $isRequired        = false;
    public int     $minSelections     = 0;
    public int     $maxSelections     = 10;

    // Item inline add
    public ?int   $expandedGroupId   = null;
    public string $newItemName        = '';
    public string $newItemPrice       = '0.00';

    // Item edit
    public ?int   $editingItemId      = null;
    public string $editItemName       = '';
    public string $editItemPrice      = '0.00';

    protected function groupRules(): array
    {
        return [
            'groupName'      => 'required|string|max:100',
            'selectionType'  => 'required|in:single,multiple',
            'minSelections'  => 'required|integer|min:0',
            'maxSelections'  => 'required|integer|min:1',
        ];
    }

    public function openGroupForm(?int $id = null): void
    {
        $this->resetGroupForm();
        $this->showGroupForm  = true;
        $this->editingGroupId = $id;

        if ($id) {
            $group = AddonGroup::findOrFail($id);
            $this->groupName      = $group->name;
            $this->selectionType  = $group->selection_type;
            $this->isRequired     = $group->is_required;
            $this->minSelections  = $group->min_selections;
            $this->maxSelections  = $group->max_selections;
        }
    }

    public function closeGroupForm(): void
    {
        $this->showGroupForm  = false;
        $this->editingGroupId = null;
        $this->resetGroupForm();
    }

    private function resetGroupForm(): void
    {
        $this->groupName        = '';
        $this->selectionType    = 'multiple';
        $this->isRequired       = false;
        $this->minSelections    = 0;
        $this->maxSelections    = 10;
    }

    public function saveGroup(): void
    {
        $this->validateOnly('groupName',     $this->groupRules());
        $this->validateOnly('selectionType', $this->groupRules());

        $data = [
            'name'            => trim($this->groupName),
            'selection_type'  => $this->selectionType,
            'is_required'     => $this->isRequired,
            'min_selections'  => $this->minSelections,
            'max_selections'  => $this->maxSelections,
        ];

        if ($this->editingGroupId) {
            AddonGroup::findOrFail($this->editingGroupId)->update($data);
        } else {
            AddonGroup::create($data);
        }

        $this->closeGroupForm();
        session()->flash('success', 'Addon group saved.');
    }

    public function deleteGroup(int $id): void
    {
        AddonGroup::findOrFail($id)->delete();
        if ($this->expandedGroupId === $id) {
            $this->expandedGroupId = null;
        }
        session()->flash('success', 'Addon group deleted.');
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedGroupId = $this->expandedGroupId === $id ? null : $id;
        $this->newItemName  = '';
        $this->newItemPrice = '0.00';
        $this->editingItemId = null;
    }

    public function addItem(int $groupId): void
    {
        $this->validate([
            'newItemName'  => 'required|string|max:100',
            'newItemPrice' => 'required|numeric|min:0',
        ]);

        $group = AddonGroup::findOrFail($groupId);
        $group->items()->create([
            'business_id'  => $group->business_id,
            'name'         => trim($this->newItemName),
            'price'        => $this->newItemPrice,
            'sort_order'   => $group->items()->max('sort_order') + 1,
        ]);

        $this->newItemName  = '';
        $this->newItemPrice = '0.00';
    }

    public function startEditItem(int $itemId): void
    {
        $item = AddonGroupItem::findOrFail($itemId);
        $this->editingItemId  = $itemId;
        $this->editItemName   = $item->name;
        $this->editItemPrice  = (string) $item->price;
    }

    public function saveItem(): void
    {
        $this->validate([
            'editItemName'  => 'required|string|max:100',
            'editItemPrice' => 'required|numeric|min:0',
        ]);

        AddonGroupItem::findOrFail($this->editingItemId)->update([
            'name'  => trim($this->editItemName),
            'price' => $this->editItemPrice,
        ]);

        $this->editingItemId = null;
    }

    public function cancelEditItem(): void
    {
        $this->editingItemId = null;
    }

    public function deleteItem(int $itemId): void
    {
        AddonGroupItem::findOrFail($itemId)->delete();
    }

    public function toggleItemAvailable(int $itemId): void
    {
        $item = AddonGroupItem::findOrFail($itemId);
        $item->update(['is_available' => ! $item->is_available]);
    }

    public function render()
    {
        $groups = AddonGroup::withCount('items')
            ->with(['items' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('name')
            ->get();

        return view('livewire.app.menu.addon-group-manager', compact('groups'))
            ->layout('layouts.app', ['heading' => 'Add-on Groups']);
    }
}
