<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-13b: callback SMS recipient is the registered parent phone only.
 * The callback-supplied phone is no longer consulted for SMS routing.
 * Payer phone is still written to wallet_transactions as evidence.
 */
class Cp13bParentRecipientTest extends TestCase
{
    public function test_fee_branch_uses_registered_parent_phone(): void
    {
        $src = file_get_contents(base_path('app/Jobs/ProcessMpesaCallback.php'));
        $this->assertNotFalse($src);

        $this->assertStringContainsString(
            'MpesaService::normalizePhone($student->parent_phone)',
            $src,
            'Fee branch does not use registered parent phone for SMS recipient'
        );
    }

    public function test_no_line_uses_payload_phone_for_sms_routing(): void
    {
        $src = file_get_contents(base_path('app/Jobs/ProcessMpesaCallback.php'));
        $this->assertNotFalse($src);

        $this->assertStringNotContainsString(
            'normalizePhone($phone ?:',
            $src,
            'A branch still prefers the payload phone for SMS routing'
        );
    }

    public function test_wallet_payer_phone_still_recorded_as_evidence(): void
    {
        $src = file_get_contents(base_path('app/Jobs/ProcessMpesaCallback.php'));
        $this->assertNotFalse($src);

        $this->assertStringContainsString(
            "'phone'         => \$phone,",
            $src,
            'Wallet branch no longer records payer phone as evidence'
        );
    }
}