<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * CP-15b: /pay/success must verify server-side state, never trust the URL.
 */
class Cp15bPaySuccessStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_page_denies_pending_payment(): void
    {
        $student = Student::factory()->create();
        $payment = Payment::factory()->pending()->create(['student_id' => $student->id]);

        $url = URL::signedRoute('pay.success', $payment);
        $response = $this->get($url);

        $response->assertRedirect('/pay');
        $response->assertSessionHas('error');
        $response->assertDontSee('Payment Successful');
    }

    public function test_success_page_denies_failed_payment(): void
    {
        $student = Student::factory()->create();
        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'status'     => 'failed',
        ]);

        $url = URL::signedRoute('pay.success', $payment);
        $response = $this->get($url);

        $response->assertRedirect('/pay');
        $response->assertDontSee('Payment Successful');
    }

    public function test_success_page_allows_completed_payment(): void
    {
        $student = Student::factory()->create();
        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'status'     => 'completed',
            'receipt_no' => 'REC-2026-00001',
        ]);

        $url = URL::signedRoute('pay.success', $payment);
        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('Payment Successful');
    }

    public function test_success_page_renders_when_receipt_no_missing(): void
    {
        // L48: a completed payment without a receipt_no must not 500 the
        // success page. The verify-receipt link is hidden instead.
        $student = Student::factory()->create();
        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'status'     => 'completed',
            'receipt_no' => null,
        ]);

        $url = URL::signedRoute('pay.success', $payment);
        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('Payment Successful');
        $response->assertDontSee('Verify this receipt online');
    }

    public function test_receipt_denies_non_completed_payment(): void
    {
        $student = Student::factory()->create();
        $payment = Payment::factory()->pending()->create(['student_id' => $student->id]);

        $url = URL::signedRoute('pay.receipt', ['payment' => $payment->id]);
        $response = $this->get($url);

        $response->assertNotFound();
    }
}
