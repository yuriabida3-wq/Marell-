<?php

namespace Database\Seeders;

use App\Models\Timetable;
use App\Models\User;
use Illuminate\Database\Seeder;

class CheckTimetableSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = User::role('teacher')->get();

        foreach ($teachers as $t) {
            $slots = Timetable::where('teacher_id', $t->id)->get();
            $map = [];
            foreach ($slots as $s) {
                $k = $s->day . '-P' . $s->period;
                $map[$k] = ($map[$k] ?? 0) + 1;
            }
            $clashes = array_filter($map, fn ($c) => $c > 1);

            $icon = empty($clashes) ? '✅' : '❌';
            $this->command->info("{$icon} {$t->name}: {$slots->count()} slots, " . count($clashes) . " clashes");

            foreach ($clashes as $k => $c) {
                $this->command->error("   └ {$k} → {$c} simultaneous classes");
            }
        }
    }
}
