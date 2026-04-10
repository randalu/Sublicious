<?php

namespace App\Livewire\App\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;

class BusinessInfo extends Component
{
    use WithFileUploads;

    public string $name        = '';
    public string $email       = '';
    public string $phone       = '';
    public string $address     = '';
    public string $currency    = 'USD';
    public string $timezone    = 'UTC';
    public $logo               = null;
    public ?string $existingLogo = null;

    public function mount(): void
    {
        $business = auth()->user()->business;

        $this->name         = $business->name ?? '';
        $this->email        = $business->email ?? '';
        $this->phone        = $business->phone ?? '';
        $this->address      = $business->address ?? '';
        $this->currency     = $business->currency ?? 'USD';
        $this->timezone     = $business->timezone ?? 'UTC';
        $this->existingLogo = $business->logo;
    }

    protected function rules(): array
    {
        return [
            'name'     => 'required|string|max:200',
            'email'    => 'nullable|email|max:200',
            'phone'    => 'nullable|string|max:30',
            'address'  => 'nullable|string|max:500',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:100',
            'logo'     => 'nullable|image|max:2048',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $business = auth()->user()->business;

        $data = [
            'name'     => trim($this->name),
            'email'    => trim($this->email) ?: null,
            'phone'    => trim($this->phone) ?: null,
            'address'  => trim($this->address) ?: null,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
        ];

        if ($this->logo) {
            $path = $this->logo->store('business-logos', 'public');
            $data['logo'] = $path;
            $this->existingLogo = $path;
        }

        $business->update($data);
        $this->logo = null;

        $this->dispatch('business-updated');
        session()->flash('success', 'Business information updated successfully.');
    }

    public function currencies(): array
    {
        return [
            'USD' => 'USD — US Dollar',
            'GBP' => 'GBP — British Pound',
            'EUR' => 'EUR — Euro',
            'LKR' => 'LKR — Sri Lankan Rupee',
            'INR' => 'INR — Indian Rupee',
            'AED' => 'AED — UAE Dirham',
            'SAR' => 'SAR — Saudi Riyal',
            'AUD' => 'AUD — Australian Dollar',
            'CAD' => 'CAD — Canadian Dollar',
            'SGD' => 'SGD — Singapore Dollar',
            'MYR' => 'MYR — Malaysian Ringgit',
            'PKR' => 'PKR — Pakistani Rupee',
            'BDT' => 'BDT — Bangladeshi Taka',
            'QAR' => 'QAR — Qatari Riyal',
            'KWD' => 'KWD — Kuwaiti Dinar',
        ];
    }

    public function timezones(): array
    {
        return [
            'UTC'                    => 'UTC',
            'America/New_York'       => 'Eastern Time (US & Canada)',
            'America/Chicago'        => 'Central Time (US & Canada)',
            'America/Denver'         => 'Mountain Time (US & Canada)',
            'America/Los_Angeles'    => 'Pacific Time (US & Canada)',
            'America/Toronto'        => 'Toronto',
            'America/Vancouver'      => 'Vancouver',
            'America/Sao_Paulo'      => 'Brasilia',
            'Europe/London'          => 'London',
            'Europe/Paris'           => 'Paris',
            'Europe/Berlin'          => 'Berlin',
            'Europe/Amsterdam'       => 'Amsterdam',
            'Europe/Istanbul'        => 'Istanbul',
            'Asia/Dubai'             => 'Dubai',
            'Asia/Riyadh'            => 'Riyadh',
            'Asia/Kolkata'           => 'Mumbai, Kolkata',
            'Asia/Colombo'           => 'Sri Jayawardenepura',
            'Asia/Dhaka'             => 'Dhaka',
            'Asia/Karachi'           => 'Karachi',
            'Asia/Singapore'         => 'Singapore',
            'Asia/Kuala_Lumpur'      => 'Kuala Lumpur',
            'Asia/Bangkok'           => 'Bangkok',
            'Asia/Tokyo'             => 'Tokyo',
            'Asia/Shanghai'          => 'Beijing, Shanghai',
            'Australia/Sydney'       => 'Sydney',
            'Australia/Melbourne'    => 'Melbourne',
            'Pacific/Auckland'       => 'Auckland',
            'Africa/Cairo'           => 'Cairo',
            'Africa/Johannesburg'    => 'Johannesburg',
        ];
    }

    public function render()
    {
        return view('livewire.app.settings.business-info', [
            'currencies' => $this->currencies(),
            'timezones'  => $this->timezones(),
        ])->layout('layouts.app', ['heading' => 'Business Info']);
    }
}
