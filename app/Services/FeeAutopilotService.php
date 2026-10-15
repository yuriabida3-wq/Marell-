<?php

namespace App\Services;

use App\Jobs\SendFeeEscalation;
use App\Models\{FeeEscalation, FeeReminderSetting, Student};
use Illuminate\Support\Facades\Log;

/**
 * L6/L7/L8: fee autopilot rewritten.
 *   - kill switch: fee_autopilot_enabled setting, default off
 *   - due day: fee_due_day setting, default 10
 *   - due-date diff: one helper (dueDate) so runDaily and triggerNext agree
 *   - escalation rows are created pending and dispatched via SendFeeEscalation
 *   - idempotency only counts `sent` rows; pending does not block retries
 *   - stages selected by one shared method (currentStage)
 */
class FeeAutopilotService
{
    public static function enabled(): bool
    {
        return (bool) setting('fee_autopilot_enabled', 0);
    }

    public static function dueDate(): \Carbon\Carbon
    {
        $day = max(1, min(28, (int) setting('fee_due_day', 10)));
        return now()->startOfMonth()->addDays($day - 1)->startOfDay();
    }

    public static function runDaily(): array
    {
        $stats = [
            'students_checked'   => 0,
            'escalations_queued' => 0,
            'escalations_sent'   => 0, // legacy key: mirrors queued for view compatibility
            'skipped'            => 0,
            'disabled'           => 0,
        ];

        if (!self::enabled()) {
            $stats['disabled'] = 1;
            Log::info('Fee autopilot skipped: fee_autopilot_enabled is off');
            return $stats;
        }

        $daysOffset = (int) self::dueDate()->diffInDays(now(), false);

        Student::where('status', 'active')
            ->where('balance', '>', 0)
            ->chunkById(100, function ($students) use ($daysOffset, &$stats) {
                foreach ($students as $student) {
                    $stats['students_checked']++;

                    $setting = self::currentStage($student, $daysOffset);
                    if (!$setting) {
                        $stats['skipped']++;
                        continue;
                    }

                    $escalation = FeeEscalation::create([
                        'student_id'    => $student->id,
                        'stage'         => $setting->stage,
                        'channel'       => $setting->channel,
                        'title'         => $setting->name,
                        'message'       => self::buildMessage($setting->template, $student),
                        'status'        => 'pending',
                        'scheduled_for' => now()->toDateString(),
                    ]);

                    $stats['escalations_queued']++;
                    SendFeeEscalation::dispatch($escalation->id)->onQueue('default');
                }
            });

        $stats['escalations_sent'] = $stats['escalations_queued'];

        Log::info('Fee autopilot run', $stats);
        return $stats;
    }

    /**
     * L8: single source of truth for stage progression.
     * Returns the next applicable FeeReminderSetting for the student, or null.
     * Idempotency: a stage counts as done only when a `sent` row exists this
     * month, so a crash mid-dispatch leaves the stage eligible for retry.
     */
    protected static function currentStage(Student $student, int $daysOffset): ?FeeReminderSetting
    {
        $candidates = FeeReminderSetting::where('active', true)
            ->where('days_offset', '<=', $daysOffset)
            ->orderBy('stage')
            ->get();

        foreach ($candidates as $setting) {
            $sent = FeeEscalation::where('student_id', $student->id)
                ->where('stage', $setting->stage)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->where('status', 'sent')
                ->exists();

            if (!$sent) {
                return $setting;
            }
        }

        return null;
    }

    public static function buildMessage(string $template, Student $student): string
    {
        return str_replace(
            ['{student}', '{balance}', '{due_date}', '{adm_no}', '{parent}', '{class}'],
            [
                $student->name,
                number_format((float) $student->balance, 0),
                self::dueDate()->format('d M Y'),
                $student->adm_no,
                $student->parent_name,
                $student->class,
            ],
            $template
        );
    }

    /**
     * L7: creates a pending row and queues SendFeeEscalation.
     * Kill-switch applies here too.
     */
    public static function triggerNext(Student $student): ?FeeEscalation
    {
        if (!self::enabled()) {
            return null;
        }

        $lastStage = (int) (FeeEscalation::where('student_id', $student->id)->max('stage') ?? 0);

        $next = FeeReminderSetting::where('active', true)
            ->where('stage', '>', $lastStage)
            ->orderBy('stage')
            ->first();

        if (!$next) {
            return null;
        }

        $escalation = FeeEscalation::create([
            'student_id'    => $student->id,
            'stage'         => $next->stage,
            'channel'       => $next->channel,
            'title'         => $next->name,
            'message'       => self::buildMessage($next->template, $student),
            'status'        => 'pending',
            'scheduled_for' => now()->toDateString(),
        ]);

        SendFeeEscalation::dispatch($escalation->id)->onQueue('default');

        return $escalation->fresh();
    }

    /**
     * L7: re-queues escalations left in `pending` by a crashed or timed-out run.
     * Called by the scheduler. Returns the number of rows requeued.
     */
    public static function reapStale(): int
    {
        if (!self::enabled()) {
            return 0;
        }

        $stale = FeeEscalation::where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(5))
            ->orderBy('id')
            ->limit(200)
            ->get();

        foreach ($stale as $row) {
            SendFeeEscalation::dispatch($row->id)->onQueue('default');
        }

        return $stale->count();
    }
}
