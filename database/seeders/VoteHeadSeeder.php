<?php
namespace Database\Seeders;
use App\Models\VoteHead;
use Illuminate\Database\Seeder;

class VoteHeadSeeder extends Seeder {
    public function run(): void {
        $heads = [
            ['TUITION',  'Tuition Fee',    'Core academic fees'],
            ['TRANSPORT','Transport Fee',  'School bus service'],
            ['LUNCH',    'Lunch Program',  'Daily meals'],
            ['EXAM',     'Exam Fee',       'Termly exam materials'],
            ['ACTIVITY', 'Activity Fee',   'Sports, clubs, trips'],
            ['DEV',      'Development Fee','Infrastructure & facilities'],
        ];
        foreach ($heads as $h) {
            VoteHead::firstOrCreate(['code' => $h[0]], [
                'name' => $h[1],
                'description' => $h[2],
                'active' => true,
            ]);
        }
        echo "Vote heads seeded: " . count($heads) . PHP_EOL;
    }
}
