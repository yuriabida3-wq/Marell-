<?php

namespace App\Console\Commands;

use App\Services\LateFineService;
use Illuminate\Console\Command;

class ApplyLateFines extends Command
{
    protected $signature = 'fines:apply {--warn-only : Only send warnings, do not apply fines}';
    protected $description = 'Apply late payment fines and warnings';

    public function handle()
    {
        if ($this->option('warn-only')) {
            $count = LateFineService::warnUpcoming();
            $this->info("Warnings sent: {$count}");
            return 0;
        }

        // Send warning first, then apply fines (only on day 11+)
        $day = (int) now()->day;
        if ($day === LateFineService::DUE_DAY) {
            $count = LateFineService::warnUpcoming();
            $this->info("Warning day — sent: {$count}");
        }

        if ($day > LateFineService::DUE_DAY) {
            $result = LateFineService::applyMonthlyFines();
            $this->info("Fines applied: {$result['applied']}, skipped: {$result['skipped']}");
        } else {
            $this->info('Not yet due day — no fines applied.');
        }

        return 0;
    }
}
