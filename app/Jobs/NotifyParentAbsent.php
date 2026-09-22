<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyParentAbsent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    /**
     * @param array<int> $attendanceIds
     */
    public function __construct(public array $attendanceIds) {}

    public function handle(): void
    {
        $sms = app(SmsService::class);

        $attendances = Attendance::with('student')
            ->whereIn('id', $this->attendanceIds)
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($attendances as $a) {
            $student = $a->student;
            if (!$student || !$student->parent_phone) continue;

            $msg = "MARELL ACADEMY\n"
                . "Habari {$student->parent_name},\n"
                . "{$student->name} ({$student->adm_no}) was marked ABSENT today ({$a->date->format('d M Y')}).\n"
                . "If this is a mistake, please call +254 700 000 000.";

            try {
                if ($sms->send($student->parent_phone, $msg)) {
                    $sent++;
                    $a->update(['sms_sent' => true, 'sms_sent_at' => now()]);
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::warning("Absent SMS failed to {$student->parent_phone}: " . $e->getMessage());
            }
        }

        Log::info("Absent notifications: sent={$sent}, failed={$failed}");
    }
}
