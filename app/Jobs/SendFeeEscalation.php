<?php

namespace App\Jobs;

use App\Models\FeeEscalation;
use App\Services\SmsService;
use App\Services\VoiceSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * L7: async dispatch of a single fee escalation.
 *
 * Status model:
 *   pending  -> row created, not yet dispatched (reaper will retry after 5 min)
 *   sent     -> provider accepted (or the channel has no sender and is a no-op)
 *   failed   -> sender threw, error recorded
 *   skipped  -> channel has no configured sender (letter, default) and is
 *               intentionally not sent. Does NOT count as success.
 *
 * Idempotent: returns immediately if the row is already in a terminal state.
 */
class SendFeeEscalation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 3;
    public array $backoff = [30, 120, 300];
    public int   $timeout = 60;

    public function __construct(public int $escalationId) {}

    public function handle(): void
    {
        $escalation = FeeEscalation::find($this->escalationId);
        if (!$escalation) {
            Log::warning("SendFeeEscalation: row {$this->escalationId} not found");
            return;
        }

        // Terminal states are not re-sent.
        if (in_array($escalation->status, ['sent', 'skipped'], true)) {
            return;
        }

        $student = $escalation->student;
        if (!$student) {
            $escalation->update([
                'status' => 'failed',
                'error'  => 'Student no longer exists',
            ]);
            return;
        }

        if (!$student->parent_phone) {
            $escalation->update([
                'status' => 'skipped',
                'error'  => 'No parent phone on file',
            ]);
            return;
        }

        try {
            $accepted = $this->dispatchByChannel($escalation, $student->parent_phone);

            if ($accepted) {
                $escalation->update([
                    'status'  => 'sent',
                    'sent_at' => now(),
                    'error'   => null,
                ]);
            } else {
                $escalation->update([
                    'status' => 'skipped',
                    'error'  => "Channel '{$escalation->channel}' has no configured sender",
                ]);
            }
        } catch (\Throwable $e) {
            $escalation->update([
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ]);
            Log::warning("SendFeeEscalation failed for row {$escalation->id}: " . $e->getMessage());
            throw $e; // let the queue retry
        }
    }

    /**
     * Returns true if the channel has a sender and it accepted the message.
     * Returns false if the channel has no sender (letter, default).
     */
    protected function dispatchByChannel(FeeEscalation $escalation, string $phone): bool
    {
        return match ($escalation->channel) {
            'sms', 'whatsapp' => (bool) app(SmsService::class)->send($phone, $escalation->message),
            'voice'           => (bool) app(VoiceSmsService::class)->call($phone, $escalation->message),
            'letter', 'default' => false,
            default           => false,
        };
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SendFeeEscalation permanently failed for row {$this->escalationId}: " . $e->getMessage());
    }
}
