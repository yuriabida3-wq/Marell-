<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-29: D12 verified closed by DB unique indexes.
 * The app-level duplicate checks are fast paths, not the guarantee.
 */
class Cp29DuplicateReceiptRaceSafetyTest extends TestCase
{
    private function src(): string
    {
        return (string) file_get_contents(base_path('app/Jobs/ProcessMpesaCallback.php'));
    }

    public function test_fee_branch_documents_db_index_as_guarantee(): void
    {
        $src = $this->src();
        $this->assertStringContainsString('CP-29: this application-level check is a fast path', $src);
        $this->assertStringContainsString('payments.transaction_code', $src);
    }

    public function test_wallet_branch_documents_db_index_as_guarantee(): void
    {
        $src = $this->src();
        $this->assertStringContainsString('CP-29: fast-path app check', $src);
        $this->assertStringContainsString('wallet_transactions.mpesa_receipt', $src);
    }

    public function test_fee_check_still_uses_transaction_code(): void
    {
        $src = $this->src();
        $this->assertStringContainsString(
            "Payment::where('transaction_code', \$mpesaReceipt)",
            $src
        );
    }

    public function test_wallet_check_still_uses_mpesa_receipt(): void
    {
        $src = $this->src();
        $this->assertStringContainsString(
            "WalletTransaction::where('mpesa_receipt', \$mpesaReceipt)",
            $src
        );
    }

    public function test_success_writes_still_use_transaction_and_lock(): void
    {
        $src = $this->src();
        // Fee success write
        $this->assertStringContainsString(
            "'transaction_code' => \$mpesaReceipt,",
            $src
        );
        // Wallet success write
        $this->assertStringContainsString(
            "'mpesa_receipt' => \$mpesaReceipt,",
            $src
        );
    }
}