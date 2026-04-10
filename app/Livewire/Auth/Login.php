<?php

namespace App\Livewire\Auth;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public string $error = '';

    public function login(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'login:' . Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->error = "Too many login attempts. Please try again in {$seconds} seconds.";
            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->error = 'Invalid email or password.';
            return;
        }

        RateLimiter::clear($key);

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $this->error = 'Your account has been deactivated.';
            return;
        }

        AuditLog::record('login', $user->business_id, null, null, [], [], 'auth');

        session()->regenerate();

        if ($user->isSuperAdmin()) {
            $this->redirect(route('admin.dashboard'), navigate: true);
        } else {
            $this->redirect(route('app.dashboard'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.auth', ['title' => 'Sign In — ' . config('app.name')]);
    }
}
