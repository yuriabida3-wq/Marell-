<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CP-11: public pay page must not reveal identity, balance, or parent
 * phone before the parent has actually paid.
 */
class Cp11PayPagePrivacyTest extends TestCase
{
    use RefreshDatabase;

    private function ctrl(): string
    {
        return (string) file_get_contents(base_path('app/Http/Controllers/PaymentController.php'));
    }

    private function payBlade(): string
    {
        return (string) file_get_contents(base_path('resources/views/public/pay.blade.php'));
    }

    public function test_controller_masks_student_response(): void
    {
        $src = $this->ctrl();
        $this->assertStringContainsString("'initials' => \$this->initials(\$found->name)", $src);
        $this->assertStringContainsString('compact(\'student\')', $src);
    }

    public function test_controller_has_initials_helper(): void
    {
        $this->assertStringContainsString('private function initials(string $name): string', $this->ctrl());
    }

    public function test_controller_error_message_does_not_echo_adm(): void
    {
        $src = $this->ctrl();
        $this->assertStringNotContainsString(
            'No student found with admission number {$request->adm}',
            $src,
            'Error message still echoes the admission number'
        );
        $this->assertStringContainsString('No student found with that admission number.', $src);
    }

    public function test_blade_shows_initials_not_full_name(): void
    {
        $src = $this->payBlade();
        $this->assertStringContainsString("\$student['initials']", $src);
        $this->assertStringNotContainsString('$student->name', $src);
    }

    public function test_blade_has_no_parent_name(): void
    {
        $this->assertStringNotContainsString('$student->parent_name', $this->payBlade());
    }

    public function test_blade_has_no_balance_or_total_fee(): void
    {
        $src = $this->payBlade();
        $this->assertStringNotContainsString('$student->balance', $src);
        $this->assertStringNotContainsString('$student->total_fee', $src);
        $this->assertStringNotContainsString('$student->paid_amount', $src);
    }

    public function test_blade_does_not_prefill_phone(): void
    {
        // Source-level defence in depth: no view may reference the parent phone.
        $this->assertStringNotContainsString('$student->parent_phone', $this->payBlade());

        // Behaviour: the rendered /pay page must not leak the phone number.
        \App\Models\Student::create([
            'adm_no'       => 'PRIV-001',
            'name'         => 'Privacy Testchild',
            'class'        => '7',
            'stream'       => 'A',
            'parent_name'  => 'Parent Shouldnotappear',
            'parent_phone' => '0712345678',
        ]);

        $response = $this->get('/pay?adm=PRIV-001');
        $response->assertOk();
        $response->assertSee('name="phone"', false);
        $response->assertDontSee('0712345678', false);
        $response->assertDontSee('value="07', false);
        $response->assertDontSee('value="01', false);
        $response->assertDontSee('Parent Shouldnotappear', false);
    }
}