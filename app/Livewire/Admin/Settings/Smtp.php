<?php

namespace App\Livewire\Admin\Settings;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class Smtp extends Component
{
    public string $smtp_host         = '';
    public string $smtp_port         = '587';
    public string $smtp_username     = '';
    public string $smtp_password     = '';
    public string $smtp_encryption   = 'tls';
    public string $mail_from_address = '';
    public string $mail_from_name    = 'Sublicious';

    public bool $showPassword = false;

    private const KEYS = [
        'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
        'smtp_encryption', 'mail_from_address', 'mail_from_name',
    ];

    public function mount(): void
    {
        foreach (self::KEYS as $key) {
            $setting = BusinessSetting::where('business_id', null)->where('key', $key)->first();
            if ($setting) {
                $this->$key = $setting->value;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'smtp_host'         => 'required|string|max:200',
            'smtp_port'         => 'required|integer|min:1|max:65535',
            'smtp_username'     => 'nullable|string|max:200',
            'smtp_password'     => 'nullable|string|max:500',
            'smtp_encryption'   => 'required|in:tls,ssl,none',
            'mail_from_address' => 'required|email|max:200',
            'mail_from_name'    => 'required|string|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        foreach (self::KEYS as $key) {
            BusinessSetting::updateOrCreate(
                ['business_id' => null, 'key' => $key],
                ['value' => $this->$key, 'group' => 'platform_smtp']
            );
        }

        // Apply settings dynamically for the current request
        config([
            'mail.mailers.smtp.host'       => $this->smtp_host,
            'mail.mailers.smtp.port'       => (int) $this->smtp_port,
            'mail.mailers.smtp.username'   => $this->smtp_username,
            'mail.mailers.smtp.password'   => $this->smtp_password,
            'mail.mailers.smtp.encryption' => $this->smtp_encryption === 'none' ? null : $this->smtp_encryption,
            'mail.from.address'            => $this->mail_from_address,
            'mail.from.name'               => $this->mail_from_name,
        ]);

        session()->flash('success', 'SMTP settings saved.');
    }

    public function testEmail(): void
    {
        $admin = auth()->user();

        try {
            Mail::raw('This is a test email from Sublicious admin panel. SMTP is configured correctly!', function ($msg) use ($admin) {
                $msg->to($admin->email, $admin->name)
                    ->subject('Test Email — Sublicious');
            });
            session()->flash('success', 'Test email sent to ' . $admin->email);
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.smtp')
            ->layout('layouts.admin', ['heading' => 'SMTP Settings']);
    }
}
