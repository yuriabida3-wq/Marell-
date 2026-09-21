<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixTimetableSeeder extends Seeder
{
    public function run(): void
    {
        $classes = Classroom::where('active', true)->get();
        $teachers = User::role('teacher')->get();
        $subjects = ['English','Kiswahili','Mathematics','Science','Social Studies','CRE','Agriculture','Creative Arts'];

        $days = ['Mon','Tue','Wed','Thu','Fri'];
        $periods = [1,2,3,4,5,6,7,8];

        $times = [
            1 => ['08:00','08:40'], 2 => ['08:40','09:20'], 3 => ['09:20','10:00'],
            4 => ['10:20','11:00'], 5 => ['11:00','11:40'], 6 => ['11:40','12:20'],
            7 => ['14:00','14:40'], 8 => ['14:40','15:20'],
        ];

        // Reset timetable for these classes
        Timetable::whereIn('class', $classes->pluck('name')->unique())->delete();

        // Track teacher busy slots: [teacher_id][day-period] = true
        $busy = [];

        $ttCount = 0;
        foreach ($classes as $class) {
            $idx = 0;
            foreach ($days as $day) {
                foreach ($periods as $p) {
                    // Find a teacher free at this (day, period)
                    $chosen = null;
                    foreach ($teachers as $t) {
                        $key = "{$day}-{$p}";
                        if (!isset($busy[$t->id][$key])) {
                            $chosen = $t;
                            break;
                        }
                    }

                    if (!$chosen) {
                        // No free teacher — leave slot empty
                        continue;
                    }

                    Timetable::create([
                        'class'      => $class->name,
                        'stream'     => $class->stream,
                        'day'        => $day,
                        'period'     => $p,
                        'subject'    => $subjects[$idx % count($subjects)],
                        'teacher_id' => $chosen->id,
                        'start_time' => $times[$p][0],
                        'end_time'   => $times[$p][1],
                    ]);

                    $busy[$chosen->id][$key] = true;
                    $idx++;
                    $ttCount++;
                }
            }
        }

        $this->command->info("Timetable rebuilt: {$ttCount} slots, no teacher double-booking");
    }
}
