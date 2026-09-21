<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Timetable;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DosController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'students'   => Student::where('status', 'active')->count(),
            'teachers'   => User::role('teacher')->count(),
            'classes'    => Classroom::where('active', true)->count(),
            'exams_open' => Exam::whereIn('status', ['open', 'draft'])->count(),
        ];

        $teachers = User::role('teacher')->take(6)->get();

        return view('dos.dashboard', compact('stats', 'teachers'));
    }

    // ---------- CLASSES ----------
    public function classesIndex()
    {
        $classes  = Classroom::with('classTeacher')->orderBy('name')->orderBy('stream')->paginate(30);
        $teachers = User::role('teacher')->orderBy('name')->get();
        $levels   = ['Baby', 'Lower', 'Upper', 'JSS'];
        return view('dos.classes.index', compact('classes', 'teachers', 'levels'));
    }

    public function classesStore(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:30',
            'stream'           => 'nullable|string|max:30',
            'level'            => 'nullable|string|max:30',
            'class_teacher_id' => 'nullable|exists:users,id',
            'capacity'         => 'nullable|integer|min:1|max:200',
        ]);

        // Check for duplicate
        $exists = Classroom::where('name', $data['name'])
            ->where(function ($q) use ($data) {
                if (!empty($data['stream'])) {
                    $q->where('stream', $data['stream']);
                } else {
                    $q->whereNull('stream')->orWhere('stream', '');
                }
            })
            ->exists();

        if ($exists) {
            $label = $data['name'] . (!empty($data['stream']) ? ' ' . $data['stream'] : '');
            return back()->with('error', "Class \"{$label}\" already exists.")->withInput();
        }

        Classroom::create($data);

        return back()->with('success', 'Class created.');
    }

    public function classesUpdate(Request $request, Classroom $classroom)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:30',
            'stream'           => 'nullable|string|max:30',
            'level'            => 'nullable|string|max:30',
            'class_teacher_id' => 'nullable|exists:users,id',
            'capacity'         => 'nullable|integer|min:1|max:200',
            'active'           => 'boolean',
        ]);

        // Check for duplicate (excluding self)
        $exists = Classroom::where('name', $data['name'])
            ->where(function ($q) use ($data) {
                if (!empty($data['stream'])) {
                    $q->where('stream', $data['stream']);
                } else {
                    $q->whereNull('stream')->orWhere('stream', '');
                }
            })
            ->where('id', '!=', $classroom->id)
            ->exists();

        if ($exists) {
            $label = $data['name'] . (!empty($data['stream']) ? ' ' . $data['stream'] : '');
            return back()->with('error', "Class \"{$label}\" already exists.");
        }

        $classroom->update($data);

        return back()->with('success', 'Class updated.');
    }

    public function classesDestroy(Classroom $classroom)
    {
        $classroom->delete();
        return back()->with('success', 'Class deleted.');
    }

    // ---------- TIMETABLE ----------
    public function timetableIndex()
    {
        $classes  = Classroom::where('active', true)->orderBy('name')->orderBy('stream')->get();
        $teachers = User::role('teacher')->orderBy('name')->get();

        return view('dos.timetable.index', compact('classes', 'teachers'));
    }

    public function timetableByClass(Classroom $classroom)
    {
        $class = $classroom;
        $grid  = $this->buildGrid($class);

        return view('dos.timetable.show', compact('class', 'grid'));
    }

    public function timetableGenerate(Request $request)
    {
        $data = $request->validate([
            'class_id'     => 'required|exists:classrooms,id',
            'subject_list' => 'required|string',
        ]);

        $class = Classroom::findOrFail($data['class_id']);

        $subjects = array_filter(array_map('trim', explode(',', $data['subject_list'])));
        if (empty($subjects)) {
            return back()->with('error', 'Please provide at least one subject.');
        }

        $days    = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
        $periods = [1, 2, 3, 4, 5, 6, 7, 8];

        $times = [
            1 => ['08:00','08:40'], 2 => ['08:40','09:20'], 3 => ['09:20','10:00'],
            4 => ['10:20','11:00'], 5 => ['11:00','11:40'], 6 => ['11:40','12:20'],
            7 => ['14:00','14:40'], 8 => ['14:40','15:20'],
        ];

        $teacherBusy = Timetable::whereNotNull('teacher_id')
            ->get(['teacher_id', 'day', 'period'])
            ->groupBy('teacher_id')
            ->map(fn ($rows) => $rows->mapWithKeys(fn ($r) => ["{$r->day}-{$r->period}" => true])->all())
            ->all();

        $teachers = User::role('teacher')->get();

        DB::beginTransaction();
        try {
            Timetable::where('class', $class->name)
                ->where('stream', $class->stream)
                ->delete();

            $subjectCount = count($subjects);
            $subjectIndex = 0;

            foreach ($days as $day) {
                foreach ($periods as $period) {
                    $subject = array_values($subjects)[$subjectIndex % $subjectCount];
                    $subjectIndex++;

                    $teacher = $teachers->first(function ($t) use ($teacherBusy, $day, $period) {
                        return !isset($teacherBusy[$t->id]["{$day}-{$period}"]);
                    });

                    $teacherId = $teacher?->id;

                    Timetable::create([
                        'class'      => $class->name,
                        'stream'     => $class->stream,
                        'day'        => $day,
                        'period'     => $period,
                        'subject'    => $subject,
                        'teacher_id' => $teacherId,
                        'start_time' => $times[$period][0],
                        'end_time'   => $times[$period][1],
                    ]);

                    if ($teacherId) {
                        $teacherBusy[$teacherId]["{$day}-{$period}"] = true;
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Generate failed: ' . $e->getMessage());
        }

        return redirect()->route('dos.timetable.show', $class)
            ->with('success', 'Timetable generated for ' . $class->label());
    }

    protected function buildGrid(Classroom $class): array
    {
        $rows = Timetable::with('teacher')
            ->where('class', $class->name)
            ->where('stream', $class->stream)
            ->get();

        $grid = [];
        foreach ($rows as $r) {
            $grid[$r->day][$r->period] = $r;
        }
        return $grid;
    }

    public function timetableManual(Request $request)
    {
        $data = $request->validate([
            'class'      => 'required|string',
            'stream'     => 'nullable|string',
            'day'        => 'required|in:Mon,Tue,Wed,Thu,Fri',
            'period'     => 'required|integer|between:1,8',
            'subject'    => 'required|string',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $times = [
            1 => ['08:00','08:40'], 2 => ['08:40','09:20'], 3 => ['09:20','10:00'],
            4 => ['10:20','11:00'], 5 => ['11:00','11:40'], 6 => ['11:40','12:20'],
            7 => ['14:00','14:40'], 8 => ['14:40','15:20'],
        ];

        Timetable::updateOrCreate(
            ['class' => $data['class'], 'stream' => $data['stream'], 'day' => $data['day'], 'period' => $data['period']],
            [
                'subject'    => $data['subject'],
                'teacher_id' => $data['teacher_id'],
                'start_time' => $times[$data['period']][0],
                'end_time'   => $times[$data['period']][1],
            ]
        );

        return back()->with('success', 'Slot saved.');
    }

    public function timetablePdf(Classroom $classroom)
    {
        $class = $classroom;
        $grid  = $this->buildGrid($class);
        $pdf   = Pdf::loadView('pdf.timetable', compact('class', 'grid'))->setPaper('A4', 'landscape');
        return $pdf->download('Timetable-' . str_replace(' ', '-', $class->label()) . '.pdf');
    }

    public function timetableTeacher(Request $request)
    {
        $teacher = null;
        if ($request->filled('teacher_id')) {
            $teacher = User::findOrFail($request->teacher_id);
        }

        $teachers = User::role('teacher')->orderBy('name')->get();

        $grid = [];
        if ($teacher) {
            $rows = Timetable::where('teacher_id', $teacher->id)->get();
            foreach ($rows as $r) {
                $grid[$r->day][$r->period] = $r;
            }
        }

        return view('dos.timetable.teacher', compact('teacher', 'teachers', 'grid'));
    }
}
