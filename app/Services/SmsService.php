<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send a single SMS.
     */
    public function send(string $to, string $message, ?int $businessId = null, ?int $orderId = null): bool
    {
        $config = $this->resolveConfig($businessId);

        if (! $config['api_key'] || ! $config['user_id']) {
            Log::warning('SMS not configured', ['business_id' => $businessId]);
            return false;
        }

        // Normalise phone number to +94 format
        $to = $this->normalisePhone($to);

        // Truncate message to 621 chars (SMSlenz limit)
        $message = mb_substr($message, 0, 621);

        try {
            $response = Http::timeout(15)->post($config['base_url'] . '/send-sms', [
                'user_id'   => $config['user_id'],
                'api_key'   => $config['api_key'],
                'sender_id' => $config['sender_id'],
                'contact'   => $to,
                'message'   => $message,
            ]);

            $sent = $response->successful();
            $providerResponse = $response->body();

            $this->logSms($to, $message, $businessId, $orderId, $sent ? 'sent' : 'failed', $providerResponse);

            if (! $sent) {
                Log::warning('SMS send failed', ['to' => $to, 'response' => $providerResponse]);
            }

            return $sent;
        } catch (\Throwable $e) {
            $this->logSms($to, $message, $businessId, $orderId, 'failed', $e->getMessage());
            Log::error('SMS exception', ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send SMS to multiple recipients.
     */
    public function sendBulk(array $contacts, string $message, ?int $businessId = null): bool
    {
        $config = $this->resolveConfig($businessId);

        if (! $config['api_key'] || ! $config['user_id']) {
            return false;
        }

        $contacts = array_map(fn($c) => $this->normalisePhone($c), $contacts);
        $message = mb_substr($message, 0, 621);

        try {
            $response = Http::timeout(30)->post($config['base_url'] . '/send-bulk-sms', [
                'user_id'   => $config['user_id'],
                'api_key'   => $config['api_key'],
                'sender_id' => $config['sender_id'],
                'contacts'  => $contacts,
                'message'   => $message,
            ]);

            $sent = $response->successful();

            foreach ($contacts as $contact) {
                $this->logSms($contact, $message, $businessId, null, $sent ? 'sent' : 'failed', $response->body());
            }

            return $sent;
        } catch (\Throwable $e) {
            Log::error('Bulk SMS exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send a test SMS (used from Settings > Integrations).
     */
    public function sendTest(string $to, ?int $businessId = null): bool
    {
        return $this->send($to, 'This is a test message from Sublicious.', $businessId);
    }

    /**
     * Resolve SMS config — business-level overrides platform defaults.
     */
    protected function resolveConfig(?int $businessId): array
    {
        $defaults = [
            'user_id'   => $this->platformSetting('sms_user_id', ''),
            'api_key'   => $this->platformSetting('sms_api_key', ''),
            'sender_id' => $this->platformSetting('sms_sender_id', 'SMSlenzDEMO'),
            'base_url'  => $this->platformSetting('sms_base_url', 'https://smslenz.lk/api'),
        ];

        if ($businessId) {
            $business = Business::find($businessId);
            if ($business) {
                return [
                    'user_id'   => $business->getSetting('sms_user_id', '') ?: $defaults['user_id'],
                    'api_key'   => $business->getSetting('sms_api_key', '') ?: $defaults['api_key'],
                    'sender_id' => $business->getSetting('sms_sender_id', '') ?: $defaults['sender_id'],
                    'base_url'  => $business->getSetting('sms_base_url', '') ?: $defaults['base_url'],
                ];
            }
        }

        return $defaults;
    }

    /**
     * Read platform-wide setting (business_id = null).
     */
    protected function platformSetting(string $key, mixed $default = null): mixed
    {
        $setting = BusinessSetting::whereNull('business_id')->where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    /**
     * Normalise phone number to +94XXXXXXXXX format.
     */
    protected function normalisePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Already international format
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // Sri Lankan local format: 07XXXXXXXX → +947XXXXXXXX
        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            return '+94' . substr($phone, 1);
        }

        // No leading zero, assume needs +
        return '+' . $phone;
    }

    /**
     * Log SMS to sms_logs table.
     */
    protected function logSms(
        string $to,
        string $message,
        ?int $businessId,
        ?int $orderId,
        string $status,
        ?string $providerResponse = null
    ): void {
        try {
            SmsLog::create([
                'business_id'       => $businessId,
                'recipient_phone'   => $to,
                'message'           => $message,
                'status'            => $status,
                'provider_response' => $providerResponse,
                'order_id'          => $orderId,
                'sent_at'           => $status === 'sent' ? now() : null,
            ]);
        } catch (\Throwable) {
            // Silently fail logging — don't break the send flow
        }
    }
}
