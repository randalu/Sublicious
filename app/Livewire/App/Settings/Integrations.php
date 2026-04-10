<?php

namespace App\Livewire\App\Settings;

use App\Services\SmsService;
use Livewire\Component;

class Integrations extends Component
{
    // Google Maps
    public string $google_maps_key = '';

    // SMS (SMSlenz)
    public string $sms_user_id   = '';
    public string $sms_api_key   = '';
    public string $sms_sender_id = '';
    public string $sms_base_url  = 'https://smslenz.lk/api';

    // Test SMS
    public string $testPhone = '';

    // UI state
    public bool $showMapsKey = false;
    public bool $showSmsKey  = false;

    public function mount(): void
    {
        $business = auth()->user()->business;

        $this->google_maps_key = $business->getSetting('google_maps_key', '');
        $this->sms_user_id    = $business->getSetting('sms_user_id', '');
        $this->sms_api_key    = $business->getSetting('sms_api_key', '');
        $this->sms_sender_id  = $business->getSetting('sms_sender_id', '');
        $this->sms_base_url   = $business->getSetting('sms_base_url', 'https://smslenz.lk/api');
    }

    protected function rules(): array
    {
        return [
            'google_maps_key' => 'nullable|string|max:200',
            'sms_user_id'     => 'nullable|string|max:100',
            'sms_api_key'     => 'nullable|string|max:300',
            'sms_sender_id'   => 'nullable|string|max:50',
            'sms_base_url'    => 'nullable|url|max:300',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $business = auth()->user()->business;
        $business->setSetting('google_maps_key', $this->google_maps_key, 'integrations');
        $business->setSetting('sms_user_id',     $this->sms_user_id,     'integrations');
        $business->setSetting('sms_api_key',     $this->sms_api_key,     'integrations');
        $business->setSetting('sms_sender_id',   $this->sms_sender_id,   'integrations');
        $business->setSetting('sms_base_url',    $this->sms_base_url,    'integrations');

        session()->flash('success', 'Integration settings saved.');
    }

    public function testSms(): void
    {
        $phone = trim($this->testPhone);
        if (! $phone) {
            $phone = auth()->user()->business->phone;
        }
        if (! $phone) {
            session()->flash('error', 'Enter a phone number or set a business phone first.');
            return;
        }

        $business = auth()->user()->business;
        $sent = app(SmsService::class)->sendTest($phone, $business->id);

        if ($sent) {
            session()->flash('success', 'Test SMS sent to ' . $phone);
        } else {
            session()->flash('error', 'Failed to send SMS. Check your API credentials.');
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
