<?php

namespace App\Livewire\App\Orders;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class OrderDetail extends Component
{
    public Order $order;

    // Cancel modal
    public bool   $showCancelModal = false;
    public string $cancelReason    = '';

    // Payment modal
    public bool   $showPayModal    = false;
    public string $paymentMethod   = 'cash';
    public string $amountPaid      = '0.00';

    public function mount(Order $order): void
    {
        $this->order = $order;
    }

    public function advanceStatus(): void
    {
        $next = $this->order->nextStatus();
        if ($next) {
            $this->order->update(['status' => $next]);
            $this->order->refresh();
            session()->flash('success', 'Status updated to ' . ucfirst($next) . '.');
        }
    }

    public function openCancelModal(): void
    {
        $this->cancelReason   = '';
        $this->showCancelModal = true;
    }

    public function confirmCancel(): void
    {
        $this->validate(['cancelReason' => 'required|string|min:3|max:255']);

        $this->order->update([
            'status'        => 'cancelled',
            'cancel_reason' => $this->cancelReason,
        ]);

        $this->order->refresh();
        $this->showCancelModal = false;
        session()->flash('success', 'Order cancelled.');
    }

    public function openPayModal(): void
    {
        $this->amountPaid  = (string) $this->order->total;
        $this->paymentMethod = 'cash';
        $this->showPayModal = true;
    }

    public function markPaid(): void
    {
        $this->validate([
            'paymentMethod' => 'required|in:cash,card,online',
            'amountPaid'    => 'required|numeric|min:0',
        ]);

        DB::transaction(function () {
            $this->order->update([
                'payment_method' => $this->paymentMethod,
                'payment_status' => 'paid',
                'amount_paid'    => $this->amountPaid,
                'change_amount'  => max(0, (float) $this->amountPaid - (float) $this->order->total),
                'status'         => 'completed',
            ]);

            // Create bill if not already exists
            if (! $this->order->bill) {
                $bill = Bill::create([
                    'order_id'        => $this->order->id,
                    'bill_number'     => 'B-' . strtoupper(Str::random(6)),
                    'customer_name'   => $this->order->customer_name
                        ?? ($this->order->table ? 'Table ' . $this->order->table->table_number : 'Walk-in'),
                    'table_id'        => $this->order->table_id,
                    'subtotal'        => $this->order->subtotal,
                    'service_charge'  => $this->order->service_charge,
                    'discount_amount' => $this->order->discount_amount ?? 0,
                    'total'           => $this->order->total,
                    'payment_method'  => $this->paymentMethod,
                    'payment_status'  => 'paid',
                    'paid_at'         => now(),
                    'created_by'      => auth()->id(),
                ]);

                foreach ($this->order->items as $oi) {
                    BillItem::create([
                        'bill_id'     => $bill->id,
                        'description' => $oi->name . ($oi->variant_name ? ' (' . $oi->variant_name . ')' : ''),
                        'quantity'    => $oi->quantity,
                        'unit_price'  => $oi->unit_price,
                        'total'       => $oi->subtotal,
                    ]);
                }

                // Release table if dine_in
                if ($this->order->table_id && $this->order->table) {
                    $this->order->table->update(['status' => 'available']);
                }
            }
        });

        $this->order->refresh();
        $this->showPayModal = false;
        session()->flash('success', 'Payment recorded and bill generated.');
    }

    public function render()
    {
        $this->order->load(['items.addons', 'table', 'customer', 'delivery.rider', 'bill']);

        return view('livewire.app.orders.order-detail')
            ->layout('layouts.app', ['heading' => 'Order ' . $this->order->order_number]);
    }
}
