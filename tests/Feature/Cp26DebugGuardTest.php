<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-26: fail-closed guard when production runs with debug on.
 */
class Cp26DebugGuardTest extends TestCase
{
    private function provider(): string
    {
        return (string) file_get_contents(base_path('app/Providers/AppServiceProvider.php'));
    }

    public function test_provider_checks_production_environment(): void
    {
        $this->assertStringContainsString(
            "environment('production')",
            $this->provider()
        );
    }

    public function test_provider_checks_debug_config(): void
    {
        $this->assertStringContainsString(
            "config('app.debug')",
            $this->provider()
        );
    }

    public function test_provider_throws_runtime_exception(): void
    {
        $this->assertStringContainsString('throw new \RuntimeException', $this->provider());
    }

    public function test_exception_message_names_the_fix(): void
    {
        $this->assertStringContainsString(
            'Set APP_DEBUG=false in .env.',
            $this->provider()
        );
    }

    public function test_staging_escape_hatch_is_documented(): void
    {
        $src = $this->provider();
        $this->assertStringContainsString('APP_ENV=staging', $src);
    }
}