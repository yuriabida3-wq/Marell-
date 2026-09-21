<?php

namespace App\Services;

use App\Models\LateFine;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LateFineService
{
    public const FINE_AMOUNT    = 200;
    public const DUE_DAY        = 10; // 10th of the month
    public const WARN_DAYS      = 3;  // 3 days before due

    /**
     * Apply KES 200 fine to all students with balance > 0
     * who have not been fined today.
     */
    public static function applyMonthlyFines(): array
    {
        $today = now()->toDateString();
        $applied = 0;
        $skipped = 0;

        DB::transaction(function () use ($today, &$applied, &$skipped) {
            $students = Student::where('balance', '>', 0)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get();

            foreach ($students as $student) {
                // Skip if fined today already
                $exists = LateFine::where('student_id', $student->id)
                    ->whereDate('applied_date', $today)
                    ->exists();

                if ($exists) { $skipped++; continue; }

                LateFine::create([
                    'student_id'   => $student->id,
                    'amount'       => self::FINE_AMOUNT,
                    'reason'       => 'Late payment after ' . self::DUE_DAY . 'th',
                    'applied_date' => $today,
                    'due_before'   => now()->startOfMonth()->addDays(self::DUE_DAY - 1)->toDateString(),
                ]);

                $student->update([
                    'fines_total' => (float) $student->fines_total + self::FINE_AMOUNT,
                    'balance'     => (float) $student->balance + self::FINE_AMOUNT,
                ]);

                $applied++;
            }
        });

        Log::info("Late fines applied: {$applied}, skipped: {$skipped}");
        return ['applied' => $applied, 'skipped' => $skipped];
    }

    /**
     * Send warning SMS to students who will be fined in N days.
     */
    public static function warnUpcoming(): int
    {
        $warnDate = now()->startOfMonth()->addDays(self::DUE_DAY - 1);
        $daysUntil = now()->diffInDays($warnDate, false);

        if ($daysUntil < 1 || $daysUntil > self::WARN_DAYS) return 0;

        $sms = app(SmsService::class);
        $count = 0;

        foreach (Student::where('balance', '>', 0)->where('status', 'active')->get() as $s) {
            if (!$s->parent_phone) continue;
            $msg = "MARELL ACADEMY\nReminder: Fee balance for {$s->name} ({$s->adm_no}) is KES " . number_format($s->balance, 2) . ".\nPay before " . $warnDate->format('d M') . " to avoid KES " . self::FINE_AMOUNT . " late fine.\nPay: marell.ac.ke/pay";
            try { $sms->send($s->parent_phone, $msg); $count++; } catch (\Throwable $e) {}
        }

        Log::info("Late fine warnings sent: {$count}");
        return $count;
    }

    public static function waive(LateFine $fine, string $reason, int $userId): void
    {
        DB::transaction(function () use ($fine, $reason, $userId) {
            $fine->update([
                'waived'        => true,
                'waived_reason' => $reason,
                'waived_by'     => $userId,
            ]);

            $student = Student::lockForUpdate()->find($fine->student_id);
            if ($student) {
                $student->update([
                    'fines_total' => max(0, (float) $student->fines_total - (float) $fine->amount),
                    'balance'     => max(0, (float) $student->balance - (float) $fine->amount),
                ]);
            }
        });
    }
}
