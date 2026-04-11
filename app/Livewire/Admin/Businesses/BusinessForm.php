<?php

namespace App\Livewire\Admin\Businesses;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class BusinessForm extends Component
{
    public ?int $businessId = null;

    public string $name     = '';
    public string $slug     = '';
    public string $email    = '';
    public string $phone    = '';
    public string $address  = '';
    public string $city     = '';
    public string $state    = '';
    public string $country  = '';
    public string $currency = 'USD';
    public string $timezone = 'UTC';
    public string $plan_id  = '';
    public bool   $is_active   = true;
    public bool   $is_verified = false;

    public string $saveError = '';

    public function mount(?Business $business = null): void
    {
        if ($business && $business->exists) {
            $this->businessId  = $business->id;
            $this->name        = $business->name;
            $this->slug        = $business->slug;
            $this->email       = $business->email ?? '';
            $this->phone       = $business->phone ?? '';
            $this->address     = $business->address ?? '';
            $this->city        = $business->city ?? '';
            $this->state       = $business->state ?? '';
            $this->country     = $business->country ?? '';
            $this->currency    = $business->currency ?? 'USD';
            $this->timezone    = $business->timezone ?? 'UTC';
            $this->plan_id     = (string) ($business->plan_id ?? '');
            $this->is_active   = (bool) $business->is_active;
            $this->is_verified = (bool) $business->is_verified;
        }
    }

    protected function rules(): array
    {
        $slugRule = 'required|string|max:100|unique:businesses,slug';
        if ($this->businessId) {
            $slugRule .= ',' . $this->businessId;
        }

        $emailRule = 'required|email|max:200|unique:businesses,email';
        if ($this->businessId) {
            $emailRule .= ',' . $this->businessId;
        }

        return [
            'name'        => 'required|string|max:200',
            'slug'        => $slugRule,
            'email'       => $emailRule,
            'phone'       => 'nullable|string|max:50',
            'address'     => 'nullable|string|max:500',
            'city'        => 'nullable|string|max:100',
            'state'       => 'nullable|string|max:100',
            'country'     => 'nullable|string|max:100',
            'currency'    => 'required|in:USD,LKR,EUR,GBP',
            'timezone'    => 'required|string|max:100',
            'plan_id'     => 'required|exists:plans,id',
            'is_active'   => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    public function updatedName(): void
    {
        if (! $this->businessId) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function save(): void
    {
        $this->saveError = '';
        $this->validate();

        $data = [
            'name'        => trim($this->name),
            'slug'        => trim($this->slug),
            'email'       => trim($this->email),
            'phone'       => trim($this->phone) ?: null,
            'address'     => trim($this->address) ?: null,
            'city'        => trim($this->city) ?: null,
            'state'       => trim($this->state) ?: null,
            'country'     => trim($this->country) ?: null,
            'currency'    => $this->currency,
            'timezone'    => $this->timezone,
            'plan_id'     => (int) $this->plan_id,
            'is_active'   => $this->is_active,
            'is_verified' => $this->is_verified,
        ];

        try {
            if ($this->businessId) {
                DB::transaction(function () use ($data) {
                    $business = Business::findOrFail($this->businessId);
                    $old = $business->only(array_keys($data));
                    $business->update($data);
                    AuditLog::record('business_updated', null, Business::class, $business->id, $old, $data);
                });
                session()->flash('success', 'Business updated successfully.');
            } else {
                $tempPassword = Str::random(12);
                $businessEmail = $data['email'];

                DB::transaction(function () use ($data, $tempPassword) {
                    $data['subscription_status'] = 'active';
                    $business = Business::create($data);

                    User::create([
                        'business_id'       => $business->id,
                        'name'              => $business->name . ' Admin',
                        'email'             => $business->email,
                        'password'          => $tempPassword,
                        'role'              => 'admin',
                        'is_active'         => true,
                        'email_verified_at' => now(),
                    ]);

                    AuditLog::record('business_created', null, Business::class, $business->id, [], $data);
                });

                session()->flash('success', "Business created. Admin login: {$businessEmail} / {$tempPassword}");
            }
        } catch (\Throwable $e) {
            $this->saveError = 'Could not save: ' . $e->getMessage();
            return;
        }

        $this->redirect(route('admin.businesses'), navigate: false);
    }

    public function render()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('livewire.admin.businesses.business-form', compact('plans'))
            ->layout('layouts.admin', ['heading' => $this->businessId ? 'Edit Business' : 'New Business']);
    }
}
