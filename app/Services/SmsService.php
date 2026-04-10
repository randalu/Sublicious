<?php

namespace App\Services;

use App\Models\SmsLog;

class SmsService
{
    public function send(string $to, string $message, ?int $businessId = null): bool
    {
        $provider = $this->resolveProvider($businessId);

        try {
            $sent = match ($provider) {
                'twilio' => $this->sendViaTwilio($to, $message, $businessId),
                'nexmo'  => $this->sendViaNexmo($to, $message, $businessId),
                default  => $this->sendViaCustom($to, $message, $businessId),
            };

            $this->logSms($to, $message, $businessId, $sent ? 'sent' : 'failed');

            return $sent;
        } catch (\Throwable $e) {
            $this->logSms($to, $message, $businessId, 'failed', $e->getMessage());
            throw $e;
        }
    }

    protected function resolveProvider(?int $businessId): string
    {
        if ($businessId) {
            $business = \App\Models\Business::find($businessId);
            if ($business) {
                return $business->getSetting('sms_gateway_provider', '')
                    ?: $this->platformSetting('default_sms_provider', 'twilio');
            }
        }
        return $this->platformSetting('default_sms_provider', 'twilio');
    }

    protected function platformSetting(string $key, mixed $default = null): mixed
    {
        $setting = \App\Models\BusinessSetting::where('business_id', null)->where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    protected function sendViaTwilio(string $to, string $message, ?int $businessId): bool
    {
        // Twilio integration placeholder
        // In production: use Twilio SDK
        return true;
    }

    protected function sendViaNexmo(string $to, string $message, ?int $businessId): bool
    {
        // Nexmo/Vonage integration placeholder
        return true;
    }

    protected function sendViaCustom(string $to, string $message, ?int $businessId): bool
    {
        // Custom provider placeholder
        return true;
    }

    protected function logSms(string $to, string $message, ?int $businessId, string $status, ?string $error = null): void
    {
        try {
            SmsLog::create([
                'business_id'       => $businessId,
                'recipient_phone'   => $to,
                'message'           => $message,
                'status'            => $status,
                'provider_response' => $error,
                'sent_at'           => $status === 'sent' ? now() : null,
            ]);
        } catch (\Throwable) {
            // Silently fail logging
        }
    }
}
