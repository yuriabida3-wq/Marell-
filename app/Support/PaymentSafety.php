<?php

namespace App\Support;

class PaymentSafety
{
    public static function killSwitchActive(): bool
    {
        return (bool) config('services.mpesa_kill_switch', false);
    }

    public static function demoModeEnabled(): bool
    {
        return (bool) config('services.demo_mode', false);
    }

    public static function skipStkEnabled(): bool
    {
        return (bool) config('services.mpesa_skip_stk', false);
    }

    public static function env(): string
    {
        return (string) config('services.mpesa.env', 'sandbox');
    }

    /**
     * Dangerous = non-sandbox env with demo/auto-complete flags on.
     * In this state real money could move while the local record is fake.
     */
    public static function configIsDangerous(): bool
    {
        if (self::env() === 'sandbox') {
            return false;
        }
        return self::demoModeEnabled() || self::skipStkEnabled();
    }

    public static function paymentsAllowed(): bool
    {
        if (self::killSwitchActive()) {
            return false;
        }
        return !self::configIsDangerous();
    }

    public static function blockReason(): ?string
    {
        if (self::killSwitchActive()) {
            return 'MPESA_KILL_SWITCH is on — new payment initiation is disabled.';
        }
        if (self::configIsDangerous()) {
            return 'Unsafe config: non-sandbox M-Pesa env with demo/skip flags on.';
        }
        return null;
    }
}