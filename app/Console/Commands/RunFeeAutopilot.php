<?php

namespace App\Console\Commands;

use App\Services\FeeAutopilotService;
use Illuminate\Console\Command;

class RunFeeAutopilot extends Command
{
    protected $signature = 'fees:autopilot';
    protected $description = 'Run daily fee escalation autopilot';

    public function handle()
    {
        $stats = FeeAutopilotService::runDaily();
        $this->info("Checked: {$stats['students_checked']}");
        $this->info("Sent: {$stats['escalations_sent']}");
        $this->info("Skipped: {$stats['skipped']}");
        return 0;
    }
}
