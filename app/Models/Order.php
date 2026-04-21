<?php

namespace App\Models;

use App\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id', 'order_number', 'customer_id', 'table_id', 'discount_code_id',
        'order_type', 'status', 'customer_name', 'customer_phone', 'customer_email',
        'delivery_address', 'delivery_lat', 'delivery_lng', 'delivery_instructions',
        'subtotal', 'service_charge', 'delivery_fee', 'discount_amount', 'total',
        'payment_method', 'payment_status', 'amount_paid', 'change_amount',
        'notes', 'source', 'cancel_reason', 'refund_amount', 'refund_reason',
        'refunded_at', 'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'delivery_lat' => 'decimal:7',
        'delivery_lng' => 'decimal:7',
        'refunded_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }

    public function isDelivery(): bool
    {
        return in_array($this->order_type, ['delivery', 'online']);
    }

    public function isDineIn(): bool
    {
        return $this->order_type === 'dine_in';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isActive(): bool
    {
        return ! in_array($this->status, ['completed', 'cancelled', 'refunded']);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'dispatched' => 'Dispatched',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'accepted' => 'blue',
            'preparing' => 'orange',
            'ready' => 'purple',
            'dispatched' => 'indigo',
            'delivered' => 'green',
            'completed' => 'green',
            'cancelled' => 'red',
            'refunded' => 'gray',
            default => 'gray',
        };
    }

    public function nextStatus(): ?string
    {
        $flow = [
            'pending' => 'accepted',
            'accepted' => 'preparing',
            'preparing' => 'ready',
            'ready' => $this->isDelivery() ? 'dispatched' : 'completed',
            'dispatched' => 'delivered',
            'delivered' => 'completed',
        ];
        return $flow[$this->status] ?? null;
    }

    public function deductInventory(): void
    {
        DB::transaction(function () {
            foreach ($this->items()->with('menuItem.inventoryItems')->get() as $orderItem) {
                if (! $orderItem->menuItem?->track_inventory) {
                    continue;
                }
                foreach ($orderItem->menuItem->inventoryItems as $inv) {
                    $qty = $inv->pivot->quantity_used * $orderItem->quantity;
                    $before = (float) $inv->current_stock;
                    $after = max(0, $before - $qty);
                    $inv->update(['current_stock' => $after]);
                    InventoryTransaction::create([
                        'business_id'       => $this->business_id,
                        'inventory_item_id' => $inv->id,
                        'type'              => 'deduction',
                        'quantity'          => $qty,
                        'quantity_before'   => $before,
                        'quantity_after'    => $after,
                        'notes'             => "Order #{$this->order_number}",
                        'user_id'           => auth()->id(),
                    ]);
                }
            }
        });
    }
}
