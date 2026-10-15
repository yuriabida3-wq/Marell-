<?php

namespace Tests\Feature;

use App\Jobs\ProcessMpesaCallback;
use App\Models\PaymentCallback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * L3: raw M-Pesa callback payloads are persisted before dispatch.
 * Duplicate callbacks (Daraja retries) are idempotent.
 */
class PaymentCallbackPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.mpesa.callback_secret'          => 'test-secret-abc',
            'services.mpesa.callback_legacy_enabled'  => true,
            'services.mpesa.callback_ip_allowlist'    => '',
        ]);
    }

    private function payload(string $coid = 'ws_CO_12345678', string $receipt = 'NLJ7RT61SV'): array
    {
        return [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID'   => 'mr-1',
                    'CheckoutRequestID'   => $coid,
                    'ResultCode'          => 0,
                    'ResultDesc'          => 'The service request is processed successfully.',
                    'CallbackMetadata'    => [
                        'Item' => [
                            ['Name' => 'Amount',             'Value' => 100],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => $receipt],
                            ['Name' => 'PhoneNumber',        'Value' => 254712345678],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_secure_callback_persists_payload_and_dispatches(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/mpesa/cb/test-secret-abc', $this->payload());

        $response->assertStatus(200);
        $response->assertJson(['ResultCode' => 0]);

        $this->assertDatabaseCount('payment_callbacks', 1);
        $this->assertDatabaseHas('payment_callbacks', [
            'checkout_request_id'  => 'ws_CO_12345678',
            'mpesa_receipt_number' => 'NLJ7RT61SV',
            'source'               => 'secure',
        ]);

        Queue::assertPushed(ProcessMpesaCallback::class, 1);
    }

    public function test_duplicate_callback_is_idempotent(): void
    {
        Queue::fake();

        $payload = $this->payload();

        $this->postJson('/api/mpesa/cb/test-secret-abc', $payload)->assertStatus(200);
        $this->postJson('/api/mpesa/cb/test-secret-abc', $payload)->assertStatus(200);

        // Only one row, only one dispatch, both requests return ResultCode 0.
        $this->assertDatabaseCount('payment_callbacks', 1);
        Queue::assertPushed(ProcessMpesaCallback::class, 1);
    }

    public function test_wrong_secret_rejects_and_persists_nothing(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/mpesa/cb/wrong-secret', $this->payload());

        $response->assertStatus(404);
        $this->assertDatabaseCount('payment_callbacks', 0);
        Queue::assertNothingPushed();
    }

    public function test_malformed_payload_rejected_before_persist(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/mpesa/cb/test-secret-abc', [
            'Body' => ['stkCallback' => ['ResultCode' => 0]], // no CheckoutRequestID
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseCount('payment_callbacks', 0);
        Queue::assertNothingPushed();
    }

    public function test_legacy_endpoint_marks_source_legacy(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/mpesa/callback', $this->payload());

        $response->assertStatus(200);
        $this->assertDatabaseHas('payment_callbacks', [
            'checkout_request_id' => 'ws_CO_12345678',
            'source'              => 'legacy',
        ]);
        Queue::assertPushed(ProcessMpesaCallback::class, 1);
    }
}
