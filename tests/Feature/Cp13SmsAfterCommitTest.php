<?php

namespace Tests\Feature;

use App\Services\SmsService;
use Tests\TestCase;

/**
 * CP-13: SMS must fire after the DB transaction commits, not inside it.
 *
 * These are source-level assertions — cheap, fast, and safe against the
 * schema-drift problem that prevents live DB tests.
 */
class Cp13SmsAfterCommitTest extends TestCase
{
    public function test_fee_branch_sends_sms_outside_transaction(): void
    {
        $src = file_get_contents(base_path('app/Jobs/ProcessMpesaCallback.php'));
        $this->assertNotFalse($src);

        // Locate the fee-branch transaction closure and the following block.
        $feeTransaction = strpos($src, 'DB::transaction(function () use ($payment');
        $this->assertNotFalse($feeTransaction, 'Fee transaction not found');

        // Look at the next 3000 chars after the transaction start.
        $slice = substr($src, $feeTransaction, 3000);

        // Inside the transaction closure, there must be no send() call.
        $transactionEnd = strpos($slice, '});');
        $this->assertNotFalse($transactionEnd, 'Transaction closure end not found');
        $insideTransaction = substr($slice, 0, $transactionEnd);

        $this->assertStringNotContainsString(
            'app(SmsService::class)->send(',
            $insideTransaction,
            'Fee branch still sends SMS inside the DB transaction'
        );

        // After the transaction, the send must fire.
        $this->assertStringContainsString(
            '// SMS after commit',
            $src,
            'Fee branch missing after-commit SMS comment'
        );
        $this->assertStringContainsString(
            'app(SmsService::class)->send($smsTo, $smsMsg);',
            $src,
            'Fee branch missing after-commit send call'
        );
    }

    public function test_wallet_branch_sends_sms_outside_transaction(): void
    {
        $src = file_get_contents(base_path('app/Jobs/ProcessMpesaCallback.php'));
        $this->assertNotFalse($src);

        $walletTransaction = strpos($src, 'DB::transaction(function () use ($wallet');
        $this->assertNotFalse($walletTransaction, 'Wallet transaction not found');

        $slice = substr($src, $walletTransaction, 3000);
        $transactionEnd = strpos($slice, '});');
        $this->assertNotFalse($transactionEnd, 'Wallet transaction closure end not found');
        $insideTransaction = substr($slice, 0, $transactionEnd);

        $this->assertStringNotContainsString(
            'app(SmsService::class)->send(',
            $insideTransaction,
            'Wallet branch still sends SMS inside the DB transaction'
        );

        $this->assertStringContainsString(
            '// SMS after commit. Also replaces the previous silent empty catch',
            $src,
            'Wallet branch missing after-commit SMS comment'
        );
        $this->assertStringContainsString(
            'Wallet SMS failed',
            $src,
            'Wallet branch does not log SMS failures'
        );
    }

    public function test_wallet_branch_no_longer_swallows_sms_errors_silently(): void
    {
        $src = file_get_contents(base_path('app/Jobs/ProcessMpesaCallback.php'));
        $this->assertStringNotContainsString(
            '} catch (\Throwable $e) {}',
            $src,
            'Wallet branch still has the empty catch that swallowed SMS errors'
        );
    }
}