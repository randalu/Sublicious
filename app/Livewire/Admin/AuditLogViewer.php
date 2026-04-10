<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Business;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogViewer extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $event = '';

    #[Url(except: '')]
    public string $businessId = '';

    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = $this->dateFrom ?: now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = $this->dateTo   ?: now()->format('Y-m-d');
    }

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingEvent(): void      { $this->resetPage(); }
    public function updatingBusinessId(): void { $this->resetPage(); }

    public function render()
    {
        $query = AuditLog::with(['user', 'business'])
            ->orderByDesc('created_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('event', 'like', '%' . $this->search . '%')
                  ->orWhere('user_email', 'like', '%' . $this->search . '%')
                  ->orWhere('auditable_type', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->event) {
            $query->where('event', $this->event);
        }
        if ($this->businessId) {
            $query->where('business_id', $this->businessId);
        }
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $logs       = $query->paginate(50);
        $events     = AuditLog::distinct()->pluck('event');
        $businesses = Business::orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.audit-log-viewer', compact('logs', 'events', 'businesses'))
            ->layout('layouts.admin', ['heading' => 'Platform Audit Logs']);
    }
}
