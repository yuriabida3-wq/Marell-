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
use Illuminate\Support\Facades\Log;

class NotifyResultsPublished implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public Exam $exam) {}

    public function handle(): void
    {
        $sms = app(SmsService::class);

        $students = Student::whereHas('results', function ($q) {
            $q->where('exam_id', $this->exam->id);
        })->get();

        $sent = 0;
        $failed = 0;

        foreach ($students as $student) {
            if (!$student->parent_phone) continue;

            try {
                $avg = Result::where('exam_id', $this->exam->id)
                    ->where('student_id', $student->id)
                    ->avg('marks');

                $avg = round((float) $avg, 1);
                $msg = "MARELL ACADEMY\n{$this->exam->name} ({$this->exam->term} {$this->exam->year}) results are out.\nStudent: {$student->name}\nMean: {$avg}\nView: marell.ac.ke/results?adm={$student->adm_no}";

                if ($sms->send($student->parent_phone, $msg)) $sent++;
                else $failed++;
            } catch (\Throwable $e) {
                Log::warning("SMS failed for {$student->adm_no}: " . $e->getMessage());
                $failed++;
            }
        }

        Log::info("Results SMS for exam {$this->exam->id}: sent={$sent}, failed={$failed}");
    }
}
