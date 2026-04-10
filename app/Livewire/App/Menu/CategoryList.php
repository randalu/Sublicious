<?php

namespace App\Livewire\App\Menu;

use App\Models\MenuCategory;
use Livewire\Attributes\On;
use Livewire\Component;

class CategoryList extends Component
{
    public string $name = '';
    public ?int $editingId = null;
    public string $editName = '';
    public bool $showForm = false;

    protected function rules(): array
    {
        return ['name' => 'required|string|max:100'];
    }

    public function save(): void
    {
        $this->validate();

        MenuCategory::create([
            'name'       => trim($this->name),
            'sort_order' => MenuCategory::max('sort_order') + 1,
        ]);

        $this->name = '';
        $this->showForm = false;
        session()->flash('success', 'Category created.');
    }

    public function startEdit(int $id): void
    {
        $cat = MenuCategory::findOrFail($id);
        $this->editingId = $id;
        $this->editName  = $cat->name;
    }

    public function saveEdit(): void
    {
        $this->validateOnly('editName', ['editName' => 'required|string|max:100']);
        MenuCategory::findOrFail($this->editingId)->update(['name' => trim($this->editName)]);
        $this->editingId = null;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
    }

    public function toggleActive(int $id): void
    {
        $cat = MenuCategory::findOrFail($id);
        $cat->update(['is_active' => ! $cat->is_active]);
    }

    public function delete(int $id): void
    {
        $cat = MenuCategory::withCount('items')->findOrFail($id);
        if ($cat->items_count > 0) {
            session()->flash('error', "Cannot delete \"{$cat->name}\" — it has {$cat->items_count} item(s). Reassign them first.");
            return;
        }
        $cat->delete();
        session()->flash('success', 'Category deleted.');
    }

    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            MenuCategory::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    public function render()
    {
        $categories = MenuCategory::withCount('items')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.app.menu.category-list', compact('categories'))
            ->layout('layouts.app', ['heading' => 'Menu Categories']);
    }
}
