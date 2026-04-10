<?php

namespace App\Livewire\App\Delivery;

use App\Models\DeliveryZone;
use Livewire\Component;

class ZoneManager extends Component
{
    public bool   $showForm         = false;
    public ?int   $editingId        = null;
    public string $name             = '';
    public string $areaDescription  = '';
    public string $deliveryFee      = '0.00';
    public string $minimumOrder     = '0.00';
    public int    $estimatedMinutes = 30;
    public bool   $isActive         = true;

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:150',
            'areaDescription'  => 'nullable|string|max:500',
            'deliveryFee'      => 'required|numeric|min:0',
            'minimumOrder'     => 'required|numeric|min:0',
            'estimatedMinutes' => 'required|integer|min:1|max:999',
            'isActive'         => 'boolean',
        ];
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        $this->showForm  = true;
        $this->editingId = $id;

        if ($id) {
            $z = DeliveryZone::findOrFail($id);
            $this->name             = $z->name;
            $this->areaDescription  = is_array($z->polygon) ? ($z->polygon['description'] ?? '') : '';
            $this->deliveryFee      = (string) $z->delivery_fee;
            $this->minimumOrder     = (string) $z->minimum_order_amount;
            $this->estimatedMinutes = $z->estimated_minutes ?? 30;
            $this->isActive         = $z->is_active;
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
        $this->name = $this->areaDescription = '';
        $this->deliveryFee      = '0.00';
        $this->minimumOrder     = '0.00';
        $this->estimatedMinutes = 30;
        $this->isActive         = true;
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $data = [
            'name'                  => trim($this->name),
            'polygon'               => $this->areaDescription ? ['description' => trim($this->areaDescription)] : null,
            'delivery_fee'          => $this->deliveryFee,
            'minimum_order_amount'  => $this->minimumOrder,
            'estimated_minutes'     => $this->estimatedMinutes,
            'is_active'             => $this->isActive,
        ];

        if ($this->editingId) {
            DeliveryZone::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Zone updated.');
        } else {
            DeliveryZone::create($data);
            session()->flash('success', 'Zone created.');
        }

        $this->closeForm();
    }

    public function toggleActive(int $id): void
    {
        $zone = DeliveryZone::findOrFail($id);
        $zone->update(['is_active' => ! $zone->is_active]);
    }

    public function delete(int $id): void
    {
        DeliveryZone::findOrFail($id)->delete();
        session()->flash('success', 'Zone deleted.');
    }

    public function render()
    {
        $zones = DeliveryZone::orderBy('name')->get();

        return view('livewire.app.delivery.zone-manager', compact('zones'))
            ->layout('layouts.app', ['heading' => 'Delivery Zones']);
    }
}
