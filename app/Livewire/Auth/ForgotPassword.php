<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Component;

class ForgotPassword extends Component
{
    public string $email = '';
    public bool $sent = false;
    public string $error = '';

    public function sendLink(): void
    {
        $this->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->sent = true;
            $this->error = '';
        } else {
            $this->error = __($status);
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')
            ->layout('layouts.auth', ['title' => 'Reset Password — ' . config('app.name')]);
    }
}
