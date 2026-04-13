<?php

namespace App\Livewire\App\Settings;

use App\Models\BusinessOperatingHour;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OperatingHours extends Component
{
    public array $hours = [];

    public string $saveError = '';

    public const DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function mount(): void
    {
        $business    = auth()->user()->business;
        $existing    = $business->operatingHours()->get()->keyBy('day_of_week');

        foreach (self::DAYS as $day => $name) {
            $record = $existing->get($day);
            $this->hours[$day] = [
                'is_closed'  => $record ? (bool) $record->is_closed : false,
                'open_time'  => $record?->open_time ?? '09:00',
                'close_time' => $record?->close_time ?? '21:00',
            ];
        }
    }

    protected function rules(): array
    {
        $rules = [];
        foreach (array_keys(self::DAYS) as $day) {
            $rules["hours.{$day}.is_closed"]  = 'boolean';
            $rules["hours.{$day}.open_time"]  = 'nullable|string';
            $rules["hours.{$day}.close_time"] = 'nullable|string';
        }
        return $rules;
    }

    public function save(): void
    {
        $this->saveError = '';
        $this->validate();

        $business = auth()->user()->business;

        try {
            DB::transaction(function () use ($business) {
                foreach ($this->hours as $day => $data) {
                    BusinessOperatingHour::updateOrCreate(
                        ['business_id' => $business->id, 'day_of_week' => (int) $day],
                        [
                            'is_closed'  => (bool) ($data['is_closed'] ?? false),
                            'open_time'  => $data['open_time'] ?? '09:00',
                            'close_time' => $data['close_time'] ?? '21:00',
                        ]
                    );
                }
            });
        } catch (\Throwable $e) {
            $this->saveError = 'Could not save: ' . $e->getMessage();
            return;
        }

        session()->flash('success', 'Operating hours saved successfully.');
    }

    public function days(): array
    {
        return self::DAYS;
    }

    public function render()
    {
        return view('livewire.app.settings.operating-hours', [
            'days' => $this->days(),
        ])->layout('layouts.app', ['heading' => 'Operating Hours']);
    }
}
