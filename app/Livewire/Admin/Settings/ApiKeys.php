<?php

namespace App\Livewire\Admin\Settings;

use App\Models\BusinessSetting;
use Livewire\Component;

class ApiKeys extends Component
{
    public string $default_google_maps_key = '';
    public string $default_sms_provider    = 'twilio';
    public string $default_sms_key         = '';
    public string $default_sms_sender_id   = '';

    // UI toggles for showing keys
    public bool $showMapsKey = false;
    public bool $showSmsKey  = false;

    private const KEYS = [
        'default_google_maps_key',
        'default_sms_provider',
        'default_sms_key',
        'default_sms_sender_id',
    ];

    public function mount(): void
    {
        foreach (self::KEYS as $key) {
            $setting = BusinessSetting::where('business_id', null)->where('key', $key)->first();
            $this->$key = $setting?->value ?? '';
        }
        if (! $this->default_sms_provider) {
            $this->default_sms_provider = 'twilio';
        }
    }

    protected function rules(): array
    {
        return [
            'default_google_maps_key' => 'nullable|string|max:300',
            'default_sms_provider'    => 'required|in:twilio,nexmo,custom',
            'default_sms_key'         => 'nullable|string|max:300',
            'default_sms_sender_id'   => 'nullable|string|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        foreach (self::KEYS as $key) {
            BusinessSetting::updateOrCreate(
                ['business_id' => null, 'key' => $key],
                ['value' => $this->$key, 'group' => 'platform']
            );
        }

        session()->flash('success', 'Platform API settings saved.');
    }

    public function maskedKey(string $key): string
    {
        if (strlen($key) <= 4) {
            return str_repeat('*', max(strlen($key), 8));
        }
        return str_repeat('*', strlen($key) - 4) . substr($key, -4);
    }

    public function render()
    {
        return view('livewire.admin.settings.api-keys')
            ->layout('layouts.admin', ['heading' => 'Platform API Keys']);
    }
}
