<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-23: OTP hardening — per-phone limits, uniform responses, hash_equals.
 */
class Cp23OtpHardeningTest extends TestCase
{
    private function parentPortal(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/ParentPortalController.php'));
    }

    private function unified(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/UnifiedAuthController.php'));
    }

    public function test_parent_portal_imports_rate_limiter(): void
    {
        $this->assertStringContainsString(
            'use Illuminate\Support\Facades\RateLimiter;',
            $this->parentPortal()
        );
    }

    public function test_parent_portal_has_per_phone_send_limit(): void
    {
        $src = $this->parentPortal();
        $this->assertStringContainsString("parent-otp-send:", $src);
        $this->assertStringContainsString('RateLimiter::tooManyAttempts($sendKey, 3)', $src);
        $this->assertStringContainsString('RateLimiter::hit($sendKey, 300)', $src);
    }

    public function test_parent_portal_has_per_phone_verify_limit(): void
    {
        $src = $this->parentPortal();
        $this->assertStringContainsString("parent-otp-verify:", $src);
        $this->assertStringContainsString('RateLimiter::tooManyAttempts($verifyKey, 5)', $src);
        $this->assertStringContainsString('RateLimiter::hit($verifyKey, 900)', $src);
    }

    public function test_parent_portal_uses_hashed_otp_with_constant_time_check(): void
    {
        $src = $this->parentPortal();

        // L19: OTP is stored hashed at rest. Hash::check performs the
        // constant-time comparison internally after verifying the hash.
        $this->assertStringContainsString('Hash::check((string) $data[\'otp\'], (string) $cached)', $src);

        // Must NOT compare plaintext OTP directly.
        $this->assertStringNotContainsString("\$cached !== \$data['otp']", $src);
        $this->assertStringNotContainsString("hash_equals((string) \$cached", $src);

        // Must NOT store plaintext OTP in the cache.
        $this->assertStringContainsString('Hash::make($otp)', $src);
    }

    public function test_parent_portal_no_longer_leaks_registration(): void
    {
        $src = $this->parentPortal();
        $this->assertStringNotContainsString(
            "'No student is registered with that phone number",
            $src,
            'ParentPortalController still returns an explicit enumeration error'
        );
        $this->assertStringContainsString(
            'If that number is registered, an OTP has been sent.',
            $src
        );
    }

    public function test_parent_portal_send_limit_runs_before_registration_check(): void
    {
        $src = $this->parentPortal();
        $sendPos = strpos($src, 'RateLimiter::tooManyAttempts($sendKey, 3)');
        $regPos  = strpos($src, "Student::where('parent_phone', \$phone)->exists()");
        $this->assertNotFalse($sendPos);
        $this->assertNotFalse($regPos);
        $this->assertLessThan(
            $regPos,
            $sendPos,
            'send rate limit must run before the registration check'
        );
    }

    public function test_unified_has_matching_limits_and_hash_equals(): void
    {
        $src = $this->unified();
        $this->assertStringContainsString("parent-otp-send:", $src);
        $this->assertStringContainsString('RateLimiter::tooManyAttempts($key, 3)', $src);
        $this->assertStringContainsString("parent-otp-verify:", $src);
        $this->assertStringContainsString('RateLimiter::tooManyAttempts($verifyKey, 5)', $src);
        $this->assertStringContainsString('hash_equals((string) $cached', $src);
    }

    public function test_unified_no_longer_leaks_registration(): void
    {
        $this->assertStringNotContainsString(
            "'No student is registered with that phone number",
            $this->unified()
        );
    }

    public function test_session_regenerate_still_present_in_parent_verify(): void
    {
        // CP-04 regression guard: session fixation fix must survive CP-23.
        $this->assertStringContainsString(
            '$request->session()->regenerate();',
            $this->parentPortal()
        );
        $this->assertStringContainsString(
            '$request->session()->regenerate();',
            $this->unified()
        );
    }
}