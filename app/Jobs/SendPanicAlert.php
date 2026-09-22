<?php

namespace App\Jobs;

use App\Models\PanicAlert;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPanicAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(public PanicAlert $alert) {}

    public function handle(): void
    {
        $sms = app(SmsService::class);

        // Collect recipients using proper Spatie role scopes separately
        $principals = User::role('principal')->whereNotNull('phone')->pluck('phone')->all();
        $dosUsers   = User::role('dos')->whereNotNull('phone')->pluck('phone')->all();
        $teachers   = User::role('teacher')->whereNotNull('phone')->pluck('phone')->all();

        $recipients = array_values(array_unique(array_filter(array_merge($principals, $dosUsers, $teachers))));

        $msg = "🚨 MARELL PANIC ALERT 🚨\n"
            . "Location: {$this->alert->location}\n"
            . "By: {$this->alert->user_name}\n"
            . ($this->alert->note ? "Note: {$this->alert->note}\n" : '')
            . "Time: " . now()->format('H:i') . "\n"
            . "Respond immediately.";

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $phone) {
            $normalized = \App\Services\MpesaService::normalizePhone($phone) ?: $phone;
            try {
                if ($sms->send($normalized, $msg)) $sent++;
                else $failed++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        $this->alert->update(['sms_sent' => $sent, 'sms_failed' => $failed]);

        Log::warning("🚨 PANIC ALERT #{$this->alert->id}: sent={$sent}, failed={$failed}, loc={$this->alert->location}");
    }
}
