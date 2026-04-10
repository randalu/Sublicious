<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use Livewire\Component;

class PlanForm extends Component
{
    public ?int   $planId          = null;
    public string $name            = '';
    public string $slug            = '';
    public string $description     = '';
    public string $price_monthly   = '0';
    public string $price_yearly    = '0';
    public string $max_orders_per_month = '500';
    public string $max_staff       = '10';
    public string $max_menu_items  = '100';
    public bool   $is_default      = false;
    public bool   $is_active       = true;

    // Features (JSON checkboxes)
    public bool $feature_delivery          = false;
    public bool $feature_hr_module         = false;
    public bool $feature_sms_notifications = false;
    public bool $feature_export            = false;
    public bool $feature_api_integrations  = false;

    public function mount(?Plan $plan = null): void
    {
        if ($plan && $plan->exists) {
            $this->planId              = $plan->id;
            $this->name                = $plan->name;
            $this->slug                = $plan->slug;
            $this->description         = $plan->description ?? '';
            $this->price_monthly       = (string) $plan->price_monthly;
            $this->price_yearly        = (string) $plan->price_yearly;
            $this->max_orders_per_month = (string) $plan->max_orders_per_month;
            $this->max_staff           = (string) $plan->max_staff;
            $this->max_menu_items      = (string) $plan->max_menu_items;
            $this->is_default          = (bool) $plan->is_default;
            $this->is_active           = (bool) $plan->is_active;

            $features = $plan->features ?? [];
            $this->feature_delivery          = (bool) ($features['delivery'] ?? false);
            $this->feature_hr_module         = (bool) ($features['hr_module'] ?? false);
            $this->feature_sms_notifications = (bool) ($features['sms_notifications'] ?? false);
            $this->feature_export            = (bool) ($features['export'] ?? false);
            $this->feature_api_integrations  = (bool) ($features['api_integrations'] ?? false);
        }
    }

    protected function rules(): array
    {
        $slugRule = 'required|string|max:100|unique:plans,slug';
        if ($this->planId) {
            $slugRule .= ',' . $this->planId;
        }
        return [
            'name'               => 'required|string|max:100',
            'slug'               => $slugRule,
            'description'        => 'nullable|string|max:500',
            'price_monthly'      => 'required|integer|min:0',
            'price_yearly'       => 'required|integer|min:0',
            'max_orders_per_month' => 'required|integer|min:1',
            'max_staff'          => 'required|integer|min:1',
            'max_menu_items'     => 'required|integer|min:1',
            'is_default'         => 'boolean',
            'is_active'          => 'boolean',
        ];
    }

    public function updatedName(): void
    {
        if (! $this->planId) {
            $this->slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($this->name)));
        }
    }

    public function save(): void
    {
        $this->validate();

        $features = [
            'delivery'          => $this->feature_delivery,
            'hr_module'         => $this->feature_hr_module,
            'sms_notifications' => $this->feature_sms_notifications,
            'export'            => $this->feature_export,
            'api_integrations'  => $this->feature_api_integrations,
        ];

        $data = [
            'name'               => trim($this->name),
            'slug'               => trim($this->slug),
            'description'        => trim($this->description) ?: null,
            'price_monthly'      => (int) $this->price_monthly,
            'price_yearly'       => (int) $this->price_yearly,
            'max_orders_per_month' => (int) $this->max_orders_per_month,
            'max_staff'          => (int) $this->max_staff,
            'max_menu_items'     => (int) $this->max_menu_items,
            'is_default'         => $this->is_default,
            'is_active'          => $this->is_active,
            'features'           => $features,
        ];

        if ($this->is_default) {
            Plan::where('is_default', true)->update(['is_default' => false]);
        }

        if ($this->planId) {
            Plan::findOrFail($this->planId)->update($data);
            session()->flash('success', 'Plan updated.');
        } else {
            Plan::create($data);
            session()->flash('success', 'Plan created.');
        }

        $this->redirect(route('admin.plans'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.plans.plan-form')
            ->layout('layouts.admin', ['heading' => $this->planId ? 'Edit Plan' : 'New Plan']);
    }
}
