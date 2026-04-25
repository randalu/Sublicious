<?php

namespace App\Livewire\App\Settings;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;

class Security extends Component
{
    public string $current_password      = '';
    public string $new_password          = '';
    public string $new_password_confirmation = '';

    // 2FA setup
    public bool   $showSetup2FA     = false;
    public string $twoFactorSecret  = '';
    public string $twoFactorQrSvg   = '';
    public string $confirmCode      = '';
    public array  $recoveryCodes    = [];
    public bool   $showRecoveryCodes = false;

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

    public function startEnable2FA(): void
    {
        $google2fa = new Google2FA();
        $this->twoFactorSecret = $google2fa->generateSecretKey();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            auth()->user()->email,
            $this->twoFactorSecret
        );

        $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
        $writer = new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                $renderer
            )
        );
        $this->twoFactorQrSvg = $writer->writeString($qrCodeUrl);

        $this->confirmCode = '';
        $this->showSetup2FA = true;
    }

    public function confirmEnable2FA(): void
    {
        $this->validate(['confirmCode' => 'required|string|size:6']);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($this->twoFactorSecret, $this->confirmCode);

        if (! $valid) {
            $this->addError('confirmCode', 'Invalid code. Please try again.');
            return;
        }

        $codes = collect(range(1, 8))->map(fn () => Str::random(10))->all();

        auth()->user()->update([
            'two_factor_secret'       => encrypt($this->twoFactorSecret),
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->recoveryCodes = $codes;
        $this->showSetup2FA = false;
        $this->showRecoveryCodes = true;
        $this->twoFactorSecret = '';
        $this->twoFactorQrSvg = '';

        session()->flash('success', 'Two-factor authentication enabled.');
    }

    public function cancelSetup(): void
    {
        $this->showSetup2FA = false;
        $this->twoFactorSecret = '';
        $this->twoFactorQrSvg = '';
        $this->confirmCode = '';
    }

    public function closeRecoveryCodes(): void
    {
        $this->showRecoveryCodes = false;
        $this->recoveryCodes = [];
    }

    public function regenerateRecoveryCodes(): void
    {
        $codes = collect(range(1, 8))->map(fn () => Str::random(10))->all();

        auth()->user()->update([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ]);

        $this->recoveryCodes = $codes;
        $this->showRecoveryCodes = true;
    }

    public function disable2FA(): void
    {
        auth()->user()->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);

        session()->flash('success', 'Two-factor authentication disabled.');
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.app.settings.security', compact('user'))
            ->layout('layouts.app', ['heading' => 'Security']);
    }
}
