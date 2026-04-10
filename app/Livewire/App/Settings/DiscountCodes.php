<?php

namespace App\Livewire\App\Settings;

use App\Models\DiscountCode;
use Livewire\Component;
use Livewire\WithPagination;

class DiscountCodes extends Component
{
    use WithPagination;

    public bool    $showForm      = false;
    public ?int    $editingId     = null;
    public bool    $confirmDelete = false;
    public ?int    $deleteId      = null;

    // Form fields
    public string  $code              = '';
    public string  $name              = '';
    public string  $type              = 'percentage';
    public string  $value             = '';
    public string  $min_order_amount  = '';
    public string  $usage_limit       = '';
    public bool    $is_active         = true;
    public string  $valid_from        = '';
    public string  $valid_until       = '';

    protected function rules(): array
    {
        return [
            'code'             => 'required|string|max:50',
            'name'             => 'required|string|max:200',
            'type'             => 'required|in:percentage,fixed',
            'value'            => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'is_active'        => 'boolean',
            'valid_from'       => 'nullable|date',
            'valid_until'      => 'nullable|date|after_or_equal:valid_from',
        ];
    }

    public function updatedCode(): void
    {
        $this->code = strtoupper($this->code);
    }

    public function openForm(?int $id = null): void
    {
        $this->resetForm();
        $this->showForm  = true;
        $this->editingId = $id;

        if ($id) {
            $discount = DiscountCode::findOrFail($id);
            $this->code             = $discount->code;
            $this->name             = $discount->name ?? '';
            $this->type             = $discount->type;
            $this->value            = (string) $discount->value;
            $this->min_order_amount = $discount->min_order_amount ? (string) $discount->min_order_amount : '';
            $this->usage_limit      = $discount->usage_limit ? (string) $discount->usage_limit : '';
            $this->is_active        = (bool) $discount->is_active;
            $this->valid_from       = $discount->valid_from?->format('Y-m-d') ?? '';
            $this->valid_until      = $discount->valid_until?->format('Y-m-d') ?? '';
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
        $this->code = $this->name = $this->value = $this->min_order_amount = $this->usage_limit = '';
        $this->valid_from = $this->valid_until = '';
        $this->type      = 'percentage';
        $this->is_active = true;
    }

    public function save(): void
    {
        $this->validate();

        $business = auth()->user()->business;

        $data = [
            'business_id'      => $business->id,
            'code'             => strtoupper(trim($this->code)),
            'name'             => trim($this->name),
            'type'             => $this->type,
            'value'            => $this->value,
            'min_order_amount' => $this->min_order_amount ?: null,
            'usage_limit'      => $this->usage_limit ?: null,
            'is_active'        => $this->is_active,
            'valid_from'       => $this->valid_from ?: null,
            'valid_until'      => $this->valid_until ?: null,
        ];

        if ($this->editingId) {
            DiscountCode::findOrFail($this->editingId)->update($data);
            $msg = 'Discount code updated.';
        } else {
            DiscountCode::create($data);
            $msg = 'Discount code created.';
        }

        $this->closeForm();
        session()->flash('success', $msg);
    }

    public function toggleActive(int $id): void
    {
        $discount = DiscountCode::findOrFail($id);
        $discount->update(['is_active' => ! $discount->is_active]);
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmDelete = true;
        $this->deleteId      = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmDelete = false;
        $this->deleteId      = null;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            DiscountCode::findOrFail($this->deleteId)->delete();
            session()->flash('success', 'Discount code deleted.');
        }
        $this->cancelDelete();
    }

    public function render()
    {
        $business  = auth()->user()->business;
        $discounts = DiscountCode::where('business_id', $business->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.app.settings.discount-codes', compact('discounts'))
            ->layout('layouts.app', ['heading' => 'Discount Codes']);
    }
}
