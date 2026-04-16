<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Collection $items) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->items->count();
        $lines = $this->items->map(fn ($item) =>
            "- **{$item->name}**: {$item->current_stock} {$item->unit} remaining (threshold: {$item->low_stock_threshold})"
        );

        $message = (new MailMessage)
            ->subject("{$count} inventory item(s) low on stock")
            ->greeting('Low Stock Alert')
            ->line("The following inventory items are at or below their low-stock threshold:");

        foreach ($lines as $line) {
            $message->line($line);
        }

        return $message
            ->action('View Inventory', url('/app/inventory?stockFilter=low'))
            ->line('Please restock these items soon.');
    }
}
