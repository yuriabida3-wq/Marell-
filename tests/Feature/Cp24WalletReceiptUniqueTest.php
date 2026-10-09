<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-24: unique indexes on wallet_transactions.mpesa_receipt and
 * wallet_transactions.checkout_request_id.
 */
class Cp24WalletReceiptUniqueTest extends TestCase
{
    private function migrationPath(): string
    {
        $files = glob(base_path('database/migrations/*_add_unique_receipt_and_coid_to_wallet_transactions.php'));
        $this->assertNotEmpty($files, 'CP-24 migration file missing');
        return $files[0];
    }

    public function test_migration_exists(): void
    {
        $this->assertFileExists($this->migrationPath());
    }

    public function test_migration_has_receipt_duplicate_gate(): void
    {
        $src = (string) file_get_contents($this->migrationPath());
        $this->assertStringContainsString('duplicate mpesa_receipt values exist', $src);
        $this->assertStringContainsString('HAVING c > 1', $src);
    }

    public function test_migration_has_coid_duplicate_gate(): void
    {
        $src = (string) file_get_contents($this->migrationPath());
        $this->assertStringContainsString('duplicate checkout_request_id values exist', $src);
    }

    public function test_migration_throws_on_duplicates(): void
    {
        $src = (string) file_get_contents($this->migrationPath());
        $this->assertStringContainsString('throw new RuntimeException', $src);
    }

    public function test_migration_adds_both_unique_indexes(): void
    {
        $src = (string) file_get_contents($this->migrationPath());
        $this->assertStringContainsString("'wallet_transactions_mpesa_receipt_unique'", $src);
        $this->assertStringContainsString("'wallet_transactions_checkout_request_id_unique'", $src);
    }

    public function test_migration_down_drops_both_indexes(): void
    {
        $src = (string) file_get_contents($this->migrationPath());
        $this->assertStringContainsString("dropUnique('wallet_transactions_mpesa_receipt_unique')", $src);
        $this->assertStringContainsString("dropUnique('wallet_transactions_checkout_request_id_unique')", $src);
    }
}