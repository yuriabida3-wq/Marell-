<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Models\Result;
use App\Models\Student;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendResultSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 180;

    public function __construct(public Exam $exam, public ?string $classFilter = null) {}

    public function handle(): void
    {
        $sms = app(SmsService::class);

        // Build class ranking once (memory-efficient)
        $studentIds = Student::where('status', 'active')
            ->when($this->classFilter, fn($q) => $q->where('class', $this->classFilter))
            ->pluck('id');

        $totals = Result::select('student_id', DB::raw('SUM(marks) as total'))
            ->where('exam_id', $this->exam->id)
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->get();

        $totalStudents = $totals->count();
        $positions = [];
        $i = 1;
        foreach ($totals as $t) {
            $positions[$t->student_id] = $i++;
        }

        $sent = 0; $failed = 0;

        foreach ($totals as $row) {
            $student = Student::find($row->student_id);
            if (!$student || !$student->parent_phone) continue;

            $position = $positions[$student->id] ?? 0;
            $mean = round((float) $row->total / max(1, Result::where('exam_id', $this->exam->id)->where('student_id', $student->id)->count()), 1);

            $msg = "MARELL ACADEMY\n"
                . "{$this->exam->name} results:\n"
                . "Student: {$student->name}\n"
                . "Position: {$position}/{$totalStudents}\n"
                . "Total: {$row->total}\n"
                . "Mean: {$mean}\n"
                . "Balance: KES " . number_format($student->balance, 0) . "\n"
                . "Full report card: marell.ac.ke/results?adm={$student->adm_no}";

            try {
                if ($sms->send($student->parent_phone, $msg)) $sent++;
                else $failed++;
            } catch (\Throwable $e) {
                Log::warning("Result SMS failed for {$student->adm_no}: " . $e->getMessage());
                $failed++;
            }
        }

        Log::info("Result SMS for exam {$this->exam->id}: sent={$sent}, failed={$failed}");
    }
}
