<?php

namespace App\Jobs;

use App\Models\Student;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDailyDefaulterReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(public string $directorPhone) {}

    public function handle(): void
    {
        $defaulters = Student::where('balance', '>', 0)
            ->where('status', 'active')
            ->orderByDesc('balance')
            ->get();

        if ($defaulters->isEmpty()) {
            Log::info('Daily defaulter report: no defaulters today');
            return;
        }

        $count = $defaulters->count();
        $total = $defaulters->sum('balance');
        $top = $defaulters->take(3)->map(fn($s) => "{$s->name} (KES " . number_format($s->balance, 0) . ")")->implode(', ');

        $msg = "MARELL DAILY 7AM ALERT\n"
            . "Defaulters: {$count}\n"
            . "Total owed: KES " . number_format($total, 0) . "\n"
            . "Top 3: {$top}\n"
            . "View: marell.ac.ke/principal/finance/defaulters";

        try {
            app(SmsService::class)->send($this->directorPhone, $msg);
            Log::info("Daily defaulter SMS sent to {$this->directorPhone}: {$count} defaulters, KES {$total}");
        } catch (\Throwable $e) {
            Log::error('Daily defaulter SMS failed: ' . $e->getMessage());
        }
    }
}
