<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $statusFilter = '';

    #[Url(except: '')]
    public string $search = '';

    public function updatingSearch(): void     { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function render()
    {
        $query = DB::table('subscriptions')
            ->join('businesses', 'subscriptions.business_id', '=', 'businesses.id')
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->select(
                'subscriptions.*',
                'businesses.name as business_name',
                'businesses.email as business_email',
                'plans.name as plan_name'
            )
            ->whereNull('businesses.deleted_at')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('businesses.name', 'like', "%{$this->search}%")
                      ->orWhere('businesses.email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('subscriptions.stripe_status', $this->statusFilter))
            ->orderByDesc('subscriptions.created_at');

        $subscriptions = $query->paginate(20);

        return view('livewire.admin.subscription-list', compact('subscriptions'))
            ->layout('layouts.admin', ['heading' => 'Subscriptions']);
    }
}
