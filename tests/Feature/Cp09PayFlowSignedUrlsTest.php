<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-09: pay.wait / pay.check / pay.success require signed URLs.
 *
 * Source-level assertions only. Integration tests against the DB are not
 * possible here because the SQLite test schema lacks the payments table
 * (D38 migration drift), and Laravel's SubstituteBindings middleware runs
 * before route middleware, so DB resolution occurs before 'signed' can
 * reject the request. Runtime verification is done by a manual browser
 * smoke test on the dev DB.
 */
class Cp09PayFlowSignedUrlsTest extends TestCase
{
    public function test_routes_have_signed_middleware(): void
    {
        $src = (string) file_get_contents(base_path('routes/web.php'));
        $this->assertStringContainsString(
            "Route::get('/pay/wait/{payment}',    [PaymentController::class, 'wait'])->middleware('signed')->name('pay.wait');",
            $src
        );
        $this->assertStringContainsString(
            "Route::get('/pay/check/{payment}',   [PaymentController::class, 'check'])->middleware('signed')->name('pay.check');",
            $src
        );
        $this->assertStringContainsString(
            "Route::get('/pay/success/{payment}', [PaymentController::class, 'success'])->middleware('signed')->name('pay.success');",
            $src
        );
    }

    public function test_controller_uses_signed_routes(): void
    {
        $src = (string) file_get_contents(base_path('app/Http/Controllers/PaymentController.php'));
        $this->assertStringContainsString("URL::signedRoute('pay.wait'", $src);
        $this->assertStringContainsString("URL::signedRoute('pay.success'", $src);
        $this->assertStringNotContainsString("route('pay.wait'", $src);
        $this->assertStringNotContainsString("route('pay.success'", $src);
    }

    public function test_view_uses_signed_check_route(): void
    {
        $src = (string) file_get_contents(base_path('resources/views/public/pay-wait.blade.php'));
        $this->assertStringContainsString("URL::signedRoute('pay.check'", $src);
        $this->assertStringNotContainsString("route('pay.check'", $src);
    }

    public function test_signed_middleware_remains_attached_to_receipt(): void
    {
        // pay.receipt was already signed before CP-09 — regression guard.
        $src = (string) file_get_contents(base_path('routes/web.php'));
        $this->assertStringContainsString(
            "Route::get('/pay/receipt/{payment}', [PaymentController::class, 'receipt'])->middleware('signed')->name('pay.receipt');",
            $src
        );
    }
}