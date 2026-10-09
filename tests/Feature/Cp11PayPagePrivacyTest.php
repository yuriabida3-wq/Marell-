<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CP-11: public pay page must not reveal identity, balance, or parent
 * phone before the parent has actually paid.
 */
class Cp11PayPagePrivacyTest extends TestCase
{
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
        $src = $this->payBlade();
        $this->assertStringNotContainsString('$student->parent_phone', $src);
        $this->assertStringContainsString("name=\"phone\" value=\"{{ old('phone') }}\"", $src);
    }
}