<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;

class BillingCharges extends Component
{
    public bool   $service_charge_enabled    = false;
    public string $service_charge_type       = 'percentage';
    public string $service_charge_value      = '0';
    public string $service_charge_applies_to = 'all';

    public function mount(): void
    {
        $business = auth()->user()->business;

        $this->service_charge_enabled    = (bool) $business->getSetting('service_charge_enabled', false);
        $this->service_charge_type       = $business->getSetting('service_charge_type', 'percentage');
        $this->service_charge_value      = (string) $business->getSetting('service_charge_value', '0');
        $this->service_charge_applies_to = $business->getSetting('service_charge_applies_to', 'all');
    }

    protected function rules(): array
    {
        return [
            'service_charge_enabled'    => 'boolean',
            'service_charge_type'       => 'required|in:percentage,fixed',
            'service_charge_value'      => 'required|numeric|min:0',
            'service_charge_applies_to' => 'required|in:all,dine_in_only',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $business = auth()->user()->business;
        $business->setSetting('service_charge_enabled',    (int) $this->service_charge_enabled, 'billing');
        $business->setSetting('service_charge_type',       $this->service_charge_type,           'billing');
        $business->setSetting('service_charge_value',      $this->service_charge_value,           'billing');
        $business->setSetting('service_charge_applies_to', $this->service_charge_applies_to,     'billing');

        session()->flash('success', 'Billing charges updated successfully.');
    }

    public function getPreviewProperty(): string
    {
        $base = 100;
        $value = (float) $this->service_charge_value;
        if ($this->service_charge_type === 'percentage') {
            $charge = round($base * $value / 100, 2);
            return "On a \$100 order: service charge = \${$charge} (" . $value . "%)";
        } else {
            return "On any order: service charge = \${$value} (flat fee)";
        }
    }

    public function render()
    {
        return view('livewire.app.settings.billing-charges')
            ->layout('layouts.app', ['heading' => 'Billing & Charges']);
    }
}
