<?php

namespace App\Livewire\Public;

use App\Models\Business;
use App\Models\Customer;
use App\Models\DeliveryZone;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class OnlineOrderForm extends Component
{
    public ?Business $business = null;

    // Customer info
    public string $customerName = '';
    public string $customerPhone = '';
    public string $customerEmail = '';
    public string $deliveryAddress = '';
    public string $orderNotes = '';

    // Cart managed via Livewire for server-side submission
    public array $cartItems = [];

    // Totals
    public float $subtotal = 0;
    public float $serviceCharge = 0;
    public float $deliveryFee = 0;
    public float $total = 0;

    // Success state
    public bool $orderPlaced = false;
    public string $orderNumber = '';

    public function mount(string $slug): void
    {
        $this->business = Business::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function addToCart(
        int $menuItemId,
        ?int $variantId,
        array $addonIds,
        int $quantity,
        string $notes
    ): void {
        $item = MenuItem::withoutGlobalScope('business')
            ->with(['variants', 'addonGroups.items'])
            ->where('business_id', $this->business->id)
            ->where('is_available', true)
            ->findOrFail($menuItemId);

        $variant = $variantId
            ? $item->variants->firstWhere('id', $variantId)
            : null;

        $unitPrice = $variant
            ? $variant->getFinalPrice((float) $item->base_price)
            : (float) $item->base_price;

        $addonData = [];
        foreach ($addonIds as $addonId) {
            foreach ($item->addonGroups as $group) {
                $addonItem = $group->items->firstWhere('id', (int) $addonId);
                if ($addonItem && $addonItem->is_available) {
                    $unitPrice += (float) $addonItem->price;
                    $addonData[] = [
                        'addon_group_item_id' => $addonItem->id,
                        'name' => $addonItem->name,
                        'price' => (float) $addonItem->price,
                        'quantity' => $quantity,
                    ];
                }
            }
        }

        $subtotal = round($unitPrice * $quantity, 2);

        $this->cartItems[] = [
            'id' => Str::uuid()->toString(),
            'menu_item_id' => $item->id,
            'item_variant_id' => $variant?->id,
            'name' => $item->name,
            'variant_name' => $variant?->name,
            'unit_price' => round($unitPrice, 2),
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'notes' => $notes ?: null,
            'addons' => $addonData,
        ];

        $this->recalculateTotals();
    }

    public function removeFromCart(string $cartId): void
    {
        $this->cartItems = array_values(
            array_filter($this->cartItems, fn($i) => $i['id'] !== $cartId)
        );
        $this->recalculateTotals();
    }

    public function incrementCartItem(string $cartId): void
    {
        foreach ($this->cartItems as &$item) {
            if ($item['id'] === $cartId) {
                $item['quantity']++;
                $item['subtotal'] = round($item['unit_price'] * $item['quantity'], 2);
                foreach ($item['addons'] as &$addon) {
                    $addon['quantity'] = $item['quantity'];
                }
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
                    foreach ($this->cartItems[$index]['addons'] as &$addon) {
                        $addon['quantity'] = $this->cartItems[$index]['quantity'];
                    }
                }
                break;
            }
        }
        $this->recalculateTotals();
    }

    private function recalculateTotals(): void
    {
        $this->subtotal = round(array_sum(array_column($this->cartItems, 'subtotal')), 2);

        // Service charge
        $this->serviceCharge = 0;
        $scType = $this->business->getSetting('service_charge_type');
        $scValue = (float) $this->business->getSetting('service_charge_value', 0);
        $scApplies = $this->business->getSetting('service_charge_applies_to', 'all');

        if ($scValue > 0) {
            $applicable = $scApplies === 'all'
                || $scApplies === 'online'
                || $scApplies === 'delivery';

            if ($applicable) {
                $this->serviceCharge = $scType === 'percentage'
                    ? round($this->subtotal * $scValue / 100, 2)
                    : $scValue;
            }
        }

        // Delivery fee from first active zone
        $this->deliveryFee = 0;
        $zone = DeliveryZone::withoutGlobalScope('business')
            ->where('business_id', $this->business->id)
            ->where('is_active', true)
            ->first();
        if ($zone) {
            $this->deliveryFee = (float) $zone->delivery_fee;
        }

        $this->total = round($this->subtotal + $this->serviceCharge + $this->deliveryFee, 2);
    }

    public function placeOrder(): void
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'required|string|max:50',
            'customerEmail' => 'nullable|email|max:255',
            'deliveryAddress' => 'required|string|max:500',
            'cartItems' => 'required|array|min:1',
        ]);

        if (! $this->business->canCreateOrder()) {
            $this->addError('order', 'This restaurant is currently unable to accept online orders. Please try again later.');
            return;
        }

        $this->recalculateTotals();

        DB::transaction(function () {
            // Find or create customer by phone
            $customer = Customer::withoutGlobalScope('business')
                ->where('business_id', $this->business->id)
                ->where('phone', $this->customerPhone)
                ->first();

            if (! $customer) {
                $customer = Customer::withoutGlobalScope('business')->create([
                    'business_id' => $this->business->id,
                    'name' => $this->customerName,
                    'phone' => $this->customerPhone,
                    'email' => $this->customerEmail ?: null,
                    'total_orders' => 0,
                    'total_spent' => 0,
                ]);
            }

            $order = Order::withoutGlobalScope('business')->create([
                'business_id' => $this->business->id,
                'order_number' => 'ONL-' . strtoupper(Str::random(6)),
                'customer_id' => $customer->id,
                'order_type' => 'online',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => 'pending',
                'source' => 'online',
                'customer_name' => $this->customerName,
                'customer_phone' => $this->customerPhone,
                'customer_email' => $this->customerEmail ?: null,
                'delivery_address' => $this->deliveryAddress,
                'notes' => $this->orderNotes ?: null,
                'subtotal' => $this->subtotal,
                'service_charge' => $this->serviceCharge,
                'delivery_fee' => $this->deliveryFee,
                'discount_amount' => 0,
                'total' => $this->total,
            ]);

            foreach ($this->cartItems as $cartItem) {
                $orderItem = $order->items()->create([
                    'business_id' => $this->business->id,
                    'menu_item_id' => $cartItem['menu_item_id'],
                    'item_variant_id' => $cartItem['item_variant_id'],
                    'name' => $cartItem['name'],
                    'variant_name' => $cartItem['variant_name'],
                    'unit_price' => $cartItem['unit_price'],
                    'quantity' => $cartItem['quantity'],
                    'subtotal' => $cartItem['subtotal'],
                    'notes' => $cartItem['notes'],
                ]);

                foreach ($cartItem['addons'] as $addon) {
                    $orderItem->addons()->create([
                        'addon_group_item_id' => $addon['addon_group_item_id'],
                        'name' => $addon['name'],
                        'price' => $addon['price'],
                        'quantity' => $addon['quantity'],
                    ]);
                }
            }

            // Update customer stats
            $customer->increment('total_orders');
            $customer->increment('total_spent', $this->total);

            $this->orderNumber = $order->order_number;
        });

        // Reset state
        $this->cartItems = [];
        $this->customerName = '';
        $this->customerPhone = '';
        $this->customerEmail = '';
        $this->deliveryAddress = '';
        $this->orderNotes = '';
        $this->subtotal = 0;
        $this->serviceCharge = 0;
        $this->deliveryFee = 0;
        $this->total = 0;
        $this->orderPlaced = true;
    }

    public function startNewOrder(): void
    {
        $this->orderPlaced = false;
        $this->orderNumber = '';
    }

    public function render()
    {
        $categories = MenuCategory::withoutGlobalScope('business')
            ->where('business_id', $this->business->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['items' => function ($q) {
                $q->withoutGlobalScope('business')
                    ->where('is_available', true)
                    ->where('is_delivery_available', true)
                    ->orderBy('sort_order')
                    ->with(['variants' => function ($vq) {
                        $vq->where('is_available', true);
                    }, 'addonGroups' => function ($gq) {
                        $gq->withoutGlobalScope('business')
                            ->with(['items' => function ($iq) {
                                $iq->where('is_available', true);
                            }]);
                    }]);
            }])
            ->get();

        return view('livewire.public.online-order-form', [
            'categories' => $categories,
        ])->layout('layouts.public', [
            'title' => 'Order from ' . $this->business->name,
        ]);
    }
}
