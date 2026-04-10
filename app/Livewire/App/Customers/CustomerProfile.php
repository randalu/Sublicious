<?php

namespace App\Livewire\App\Customers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Livewire\Component;

class CustomerProfile extends Component
{
    public Customer $customer;

    // Address form
    public bool   $showAddressForm = false;
    public ?int   $editingAddressId = null;
    public string $label            = '';
    public string $addressLine1     = '';
    public string $city             = '';
    public bool   $isDefault        = false;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }

    protected function addressRules(): array
    {
        return [
            'label'       => 'nullable|string|max:50',
            'addressLine1'=> 'required|string|max:300',
            'city'        => 'nullable|string|max:100',
        ];
    }

    public function openAddressForm(?int $id = null): void
    {
        $this->resetAddressForm();
        $this->showAddressForm  = true;
        $this->editingAddressId = $id;

        if ($id) {
            $a = CustomerAddress::findOrFail($id);
            $this->label       = $a->label ?? '';
            $this->addressLine1 = $a->address_line_1;
            $this->city        = $a->city ?? '';
            $this->isDefault   = $a->is_default;
        }
    }

    public function closeAddressForm(): void
    {
        $this->showAddressForm  = false;
        $this->editingAddressId = null;
        $this->resetAddressForm();
    }

    private function resetAddressForm(): void
    {
        $this->label = $this->addressLine1 = $this->city = '';
        $this->isDefault = false;
    }

    public function saveAddress(): void
    {
        $this->validate($this->addressRules());

        if ($this->isDefault) {
            $this->customer->addresses()->update(['is_default' => false]);
        }

        $data = [
            'label'          => $this->label ?: null,
            'address_line_1' => $this->addressLine1,
            'city'           => $this->city ?: null,
            'is_default'     => $this->isDefault,
            'business_id'    => $this->customer->business_id,
        ];

        if ($this->editingAddressId) {
            CustomerAddress::findOrFail($this->editingAddressId)->update($data);
        } else {
            $this->customer->addresses()->create($data);
        }

        $this->closeAddressForm();
    }

    public function deleteAddress(int $id): void
    {
        CustomerAddress::findOrFail($id)->delete();
    }

    public function setDefault(int $id): void
    {
        $this->customer->addresses()->update(['is_default' => false]);
        CustomerAddress::findOrFail($id)->update(['is_default' => true]);
    }

    public function render()
    {
        $this->customer->load(['addresses', 'orders' => fn ($q) => $q->with('items')->orderByDesc('created_at')->limit(20)]);

        return view('livewire.app.customers.customer-profile')
            ->layout('layouts.app', ['heading' => $this->customer->name]);
    }
}
