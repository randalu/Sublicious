<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;

class NotificationSettings extends Component
{
    public bool $notify_new_order_sms           = false;
    public bool $notify_order_accepted_sms      = false;
    public bool $notify_delivery_dispatched_sms = false;
    public bool $notify_delivery_delivered_sms  = false;
    public bool $notify_new_order_email         = false;
    public bool $notify_low_stock_email         = false;

    private const KEYS = [
        'notify_new_order_sms',
        'notify_order_accepted_sms',
        'notify_delivery_dispatched_sms',
        'notify_delivery_delivered_sms',
        'notify_new_order_email',
        'notify_low_stock_email',
    ];

    public function mount(): void
    {
        $business = auth()->user()->business;

        foreach (self::KEYS as $key) {
            $this->$key = (bool) $business->getSetting($key, false);
        }
    }

    protected function rules(): array
    {
        $rules = [];
        foreach (self::KEYS as $key) {
            $rules[$key] = 'boolean';
        }
        return $rules;
    }

    public function save(): void
    {
        $this->validate();

        $business = auth()->user()->business;

        foreach (self::KEYS as $key) {
            $business->setSetting($key, (int) $this->$key, 'notifications');
        }

        session()->flash('success', 'Notification settings saved.');
    }

    public function render()
    {
        return view('livewire.app.settings.notification-settings')
            ->layout('layouts.app', ['heading' => 'Notification Settings']);
    }
}
