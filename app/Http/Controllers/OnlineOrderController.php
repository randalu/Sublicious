<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\InventoryTransaction;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnlineOrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'business_id'       => 'required|exists:businesses,id',
            'customer_name'     => 'required|string|max:100',
            'customer_phone'    => 'required|string|max:20',
            'customer_email'    => 'nullable|email|max:100',
            'delivery_address'  => 'required|string|max:500',
            'delivery_lat'      => 'nullable|numeric',
            'delivery_lng'      => 'nullable|numeric',
            'notes'             => 'nullable|string|max:500',
            'items'             => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.variant_id'   => 'nullable|exists:item_variants,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.notes'        => 'nullable|string|max:200',
            'items.*.addons'       => 'nullable|array',
            'items.*.addons.*.addon_group_item_id' => 'required|exists:addon_group_items,id',
            'items.*.addons.*.quantity'             => 'nullable|integer|min:1',
        ]);

        $business = Business::findOrFail($request->business_id);

        if (! $business->is_active) {
            return response()->json(['error' => 'This business is not currently accepting orders.'], 422);
        }

        if (! $business->canCreateOrder()) {
            return response()->json(['error' => 'This business has reached its order limit. Please try again later.'], 422);
        }

        try {
            $order = DB::transaction(function () use ($request, $business) {
                // Find or create customer
                $customer = Customer::firstOrCreate(
                    ['business_id' => $business->id, 'phone' => $request->customer_phone],
                    ['name' => $request->customer_name, 'email' => $request->customer_email]
                );

                // Calculate service charge
                $serviceChargeType  = $business->getSetting('service_charge_type', 'percentage');
                $serviceChargeValue = (float) $business->getSetting('service_charge_value', 0);
                $serviceChargeApplies = $business->getSetting('service_charge_applies_to', 'all');

                // Build order items and calculate subtotal
                $subtotal = 0;
                $orderItemsData = [];

                foreach ($request->items as $cartItem) {
                    $menuItem = MenuItem::where('business_id', $business->id)
                        ->where('id', $cartItem['menu_item_id'])
                        ->where('is_available', true)
                        ->where('is_delivery_available', true)
                        ->firstOrFail();

                    $unitPrice = $menuItem->base_price;
                    $variantName = null;
                    $variantId = null;

                    if (! empty($cartItem['variant_id'])) {
                        $variant = $menuItem->variants()->find($cartItem['variant_id']);
                        if ($variant) {
                            $variantId = $variant->id;
                            $variantName = $variant->name;
                            $unitPrice = match ($variant->price_type) {
                                'replace' => $variant->price_adjustment,
                                'add'     => $unitPrice + $variant->price_adjustment,
                                default   => $unitPrice + $variant->price_adjustment,
                            };
                        }
                    }

                    $qty = $cartItem['quantity'];
                    $itemSubtotal = $unitPrice * $qty;

                    $addonsData = [];
                    if (! empty($cartItem['addons'])) {
                        foreach ($cartItem['addons'] as $addon) {
                            $addonItem = \App\Models\AddonGroupItem::find($addon['addon_group_item_id']);
                            if ($addonItem) {
                                $addonQty = $addon['quantity'] ?? 1;
                                $itemSubtotal += $addonItem->price * $addonQty;
                                $addonsData[] = [
                                    'addon_group_item_id' => $addonItem->id,
                                    'name'     => $addonItem->name,
                                    'price'    => $addonItem->price,
                                    'quantity' => $addonQty,
                                ];
                            }
                        }
                    }

                    $subtotal += $itemSubtotal;
                    $orderItemsData[] = [
                        'menu_item_id'    => $menuItem->id,
                        'item_variant_id' => $variantId,
                        'name'            => $menuItem->name,
                        'variant_name'    => $variantName,
                        'unit_price'      => $unitPrice,
                        'quantity'        => $qty,
                        'subtotal'        => $itemSubtotal,
                        'notes'           => $cartItem['notes'] ?? null,
                        'addons'          => $addonsData,
                    ];
                }

                // Delivery fee (first active zone)
                $zone = $business->deliveryZones()->where('is_active', true)->first();
                $deliveryFee = $zone?->delivery_fee ?? 0;

                // Service charge
                $serviceCharge = 0;
                if ($serviceChargeValue > 0 && in_array($serviceChargeApplies, ['all', 'delivery'])) {
                    $serviceCharge = $serviceChargeType === 'percentage'
                        ? round($subtotal * $serviceChargeValue / 100, 2)
                        : $serviceChargeValue;
                }

                $total = $subtotal + $serviceCharge + $deliveryFee;

                // Create order
                $order = Order::create([
                    'business_id'      => $business->id,
                    'order_number'     => 'ON-' . strtoupper(Str::random(6)),
                    'customer_id'      => $customer->id,
                    'order_type'       => 'online',
                    'status'           => 'pending',
                    'subtotal'         => $subtotal,
                    'service_charge'   => $serviceCharge,
                    'delivery_fee'     => $deliveryFee,
                    'total'            => $total,
                    'payment_method'   => 'pending',
                    'payment_status'   => 'unpaid',
                    'customer_name'    => $request->customer_name,
                    'customer_phone'   => $request->customer_phone,
                    'delivery_address' => $request->delivery_address,
                    'delivery_lat'     => $request->delivery_lat,
                    'delivery_lng'     => $request->delivery_lng,
                    'notes'            => $request->notes,
                    'source'           => 'online',
                ]);

                // Create order items
                foreach ($orderItemsData as $itemData) {
                    $addons = $itemData['addons'];
                    unset($itemData['addons']);

                    $itemData['business_id'] = $business->id;
                    $itemData['order_id'] = $order->id;
                    $orderItem = OrderItem::create($itemData);

                    foreach ($addons as $addon) {
                        $addon['order_item_id'] = $orderItem->id;
                        OrderItemAddon::create($addon);
                    }
                }

                // Deduct inventory for tracked menu items
                foreach ($orderItemsData as $itemData) {
                    $menuItem = MenuItem::with('inventoryItems')->find($itemData['menu_item_id']);
                    if ($menuItem && $menuItem->track_inventory) {
                        foreach ($menuItem->inventoryItems as $invItem) {
                            $deductQty = (float) $invItem->pivot->quantity_used * $itemData['quantity'];
                            $before = (float) $invItem->current_stock;
                            $after  = max(0, $before - $deductQty);
                            $invItem->update(['current_stock' => $after]);
                            InventoryTransaction::create([
                                'business_id'       => $business->id,
                                'inventory_item_id' => $invItem->id,
                                'type'              => 'deduction',
                                'quantity'          => $deductQty,
                                'quantity_before'   => $before,
                                'quantity_after'    => $after,
                                'notes'             => 'Order #' . $order->order_number,
                                'user_id'           => null,
                            ]);
                        }
                    }
                }

                // Update customer stats
                $customer->increment('total_orders');
                $customer->increment('total_spent', $total);

                return $order;
            });

            // Send SMS notification to business
            try {
                if ($business->phone) {
                    app(SmsService::class)->send(
                        $business->phone,
                        "New online order #{$order->order_number} from {$order->customer_name}. Total: {$business->currency} " . number_format($order->total, 2),
                        $business->id,
                        $order->id
                    );
                }
            } catch (\Throwable) {
                // Don't fail order creation if SMS fails
            }

            return response()->json([
                'status'       => 'success',
                'order_number' => $order->order_number,
                'total'        => $order->total,
                'message'      => 'Your order has been placed successfully!',
            ], 201);

        } catch (\Throwable $e) {
            return response()->json(['error' => 'Failed to place order. Please try again.'], 500);
        }
    }
}
