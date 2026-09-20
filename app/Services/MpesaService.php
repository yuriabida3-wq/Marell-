<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MpesaService
{
    protected string $env;
    protected string $consumerKey;
    protected string $consumerSecret;
    protected string $shortcode;
    protected string $passkey;
    protected string $callbackUrl;

    public function __construct()
    {
        $this->env            = config('services.mpesa.env', 'sandbox');
        $this->consumerKey    = (string) config('services.mpesa.consumer_key');
        $this->consumerSecret = (string) config('services.mpesa.consumer_secret');
        $this->shortcode      = (string) config('services.mpesa.shortcode');
        $this->passkey        = (string) config('services.mpesa.passkey');
        $this->callbackUrl    = (string) config('services.mpesa.callback_url');
    }

    protected function baseUrl(): string
    {
        return $this->env === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Get OAuth access token (cached 55 min).
     */
    public function getAccessToken(): ?string
    {
        return Cache::remember('mpesa_access_token', 3300, function () {
            try {
                $res = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                    ->timeout(15)
                    ->get($this->baseUrl() . '/oauth/v1/generate?grant_type=client_credentials');

                if (!$res->successful()) {
                    Log::error('M-Pesa OAuth failed: ' . $res->body());
                    return null;
                }

                return $res->json('access_token');
            } catch (\Throwable $e) {
                Log::error('M-Pesa OAuth exception: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Trigger STK Push. Returns Daraja JSON response or error array.
     */
    public function stkPush(string $phone, float $amount, string $accountRef, string $desc = 'School Fees'): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['error' => 'Could not authenticate with Daraja. Check credentials.'];
        }

        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => (int) round($amount),
            'PartyA'            => $phone,
            'PartyB'            => $this->shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $this->callbackUrl,
            'AccountReference'  => $accountRef,
            'TransactionDesc'   => $desc,
        ];

        try {
            $res = Http::withToken($token)
                ->timeout(20)
                ->acceptJson()
                ->post($this->baseUrl() . '/mpesa/stkpush/v1/processrequest', $payload);

            $json = $res->json() ?: ['error' => $res->body()];

            Log::info('STK Push request', ['phone' => $phone, 'amount' => $amount, 'response' => $json]);

            return $json;
        } catch (\Throwable $e) {
            Log::error('STK Push exception: ' . $e->getMessage());
            return ['error' => 'Network error contacting Daraja.'];
        }
    }

    /**
     * Normalize Kenyan phone to 2547XXXXXXXX or 2541XXXXXXXX format.
     */
    public static function normalizePhone(string $phone): ?string
    {
        $p = preg_replace('/\D/', '', $phone);
        if (str_starts_with($p, '0'))  $p = '254' . substr($p, 1);
        if (str_starts_with($p, '7') || str_starts_with($p, '1')) $p = '254' . $p;
        if (str_starts_with($p, '254') && strlen($p) === 12) return $p;
        return null;
    }
}
