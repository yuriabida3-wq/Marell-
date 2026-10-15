<?php

namespace Tests\Feature;

use App\Jobs\ProcessMpesaCallback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * CP-02: /api/mpesa/callback lockdown.
 *
 * All tests use Queue::fake() so ProcessMpesaCallback NEVER runs.
 * No writes to payments or wallet_transactions. Raw payloads are written
 * to payment_callbacks (L3), so RefreshDatabase isolates each test.
 */
class Cp02MpesaCallbackSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(): array
    {
        return [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => 'test-merchant',
                    'CheckoutRequestID' => 'ws_CO_TEST_123',
                    'ResultCode'        => 0,
                    'ResultDesc'        => 'ok',
                ],
            ],
        ];
    }

    public function test_legacy_path_404_when_flag_off(): void
    {
        config(['services.mpesa.callback_legacy_enabled' => false]);
        Queue::fake();

        $this->postJson('/api/mpesa/callback', $this->validPayload())
            ->assertStatus(404);

        Queue::assertNothingPushed();
    }

    public function test_legacy_path_accepts_valid_payload_when_flag_on(): void
    {
        config([
            'services.mpesa.callback_legacy_enabled' => true,
            'services.mpesa.callback_ip_allowlist'   => '',
        ]);
        Queue::fake();

        $this->postJson('/api/mpesa/callback', $this->validPayload())
            ->assertStatus(200)
            ->assertJson(['ResultCode' => 0]);

        Queue::assertPushed(ProcessMpesaCallback::class, 1);
    }

    public function test_secure_path_404_on_wrong_secret(): void
    {
        config(['services.mpesa.callback_secret' => 'correct-secret']);
        Queue::fake();

        $this->postJson('/api/mpesa/cb/wrong-secret', $this->validPayload())
            ->assertStatus(404);

        Queue::assertNothingPushed();
    }

    public function test_secure_path_404_when_secret_config_empty(): void
    {
        config(['services.mpesa.callback_secret' => '']);
        Queue::fake();

        $this->postJson('/api/mpesa/cb/anything', $this->validPayload())
            ->assertStatus(404);

        Queue::assertNothingPushed();
    }

    public function test_secure_path_400_on_missing_checkout_request_id(): void
    {
        config([
            'services.mpesa.callback_secret'       => 's3cr3t',
            'services.mpesa.callback_ip_allowlist' => '',
        ]);
        Queue::fake();

        $this->postJson('/api/mpesa/cb/s3cr3t', [
            'Body' => ['stkCallback' => ['ResultCode' => 0]],
        ])->assertStatus(400);

        Queue::assertNothingPushed();
    }

    public function test_secure_path_accepts_valid_payload(): void
    {
        config([
            'services.mpesa.callback_secret'       => 's3cr3t',
            'services.mpesa.callback_ip_allowlist' => '',
        ]);
        Queue::fake();

        $this->postJson('/api/mpesa/cb/s3cr3t', $this->validPayload())
            ->assertStatus(200)
            ->assertJson(['ResultCode' => 0]);

        Queue::assertPushed(ProcessMpesaCallback::class, 1);
    }

    public function test_ip_allowlist_rejects_non_listed_ip(): void
    {
        config([
            'services.mpesa.callback_secret'         => 's3cr3t',
            'services.mpesa.callback_ip_allowlist'   => '10.0.0.1,10.0.0.2',
        ]);
        Queue::fake();

        $this->postJson('/api/mpesa/cb/s3cr3t', $this->validPayload())
            ->assertStatus(403);

        Queue::assertNothingPushed();
    }

    public function test_ip_allowlist_empty_permits_any_ip(): void
    {
        config([
            'services.mpesa.callback_secret'       => 's3cr3t',
            'services.mpesa.callback_ip_allowlist' => '',
        ]);
        Queue::fake();

        $this->postJson('/api/mpesa/cb/s3cr3t', $this->validPayload())
            ->assertStatus(200);
    }
}
