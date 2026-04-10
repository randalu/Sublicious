<?php

namespace App\Livewire\App\Settings;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Security extends Component
{
    public string $current_password      = '';
    public string $new_password          = '';
    public string $new_password_confirmation = '';

    protected function rules(): array
    {
        return [
            'current_password'           => 'required|string',
            'new_password'               => 'required|string|min:8|confirmed',
            'new_password_confirmation'  => 'required|string',
        ];
    }

    public function changePassword(): void
    {
        $this->validate();

        $user = auth()->user();

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password is incorrect.');
            return;
        }

        $user->update(['password' => Hash::make($this->new_password)]);

        $this->current_password     = '';
        $this->new_password         = '';
        $this->new_password_confirmation = '';

        session()->flash('success', 'Password changed successfully.');
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.app.settings.security', compact('user'))
            ->layout('layouts.app', ['heading' => 'Security']);
    }
}
