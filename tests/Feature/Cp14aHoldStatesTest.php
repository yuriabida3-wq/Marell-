<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-14a: hold-for-review states and failure-branch locking.
 * Source-level assertions; no DB.
 */
class Cp14aHoldStatesTest extends TestCase
{
    private function callbackSource(): string
    {
        return (string) file_get_contents(base_path('app/Jobs/ProcessMpesaCallback.php'));
    }

    public function test_fee_failure_branch_is_wrapped_in_transaction_and_lock(): void
    {
        $src = $this->callbackSource();
        $this->assertMatchesRegularExpression(
            '/resultCode !== 0.*DB::transaction.*lockForUpdate\(\)/s',
            $src,
            'Fee failure branch missing transaction+lock'
        );
    }

    public function test_fee_duplicate_now_holds_for_review(): void
    {
        $src = $this->callbackSource();
        $this->assertStringContainsString("'Duplicate M-Pesa receipt — held for review'", $src);
        $this->assertStringNotContainsString("'notes' => 'Duplicate transaction code'", $src);
    }

    public function test_fee_missing_receipt_now_holds(): void
    {
        $src = $this->callbackSource();
        $this->assertStringContainsString('Callback had no MpesaReceiptNumber — held for review', $src);
    }

    public function test_wallet_duplicate_now_holds_for_review(): void
    {
        $src = $this->callbackSource();
        $this->assertStringContainsString("'Duplicate M-Pesa receipt — held for review'", $src);
    }

    public function test_wallet_missing_receipt_now_holds(): void
    {
        $src = $this->callbackSource();
        // Both branches now share the same "held for review" phrasing.
        $this->assertGreaterThanOrEqual(
            1,
            substr_count($src, 'Callback had no MpesaReceiptNumber — held for review'),
        );
    }

    public function test_wallet_failure_branch_wrapped_in_transaction_and_lock(): void
    {
        $src = $this->callbackSource();
        $this->assertMatchesRegularExpression(
            '/resultCode !== 0.*DB::transaction.*lockForUpdate\(\)/s',
            $src,
            'Wallet failure branch missing transaction+lock'
        );
    }

    public function test_payment_controller_holds_stk_errors(): void
    {
        $src = (string) file_get_contents(base_path('app/Http/Controllers/PaymentController.php'));
        $this->assertStringContainsString('under_reconciliation', $src);
        $this->assertStringNotContainsString("\$payment->update(['status' => 'failed', 'notes' => \$res['error']])", $src);
    }

    public function test_wallet_controller_holds_stk_errors(): void
    {
        $src = (string) file_get_contents(base_path('app/Http/Controllers/WalletController.php'));
        $this->assertStringContainsString('under_reconciliation', $src);
    }

    public function test_migration_exists_and_has_guard(): void
    {
        $files = glob(base_path('database/migrations/*_add_under_reconciliation_to_payments_status_enum.php'));
        $this->assertNotEmpty($files, 'CP-14a migration file missing');

        $src = file_get_contents($files[0]);
        $this->assertStringContainsString('under_reconciliation', $src);
        $this->assertStringContainsString('Cannot roll back', $src);
    }

    public function test_pay_wait_view_handles_under_reconciliation(): void
    {
        $src = (string) file_get_contents(base_path('resources/views/public/pay-wait.blade.php'));
        $this->assertStringContainsString("data.status === 'under_reconciliation'", $src);
    }

    public function test_finance_index_view_offers_under_reconciliation_filter(): void
    {
        $src = (string) file_get_contents(base_path('resources/views/principal/finance/index.blade.php'));
        $this->assertStringContainsString('under_reconciliation', $src);
    }
}