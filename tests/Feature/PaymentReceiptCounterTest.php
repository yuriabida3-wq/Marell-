<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * L2: receipt number generation uses a locked counter row, format RCP-YYYY-NNNNN.
 */
class PaymentReceiptCounterTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_sequential_receipts(): void
    {
        $a = Payment::generateReceiptNo();
        $b = Payment::generateReceiptNo();
        $c = Payment::generateReceiptNo();

        $year = date('Y');
        $this->assertSame("RCP-{$year}-00001", $a);
        $this->assertSame("RCP-{$year}-00002", $b);
        $this->assertSame("RCP-{$year}-00003", $c);
    }

    public function test_counter_row_is_updated(): void
    {
        Payment::generateReceiptNo();
        Payment::generateReceiptNo();

        $row = DB::table('receipt_counters')->where('year', (int) date('Y'))->first();
        $this->assertNotNull($row);
        $this->assertSame(2, (int) $row->last);
    }

    public function test_receipt_format_matches_rcp_prefix(): void
    {
        $rn = Payment::generateReceiptNo();

        $this->assertMatchesRegularExpression('/^RCP-\d{4}-\d{5}$/', $rn);
    }

    public function test_receipts_are_unique_under_repeated_calls(): void
    {
        $numbers = [];
        for ($i = 0; $i < 20; $i++) {
            $numbers[] = Payment::generateReceiptNo();
        }

        $this->assertCount(20, array_unique($numbers), 'Receipt numbers must all be distinct');
    }

    public function test_payment_persisted_with_generated_receipt_is_stored(): void
    {
        $student = Student::factory()->create();
        $rn      = Payment::generateReceiptNo();

        $payment = Payment::factory()->create([
            'student_id' => $student->id,
            'receipt_no' => $rn,
        ]);

        $this->assertSame($rn, $payment->fresh()->receipt_no);
    }
}
