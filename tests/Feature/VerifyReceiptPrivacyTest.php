<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * L22: public receipt verification privacy.
 * Public (unsigned) → VALID/NOT VALID + amount + date only.
 * Signed → full details.
 * Throttled at 30/min.
 *
 * L51 noted (not fixed here): VerifyController does not filter by status,
 * so a hypothetical payment with a receipt_no but status != completed would
 * still show as VERIFIED. In current flows receipt_no is only set at
 * completion time, so the gap is theoretical. Filed for Phase 2 hardening.
 */
class VerifyReceiptPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function makePayment(): Payment
    {
        $student = Student::factory()->create([
            'name'   => 'ZZQUIETNAMEZZ',
            'adm_no' => 'ZZADM999ZZ',
        ]);

        return Payment::factory()->create([
            'student_id'       => $student->id,
            'amount'           => 2000,
            'receipt_no'       => 'ZZ-RCP-PRIV-001',
            'transaction_code' => 'ZZTXNCODEZZ',
            'recorded_by'      => 'ZZSTAFFZZ',
            'method'           => 'M-Pesa',
        ]);
    }

    public function test_unsigned_url_shows_verdict_and_amount_only(): void
    {
        $payment  = $this->makePayment();
        $response = $this->get("/verify-receipt/{$payment->receipt_no}");

        $response->assertOk();
        $response->assertSee('VERIFIED');
        $response->assertSee('Amount');
        $response->assertSee('Date Issued');
        $response->assertSee(number_format(2000, 2));

        $response->assertDontSee('ZZQUIETNAMEZZ');
        $response->assertDontSee('ZZADM999ZZ');
        $response->assertDontSee('ZZTXNCODEZZ');
        $response->assertDontSee('ZZSTAFFZZ');
    }

    public function test_unsigned_url_with_unknown_receipt_shows_not_verified(): void
    {
        $response = $this->get('/verify-receipt/ZZ-NONEXISTENT-RCP');
        $response->assertOk();
        $response->assertSee('NOT VERIFIED');
    }

    public function test_signed_url_shows_full_details(): void
    {
        $payment = $this->makePayment();
        $url     = URL::signedRoute('verify.receipt', ['receipt_no' => $payment->receipt_no]);
        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('VERIFIED');
        $response->assertSee('ZZQUIETNAMEZZ');
        $response->assertSee('ZZADM999ZZ');
        $response->assertSee('ZZTXNCODEZZ');
        $response->assertSee('ZZSTAFFZZ');
    }

    public function test_signed_url_with_unknown_receipt_shows_not_verified(): void
    {
        $url = URL::signedRoute('verify.receipt', ['receipt_no' => 'ZZ-FAKE-RCP']);
        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('NOT VERIFIED');
    }

    public function test_throttle_enforced_at_30_per_minute(): void
    {
        Cache::flush();
        $payment = $this->makePayment();

        for ($i = 0; $i < 30; $i++) {
            $this->get("/verify-receipt/{$payment->receipt_no}")->assertOk();
        }

        $this->get("/verify-receipt/{$payment->receipt_no}")->assertStatus(429);
    }
}
