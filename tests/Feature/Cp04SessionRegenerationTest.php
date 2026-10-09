<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-04: session id rotation on login and invalidation on logout.
 *
 * Runtime HTTP tests for the two live logout endpoints, plus a source-level
 * assertion that the four login paths contain session()->regenerate().
 *
 * The dead-code UnifiedAuthController paths are asserted too, so that if
 * anyone ever wires those routes up, the fix is already verified.
 */
class Cp04SessionRegenerationTest extends TestCase
{
    public function test_security_guard_logout_invalidates_session(): void
    {
        $this->startSession();
        $this->withSession(['guard_id' => 999, 'guard_name' => 'x']);
        $before = session()->getId();

        $this->post('/security/logout');

        $this->assertNotSame($before, session()->getId(),
            'Session id did not rotate after security logout');
    }

    public function test_parent_logout_invalidates_session(): void
    {
        $this->startSession();
        $this->withSession(['parent_phone' => '254700000000']);
        $before = session()->getId();

        $this->post('/parent/logout');

        $this->assertNotSame($before, session()->getId(),
            'Session id did not rotate after parent logout');
    }

    public function test_all_login_methods_call_session_regenerate(): void
    {
        $targets = [
            'app/Http/Controllers/UnifiedAuthController.php'   => ['guardLogin', 'parentVerifyOtp'],
            'app/Http/Controllers/SecurityGuardController.php' => ['login'],
            'app/Http/Controllers/ParentPortalController.php'  => ['verifyOtp'],
        ];

        foreach ($targets as $file => $methods) {
            $src = file_get_contents(base_path($file));
            $this->assertNotFalse($src, "Cannot read {$file}");

            foreach ($methods as $method) {
                $pos = strpos($src, "function {$method}(");
                $this->assertNotFalse($pos, "Method {$method} not found in {$file}");

                // Search for regenerate() within the method body (next ~2000 chars).
                $slice = substr($src, $pos, 2000);
                $this->assertStringContainsString(
                    'session()->regenerate()',
                    $slice,
                    "{$file}::{$method} does not call session()->regenerate()"
                );
            }
        }
    }

    public function test_all_logout_methods_invalidate_session(): void
    {
        $targets = [
            'app/Http/Controllers/UnifiedAuthController.php'   => ['logout'],
            'app/Http/Controllers/SecurityGuardController.php' => ['logout'],
            'app/Http/Controllers/ParentPortalController.php'  => ['logout'],
        ];

        foreach ($targets as $file => $methods) {
            $src = file_get_contents(base_path($file));
            foreach ($methods as $method) {
                $pos = strpos($src, "function {$method}(");
                $this->assertNotFalse($pos, "Method {$method} not found in {$file}");
                $slice = substr($src, $pos, 2000);
                $this->assertStringContainsString(
                    'session()->invalidate()',
                    $slice,
                    "{$file}::{$method} does not call session()->invalidate()"
                );
            }
        }
    }
}
