<?php

namespace App\Livewire\App\Tables;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Services\BillingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TableSession extends Component
{
    public RestaurantTable $table;

    // Item selector
    public string $itemSearch    = '';
    public ?int   $selectedItemId = null;
    public ?int   $selectedVariantId = null;
    public array  $selectedAddons    = [];
    public int    $quantity          = 1;
    public string $itemNotes         = '';
    public bool   $showItemPicker    = false;

    // Payment modal
    public bool   $showPayModal   = false;
    public string $paymentMethod  = 'cash';
    public string $amountPaid     = '0.00';

    public function mount(RestaurantTable $table): void
    {
        $this->table = $table;
        // Open the table if it was available
        if ($this->table->status === 'available') {
            $this->table->update(['status' => 'occupied']);
        }
        // Ensure an active order exists
        $this->ensureOrder();
    }

    #[Computed]
    public function order(): Order
    {
        return Order::with(['items.variant', 'items.addons'])
            ->where('table_id', $this->table->id)
            ->where('payment_status', 'unpaid')
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->orderByDesc('id')
            ->firstOrFail();
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

    private function ensureOrder(): void
    {
        $exists = Order::where('table_id', $this->table->id)
            ->where('payment_status', 'unpaid')
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->exists();

        if (! $exists) {
            Order::create([
                'order_number'   => 'T-' . strtoupper(Str::random(6)),
                'table_id'       => $this->table->id,
                'order_type'     => 'dine_in',
                'status'         => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => 'pending',
                'source'         => 'pos',
                'subtotal'       => 0,
                'service_charge' => 0,
                'total'          => 0,
                'created_by'     => auth()->id(),
            ]);
        }
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

        // Auto-select first variant if exists
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

    public function addToOrder(): void
    {
        if (! $this->selectedItemId) return;

        $item    = MenuItem::with(['variants', 'addonGroups.items'])->findOrFail($this->selectedItemId);
        $variant = $this->selectedVariantId ? $item->variants->firstWhere('id', $this->selectedVariantId) : null;

        $unitPrice = $variant
            ? $variant->getFinalPrice((float) $item->base_price)
            : (float) $item->base_price;

        // Add selected addon prices
        $addonItems = [];
        foreach ($this->selectedAddons as $addonId) {
            foreach ($item->addonGroups as $group) {
                $addonItem = $group->items->firstWhere('id', $addonId);
                if ($addonItem) {
                    $unitPrice   += (float) $addonItem->price;
                    $addonItems[] = $addonItem;
                }
            }
        }

        $subtotal = $unitPrice * $this->quantity;

        $orderItem = $this->order->items()->create([
            'business_id'     => $this->order->business_id,
            'menu_item_id'    => $item->id,
            'item_variant_id' => $variant?->id,
            'name'            => $item->name,
            'variant_name'    => $variant?->name,
            'unit_price'      => $unitPrice,
            'quantity'        => $this->quantity,
            'subtotal'        => $subtotal,
            'notes'           => $this->itemNotes ?: null,
        ]);

        foreach ($addonItems as $addonItem) {
            $orderItem->addons()->create([
                'addon_group_item_id' => $addonItem->id,
                'name'                => $addonItem->name,
                'price'               => $addonItem->price,
                'quantity'            => $this->quantity,
            ]);
        }

        $this->recalculate();
        $this->resetItemPicker();
        unset($this->order);
    }

    public function removeItem(int $itemId): void
    {
        OrderItem::findOrFail($itemId)->delete();
        $this->recalculate();
        unset($this->order);
    }

    public function incrementItem(int $itemId): void
    {
        $item = OrderItem::findOrFail($itemId);
        $item->increment('quantity');
        $item->update(['subtotal' => $item->unit_price * $item->quantity]);
        $this->recalculate();
        unset($this->order);
    }

    public function decrementItem(int $itemId): void
    {
        $item = OrderItem::findOrFail($itemId);
        if ($item->quantity <= 1) {
            $item->delete();
        } else {
            $item->decrement('quantity');
            $item->update(['subtotal' => $item->unit_price * $item->quantity]);
        }
        $this->recalculate();
        unset($this->order);
    }

    private function recalculate(): void
    {
        $order    = $this->order;
        $subtotal = $order->items()->sum('subtotal');

        $serviceCharge = 0;
        $business      = auth()->user()->business;
        if ($business) {
            $scType    = $business->getSetting('service_charge_type');
            $scValue   = (float) $business->getSetting('service_charge_value', 0);
            $scApplies = $business->getSetting('service_charge_applies_to', 'all');

            if ($scValue > 0 && ($scApplies === 'all' || $scApplies === 'dine_in_only')) {
                $serviceCharge = $scType === 'percentage'
                    ? round($subtotal * $scValue / 100, 2)
                    : $scValue;
            }
        }

        $order->update([
            'subtotal'       => $subtotal,
            'service_charge' => $serviceCharge,
            'total'          => $subtotal + $serviceCharge,
            'status'         => $subtotal > 0 ? 'accepted' : 'pending',
        ]);
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
    }

    public function openPayModal(): void
    {
        $this->amountPaid  = (string) $this->order->total;
        $this->showPayModal = true;
    }

    public function closeTable(): void
    {
        $order = $this->order;

        if ($order->items()->count() === 0) {
            session()->flash('error', 'Cannot close an empty order.');
            return;
        }

        $this->validate([
            'paymentMethod' => 'required|in:cash,card,online',
            'amountPaid'    => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($order) {
            // Mark order paid
            $order->update([
                'payment_method' => $this->paymentMethod,
                'payment_status' => 'paid',
                'amount_paid'    => $this->amountPaid,
                'change_amount'  => max(0, (float) $this->amountPaid - (float) $order->total),
                'status'         => 'completed',
            ]);

            // Create bill
            $bill = Bill::create([
                'order_id'       => $order->id,
                'bill_number'    => 'B-' . strtoupper(Str::random(6)),
                'customer_name'  => $order->customer_name ?? ('Table ' . $this->table->table_number),
                'table_id'       => $this->table->id,
                'subtotal'       => $order->subtotal,
                'service_charge' => $order->service_charge,
                'discount_amount'=> $order->discount_amount ?? 0,
                'total'          => $order->total,
                'payment_method' => $this->paymentMethod,
                'payment_status' => 'paid',
                'paid_at'        => now(),
            ]);

            foreach ($order->items as $item) {
                BillItem::create([
                    'bill_id'     => $bill->id,
                    'description' => $item->name . ($item->variant_name ? ' (' . $item->variant_name . ')' : ''),
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'total'       => $item->subtotal,
                ]);
            }

            // Reset table
            $this->table->update(['status' => 'available']);
        });

        $this->showPayModal = false;
        session()->flash('success', 'Table closed and bill created.');
        $this->redirectRoute('app.tables', navigate: false);
    }

    public function render()
    {
        $categories = MenuCategory::with(['items' => fn ($q) => $q->where('is_available', true)->orderBy('sort_order')])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.app.tables.table-session', compact('categories'))
            ->layout('layouts.app', ['heading' => 'Table ' . $this->table->table_number]);
    }
}
