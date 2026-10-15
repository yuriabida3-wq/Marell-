<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * L19: parent OTP must be hashed at rest, single-use, 5-minute expiry.
 */
class OtpHashingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_otp_is_not_stored_as_plaintext(): void
    {
        Student::factory()->create(['parent_phone' => '254712345678']);

        $this->post('/parent/send-otp', ['phone' => '0712345678']);

        $cached = Cache::get('parent_otp:254712345678');
        $this->assertNotNull($cached, 'OTP should have been cached');

        // Must be a bcrypt hash, not the raw 6-digit code.
        $this->assertMatchesRegularExpression('/^\$2[ay]\$/', (string) $cached);
        $this->assertDoesNotMatchRegularExpression('/^\d{6}$/', (string) $cached);
    }

    public function test_verify_accepts_the_correct_code(): void
    {
        Student::factory()->create(['parent_phone' => '254712345678']);

        // Simulate: send OTP, then read the plaintext from the SMS stub log
        // is not practical. Instead, bypass sendOtp by writing the same
        // hashed value the controller would have written for a known code.
        $code = '123456';
        Cache::put('parent_otp:254712345678', Hash::make($code), now()->addMinutes(5));

        $response = $this
            ->withSession(['parent_pending_phone' => '254712345678'])
            ->post('/parent/verify', ['otp' => $code]);

        $response->assertRedirect(route('parent.dashboard'));
        $this->assertSame('254712345678', session('parent_phone'));
    }

    public function test_verify_rejects_a_wrong_code(): void
    {
        Student::factory()->create(['parent_phone' => '254712345678']);

        Cache::put('parent_otp:254712345678', Hash::make('123456'), now()->addMinutes(5));

        $response = $this
            ->withSession(['parent_pending_phone' => '254712345678'])
            ->post('/parent/verify', ['otp' => '999999']);

        $response->assertSessionHas('error');
        $this->assertNull(session('parent_phone'));
    }

    public function test_verify_rejects_a_used_code_second_time(): void
    {
        Student::factory()->create(['parent_phone' => '254712345678']);

        Cache::put('parent_otp:254712345678', Hash::make('123456'), now()->addMinutes(5));

        // First use succeeds and consumes the code.
        $this->withSession(['parent_pending_phone' => '254712345678'])
            ->post('/parent/verify', ['otp' => '123456'])
            ->assertRedirect(route('parent.dashboard'));

        // Second attempt with the same code should fail (already consumed).
        $response = $this->withSession(['parent_pending_phone' => '254712345678'])
            ->post('/parent/verify', ['otp' => '123456']);

        $response->assertSessionHas('error');
    }

    public function test_otp_send_is_rate_limited_per_phone(): void
    {
        Student::factory()->create(['parent_phone' => '254712345678']);

        // Limit is 3 sends per 5 minutes. Fourth must be refused.
        for ($i = 0; $i < 3; $i++) {
            $this->post('/parent/send-otp', ['phone' => '0712345678']);
        }

        $response = $this->post('/parent/send-otp', ['phone' => '0712345678']);
        $response->assertSessionHas('error');
    }
}
