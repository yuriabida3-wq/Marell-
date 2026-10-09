<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-27: rate limits on /pay initiate and wallet top-up.
 */
class Cp27PayInitRateLimitTest extends TestCase
{
    private function payment(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/PaymentController.php'));
    }

    private function wallet(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/WalletController.php'));
    }

    public function test_payment_controller_imports_rate_limiter(): void
    {
        $this->assertStringContainsString(
            'use Illuminate\Support\Facades\RateLimiter;',
            $this->payment()
        );
    }

    public function test_per_phone_limit(): void
    {
        $src = $this->payment();
        $this->assertStringContainsString("'pay-init:phone:'", $src);
        $this->assertStringContainsString('RateLimiter::tooManyAttempts($phoneKey, 5)', $src);
        $this->assertStringContainsString('RateLimiter::hit($phoneKey, 900)', $src);
    }

    public function test_per_admission_limit(): void
    {
        $src = $this->payment();
        $this->assertStringContainsString("'pay-init:adm:'", $src);
        $this->assertStringContainsString('RateLimiter::tooManyAttempts($admKey, 5)', $src);
        $this->assertStringContainsString('RateLimiter::hit($admKey, 900)', $src);
    }

    public function test_global_limit(): void
    {
        $src = $this->payment();
        $this->assertStringContainsString("'pay-init:global'", $src);
        $this->assertStringContainsString('RateLimiter::tooManyAttempts($globalKey, 200)', $src);
        $this->assertStringContainsString('RateLimiter::hit($globalKey, 3600)', $src);
    }

    public function test_checks_run_before_payment_create(): void
    {
        $src = $this->payment();
        $rlPos  = strpos($src, 'RateLimiter::tooManyAttempts($globalKey');
        $create = strpos($src, 'Payment::create([');
        $this->assertNotFalse($rlPos);
        $this->assertNotFalse($create);
        $this->assertLessThan($create, $rlPos, 'rate limit must run before Payment::create');
    }

    public function test_checks_run_before_stk_push(): void
    {
        $src = $this->payment();
        $rlPos = strpos($src, 'RateLimiter::tooManyAttempts($globalKey');
        $stk   = strpos($src, '->stkPush(');
        $this->assertNotFalse($rlPos);
        $this->assertNotFalse($stk);
        $this->assertLessThan($stk, $rlPos, 'rate limit must run before stkPush');
    }

    public function test_safety_check_runs_before_rate_limit(): void
    {
        $src = $this->payment();
        $safety = strpos($src, 'PaymentSafety::paymentsAllowed()');
        $rlPos  = strpos($src, 'RateLimiter::tooManyAttempts($globalKey');
        $this->assertNotFalse($safety);
        $this->assertNotFalse($rlPos);
        $this->assertLessThan($rlPos, $safety, 'safety check must run before rate limit');
    }

    public function test_wallet_topup_has_per_phone_limit(): void
    {
        $src = $this->wallet();
        $this->assertStringContainsString("'wallet-topup:'", $src);
        $this->assertStringContainsString('RateLimiter::tooManyAttempts($rlKey, 5)', $src);
        $this->assertStringContainsString('RateLimiter::hit($rlKey, 900)', $src);
    }
}