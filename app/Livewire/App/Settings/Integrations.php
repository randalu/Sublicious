<?php

namespace App\Livewire\App\Settings;

use App\Services\SmsService;
use Livewire\Component;

class Integrations extends Component
{
    // Google Maps
    public string $google_maps_key = '';

    // SMS
    public string $sms_gateway_provider = 'twilio';
    public string $sms_gateway_key      = '';
    public string $sms_sender_id        = '';

    // UI state
    public bool $showMapsKey = false;
    public bool $showSmsKey  = false;

    public function mount(): void
    {
        $business = auth()->user()->business;

        $this->google_maps_key       = $business->getSetting('google_maps_key', '');
        $this->sms_gateway_provider  = $business->getSetting('sms_gateway_provider', 'twilio');
        $this->sms_gateway_key       = $business->getSetting('sms_gateway_key', '');
        $this->sms_sender_id         = $business->getSetting('sms_sender_id', '');
    }

    protected function rules(): array
    {
        return [
            'google_maps_key'      => 'nullable|string|max:200',
            'sms_gateway_provider' => 'required|in:twilio,nexmo,custom',
            'sms_gateway_key'      => 'nullable|string|max:300',
            'sms_sender_id'        => 'nullable|string|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $business = auth()->user()->business;
        $business->setSetting('google_maps_key',      $this->google_maps_key,      'integrations');
        $business->setSetting('sms_gateway_provider', $this->sms_gateway_provider, 'integrations');
        $business->setSetting('sms_gateway_key',      $this->sms_gateway_key,      'integrations');
        $business->setSetting('sms_sender_id',        $this->sms_sender_id,        'integrations');

        session()->flash('success', 'Integration settings saved.');
    }

    public function testSms(): void
    {
        $business = auth()->user()->business;
        if (! $business->phone) {
            session()->flash('error', 'No business phone number configured.');
            return;
        }

        try {
            app(SmsService::class)->send($business->phone, 'Test SMS from Sublicious');
            session()->flash('success', 'Test SMS sent to ' . $business->phone);
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to send SMS: ' . $e->getMessage());
        }
    }

    public function maskedKey(string $key): string
    {
        if (strlen($key) <= 4) {
            return str_repeat('*', strlen($key));
        }
        return str_repeat('*', strlen($key) - 4) . substr($key, -4);
    }

    public function render()
    {
        return view('livewire.app.settings.integrations')
            ->layout('layouts.app', ['heading' => 'Integrations']);
    }
}
