<?php

namespace App\Livewire\App\Menu;

use App\Models\AddonGroup;
use App\Models\InventoryItem;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ItemForm extends Component
{
    use WithFileUploads;

    public ?MenuItem $item = null;

    // Basic fields
    public string  $name                    = '';
    public string  $description             = '';
    public ?int    $categoryId              = null;
    public string  $basePrice               = '0.00';
    public bool    $isAvailable             = true;
    public bool    $isDeliveryAvailable     = true;
    public bool    $isFeatured              = false;
    public int     $preparationTimeMinutes  = 15;
    public bool    $trackInventory          = false;
    public $photo = null;

    // Variants
    public array $variants = [];

    // Addon groups — array of attached group IDs
    public array $attachedAddonGroupIds = [];

    // Inventory items — [{inventory_item_id, quantity_used}]
    public array $inventoryLinks = [];

    protected function rules(): array
    {
        return [
            'name'                   => 'required|string|max:200',
            'description'            => 'nullable|string|max:1000',
            'categoryId'             => 'nullable|integer|exists:menu_categories,id',
            'basePrice'              => 'required|numeric|min:0',
            'preparationTimeMinutes' => 'required|integer|min:1|max:300',
            'photo'                  => 'nullable|image|max:2048',
            'variants'               => 'array',
            'variants.*.name'        => 'required|string|max:100',
            'variants.*.price_type'  => 'required|in:replace,add,subtract',
            'variants.*.price_adjustment' => 'required|numeric|min:0',
        ];
    }

    protected array $messages = [
        'variants.*.name.required'             => 'Variant name is required.',
        'variants.*.price_adjustment.required' => 'Variant price is required.',
    ];

    public function mount(?MenuItem $item = null): void
    {
        if ($item && $item->exists) {
            $this->item = $item->load(['variants', 'addonGroups']);
            $this->fill([
                'name'                   => $this->item->name,
                'description'            => $this->item->description ?? '',
                'categoryId'             => $this->item->category_id,
                'basePrice'              => (string) $this->item->base_price,
                'isAvailable'            => $this->item->is_available,
                'isDeliveryAvailable'    => $this->item->is_delivery_available,
                'isFeatured'             => $this->item->is_featured,
                'preparationTimeMinutes' => $this->item->preparation_time_minutes,
                'trackInventory'         => $this->item->track_inventory,
            ]);
            $this->variants = $this->item->variants->map(fn ($v) => [
                'id'               => $v->id,
                'name'             => $v->name,
                'price_type'       => $v->price_type,
                'price_adjustment' => (string) $v->price_adjustment,
                'is_available'     => $v->is_available,
            ])->toArray();

            $this->attachedAddonGroupIds = $this->item->addonGroups->pluck('id')->toArray();

            $this->inventoryLinks = $this->item->inventoryItems->map(fn ($inv) => [
                'inventory_item_id' => $inv->id,
                'quantity_used'     => (string) $inv->pivot->quantity_used,
            ])->toArray();
        }
    }

    public function addVariant(): void
    {
        $this->variants[] = [
            'id'               => null,
            'name'             => '',
            'price_type'       => 'replace',
            'price_adjustment' => '0.00',
            'is_available'     => true,
        ];
    }

    public function removeVariant(int $index): void
    {
        array_splice($this->variants, $index, 1);
    }

    public function addInventoryLink(): void
    {
        $this->inventoryLinks[] = [
            'inventory_item_id' => '',
            'quantity_used'     => '1',
        ];
    }

    public function removeInventoryLink(int $index): void
    {
        array_splice($this->inventoryLinks, $index, 1);
    }

    public function toggleAddonGroup(int $groupId): void
    {
        if (in_array($groupId, $this->attachedAddonGroupIds)) {
            $this->attachedAddonGroupIds = array_values(
                array_filter($this->attachedAddonGroupIds, fn ($id) => $id !== $groupId)
            );
        } else {
            $this->attachedAddonGroupIds[] = $groupId;
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        $imagePath = $this->item?->image;
        if ($this->photo) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->photo->store('menu-items', 'public');
        }

        $attributes = [
            'name'                    => trim($this->name),
            'description'             => trim($this->description) ?: null,
            'category_id'             => $this->categoryId,
            'base_price'              => $this->basePrice,
            'is_available'            => $this->isAvailable,
            'is_delivery_available'   => $this->isDeliveryAvailable,
            'is_featured'             => $this->isFeatured,
            'preparation_time_minutes'=> $this->preparationTimeMinutes,
            'track_inventory'         => $this->trackInventory,
            'image'                   => $imagePath,
        ];

        if ($this->item) {
            $this->item->update($attributes);
            $item = $this->item;
        } else {
            $attributes['sort_order'] = MenuItem::max('sort_order') + 1;
            $item = MenuItem::create($attributes);
        }

        // Sync variants
        $keptIds = [];
        foreach ($this->variants as $v) {
            if (! empty($v['id'])) {
                $item->variants()->where('id', $v['id'])->update([
                    'name'             => $v['name'],
                    'price_type'       => $v['price_type'],
                    'price_adjustment' => $v['price_adjustment'],
                    'is_available'     => $v['is_available'] ?? true,
                ]);
                $keptIds[] = $v['id'];
            } else {
                $new = $item->variants()->create([
                    'business_id'      => $item->business_id,
                    'name'             => $v['name'],
                    'price_type'       => $v['price_type'],
                    'price_adjustment' => $v['price_adjustment'],
                    'is_available'     => $v['is_available'] ?? true,
                    'sort_order'       => count($keptIds),
                ]);
                $keptIds[] = $new->id;
            }
        }
        $item->variants()->whereNotIn('id', $keptIds)->delete();

        // Sync addon groups
        $syncData = [];
        foreach ($this->attachedAddonGroupIds as $index => $groupId) {
            $syncData[$groupId] = ['sort_order' => $index];
        }
        $item->addonGroups()->sync($syncData);

        // Sync inventory links
        $invSync = [];
        foreach ($this->inventoryLinks as $link) {
            if (! empty($link['inventory_item_id'])) {
                $invSync[(int) $link['inventory_item_id']] = [
                    'quantity_used' => (float) ($link['quantity_used'] ?: 1),
                ];
            }
        }
        $item->inventoryItems()->sync($invSync);

        session()->flash('success', $this->item ? 'Item updated.' : 'Item created.');
        $this->redirectRoute('app.menu.items', navigate: false);
    }

    public function render()
    {
        $categories     = MenuCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $addonGroups    = AddonGroup::with('items')->orderBy('name')->get();
        $inventoryItems = InventoryItem::orderBy('name')->get();

        $heading = $this->item ? 'Edit: ' . $this->item->name : 'New Menu Item';

        return view('livewire.app.menu.item-form', compact('categories', 'addonGroups', 'inventoryItems'))
            ->layout('layouts.app', ['heading' => $heading]);
    }
}
