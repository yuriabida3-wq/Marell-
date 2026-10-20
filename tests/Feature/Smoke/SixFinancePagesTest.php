<?php

namespace Tests\Feature\Smoke;

use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Gate 1 §14 smoke checklist: the six finance pages must render.
 */
class SixFinancePagesTest extends TestCase
{
    use RefreshDatabase;

    private const ERROR_MARKERS = [
        'Whoops', 'SQLSTATE[', 'Fatal error:', 'Parse error:',
        'Call to undefined', 'Undefined variable', 'Undefined property',
        'Undefined array', 'Stack trace:', 'Base table or view not found',
        'Trying to access array offset', '@extends(', '@section(', '@yield(',
    ];

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['principal','dos','bursar','teacher','security'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }
        $principal = User::factory()->create(['role_label' => 'principal']);
        $principal->assignRole('principal');
        $this->actingAs($principal);

        $this->student = Student::factory()->create([
            'total_fee'   => 30000,
            'paid_amount' => 5000,
            'balance'     => 25000,
        ]);

        Payment::factory()->create([
            'student_id' => $this->student->id,
            'status'     => 'completed',
            'receipt_no' => 'RCP-2026-00099',
        ]);
    }

    public function test_finance_page_renders(): void
    {
        $this->assertPageRenders('/principal/finance', ['Finance', 'Payment', 'Fee']);
    }

    public function test_wallet_page_renders(): void
    {
        $this->assertPageRenders('/principal/wallet', ['Wallet', 'Canteen']);
    }

    public function test_fee_autopilot_page_renders(): void
    {
        $this->assertPageRenders('/principal/fee-autopilot', ['Autopilot', 'Fee', 'Reminder']);
    }

    public function test_fee_autopilot_student_page_renders(): void
    {
        $this->assertPageRenders("/principal/fee-autopilot/student/{$this->student->id}", ['Autopilot', 'Fee', 'Reminder', 'Stage']);
    }

    public function test_results_page_renders(): void
    {
        $this->assertPageRenders('/principal/results', ['Result', 'Exam', 'Mark']);
    }

    public function test_sms_page_renders(): void
    {
        $this->assertPageRenders('/principal/sms', ['SMS', 'Message', 'Audience']);
    }

    private function assertPageRenders(string $url, array $expectedText): void
    {
        $response = $this->get($url);
        $this->assertSame(200, $response->getStatusCode(), "{$url} did not return 200");
        $body = $response->getContent();
        $this->assertGreaterThan(500, strlen($body), "{$url} body too short — likely empty or error page");
        $this->assertStringContainsString('</html>', $body, "{$url} body missing </html>");

        foreach (self::ERROR_MARKERS as $marker) {
            $this->assertStringNotContainsString($marker, $body, "{$url} contains error marker '{$marker}'");
        }

        $found = false;
        foreach ($expectedText as $needle) {
            if (stripos($body, $needle) !== false) { $found = true; break; }
        }
        $this->assertTrue($found, "{$url} missing expected text (any of: " . implode(', ', $expectedText) . ")");
    }
}
