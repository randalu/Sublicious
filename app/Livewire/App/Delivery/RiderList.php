<?php

namespace App\Livewire\App\Delivery;

use App\Models\DeliveryRider;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class RiderList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public bool   $showForm       = false;
    public ?int   $editingId      = null;
    public string $name           = '';
    public string $phone          = '';
    public string $vehicleType    = '';
    public string $vehicleNumber  = '';
    public string $commissionType = 'per_delivery';
    public string $commissionValue = '0.00';
    public bool   $isActive       = true;

    public function updatedSearch(): void { $this->resetPage(); }

    protected function rules(): array
    {
        return [
            'name'            => 'required|string|max:150',
            'phone'           => 'required|string|max:30',
            'vehicleType'     => 'nullable|string|max:50',
            'vehicleNumber'   => 'nullable|string|max:50',
            'commissionType'  => 'required|in:per_delivery,percentage',
            'commissionValue' => 'required|numeric|min:0',
            'isActive'        => 'boolean',
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        $this->showForm  = true;
        $this->editingId = $id;

        if ($id) {
            $r = DeliveryRider::findOrFail($id);
            $this->name            = $r->name;
            $this->phone           = $r->phone;
            $this->vehicleType     = $r->vehicle_type ?? '';
            $this->vehicleNumber   = $r->vehicle_number ?? '';
            $this->commissionType  = $r->commission_type;
            $this->commissionValue = (string) $r->commission_value;
            $this->isActive        = $r->is_active;
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
        $this->name = $this->phone = $this->vehicleType = $this->vehicleNumber = '';
        $this->commissionType  = 'per_delivery';
        $this->commissionValue = '0.00';
        $this->isActive        = true;
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $data = [
            'name'             => trim($this->name),
            'phone'            => trim($this->phone),
            'vehicle_type'     => trim($this->vehicleType) ?: null,
            'vehicle_number'   => trim($this->vehicleNumber) ?: null,
            'commission_type'  => $this->commissionType,
            'commission_value' => $this->commissionValue,
            'is_active'        => $this->isActive,
        ];

        if ($this->editingId) {
            DeliveryRider::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Rider updated.');
        } else {
            DeliveryRider::create($data);
            session()->flash('success', 'Rider added.');
        }

        $this->closeForm();
    }

    public function toggleActive(int $id): void
    {
        $rider = DeliveryRider::findOrFail($id);
        $rider->update(['is_active' => ! $rider->is_active]);
    }

    public function delete(int $id): void
    {
        DeliveryRider::findOrFail($id)->delete();
        session()->flash('success', 'Rider deleted.');
    }

    public function render()
    {
        $riders = DeliveryRider::when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%")
                      ->orWhere('vehicle_number', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.app.delivery.rider-list', compact('riders'))
            ->layout('layouts.app', ['heading' => 'Delivery Riders']);
    }
}
