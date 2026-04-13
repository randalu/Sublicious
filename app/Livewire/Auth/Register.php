<?php

namespace App\Livewire\Auth;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class Register extends Component
{
    // Step tracking
    public int $step = 1;
    public int $totalSteps = 3;

    // Step 1 — Business info
    public string $businessName = '';
    public string $businessEmail = '';
    public string $businessPhone = '';
    public string $businessAddress = '';
    public string $businessCity = '';
    public string $businessCountry = 'US';
    public string $currency = 'USD';
    public string $timezone = 'UTC';

    // Step 2 — Plan selection
    public ?int $selectedPlanId = null;

    // Step 3 — Admin account
    public string $adminName = '';
    public string $adminEmail = '';
    public string $adminPassword = '';
    public string $adminPasswordConfirmation = '';
    public bool $agreeTerms = false;

    public string $saveError = '';

    public function mount(): void
    {
        $defaultPlan = Plan::getDefault();
        $this->selectedPlanId = $defaultPlan?->id;
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    private function validateCurrentStep(): void
    {
        match ($this->step) {
            1 => $this->validateStep1(),
            2 => $this->validateStep2(),
            3 => $this->validateStep3(),
            default => null,
        };
    }

    private function validateStep1(): void
    {
        $this->validate([
            'businessName' => 'required|string|min:2|max:100',
            'businessEmail' => 'required|email|unique:businesses,email',
            'businessPhone' => 'nullable|string|max:20',
            'businessCity' => 'nullable|string|max:100',
            'businessCountry' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
        ]);
    }

    private function validateStep2(): void
    {
        $this->validate([
            'selectedPlanId' => 'required|exists:plans,id',
        ]);
    }

    private function validateStep3(): void
    {
        $this->validate([
            'adminName' => 'required|string|min:2|max:100',
            'adminEmail' => 'required|email|unique:users,email',
            'adminPassword' => 'required|string|min:8|same:adminPasswordConfirmation',
            'agreeTerms' => 'accepted',
        ]);
    }

    public function register(): void
    {
        $this->saveError = '';
        $this->validateStep3();

        try {
            DB::transaction(function () {
                $slug = Str::slug($this->businessName) . '-' . Str::random(4);
                $plan = Plan::findOrFail($this->selectedPlanId);

                $business = Business::create([
                    'plan_id' => $plan->id,
                    'name' => $this->businessName,
                    'slug' => $slug,
                    'email' => $this->businessEmail,
                    'phone' => $this->businessPhone,
                    'address' => $this->businessAddress,
                    'city' => $this->businessCity,
                    'country' => $this->businessCountry,
                    'currency' => $this->currency,
                    'timezone' => $this->timezone,
                    'subscription_status' => 'trialing',
                    'trial_ends_at' => now()->addDays(14),
                    'is_active' => true,
                    'is_verified' => false,
                ]);

                $user = User::create([
                    'business_id' => $business->id,
                    'name' => $this->adminName,
                    'email' => $this->adminEmail,
                    'password' => Hash::make($this->adminPassword),
                    'role' => 'admin',
                    'is_active' => true,
                ]);

                // Seed default operating hours (Mon-Fri 9-5, closed weekends)
                for ($day = 0; $day <= 6; $day++) {
                    $business->operatingHours()->create([
                        'day_of_week' => $day,
                        'open_time' => '09:00',
                        'close_time' => '21:00',
                        'is_closed' => in_array($day, [0]), // Sunday closed by default
                    ]);
                }

                AuditLog::record('business_registered', $business->id, Business::class, $business->id, [], [
                    'name' => $business->name,
                    'plan' => $plan->name,
                ], 'registration');

                Auth::login($user);
                session()->regenerate();
            });
        } catch (\Throwable $e) {
            $this->saveError = 'Could not complete registration: ' . $e->getMessage();
            return;
        }

        $this->redirect(route('app.dashboard'), navigate: false);
    }

    public function getPlansProperty()
    {
        return Plan::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function render()
    {
        return view('livewire.auth.register', [
            'plans' => $this->plans,
        ])->layout('layouts.auth', [
            'title' => 'Register — ' . config('app.name'),
            'wide' => $this->step === 2,
        ]);
    }
}
