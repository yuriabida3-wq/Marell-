<?php

namespace Tests\Feature;

use App\Jobs\SendFeeEscalation;
use App\Models\FeeEscalation;
use App\Models\FeeReminderSetting;
use App\Models\Student;
use App\Services\FeeAutopilotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * L6/L7/L8: fee autopilot behaviour.
 */
class FeeAutopilotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    protected function enableAutopilot(int $dueDay = 10): void
    {
        \App\Models\Setting::set('fee_autopilot_enabled', '1');
        \App\Models\Setting::set('fee_due_day', (string) $dueDay);
    }

    protected function seedStages(): void
    {
        FeeReminderSetting::create([
            'stage' => 1, 'name' => 'First reminder', 'days_offset' => -5,
            'channel' => 'sms', 'template' => 'Hi {parent} {balance}', 'active' => true,
        ]);
        FeeReminderSetting::create([
            'stage' => 2, 'name' => 'Letter', 'days_offset' => 3,
            'channel' => 'letter', 'template' => 'Dear {parent}', 'active' => true,
        ]);
    }

    public function test_kill_switch_returns_early_without_writing_rows(): void
    {
        // fee_autopilot_enabled defaults to 0.
        Student::factory()->create(['balance' => 5000, 'status' => 'active']);
        $this->seedStages();

        $stats = FeeAutopilotService::runDaily();

        $this->assertSame(1, $stats['disabled']);
        $this->assertSame(0, FeeEscalation::count());
        Queue::assertNothingPushed();
    }

    public function test_enabled_run_creates_pending_rows_and_queues_jobs(): void
    {
        $this->enableAutopilot();
        $this->seedStages();
        Student::factory()->create(['balance' => 5000, 'status' => 'active']);

        $stats = FeeAutopilotService::runDaily();

        $this->assertSame(1, $stats['escalations_queued']);
        $this->assertSame(0, $stats['skipped']);
        $this->assertDatabaseHas('fee_escalations', ['status' => 'pending', 'stage' => 1]);
        Queue::assertPushed(SendFeeEscalation::class, 1);
    }

    public function test_student_with_zero_balance_is_not_escalated(): void
    {
        $this->enableAutopilot();
        $this->seedStages();
        Student::factory()->create(['balance' => 0, 'status' => 'active']);

        $stats = FeeAutopilotService::runDaily();

        $this->assertSame(0, $stats['escalations_queued']);
        $this->assertSame(0, FeeEscalation::count());
    }

    public function test_crash_window_pending_row_does_not_block_next_run(): void
    {
        $this->enableAutopilot();
        $this->seedStages();
        $student = Student::factory()->create(['balance' => 5000, 'status' => 'active']);

        // Simulate crash: row created pending, never dispatched.
        FeeEscalation::create([
            'student_id' => $student->id, 'stage' => 1, 'channel' => 'sms',
            'title' => 'First reminder', 'message' => 'hi',
            'status' => 'pending', 'scheduled_for' => now()->toDateString(),
        ]);

        $stats = FeeAutopilotService::runDaily();

        // Pending does not count as sent -> stage 1 still eligible? No:
        // currentStage checks for `sent` only, so stage 1 requeues.
        $this->assertSame(1, $stats['escalations_queued']);
    }

    public function test_already_sent_stage_is_skipped_second_run(): void
    {
        $this->enableAutopilot();
        $this->seedStages();
        $student = Student::factory()->create(['balance' => 5000, 'status' => 'active']);

        FeeEscalation::create([
            'student_id' => $student->id, 'stage' => 1, 'channel' => 'sms',
            'title' => 'First reminder', 'message' => 'hi',
            'status' => 'sent', 'scheduled_for' => now()->toDateString(), 'sent_at' => now(),
        ]);

        $stats = FeeAutopilotService::runDaily();

        // Stage 1 is sent -> stage 2 (letter) is next candidate.
        $this->assertSame(1, $stats['escalations_queued']);
        $this->assertDatabaseHas('fee_escalations', ['status' => 'pending', 'stage' => 2]);
    }

    public function test_due_date_uses_setting(): void
    {
        $this->enableAutopilot(15);
        $student = Student::factory()->create([
            'balance' => 5000, 'status' => 'active', 'parent_name' => 'Mr Test',
        ]);

        $message = FeeAutopilotService::buildMessage('Due {due_date}', $student);

        $this->assertStringContainsString('15 ', $message);
    }

    public function test_letter_channel_marked_skipped_not_sent(): void
    {
        $student = Student::factory()->create(['parent_phone' => '254712345678']);
        $row = FeeEscalation::create([
            'student_id' => $student->id, 'stage' => 2, 'channel' => 'letter',
            'title' => 'Letter', 'message' => 'Dear parent',
            'status' => 'pending', 'scheduled_for' => now()->toDateString(),
        ]);

        (new SendFeeEscalation($row->id))->handle();

        $this->assertSame('skipped', $row->fresh()->status);
    }

    public function test_sms_success_marks_sent(): void
    {
        // SmsService has a stub path when creds are absent. That stub returns true.
        config([
            'services.africastalking.username' => null,
            'services.africastalking.api_key'  => null,
        ]);

        $student = Student::factory()->create(['parent_phone' => '254712345678']);
        $row = FeeEscalation::create([
            'student_id' => $student->id, 'stage' => 1, 'channel' => 'sms',
            'title' => 'First reminder', 'message' => 'Hello',
            'status' => 'pending', 'scheduled_for' => now()->toDateString(),
        ]);

        (new SendFeeEscalation($row->id))->handle();

        $this->assertSame('sent', $row->fresh()->status);
        $this->assertNotNull($row->fresh()->sent_at);
    }

    public function test_trigger_next_creates_pending_and_queues(): void
    {
        $this->enableAutopilot();
        $this->seedStages();
        $student = Student::factory()->create(['balance' => 5000, 'status' => 'active']);

        $escalation = FeeAutopilotService::triggerNext($student);

        $this->assertNotNull($escalation);
        $this->assertSame('pending', $escalation->status);
        $this->assertSame(1, $escalation->stage);
        Queue::assertPushed(SendFeeEscalation::class, 1);
    }

    public function test_trigger_next_disabled_returns_null(): void
    {
        // Default: disabled.
        $this->seedStages();
        $student = Student::factory()->create(['balance' => 5000, 'status' => 'active']);

        $this->assertNull(FeeAutopilotService::triggerNext($student));
        Queue::assertNothingPushed();
    }

    public function test_reap_stale_requeues_pending(): void
    {
        $this->enableAutopilot();
        $student = Student::factory()->create(['balance' => 5000, 'status' => 'active']);

        // created_at is not in FeeEscalation::$fillable, so create() would
        // silently drop it. Insert directly to control the timestamp.
        $row = FeeEscalation::create([
            'student_id' => $student->id, 'stage' => 1, 'channel' => 'sms',
            'title' => 'First reminder', 'message' => 'hi',
            'status' => 'pending', 'scheduled_for' => now()->toDateString(),
        ]);
        \Illuminate\Support\Facades\DB::table('fee_escalations')
            ->where('id', $row->id)
            ->update([
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ]);

        $count = FeeAutopilotService::reapStale();

        $this->assertSame(1, $count);
        Queue::assertPushed(SendFeeEscalation::class, 1);
    }

    public function test_reap_stale_disabled_returns_zero(): void
    {
        // Default off.
        $count = FeeAutopilotService::reapStale();
        $this->assertSame(0, $count);
        Queue::assertNothingPushed();
    }
}
