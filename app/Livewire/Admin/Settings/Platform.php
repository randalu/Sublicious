<?php

namespace App\Livewire\Admin\Settings;

use App\Models\BusinessSetting;
use Livewire\Component;

class Platform extends Component
{
    public string $platform_name      = '';
    public string $platform_logo      = '';
    public string $default_currency   = 'USD';
    public string $default_timezone   = 'UTC';
    public bool   $allow_registrations = true;
    public bool   $maintenance_mode    = false;
    public string $support_email      = '';
    public string $support_phone      = '';

    private const KEYS = [
        'platform_name',
        'platform_logo',
        'default_currency',
        'default_timezone',
        'allow_registrations',
        'maintenance_mode',
        'support_email',
        'support_phone',
    ];

    private const BOOLEAN_KEYS = [
        'allow_registrations',
        'maintenance_mode',
    ];

    public function mount(): void
    {
        foreach (self::KEYS as $key) {
            $setting = BusinessSetting::where('business_id', null)->where('key', $key)->first();
            if ($setting) {
                if (in_array($key, self::BOOLEAN_KEYS)) {
                    $this->$key = (bool) $setting->value;
                } else {
                    $this->$key = $setting->value ?? '';
                }
            }
        }
    }

    protected function rules(): array
    {
        return [
            'platform_name'       => 'required|string|max:200',
            'platform_logo'       => 'nullable|url|max:500',
            'default_currency'    => 'required|in:USD,LKR,EUR,GBP',
            'default_timezone'    => 'required|string|max:100',
            'allow_registrations' => 'boolean',
            'maintenance_mode'    => 'boolean',
            'support_email'       => 'nullable|email|max:200',
            'support_phone'       => 'nullable|string|max:50',
        ];
    }

    public function save(): void
    {
        $this->validate();

        foreach (self::KEYS as $key) {
            $value = in_array($key, self::BOOLEAN_KEYS)
                ? ($this->$key ? '1' : '0')
                : $this->$key;

            BusinessSetting::updateOrCreate(
                ['business_id' => null, 'key' => $key],
                ['value' => $value, 'group' => 'platform']
            );
        }

        session()->flash('success', 'Platform settings saved.');
    }

    public function render()
    {
        return view('livewire.admin.settings.platform')
            ->layout('layouts.admin', ['heading' => 'Platform Settings']);
    }
}
