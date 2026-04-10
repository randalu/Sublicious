<?php

namespace App\Livewire\Admin\Plans;

use App\Models\Plan;
use Livewire\Component;

class PlanList extends Component
{
    public bool   $showForm     = false;
    public ?int   $editingId    = null;

    // Form fields
    public string $name              = '';
    public string $slug              = '';
    public string $description       = '';
    public string $price_monthly     = '0';
    public string $price_yearly      = '0';
    public string $max_orders_per_month = '500';
    public string $max_staff         = '10';
    public string $max_menu_items    = '100';
    public bool   $is_default        = false;
    public bool   $is_active         = true;

    protected function rules(): array
    {
        $slugRule = 'required|string|max:100|unique:plans,slug';
        if ($this->editingId) {
            $slugRule .= ',' . $this->editingId;
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
        if (! $this->editingId) {
            $this->slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($this->name)));
        }
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        $this->showForm  = true;
        $this->editingId = $id;

        if ($id) {
            $plan = Plan::findOrFail($id);
            $this->name               = $plan->name;
            $this->slug               = $plan->slug;
            $this->description        = $plan->description ?? '';
            $this->price_monthly      = (string) $plan->price_monthly;
            $this->price_yearly       = (string) $plan->price_yearly;
            $this->max_orders_per_month = (string) $plan->max_orders_per_month;
            $this->max_staff          = (string) $plan->max_staff;
            $this->max_menu_items     = (string) $plan->max_menu_items;
            $this->is_default         = (bool) $plan->is_default;
            $this->is_active          = (bool) $plan->is_active;
        }
    }

    public function closeForm(): void
    {
        $this->showForm  = false;
        $this->editingId = null;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = $this->slug = $this->description = '';
        $this->price_monthly = $this->price_yearly = '0';
        $this->max_orders_per_month = '500';
        $this->max_staff = '10';
        $this->max_menu_items = '100';
        $this->is_default = false;
        $this->is_active  = true;
    }

    public function save(): void
    {
        $this->validate();

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
        ];

        if ($this->is_default) {
            Plan::where('is_default', true)->update(['is_default' => false]);
        }

        if ($this->editingId) {
            Plan::findOrFail($this->editingId)->update($data);
            $msg = 'Plan updated.';
        } else {
            Plan::create($data);
            $msg = 'Plan created.';
        }

        $this->closeForm();
        session()->flash('success', $msg);
    }

    public function toggleDefault(int $id): void
    {
        Plan::where('is_default', true)->update(['is_default' => false]);
        Plan::findOrFail($id)->update(['is_default' => true]);
    }

    public function deletePlan(int $id): void
    {
        $plan = Plan::withCount('businesses')->findOrFail($id);
        if ($plan->businesses_count > 0) {
            session()->flash('error', 'Cannot delete a plan that has businesses assigned to it.');
            return;
        }
        $plan->delete();
        session()->flash('success', 'Plan deleted.');
    }

    public function render()
    {
        $plans = Plan::withCount('businesses')->orderBy('price_monthly')->get();

        return view('livewire.admin.plans.plan-list', compact('plans'))
            ->layout('layouts.admin', ['heading' => 'Plans']);
    }
}
