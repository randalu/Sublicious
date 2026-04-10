<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;

class ResetPassword extends Component
{
    #[Url]
    public string $token = '';

    public string $email = '';
    public string $password = '';
    public string $passwordConfirmation = '';
    public string $error = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            ['email' => $this->email, 'password' => $this->password, 'token' => $this->token],
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $this->redirect(route('login') . '?reset=1', navigate: true);
        } else {
            $this->error = __($status);
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password')
            ->layout('layouts.auth', ['title' => 'Set New Password — ' . config('app.name')]);
    }
}
