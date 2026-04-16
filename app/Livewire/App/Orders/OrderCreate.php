<?php

namespace App\Livewire\App\Orders;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Customer;
use App\Models\InventoryTransaction;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\InventoryItem;
use App\Notifications\LowStockNotification;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OrderCreate extends Component
{
    // Order meta
    public string $orderType     = 'dine_in';
    public ?int   $tableId       = null;
    public string $customerName  = '';
    public string $customerPhone = '';
    public string $deliveryAddress = '';
    public string $orderNotes    = '';

    // Item selector
    public string $itemSearch       = '';
    public ?int   $selectedItemId   = null;
    public ?int   $selectedVariantId = null;
    public array  $selectedAddons   = [];
    public int    $quantity         = 1;
    public string $itemNotes        = '';
    public bool   $showItemPicker   = false;

    // Cart (temporary, stored in component state until placed)
    public array $cartItems = [];  // [{menu_item_id, item_variant_id, name, variant_name, unit_price, quantity, subtotal, notes, addons:[]}]

    // Totals
    public float $subtotal      = 0;
    public float $serviceCharge = 0;
    public float $deliveryFee   = 0;
    public float $total         = 0;

    #[Computed]
    public function availableTables()
    {
        return RestaurantTable::where('status', 'available')->orderBy('table_number')->get();
    }

    #[Computed]
    public function searchResults()
    {
        if (strlen($this->itemSearch) < 1) {
            return collect();
        }
        return MenuItem::with('variants')
            ->where('is_available', true)
            ->where('name', 'like', "%{$this->itemSearch}%")
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function selectedItem(): ?MenuItem
    {
        return $this->selectedItemId
            ? MenuItem::with(['variants', 'addonGroups.items'])->find($this->selectedItemId)
            : null;
    }

    public function updatedOrderType(): void
    {
        $this->tableId = null;
        $this->recalculateTotals();
    }

    public function selectItem(int $itemId): void
    {
        $this->selectedItemId    = $itemId;
        $this->selectedVariantId = null;
        $this->selectedAddons    = [];
        $this->quantity          = 1;
        $this->itemNotes         = '';
        $this->itemSearch        = '';
        $this->showItemPicker    = true;

        $item = MenuItem::with('variants')->find($itemId);
        if ($item && $item->variants->count() === 1) {
            $this->selectedVariantId = $item->variants->first()->id;
        }
    }

    public function toggleAddon(int $addonItemId): void
    {
        if (in_array($addonItemId, $this->selectedAddons)) {
            $this->selectedAddons = array_values(array_filter($this->selectedAddons, fn ($id) => $id !== $addonItemId));
        } else {
            $this->selectedAddons[] = $addonItemId;
        }
    }

    public function addToCart(): void
    {
        if (! $this->selectedItemId) return;

        $item    = MenuItem::with(['variants', 'addonGroups.items'])->findOrFail($this->selectedItemId);
        $variant = $this->selectedVariantId ? $item->variants->firstWhere('id', $this->selectedVariantId) : null;

        $unitPrice = $variant
            ? $variant->getFinalPrice((float) $item->base_price)
            : (float) $item->base_price;

        $addonData = [];
        foreach ($this->selectedAddons as $addonId) {
            foreach ($item->addonGroups as $group) {
                $addonItem = $group->items->firstWhere('id', $addonId);
                if ($addonItem) {
                    $unitPrice   += (float) $addonItem->price;
                    $addonData[]  = [
                        'addon_group_item_id' => $addonItem->id,
                        'name'                => $addonItem->name,
                        'price'               => (float) $addonItem->price,
                        'quantity'            => $this->quantity,
                    ];
                }
            }
        }

        $subtotal = round($unitPrice * $this->quantity, 2);

        $this->cartItems[] = [
            'id'              => Str::uuid()->toString(), // local cart id
            'menu_item_id'    => $item->id,
            'item_variant_id' => $variant?->id,
            'name'            => $item->name,
            'variant_name'    => $variant?->name,
            'unit_price'      => round($unitPrice, 2),
            'quantity'        => $this->quantity,
            'subtotal'        => $subtotal,
            'notes'           => $this->itemNotes ?: null,
            'addons'          => $addonData,
        ];

        $this->recalculateTotals();
        $this->resetItemPicker();
    }

    public function removeFromCart(string $cartId): void
    {
        $this->cartItems = array_values(array_filter($this->cartItems, fn ($i) => $i['id'] !== $cartId));
        $this->recalculateTotals();
    }

    public function incrementCartItem(string $cartId): void
    {
        foreach ($this->cartItems as &$item) {
            if ($item['id'] === $cartId) {
                $item['quantity']++;
                $item['subtotal'] = round($item['unit_price'] * $item['quantity'], 2);
                break;
            }
        }
        $this->recalculateTotals();
    }

    public function decrementCartItem(string $cartId): void
    {
        foreach ($this->cartItems as $index => $item) {
            if ($item['id'] === $cartId) {
                if ($item['quantity'] <= 1) {
                    array_splice($this->cartItems, $index, 1);
                } else {
                    $this->cartItems[$index]['quantity']--;
                    $this->cartItems[$index]['subtotal'] = round(
                        $this->cartItems[$index]['unit_price'] * $this->cartItems[$index]['quantity'],
                        2
                    );
                }
                break;
            }
        }
        $this->recalculateTotals();
    }

    private function recalculateTotals(): void
    {
        $this->subtotal = round(array_sum(array_column($this->cartItems, 'subtotal')), 2);

        $this->serviceCharge = 0;
        $business = auth()->user()->business;
        if ($business) {
            $scType    = $business->getSetting('service_charge_type');
            $scValue   = (float) $business->getSetting('service_charge_value', 0);
            $scApplies = $business->getSetting('service_charge_applies_to', 'all');

            if ($scValue > 0) {
                $applicable = $scApplies === 'all'
                    || ($scApplies === 'dine_in_only' && $this->orderType === 'dine_in');

                if ($applicable) {
                    $this->serviceCharge = $scType === 'percentage'
                        ? round($this->subtotal * $scValue / 100, 2)
                        : $scValue;
                }
            }
        }

        $this->deliveryFee = 0;
        if ($this->orderType === 'delivery') {
            $this->deliveryFee = (float) ($business?->getSetting('default_delivery_fee') ?? 0);
        }

        $this->total = $this->subtotal + $this->serviceCharge + $this->deliveryFee;
    }

    private function resetItemPicker(): void
    {
        $this->selectedItemId    = null;
        $this->selectedVariantId = null;
        $this->selectedAddons    = [];
        $this->quantity          = 1;
        $this->itemNotes         = '';
        $this->showItemPicker    = false;
        $this->itemSearch        = '';
        unset($this->selectedItem);
    }

    public function placeOrder(): void
    {
        $business = auth()->user()->business;

        if (! $business->canCreateOrder()) {
            $this->addError('limit', 'You have reached your monthly order limit. Please upgrade your plan.');
            return;
        }

        $rules = [
            'orderType' => 'required|in:dine_in,takeaway,delivery',
            'cartItems' => 'required|array|min:1',
        ];

        if ($this->orderType === 'dine_in') {
            $rules['tableId'] = 'required|exists:restaurant_tables,id';
        }

        if ($this->orderType === 'delivery') {
            $rules['customerName']    = 'required|string|max:255';
            $rules['customerPhone']   = 'required|string|max:50';
            $rules['deliveryAddress'] = 'required|string|max:500';
        }

        $this->validate($rules);

        DB::transaction(function () use ($business) {
            $order = Order::create([
                'order_number'    => 'POS-' . strtoupper(Str::random(6)),
                'table_id'        => $this->orderType === 'dine_in' ? $this->tableId : null,
                'order_type'      => $this->orderType,
                'status'          => 'accepted',
                'payment_status'  => 'unpaid',
                'payment_method'  => 'pending',
                'source'          => 'pos',
                'customer_name'   => $this->customerName ?: null,
                'customer_phone'  => $this->customerPhone ?: null,
                'delivery_address'=> $this->orderType === 'delivery' ? $this->deliveryAddress : null,
                'notes'           => $this->orderNotes ?: null,
                'subtotal'        => $this->subtotal,
                'service_charge'  => $this->serviceCharge,
                'delivery_fee'    => $this->deliveryFee,
                'discount_amount' => 0,
                'total'           => $this->total,
                'created_by'      => auth()->id(),
            ]);

            foreach ($this->cartItems as $cartItem) {
                $orderItem = $order->items()->create([
                    'business_id'     => $order->business_id,
                    'menu_item_id'    => $cartItem['menu_item_id'],
                    'item_variant_id' => $cartItem['item_variant_id'],
                    'name'            => $cartItem['name'],
                    'variant_name'    => $cartItem['variant_name'],
                    'unit_price'      => $cartItem['unit_price'],
                    'quantity'        => $cartItem['quantity'],
                    'subtotal'        => $cartItem['subtotal'],
                    'notes'           => $cartItem['notes'],
                ]);

                foreach ($cartItem['addons'] as $addon) {
                    $orderItem->addons()->create([
                        'addon_group_item_id' => $addon['addon_group_item_id'],
                        'name'                => $addon['name'],
                        'price'               => $addon['price'],
                        'quantity'            => $addon['quantity'],
                    ]);
                }
            }

            // Deduct inventory for tracked menu items
            foreach ($this->cartItems as $cartItem) {
                $menuItem = MenuItem::with('inventoryItems')->find($cartItem['menu_item_id']);
                if ($menuItem && $menuItem->track_inventory) {
                    foreach ($menuItem->inventoryItems as $invItem) {
                        $deductQty = (float) $invItem->pivot->quantity_used * $cartItem['quantity'];
                        $before = (float) $invItem->current_stock;
                        $after  = max(0, $before - $deductQty);
                        $invItem->update(['current_stock' => $after]);
                        InventoryTransaction::create([
                            'business_id'       => $invItem->business_id,
                            'inventory_item_id' => $invItem->id,
                            'type'              => 'deduction',
                            'quantity'          => $deductQty,
                            'quantity_before'   => $before,
                            'quantity_after'    => $after,
                            'notes'             => 'Order #' . $order->order_number,
                            'user_id'           => auth()->id(),
                        ]);
                    }
                }
            }

            // If dine_in, mark table occupied
            if ($this->orderType === 'dine_in' && $this->tableId) {
                $table = RestaurantTable::find($this->tableId);
                $table?->update(['status' => 'occupied']);
            }

            // Send email notifications if enabled
            try {
                $owner = $business->owner();
                if ($owner?->email) {
                    if ($business->getSetting('notify_new_order_email')) {
                        $order->load('items');
                        $owner->notify(new NewOrderNotification($order));
                    }
                    if ($business->getSetting('notify_low_stock_email') && $business->hasFeature('inventory')) {
                        $lowItems = InventoryItem::whereColumn('current_stock', '<=', 'low_stock_threshold')->get();
                        if ($lowItems->isNotEmpty()) {
                            $owner->notify(new LowStockNotification($lowItems));
                        }
                    }
                }
            } catch (\Throwable) {}

            session()->flash('success', 'Order #' . $order->order_number . ' placed successfully.');
            $this->redirectRoute('app.orders.show', ['order' => $order->id], navigate: false);
        });
    }

    public function render()
    {
        $categories = MenuCategory::with(['items' => fn ($q) => $q->where('is_available', true)->orderBy('sort_order')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.app.orders.order-create', compact('categories'))
            ->layout('layouts.app', ['heading' => 'New Order']);
    }
}
