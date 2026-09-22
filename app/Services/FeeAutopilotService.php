<?php

namespace App\Services;

use App\Models\{FeeEscalation, FeeReminderSetting, Student};
use Illuminate\Support\Facades\Log;

class FeeAutopilotService
{
    public static function runDaily(): array
    {
        $dueDate = now()->startOfMonth()->addDays(9);
        $today = now();

        $stats = ['students_checked' => 0, 'escalations_sent' => 0, 'escalations_queued' => 0, 'skipped' => 0];

        $students = Student::where('status', 'active')->where('balance', '>', 0)->get();

        foreach ($students as $student) {
            $stats['students_checked']++;
            $daysOffset = (int) $dueDate->diffInDays($today, false);

            $settings = FeeReminderSetting::where('active', true)
                ->where('days_offset', '<=', $daysOffset)
                ->orderBy('stage')
                ->get();

            foreach ($settings as $setting) {
                $exists = FeeEscalation::where('student_id', $student->id)
                    ->where('stage', $setting->stage)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->exists();

                if ($exists) { $stats['skipped']++; continue; }

                $message = self::buildMessage($setting->template, $student);

                $escalation = FeeEscalation::create([
                    'student_id'    => $student->id,
                    'stage'         => $setting->stage,
                    'channel'       => $setting->channel,
                    'title'         => $setting->name,
                    'message'       => $message,
                    'status'        => 'pending',
                    'scheduled_for' => now()->toDateString(),
                ]);

                $stats['escalations_queued']++;

                try {
                    self::dispatchEscalation($escalation, $student);
                    $escalation->update(['status' => 'sent', 'sent_at' => now()]);
                    $stats['escalations_sent']++;
                } catch (\Throwable $e) {
                    $escalation->update(['status' => 'failed', 'error' => $e->getMessage()]);
                    Log::warning("Escalation failed for student {$student->id}: " . $e->getMessage());
                }
            }
        }

        Log::info('Fee autopilot run', $stats);
        return $stats;
    }

    protected static function buildMessage(string $template, Student $student): string
    {
        return str_replace(
            ['{student}', '{balance}', '{due_date}', '{adm_no}', '{parent}', '{class}'],
            [
                $student->name,
                number_format((float) $student->balance, 0),
                now()->startOfMonth()->addDays(9)->format('d M Y'),
                $student->adm_no,
                $student->parent_name,
                $student->class,
            ],
            $template
        );
    }

    protected static function dispatchEscalation(FeeEscalation $escalation, Student $student): void
    {
        if (!$student->parent_phone) throw new \RuntimeException('No parent phone');

        match ($escalation->channel) {
            'sms', 'whatsapp' => app(SmsService::class)->send($student->parent_phone, $escalation->message),
            'voice'           => app(VoiceSmsService::class)->call($student->parent_phone, $escalation->message),
            'letter'          => null,
            default           => null,
        };
    }

    public static function triggerNext(Student $student): ?FeeEscalation
    {
        $lastStage = FeeEscalation::where('student_id', $student->id)->max('stage') ?? 0;

        $next = FeeReminderSetting::where('active', true)
            ->where('stage', '>', $lastStage)
            ->orderBy('stage')
            ->first();

        if (!$next) return null;

        $message = self::buildMessage($next->template, $student);

        $escalation = FeeEscalation::create([
            'student_id'    => $student->id,
            'stage'         => $next->stage,
            'channel'       => $next->channel,
            'title'         => $next->name,
            'message'       => $message,
            'status'        => 'pending',
            'scheduled_for' => now()->toDateString(),
        ]);

        try {
            self::dispatchEscalation($escalation, $student);
            $escalation->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Throwable $e) {
            $escalation->update(['status' => 'failed', 'error' => $e->getMessage()]);
        }

        return $escalation->fresh();
    }
}
