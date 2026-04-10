<?php

namespace App\Livewire\Admin\Businesses;

use App\Models\AuditLog;
use App\Models\Business;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BusinessLogs extends Component
{
    use WithPagination;

    public Business $business;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $event = '';

    public function mount(Business $business): void
    {
        $this->business = $business;
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingEvent(): void  { $this->resetPage(); }

    public function render()
    {
        $query = AuditLog::where('business_id', $this->business->id)
            ->with('user')
            ->orderByDesc('created_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('event', 'like', '%' . $this->search . '%')
                  ->orWhere('user_email', 'like', '%' . $this->search . '%')
                  ->orWhere('auditable_type', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->event) {
            $query->where('event', $this->event);
        }

        $logs      = $query->paginate(30);
        $events    = AuditLog::where('business_id', $this->business->id)
            ->distinct()
            ->pluck('event');

        return view('livewire.admin.businesses.business-logs', compact('logs', 'events'))
            ->layout('layouts.admin', ['heading' => $this->business->name . ' — Audit Logs']);
    }
}
