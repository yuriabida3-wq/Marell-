<?php

namespace App\Jobs;

use App\Models\EmergencyAlert;
use App\Models\Student;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmergencyAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(public EmergencyAlert $alert) {}

    public function handle(): void
    {
        $alert = $this->alert;
        $alert->update(['status' => 'sending']);

        $phones = [];

        if (in_array($alert->audience, ['parents', 'both'])) {
            $phones = array_merge($phones,
                Student::whereNotNull('parent_phone')->distinct()->pluck('parent_phone')->all()
            );
        }

        if (in_array($alert->audience, ['teachers', 'both'])) {
            $phones = array_merge($phones,
                User::role('teacher')->whereNotNull('phone')->pluck('phone')->all()
            );
        }

        $phones = array_values(array_unique(array_filter($phones)));
        $alert->update(['total_recipients' => count($phones)]);

        $sms = app(SmsService::class);
        $sent = 0;
        $failed = 0;

        $prefix = match ($alert->severity) {
            'critical' => "🚨 MARELL EMERGENCY\n",
            'warning'  => "⚠️ MARELL ALERT\n",
            default    => "MARELL NOTICE\n",
        };

        $body = $prefix . $alert->title . "\n\n" . $alert->message;

        foreach ($phones as $phone) {
            $normalized = \App\Services\MpesaService::normalizePhone($phone) ?: $phone;
            try {
                if ($sms->send($normalized, $body)) $sent++;
                else $failed++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning("Emergency SMS failed to {$phone}: " . $e->getMessage());
            }
        }

        $alert->update([
            'status'       => 'sent',
            'sent_count'   => $sent,
            'failed_count' => $failed,
            'sent_at'      => now(),
        ]);

        Log::info("Emergency alert #{$alert->id} sent: {$sent} ok, {$failed} failed");
    }
}
