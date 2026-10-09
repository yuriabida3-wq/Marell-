<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DemoAutoCompletePayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public Payment $payment) {}

    public function handle(): void
    {
        // Fail-closed: demo auto-complete only fires when all three are true.
        if (!config('services.demo_mode', false)) {
            return;
        }
        if (!config('services.mpesa_skip_stk', false)) {
            return;
        }
        if (config('services.mpesa.env', 'sandbox') !== 'sandbox') {
            return;
        }

        $payment = $this->payment->fresh();
        if (!$payment || $payment->status === 'completed') {
            return;
        }

        Log::info("🎬 Demo mode: auto-completing payment #{$payment->id}");

        // Fire the same callback the real M-Pesa would fire
        \App\Jobs\ProcessMpesaCallback::dispatchSync([
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID'   => 'demo-merchant-' . uniqid(),
                    'CheckoutRequestID'   => $payment->checkout_request_id,
                    'ResultCode'          => 0,
                    'ResultDesc'          => 'The service request is processed successfully.',
                    'CallbackMetadata'    => [
                        'Item' => [
                            ['Name' => 'Amount',             'Value' => (float) $payment->amount],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'DEMO' . strtoupper(bin2hex(random_bytes(4)))],
                            ['Name' => 'TransactionDate',    'Value' => (int) now()->format('YmdHis')],
                            ['Name' => 'PhoneNumber',        'Value' => 254708374149],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
