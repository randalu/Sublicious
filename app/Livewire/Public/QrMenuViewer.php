<?php

namespace App\Livewire\Public;

use App\Models\MenuCategory;
use App\Models\RestaurantTable;
use Livewire\Component;

class QrMenuViewer extends Component
{
    public ?RestaurantTable $table = null;
    public $business = null;

    public function mount(string $token): void
    {
        $this->table = RestaurantTable::withoutGlobalScope('business')
            ->where('qr_code_token', $token)
            ->firstOrFail();

        $this->business = $this->table->business;
    }

    public function render()
    {
        $categories = MenuCategory::withoutGlobalScope('business')
            ->where('business_id', $this->business->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['items' => function ($q) {
                $q->withoutGlobalScope('business')
                    ->where('is_available', true)
                    ->orderBy('sort_order')
                    ->with(['variants' => function ($vq) {
                        $vq->where('is_available', true);
                    }]);
            }])
            ->get();

        return view('livewire.public.qr-menu-viewer', [
            'categories' => $categories,
        ])->layout('layouts.public', [
            'title' => $this->business->name . ' - Menu',
        ]);
    }
}
