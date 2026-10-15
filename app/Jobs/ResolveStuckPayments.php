<?php

namespace App\Jobs;

use App\Models\Payment;
use App\Models\Student;
use App\Services\MpesaService;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * L4: resolve payments stuck in `pending` after the STK push window.
 * Queries Daraja for the actual status and moves the payment to a terminal state.
 *
 * Runs every 2 minutes (schedule, config-gated). Idempotent: skips payments
 * already completed/failed/expired/under_reconciliation.
 */
class ResolveStuckPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30;
    public int $timeout = 120;

    public function handle(): void
    {
        // First pass: payments older than 2 minutes.
        $this->resolve(now()->subMinutes(2));

        // Second pass: payments older than 10 minutes (retries once more).
        $this->resolve(now()->subMinutes(10));
    }

    protected function resolve(\DateTimeInterface $olderThan): void
    {
        $payments = Payment::where('status', 'pending')
            ->whereNotNull('checkout_request_id')
            ->where('created_at', '<=', $olderThan)
            ->orderBy('id')
            ->limit(100)
            ->get();

        if ($payments->isEmpty()) {
            return;
        }

        $mpesa = app(MpesaService::class);

        foreach ($payments as $payment) {
            try {
                $this->resolveOne($payment, $mpesa);
            } catch (\Throwable $e) {
                Log::error("ResolveStuckPayments failed for payment {$payment->id}: " . $e->getMessage());
            }
        }
    }

    protected function resolveOne(Payment $payment, MpesaService $mpesa): void
    {
        $response = $mpesa->stkQuery((string) $payment->checkout_request_id);

        if (isset($response['error'])) {
            Log::info("STK Query error for payment {$payment->id}: " . (string) $response['error']);
            return; // leave pending; next run will retry
        }

        $resultCode = (int) ($response['ResultCode'] ?? -1);
        $resultDesc = (string) ($response['ResultDesc'] ?? '');
        $mpesaReceipt = $response['MpesaReceiptNumber'] ?? null;

        match ($resultCode) {
            0       => $this->markCompleted($payment, $mpesaReceipt, $resultDesc),
            1032    => $this->markFailed($payment, $resultDesc),
            1037    => $this->markExpired($payment, $resultDesc),
            default => $this->markUnderReview($payment, $resultCode, $resultDesc),
        };
    }

    protected function markCompleted(Payment $payment, ?string $mpesaReceipt, string $desc): void
    {
        if (!$mpesaReceipt) {
            $this->markUnderReview($payment, 0, 'Paid but no MpesaReceiptNumber in query response');
            return;
        }

        $smsTo  = null;
        $smsMsg = null;

        DB::transaction(function () use ($payment, $mpesaReceipt, $desc, &$smsTo, &$smsMsg) {
            $fresh = Payment::where('id', $payment->id)->lockForUpdate()->first();
            if (!$fresh || $fresh->status !== 'pending') {
                return; // someone else resolved it
            }

            $student = Student::where('id', $fresh->student_id)->lockForUpdate()->first();
            if (!$student) {
                throw new \RuntimeException("Student {$fresh->student_id} not found");
            }

            $amount    = (float) $fresh->amount;
            $receiptNo = Payment::generateReceiptNo();

            $fresh->update([
                'status'           => 'completed',
                'transaction_code' => $mpesaReceipt,
                'receipt_no'       => $receiptNo,
                'notes'            => 'Auto-completed via STK query (L4): ' . $desc,
            ]);

            $newPaid    = (float) $student->paid_amount + $amount;
            $newBalance = max(0, (float) $student->total_fee - $newPaid);
            $student->update([
                'paid_amount' => $newPaid,
                'balance'     => $newBalance,
            ]);

            if ($student->parent_phone) {
                $to = MpesaService::normalizePhone($student->parent_phone);
                if ($to) {
                    $smsTo  = $to;
                    $smsMsg = sprintf(
                        "Received KES %s for %s.\nReceipt: %s\nNew balance: KES %s",
                        number_format($amount, 2),
                        $student->name,
                        $receiptNo,
                        number_format($newBalance, 2)
                    );
                }
            }
        });

        if ($smsTo && $smsMsg) {
            try {
                app(SmsService::class)->send($smsTo, $smsMsg);
            } catch (\Throwable $e) {
                Log::warning('ResolveStuckPayments SMS failed: ' . \App\Support\LogRedactor::maskPhonesInString($e->getMessage()));
            }
        }
    }

    protected function markFailed(Payment $payment, string $desc): void
    {
        DB::transaction(function () use ($payment, $desc) {
            $fresh = Payment::where('id', $payment->id)->lockForUpdate()->first();
            if ($fresh && $fresh->status === 'pending') {
                $fresh->update([
                    'status' => 'failed',
                    'notes'  => 'Cancelled by user (1032): ' . $desc,
                ]);
            }
        });
    }

    protected function markExpired(Payment $payment, string $desc): void
    {
        DB::transaction(function () use ($payment, $desc) {
            $fresh = Payment::where('id', $payment->id)->lockForUpdate()->first();
            if ($fresh && $fresh->status === 'pending') {
                $fresh->update([
                    'status' => 'expired',
                    'notes'  => 'STK timeout (1037): ' . $desc,
                ]);
            }
        });
    }

    protected function markUnderReview(Payment $payment, int $code, string $desc): void
    {
        DB::transaction(function () use ($payment, $code, $desc) {
            $fresh = Payment::where('id', $payment->id)->lockForUpdate()->first();
            if ($fresh && $fresh->status === 'pending') {
                $fresh->update([
                    'status' => 'under_reconciliation',
                    'notes'  => "STK query result code {$code}: {$desc}",
                ]);
            }
        });
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ResolveStuckPayments job failed: ' . $e->getMessage());
    }
}
