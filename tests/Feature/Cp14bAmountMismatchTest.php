<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-14b: amount mismatch holds for review. Source-level assertions.
 */
class Cp14bAmountMismatchTest extends TestCase
{
    private function src(): string
    {
        return (string) file_get_contents(base_path('app/Jobs/ProcessMpesaCallback.php'));
    }

    public function test_fee_branch_has_mismatch_check(): void
    {
        $src = $this->src();
        $this->assertStringContainsString("amount mismatch on payment", $src);
        $this->assertStringContainsString("\$initiated = (float) (\$payment->initiated_amount ?? 0)", $src);
    }

    public function test_wallet_branch_has_mismatch_check(): void
    {
        $src = $this->src();
        $this->assertStringContainsString("amount mismatch on wallet", $src);
        $this->assertStringContainsString("\$initiated = (float) (\$wallet->initiated_amount ?? 0)", $src);
    }

    public function test_both_branches_use_abs_tolerance(): void
    {
        $src = $this->src();
        $this->assertSame(2, substr_count($src, 'abs($paidAmount - $initiated) > 0.001'));
    }

    public function test_both_branches_guard_against_zero_initiated(): void
    {
        $src = $this->src();
        $this->assertSame(2, substr_count($src, '$initiated > 0 &&'));
    }

    public function test_both_branches_hold_with_under_reconciliation(): void
    {
        $src = $this->src();
        $this->assertSame(2, substr_count($src, "'Amount mismatch: initiated %s, callback %s — held for review'"));
    }

    public function test_log_context_has_no_pii_keys(): void
    {
        $src = $this->src();
        // Find the two mismatch log lines and check the following ~120 chars for PII keys.
        $needles = [
            'amount mismatch on payment',
            'amount mismatch on wallet',
        ];
        foreach ($needles as $needle) {
            $pos = strpos($src, $needle);
            $this->assertNotFalse($pos);
            $slice = substr($src, $pos, 200);
            $this->assertStringNotContainsString("'phone'", $slice);
            $this->assertStringNotContainsString("'receipt'", $slice);
            $this->assertStringNotContainsString("'mpesa_receipt'", $slice);
        }
    }

    public function test_mismatch_uses_transaction_and_lock(): void
    {
        $src = $this->src();
        // Count lockForUpdate occurrences; CP-14a+14b total: 2 fail + 2 missing + 2 dup + 2 mismatch + 2 success = 10
        $count = substr_count($src, 'lockForUpdate()');
        $this->assertGreaterThanOrEqual(10, $count, "Expected >=10 lockForUpdate calls, got {$count}");
    }
}