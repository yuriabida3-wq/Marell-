<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-12: initiated_amount is captured at creation and never overwritten.
 * Source-level assertions only — no DB, safe against schema drift.
 */
class Cp12InitiatedAmountTest extends TestCase
{
    public function test_payment_model_lists_initiated_amount(): void
    {
        $src = file_get_contents(base_path('app/Models/Payment.php'));
        $this->assertNotFalse($src);
        $this->assertStringContainsString("'initiated_amount'", $src);
    }

    public function test_wallet_model_lists_initiated_amount(): void
    {
        $src = file_get_contents(base_path('app/Models/WalletTransaction.php'));
        $this->assertNotFalse($src);
        $this->assertStringContainsString("'initiated_amount'", $src);
    }

    public function test_payment_controller_sets_initiated_amount(): void
    {
        $src = file_get_contents(base_path('app/Http/Controllers/PaymentController.php'));
        $this->assertNotFalse($src);
        $this->assertStringContainsString(
            "'initiated_amount' => \$data['amount']",
            $src,
            'PaymentController@initiate does not set initiated_amount'
        );
    }

    public function test_wallet_controller_sets_initiated_amount(): void
    {
        $src = file_get_contents(base_path('app/Http/Controllers/WalletController.php'));
        $this->assertNotFalse($src);
        $this->assertStringContainsString(
            "'initiated_amount' => \$data['amount']",
            $src,
            'WalletController@parentTopUp does not set initiated_amount'
        );
    }

    public function test_callback_job_never_overwrites_initiated_amount(): void
    {
        $src = file_get_contents(base_path('app/Jobs/ProcessMpesaCallback.php'));
        $this->assertNotFalse($src);
        $this->assertStringNotContainsString(
            "'initiated_amount'",
            $src,
            'Callback job references initiated_amount — must never touch it'
        );
    }

    public function test_migration_exists(): void
    {
        $files = glob(base_path('database/migrations/*_add_initiated_amount_to_payments_and_wallet_tables.php'));
        $this->assertNotEmpty($files, 'CP-12 migration file missing');
    }
}