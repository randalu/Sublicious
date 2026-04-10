<?php

namespace App\Livewire\App\Menu;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ItemList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $categoryFilter = '';

    #[Url(except: '')]
    public string $availabilityFilter = '';

    public function updatedSearch(): void           { $this->resetPage(); }
    public function updatedCategoryFilter(): void   { $this->resetPage(); }
    public function updatedAvailabilityFilter(): void { $this->resetPage(); }

    public function toggleAvailable(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['is_available' => ! $item->is_available]);
    }

    public function toggleDelivery(int $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['is_delivery_available' => ! $item->is_delivery_available]);
    }

    public function delete(int $id): void
    {
        MenuItem::findOrFail($id)->delete();
        session()->flash('success', 'Item deleted.');
    }

    public function render()
    {
        $query = MenuItem::with('category')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->availabilityFilter !== '', fn ($q) => $q->where('is_available', (bool) $this->availabilityFilter))
            ->orderBy('sort_order')
            ->orderBy('name');

        $items      = $query->paginate(20);
        $categories = MenuCategory::orderBy('name')->get();

        return view('livewire.app.menu.item-list', compact('items', 'categories'))
            ->layout('layouts.app', ['heading' => 'Menu Items']);
    }
}
