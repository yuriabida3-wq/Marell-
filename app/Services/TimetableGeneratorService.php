<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\TeacherSubject;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TimetableGeneratorService
{
    const DAYS    = ['Mon','Tue','Wed','Thu','Fri'];
    const PERIODS = [1,2,3,4,5,6,7,8];

    const TIMES = [
        1 => ['08:00','08:40'], 2 => ['08:40','09:20'], 3 => ['09:20','10:00'],
        4 => ['10:20','11:00'], 5 => ['11:00','11:40'], 6 => ['11:40','12:20'],
        7 => ['14:00','14:40'], 8 => ['14:40','15:20'],
    ];

    /**
     * Generate timetable for ALL classes for a term/year.
     * Guarantees zero teacher overlap across the entire school.
     */
    public function generateForSchool(string $term, string $year): array
    {
        $classes = Classroom::where('active', true)->orderBy('name')->orderBy('stream')->get();
        if ($classes->isEmpty()) {
            return ['error' => 'No active classes found.'];
        }

        // Build teacher → assignments map
        // teacher_subject rows: [teacher_id, subject, class, stream, periods_per_week]
        $assignments = TeacherSubject::active()->get();

        // Group by class-stream key
        $byClass = [];
        foreach ($assignments as $a) {
            $key = $a->class . '|' . ($a->stream ?? '');
            $byClass[$key][] = $a;
        }

        // Track busy: [teacher_id]["Day-P"] = true (shared across whole school)
        $busy = [];

        $result = DB::transaction(function () use ($classes, $byClass, $term, $year, &$busy) {
            // Wipe existing for this term/year
            Timetable::where('term', $term)->where('year', $year)->delete();

            $totalSlots = 0;
            $unassigned = 0;

            foreach ($classes as $class) {
                $key = $class->name . '|' . ($class->stream ?? '');
                $pool = $byClass[$key] ?? [];

                // Build weighted subject queue for this class
                $subjectQueue = $this->buildSubjectQueue($pool);

                if (empty($subjectQueue)) {
                    // Fallback: create generic subjects with no teacher
                    $subjectQueue = $this->genericSubjects();
                }

                $idx = 0;
                foreach (self::DAYS as $day) {
                    foreach (self::PERIODS as $period) {
                        $slotKey = "{$day}-{$period}";

                        // Try to assign subject with an available teacher
                        $assignment = null;
                        $tried = 0;
                        while ($tried < count($subjectQueue)) {
                            $candidate = $subjectQueue[$idx % count($subjectQueue)];
                            $idx++;
                            $tried++;

                            if ($candidate['teacher_id'] === null) {
                                $assignment = $candidate;
                                break;
                            }

                            // Is this teacher free at this slot?
                            if (!isset($busy[$candidate['teacher_id']][$slotKey])) {
                                $assignment = $candidate;
                                break;
                            }
                        }

                        // Fallback if no assignment found
                        if (!$assignment) {
                            $assignment = [
                                'subject' => $subjectQueue[0]['subject'] ?? 'Study',
                                'teacher_id' => null,
                            ];
                            $unassigned++;
                        }

                        Timetable::create([
                            'class'      => $class->name,
                            'stream'     => $class->stream,
                            'term'       => $term,
                            'year'       => $year,
                            'day'        => $day,
                            'period'     => $period,
                            'subject'    => $assignment['subject'],
                            'teacher_id' => $assignment['teacher_id'],
                            'start_time' => self::TIMES[$period][0],
                            'end_time'   => self::TIMES[$period][1],
                        ]);

                        if ($assignment['teacher_id']) {
                            $busy[$assignment['teacher_id']][$slotKey] = true;
                        }

                        $totalSlots++;
                    }
                }
            }

            return [
                'classes'     => $classes->count(),
                'slots'       => $totalSlots,
                'unassigned'  => $unassigned,
                'term'        => $term,
                'year'        => $year,
            ];
        });

        return $result;
    }

    /**
     * Build weighted subject queue: each subject repeated N times
     * where N = periods per week from the assignment.
     * Rotation ordering ensures even distribution.
     */
    protected function buildSubjectQueue(array $pool): array
    {
        if (empty($pool)) return [];

        $queue = [];
        // For each assignment, add N entries (spread evenly)
        $maxPeriods = 8;
        for ($i = 0; $i < $maxPeriods; $i++) {
            foreach ($pool as $a) {
                if ($i < $a->periods_per_week) {
                    $queue[] = [
                        'subject'    => $a->subject,
                        'teacher_id' => $a->teacher_id,
                    ];
                }
            }
        }

        // Shuffle lightly so consecutive slots vary
        return $queue;
    }

    protected function genericSubjects(): array
    {
        $list = ['English','Kiswahili','Mathematics','Science','Social Studies','CRE','Agriculture','Creative Arts'];
        $out = [];
        foreach ($list as $s) $out[] = ['subject' => $s, 'teacher_id' => null];
        return $out;
    }

    /**
     * Verify no teacher is double-booked for a term/year.
     * Returns array of clashes (empty = clean).
     */
    public function verify(string $term, string $year): array
    {
        $rows = Timetable::where('term', $term)->where('year', $year)
            ->whereNotNull('teacher_id')
            ->get(['teacher_id','day','period','class','stream']);

        $seen = [];
        $clashes = [];

        foreach ($rows as $r) {
            $k = "{$r->teacher_id}-{$r->day}-{$r->period}";
            if (isset($seen[$k])) {
                $clashes[] = [
                    'teacher_id' => $r->teacher_id,
                    'slot'       => "{$r->day}-P{$r->period}",
                    'class_a'    => $seen[$k],
                    'class_b'    => "{$r->class} {$r->stream}",
                ];
            } else {
                $seen[$k] = "{$r->class} {$r->stream}";
            }
        }

        return $clashes;
    }

    /**
     * Get teacher load summary for a term.
     */
    public function teacherLoad(string $term, string $year): array
    {
        $rows = Timetable::where('term', $term)->where('year', $year)
            ->whereNotNull('teacher_id')
            ->select('teacher_id', DB::raw('COUNT(*) as slots'))
            ->groupBy('teacher_id')
            ->get();

        return User::whereIn('id', $rows->pluck('teacher_id'))
            ->get()
            ->map(function ($t) use ($rows) {
                $slot = $rows->firstWhere('teacher_id', $t->id);
                return ['name' => $t->name, 'slots' => $slot->slots ?? 0];
            })
            ->sortByDesc('slots')
            ->values()
            ->all();
    }
}
