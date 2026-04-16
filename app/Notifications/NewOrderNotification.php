<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $currency = $order->business->currency ?? 'USD';
        $itemCount = $order->items()->count();

        return (new MailMessage)
            ->subject("New Order #{$order->order_number}")
            ->greeting("New {$this->orderTypeLabel()} Order!")
            ->line("Order **#{$order->order_number}** has been placed.")
            ->line("**Customer:** " . ($order->customer_name ?? 'Walk-in'))
            ->line("**Items:** {$itemCount} item(s)")
            ->line("**Total:** {$currency} " . number_format($order->total, 2))
            ->when($order->delivery_address, fn ($msg) => $msg->line("**Delivery to:** {$order->delivery_address}"))
            ->action('View Order', url("/app/orders/{$order->id}"))
            ->line('Please process this order promptly.');
    }

    private function orderTypeLabel(): string
    {
        return match ($this->order->order_type) {
            'dine_in'  => 'Dine-In',
            'takeaway'  => 'Takeaway',
            'delivery'  => 'Delivery',
            'online'    => 'Online',
            default     => ucfirst($this->order->order_type),
        };
    }
}
