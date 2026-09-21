<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\StudentFeeVote;
use App\Models\VoteHead;
use Illuminate\Console\Command;

class DistributeVoteHeads extends Command
{
    protected $signature = 'votes:distribute {--reset : Wipe existing splits first}';
    protected $description = 'Split each student total_fee across vote heads';

    public function handle()
    {
        $heads = VoteHead::where('active', true)->get();
        if ($heads->isEmpty()) { $this->error('No vote heads found.'); return 1; }

        if ($this->option('reset')) {
            StudentFeeVote::truncate();
            $this->info('Existing splits wiped.');
        }

        $ratios = [
            'TUITION'   => 0.70,
            'TRANSPORT' => 0.10,
            'LUNCH'     => 0.10,
            'EXAM'      => 0.03,
            'ACTIVITY'  => 0.03,
            'DEV'       => 0.04,
        ];

        $bar = $this->output->createProgressBar(Student::count());
        $bar->start();

        foreach (Student::cursor() as $student) {
            foreach ($heads as $head) {
                $ratio = $ratios[$head->code] ?? 0;
                $due = round((float) $student->total_fee * $ratio, 2);

                $paidRatio = $student->total_fee > 0
                    ? (float) $student->paid_amount / (float) $student->total_fee
                    : 0;
                $paid = round($due * $paidRatio, 2);

                StudentFeeVote::updateOrCreate(
                    ['student_id' => $student->id, 'vote_head_id' => $head->id],
                    ['amount_due' => $due, 'amount_paid' => $paid]
                );
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('Vote splits distributed for ' . Student::count() . ' students.');
        return 0;
    }
}
