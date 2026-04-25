<?php

namespace App\Livewire\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public string $error = '';

    // 2FA challenge
    public bool   $challengingTwoFactor = false;
    public string $twoFactorCode        = '';
    public bool   $useRecoveryCode      = false;
    public string $recoveryCode         = '';

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

        if ($user->twoFactorEnabled()) {
            Auth::logout();
            session()->put('2fa:user_id', $user->id);
            session()->put('2fa:remember', $this->remember);
            $this->challengingTwoFactor = true;
            $this->error = '';
            return;
        }

        $this->completeLogin($user);
    }

    public function verifyTwoFactor(): void
    {
        $userId = session('2fa:user_id');
        if (! $userId) {
            $this->challengingTwoFactor = false;
            $this->error = 'Session expired. Please log in again.';
            return;
        }

        $user = User::find($userId);
        if (! $user) {
            $this->challengingTwoFactor = false;
            $this->error = 'User not found.';
            return;
        }

        if ($this->useRecoveryCode) {
            $this->validate(['recoveryCode' => 'required|string']);
            $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);

            if (! in_array($this->recoveryCode, $codes)) {
                $this->addError('recoveryCode', 'Invalid recovery code.');
                return;
            }

            $remaining = array_values(array_filter($codes, fn ($c) => $c !== $this->recoveryCode));
            $user->update(['two_factor_recovery_codes' => encrypt(json_encode($remaining))]);
        } else {
            $this->validate(['twoFactorCode' => 'required|string|size:6']);
            $google2fa = new Google2FA();
            $secret = decrypt($user->two_factor_secret);

            if (! $google2fa->verifyKey($secret, $this->twoFactorCode)) {
                $this->addError('twoFactorCode', 'Invalid authentication code.');
                return;
            }
        }

        $remember = session('2fa:remember', false);
        session()->forget(['2fa:user_id', '2fa:remember']);

        Auth::login($user, $remember);
        $this->completeLogin($user);
    }

    public function toggleRecoveryMode(): void
    {
        $this->useRecoveryCode = ! $this->useRecoveryCode;
        $this->twoFactorCode = '';
        $this->recoveryCode = '';
        $this->resetErrorBag();
    }

    public function cancelTwoFactor(): void
    {
        session()->forget(['2fa:user_id', '2fa:remember']);
        $this->challengingTwoFactor = false;
        $this->twoFactorCode = '';
        $this->recoveryCode = '';
        $this->useRecoveryCode = false;
    }

    private function completeLogin(User $user): void
    {
        AuditLog::record('login', $user->business_id, null, null, [], [], 'auth');

        session()->regenerate();

        if ($user->isSuperAdmin()) {
            $this->redirect(route('admin.dashboard'), navigate: false);
        } else {
            $this->redirect(route('app.dashboard'), navigate: false);
        }
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.auth', ['title' => 'Sign In — ' . config('app.name')]);
    }
}
