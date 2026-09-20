<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Student;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMpesaCallback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 5;
    public int $timeout = 60;

    public function __construct(public array $payload) {}

    public function handle(): void
    {
        $stk = $this->payload['Body']['stkCallback'] ?? null;
        if (!$stk) {
            Log::warning('M-Pesa callback: missing stkCallback', $this->payload);
            return;
        }

        $checkoutId = $stk['CheckoutRequestID'] ?? null;
        $resultCode = (int) ($stk['ResultCode'] ?? -1);
        $resultDesc = $stk['ResultDesc'] ?? '';

        if (!$checkoutId) {
            Log::warning('M-Pesa callback: no CheckoutRequestID');
            return;
        }

        $payment = Payment::where('checkout_request_id', $checkoutId)->first();
        if (!$payment) {
            Log::warning("M-Pesa callback: no payment matches checkout {$checkoutId}");
            return;
        }

        if ($payment->status === 'completed') {
            Log::info("M-Pesa callback: payment {$payment->id} already completed. Skipping.");
            return;
        }

        // -------- FAILED / CANCELLED --------
        if ($resultCode !== 0) {
            $payment->update([
                'status' => 'failed',
                'notes'  => "Daraja: {$resultDesc} (code {$resultCode})",
            ]);
            Log::info("M-Pesa payment {$payment->id} failed: {$resultDesc}");
            return;
        }

        // -------- SUCCESS --------
        $meta = collect($stk['CallbackMetadata']['Item'] ?? [])
            ->mapWithKeys(fn ($i) => [$i['Name'] => $i['Value'] ?? null])
            ->all();

        $mpesaReceipt = $meta['MpesaReceiptNumber'] ?? null;
        $paidAmount   = (float) ($meta['Amount'] ?? $payment->amount);
        $phone        = $meta['PhoneNumber']       ?? null;

        if (!$mpesaReceipt) {
            Log::warning("M-Pesa callback: no MpesaReceiptNumber for payment {$payment->id}");
            return;
        }

        $dupe = Payment::where('transaction_code', $mpesaReceipt)
            ->where('id', '!=', $payment->id)
            ->where('status', 'completed')
            ->exists();

        if ($dupe) {
            Log::warning("M-Pesa callback: duplicate transaction_code {$mpesaReceipt}");
            $payment->update(['status' => 'failed', 'notes' => 'Duplicate transaction code']);
            return;
        }

        DB::transaction(function () use ($payment, $mpesaReceipt, $paidAmount, $phone) {
            $student = Student::where('id', $payment->student_id)
                ->lockForUpdate()
                ->first();

            if (!$student) {
                throw new \RuntimeException("Student {$payment->student_id} not found");
            }

            $fresh = Payment::where('id', $payment->id)->lockForUpdate()->first();
            if ($fresh->status === 'completed') {
                return;
            }

            $receiptNo = Payment::generateReceiptNo();

            $fresh->update([
                'status'           => 'completed',
                'transaction_code' => $mpesaReceipt,
                'amount'           => $paidAmount,
                'receipt_no'       => $receiptNo,
                'notes'            => 'Auto-completed via M-Pesa callback',
            ]);

            $newPaid    = (float) $student->paid_amount + $paidAmount;
            $newBalance = max(0, (float) $student->total_fee - $newPaid);

            $student->update([
                'paid_amount' => $newPaid,
                'balance'     => $newBalance,
            ]);

            Log::info("M-Pesa payment {$fresh->id} completed. Receipt {$receiptNo}. New balance: {$newBalance}");

            if ($phone || $student->parent_phone) {
                $to = \App\Services\MpesaService::normalizePhone($phone ?: $student->parent_phone);
                if ($to) {
                    $msg = sprintf(
                        "MARELL ACADEMY\nReceived KES %s from %s for %s.\nReceipt: %s\nNew balance: KES %s\nThank you.",
                        number_format($paidAmount, 2),
                        $mpesaReceipt,
                        $student->name,
                        $receiptNo,
                        number_format($newBalance, 2)
                    );

                    try {
                        app(SmsService::class)->send($to, $msg);
                    } catch (\Throwable $e) {
                        Log::warning('SMS receipt failed: ' . $e->getMessage());
                    }
                }
            }
        });
    }
}
