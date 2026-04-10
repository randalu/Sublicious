<?php

namespace App\Livewire\Admin\Businesses;

use App\Models\AuditLog;
use App\Models\Business;
use Livewire\Component;
use Livewire\WithPagination;

class BusinessList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public ?int $confirmDeleteId = null;
    public ?int $confirmSuspendId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function suspend(int $id): void
    {
        $business = Business::findOrFail($id);
        $business->update([
            'is_active' => false,
            'subscription_status' => 'suspended',
        ]);
        AuditLog::record('business_suspended', null, Business::class, $id, ['is_active' => true], ['is_active' => false]);
        $this->confirmSuspendId = null;
        session()->flash('success', "Business \"{$business->name}\" has been suspended.");
    }

    public function restore(int $id): void
    {
        $business = Business::findOrFail($id);
        $business->update([
            'is_active' => true,
            'subscription_status' => 'active',
        ]);
        AuditLog::record('business_restored', null, Business::class, $id, ['is_active' => false], ['is_active' => true]);
        session()->flash('success', "Business \"{$business->name}\" has been restored.");
    }

    public function delete(int $id): void
    {
        $business = Business::findOrFail($id);
        $name = $business->name;
        $business->delete();
        AuditLog::record('business_deleted', null, Business::class, $id, ['name' => $name], []);
        $this->confirmDeleteId = null;
        session()->flash('success', "Business \"{$name}\" has been deleted.");
    }

    public function render()
    {
        $businesses = Business::with('plan')
            ->when($this->search, fn($q) => $q->where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->statusFilter === 'suspended', fn($q) => $q->where('is_active', false))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.admin.businesses.business-list', compact('businesses'))
            ->layout('layouts.admin', ['title' => 'Businesses — Admin', 'heading' => 'Businesses']);
    }
}
