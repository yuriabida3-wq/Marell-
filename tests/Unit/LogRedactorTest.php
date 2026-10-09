<?php

namespace Tests\Unit;

use App\Support\LogRedactor;
use PHPUnit\Framework\TestCase;

class LogRedactorTest extends TestCase
{
    public function test_masks_phonenumber_key(): void
    {
        $out = LogRedactor::redact(['PhoneNumber' => '254712345678']);
        $this->assertSame('[redacted]', $out['PhoneNumber']);
    }

    public function test_masks_phone_key_case_insensitive(): void
    {
        $out = LogRedactor::redact(['phone' => '254712345678', 'PHONE' => '254712345678']);
        $this->assertSame('[redacted]', $out['phone']);
        $this->assertSame('[redacted]', $out['PHONE']);
    }

    public function test_masks_mpesa_receipt_key(): void
    {
        $out = LogRedactor::redact(['MpesaReceiptNumber' => 'ABC123XYZ']);
        $this->assertSame('[redacted]', $out['MpesaReceiptNumber']);
    }

    public function test_masks_checkout_request_id_key(): void
    {
        $out = LogRedactor::redact(['CheckoutRequestID' => 'ws_CO_123']);
        $this->assertSame('[redacted]', $out['CheckoutRequestID']);
    }

    public function test_masks_password_and_pin(): void
    {
        $out = LogRedactor::redact(['password' => 'secret', 'pin' => '1234']);
        $this->assertSame('[redacted]', $out['password']);
        $this->assertSame('[redacted]', $out['pin']);
    }

    public function test_recurses_into_nested_arrays(): void
    {
        $out = LogRedactor::redact([
            'Body' => [
                'stkCallback' => [
                    'PhoneNumber' => '254712345678',
                    'ResultCode'  => 0,
                ],
            ],
        ]);
        $this->assertSame('[redacted]', $out['Body']['stkCallback']['PhoneNumber']);
        $this->assertSame(0, $out['Body']['stkCallback']['ResultCode']);
    }

    public function test_empty_array_returns_empty(): void
    {
        $this->assertSame([], LogRedactor::redact([]));
    }

    public function test_non_sensitive_keys_untouched(): void
    {
        $out = LogRedactor::redact(['amount' => 100, 'result' => 'ok']);
        $this->assertSame(100, $out['amount']);
        $this->assertSame('ok', $out['result']);
    }

    public function test_masks_phone_in_free_text(): void
    {
        $out = LogRedactor::maskPhonesInString('Payment from 254712345678 received');
        $this->assertStringNotContainsString('254712345678', $out);
        $this->assertStringContainsString('2547****678', $out);
    }

    public function test_leaves_clean_string_unchanged(): void
    {
        $in = 'Nothing sensitive here';
        $this->assertSame($in, LogRedactor::maskPhonesInString($in));
    }
}
