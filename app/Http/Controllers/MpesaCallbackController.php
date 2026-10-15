<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMpesaCallback;
use App\Models\PaymentCallback;
use App\Support\LogRedactor;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaCallbackController extends Controller
{
    /**
     * Secure callback endpoint (CP-02).
     * Path contains a shared secret segment that only Safaricom's configured
     * CallBackURL knows. Any mismatch -> 404 (do not reveal that the endpoint
     * exists but the secret is wrong).
     */
    public function handleSecure(string $secret, Request $request)
    {
        $expected = (string) config('services.mpesa.callback_secret', '');

        // Fail-closed: if no secret is configured, refuse everything.
        if ($expected === '' || !hash_equals($expected, $secret)) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Not found'], 404);
        }

        // Optional IP allowlist (disabled by default).
        if (!$this->ipAllowed($request)) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Forbidden'], 403);
        }

        // Shape validation — cheap reject before queueing.
        if (!$this->looksLikeStkCallback($request)) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Bad request'], 400);
        }

        return $this->accept($request, 'secure');
    }

    /**
     * Legacy callback endpoint (kept during Daraja cutover).
     * When the flag is off it returns 404, indistinguishable from unmounted.
     */
    public function handle(Request $request)
    {
        if (!config('services.mpesa.callback_legacy_enabled', true)) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Not found'], 404);
        }

        // Optional IP allowlist (disabled by default).
        if (!$this->ipAllowed($request)) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Forbidden'], 403);
        }

        // Shape validation — cheap reject before queueing.
        if (!$this->looksLikeStkCallback($request)) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Bad request'], 400);
        }

        return $this->accept($request, 'legacy');
    }

    /**
     * Common path for a callback that has passed the gates.
     * Dispatches to the mpesa queue and acknowledges within the 5-second window.
     */
    protected function accept(Request $request, string $source = 'secure')
    {
        $payload = $request->all();

        Log::info('M-Pesa callback received', LogRedactor::redact($payload));

        $stk          = $payload['Body']['stkCallback'] ?? [];
        $checkoutId   = is_array($stk) ? ($stk['CheckoutRequestID'] ?? null) : null;
        $meta         = collect($stk['CallbackMetadata']['Item'] ?? [])
            ->mapWithKeys(fn ($i) => [$i['Name'] ?? '_' => $i['Value'] ?? null])
            ->all();
        $mpesaReceipt = $meta['MpesaReceiptNumber'] ?? null;

        try {
            PaymentCallback::create([
                'checkout_request_id'  => $checkoutId,
                'mpesa_receipt_number' => $mpesaReceipt,
                'payload'              => $payload,
                'source'               => $source,
                'ip'                   => $request->ip(),
                'received_at'          => now(),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Daraja retry: identical callback already accepted. Do not re-dispatch.
            Log::info('M-Pesa callback: duplicate checkout_request_id, ignoring');
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Accepted',
            ]);
        }

        ProcessMpesaCallback::dispatch($payload)->onQueue('mpesa');

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }

    /**
     * True if the request should be accepted by the IP allowlist.
     * Empty list = disabled = always true.
     */
    protected function ipAllowed(Request $request): bool
    {
        $raw = (string) config('services.mpesa.callback_ip_allowlist', '');
        if (trim($raw) === '') {
            return true;
        }

        $allowed = array_filter(array_map('trim', explode(',', $raw)));
        if (empty($allowed)) {
            return true;
        }

        return in_array($request->ip(), $allowed, true);
    }

    /**
     * Minimal shape check: Body.stkCallback.CheckoutRequestID must be a
     * non-empty string, and ResultCode must be present.
     */
    protected function looksLikeStkCallback(Request $request): bool
    {
        $stk = $request->input('Body.stkCallback');
        if (!is_array($stk)) {
            return false;
        }
        if (!array_key_exists('ResultCode', $stk)) {
            return false;
        }
        $coid = $stk['CheckoutRequestID'] ?? null;
        if (!is_string($coid) || $coid === '') {
            return false;
        }

        return true;
    }
}
