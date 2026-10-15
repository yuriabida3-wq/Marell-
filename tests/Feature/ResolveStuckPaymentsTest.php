<?php

namespace Tests\Feature;

use App\Jobs\ResolveStuckPayments;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * L4: stuck pending payments are resolved via STK status query.
 */
class ResolveStuckPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.mpesa.env'             => 'sandbox',
            'services.mpesa.consumer_key'    => 'test-key',
            'services.mpesa.consumer_secret' => 'test-secret',
            'services.mpesa.shortcode'       => '174379',
            'services.mpesa.passkey'         => 'test-passkey',
            'services.mpesa.callback_url'    => 'https://example.test/api/mpesa/cb/xyz',
        ]);

        Cache::forget('mpesa_access_token');
    }

    protected function fakeDaraja(array $queryResponse): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
                'access_token' => 'fake-token',
                'expires_in'   => 3599,
            ], 200),
            'sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query' => Http::response($queryResponse, 200),
        ]);
    }

    protected function makePendingPayment(int $minutesOld = 5): Payment
    {
        $student = Student::factory()->create([
            'total_fee'    => 30000,
            'paid_amount'  => 5000,
            'balance'      => 25000,
        ]);

        $payment = Payment::factory()->pending()->create([
            'student_id'          => $student->id,
            'amount'              => 2000,
            'initiated_amount'    => 2000,
            'checkout_request_id' => 'ws_CO_TEST_' . uniqid(),
            'created_at'          => now()->subMinutes($minutesOld),
            'updated_at'          => now()->subMinutes($minutesOld),
        ]);

        return $payment;
    }

    public function test_skips_payments_newer_than_two_minutes(): void
    {
        Http::fake();
        $payment = $this->makePendingPayment(1);

        (new ResolveStuckPayments)->handle();

        $this->assertSame('pending', $payment->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_result_code_zero_marks_completed_with_receipt(): void
    {
        $this->fakeDaraja([
            'ResponseCode'       => '0',
            'ResultCode'         => '0',
            'ResultDesc'         => 'The service request is processed successfully.',
            'MpesaReceiptNumber' => 'NLJ7RT61SV',
        ]);

        $payment = $this->makePendingPayment(5);
        $student = $payment->student;

        (new ResolveStuckPayments)->handle();

        $fresh = $payment->fresh();
        $this->assertSame('completed', $fresh->status);
        $this->assertSame('NLJ7RT61SV', $fresh->transaction_code);
        $this->assertMatchesRegularExpression('/^RCP-\d{4}-\d{5}$/', (string) $fresh->receipt_no);

        $student->refresh();
        $this->assertSame('7000.00', (string) $student->paid_amount);
        $this->assertSame('23000.00', (string) $student->balance);
    }

    public function test_result_code_1032_marks_failed(): void
    {
        $this->fakeDaraja([
            'ResponseCode' => '0',
            'ResultCode'   => '1032',
            'ResultDesc'   => 'Request cancelled by user',
        ]);

        $payment = $this->makePendingPayment(5);

        (new ResolveStuckPayments)->handle();

        $this->assertSame('failed', $payment->fresh()->status);
    }

    public function test_result_code_1037_marks_expired(): void
    {
        $this->fakeDaraja([
            'ResponseCode' => '0',
            'ResultCode'   => '1037',
            'ResultDesc'   => 'Timeout',
        ]);

        $payment = $this->makePendingPayment(5);

        (new ResolveStuckPayments)->handle();

        $this->assertSame('expired', $payment->fresh()->status);
    }

    public function test_unknown_result_code_marks_under_reconciliation(): void
    {
        $this->fakeDaraja([
            'ResponseCode' => '0',
            'ResultCode'   => '9999',
            'ResultDesc'   => 'Something unexpected',
        ]);

        $payment = $this->makePendingPayment(5);

        (new ResolveStuckPayments)->handle();

        $this->assertSame('under_reconciliation', $payment->fresh()->status);
    }

    public function test_success_without_receipt_goes_to_under_reconciliation(): void
    {
        $this->fakeDaraja([
            'ResponseCode' => '0',
            'ResultCode'   => '0',
            'ResultDesc'   => 'OK',
            // no MpesaReceiptNumber
        ]);

        $payment = $this->makePendingPayment(5);

        (new ResolveStuckPayments)->handle();

        $this->assertSame('under_reconciliation', $payment->fresh()->status);
    }

    public function test_rerunning_does_not_double_process_resolved_payment(): void
    {
        $this->fakeDaraja([
            'ResponseCode'       => '0',
            'ResultCode'         => '0',
            'ResultDesc'         => 'OK',
            'MpesaReceiptNumber' => 'NLJ7RT61SV',
        ]);

        $payment = $this->makePendingPayment(5);
        $student = $payment->student;

        (new ResolveStuckPayments)->handle();
        (new ResolveStuckPayments)->handle();

        $student->refresh();
        $this->assertSame('7000.00', (string) $student->paid_amount);
        $this->assertSame('23000.00', (string) $student->balance);
    }
}
