<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phone, string $message): bool
    {
        $username = config('services.africastalking.username');
        $apiKey   = config('services.africastalking.api_key');
        $sender   = config('services.africastalking.sender', 'MARELL');

        // Skip if not configured (dev)
        if (!$username || !$apiKey || $apiKey === 'your_api_key_here') {
            Log::info("[SMS-STUB] to {$phone}: {$message}");
            return true;
        }

        try {
            $isSandbox = $username === 'sandbox';
            $endpoint  = $isSandbox
                ? 'https://api.sandbox.africastalking.com/version1/messaging'
                : 'https://api.africastalking.com/version1/messaging';

            $response = Http::withHeaders([
                'apiKey' => $apiKey,
                'Accept' => 'application/json',
            ])->asForm()->post($endpoint, [
                'username' => $username,
                'to'       => $phone,
                'message'  => $message,
                'from'     => $sender,
            ]);

            if (!$response->successful()) {
                Log::warning("SMS failed to {$phone}: " . $response->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("SMS exception: " . $e->getMessage());
            return false;
        }
    }
}
