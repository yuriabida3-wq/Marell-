<?php

namespace Tests\Feature;

use App\Support\PaymentSafety;
use Tests\TestCase;

/**
 * CP-15: payment safety switches.
 *
 * All tests are pure config / helper assertions. No DB, no queue, no network.
 */
class Cp15PaymentSafetyTest extends TestCase
{
    public function test_default_config_allows_payments(): void
    {
        config([
            'services.mpesa.env'             => 'sandbox',
            'services.demo_mode'             => false,
            'services.mpesa_skip_stk'        => false,
            'services.mpesa_kill_switch'     => false,
        ]);
        $this->assertTrue(PaymentSafety::paymentsAllowed());
        $this->assertNull(PaymentSafety::blockReason());
    }

    public function test_kill_switch_blocks_payments(): void
    {
        config([
            'services.mpesa.env'         => 'production',
            'services.demo_mode'         => false,
            'services.mpesa_skip_stk'    => false,
            'services.mpesa_kill_switch' => true,
        ]);
        $this->assertFalse(PaymentSafety::paymentsAllowed());
        $this->assertStringContainsString('KILL_SWITCH', (string) PaymentSafety::blockReason());
    }

    public function test_production_plus_demo_mode_blocks(): void
    {
        config([
            'services.mpesa.env'         => 'production',
            'services.demo_mode'         => true,
            'services.mpesa_skip_stk'    => false,
            'services.mpesa_kill_switch' => false,
        ]);
        $this->assertFalse(PaymentSafety::paymentsAllowed());
    }

    public function test_production_plus_skip_stk_blocks(): void
    {
        config([
            'services.mpesa.env'         => 'production',
            'services.demo_mode'         => false,
            'services.mpesa_skip_stk'    => true,
            'services.mpesa_kill_switch' => false,
        ]);
        $this->assertFalse(PaymentSafety::paymentsAllowed());
    }

    public function test_production_clean_config_allows(): void
    {
        config([
            'services.mpesa.env'         => 'production',
            'services.demo_mode'         => false,
            'services.mpesa_skip_stk'    => false,
            'services.mpesa_kill_switch' => false,
        ]);
        $this->assertTrue(PaymentSafety::paymentsAllowed());
    }

    public function test_sandbox_with_demo_flags_allows(): void
    {
        config([
            'services.mpesa.env'         => 'sandbox',
            'services.demo_mode'         => true,
            'services.mpesa_skip_stk'    => true,
            'services.mpesa_kill_switch' => false,
        ]);
        $this->assertTrue(PaymentSafety::paymentsAllowed());
    }
}