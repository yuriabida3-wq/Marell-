<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-10: public receipt verification shows a masked view by default,
 * full details only when the URL carries a valid signature.
 */
class Cp10ReceiptVerificationPrivacyTest extends TestCase
{
    private function routes(): string
    {
        return (string) file_get_contents(base_path('routes/web.php'));
    }

    private function ctrl(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/VerifyController.php'));
    }

    private function vblade(): string
    {
        return (string) file_get_contents(base_path('resources/views/public/verify-receipt.blade.php'));
    }

    public function test_route_has_throttle(): void
    {
        $this->assertStringContainsString(
            "->middleware('throttle:30,1')->name('verify.receipt')",
            $this->routes()
        );
    }

    public function test_controller_accepts_request_and_checks_signature(): void
    {
        $src = $this->ctrl();
        $this->assertStringContainsString('Request $request', $src);
        $this->assertStringContainsString('hasValidSignature()', $src);
        $this->assertStringContainsString("'full'      => \$full", $src);
    }

    public function test_blade_gates_identity_on_full(): void
    {
        $src = $this->vblade();
        $fullStart = strpos($src, '@if ($full)');
        $fullEnd   = strpos($src, '@endif', $fullStart);
        $this->assertNotFalse($fullStart);
        $this->assertNotFalse($fullEnd);
        $inside = substr($src, $fullStart, $fullEnd - $fullStart);
        $this->assertStringContainsString('$payment->student->name', $inside);
        $this->assertStringContainsString('$payment->student->adm_no', $inside);
        $this->assertStringContainsString('$payment->transaction_code', $inside);
    }

    public function test_blade_always_shows_amount_and_date(): void
    {
        $src = $this->vblade();
        $amountPos = strpos($src, 'number_format($payment->amount');
        $datePos   = strpos($src, "created_at->format('d M Y H:i')");
        $fullPos   = strpos($src, '@if ($full)');
        $this->assertNotFalse($amountPos);
        $this->assertNotFalse($datePos);
        $this->assertNotFalse($fullPos);
        $this->assertLessThan($fullPos, $amountPos, 'amount must appear before the full block');
        $this->assertLessThan($fullPos, $datePos, 'date must appear before the full block');
    }

    public function test_pay_success_uses_signed_route(): void
    {
        $src = (string) file_get_contents(base_path('resources/views/public/pay-success.blade.php'));
        $this->assertStringContainsString("URL::signedRoute('verify.receipt'", $src);
        $this->assertStringNotContainsString('href="/verify-receipt/{{', $src);
    }

    public function test_qr_hub_uses_signed_route(): void
    {
        $src = (string) file_get_contents(base_path('resources/views/qr/hub.blade.php'));
        $this->assertStringContainsString("URL::signedRoute('verify.receipt'", $src);
    }

    public function test_whatsapp_bot_uses_signed_route(): void
    {
        $src = (string) file_get_contents(base_path('app/Http/Controllers/WhatsappBotController.php'));
        $this->assertStringContainsString("URL::signedRoute('verify.receipt'", $src);
    }
}