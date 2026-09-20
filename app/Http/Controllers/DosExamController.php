<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DosExamController extends Controller
{
    // ---------- EXAMS ----------
    public function index()
    {
        $exams = Exam::withCount('results')->orderByDesc('created_at')->paginate(20);
        return view('dos.exams.index', compact('exams'));
    }

    public function create()
    {
        return view('dos.exams.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'term' => 'required|string|max:20',
            'year' => 'required|string|max:10',
        ]);

        $data['status']     = 'draft';
        $data['created_by'] = auth()->id();

        $exam = Exam::create($data);

        return redirect()->route('dos.exams.show', $exam)
            ->with('success', "Exam '{$exam->name}' created.");
    }

    public function show(Exam $exam)
    {
        $classes = Classroom::where('active', true)->orderBy('name')->get();

        // For each class, compute how many marks entries exist vs expected
        $summary = [];
        foreach ($classes as $c) {
            $studentsCount = Student::where('class', $c->name)
                ->where('stream', $c->stream)
                ->where('status', 'active')
                ->count();

            $entries = Result::where('exam_id', $exam->id)
                ->whereHas('student', function ($q) use ($c) {
                    $q->where('class', $c->name)->where('stream', $c->stream);
                })
                ->count();

            $summary[] = [
                'class'    => $c,
                'students' => $studentsCount,
                'entries'  => $entries,
            ];
        }

        return view('dos.exams.show', compact('exam', 'summary'));
    }

    public function updateStatus(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,open,closed,published',
        ]);

        $exam->update(['status' => $data['status']]);

        return back()->with('success', "Exam status: {$data['status']}.");
    }

    // ---------- MARKS ENTRY ----------
    public function marksSelect(Request $request)
    {
        $exam      = null;
        $class     = null;
        $students  = collect();
        $subjects  = ['English','Kiswahili','Mathematics','Science','Social Studies','CRE','Agriculture','Creative Arts','Integrated Science','Pre-Technical Studies','Business Studies','ICT'];

        if ($request->filled('exam_id') && $request->filled('class_id')) {
            $exam  = Exam::findOrFail($request->exam_id);
            $class = Classroom::findOrFail($request->class_id);

            $students = Student::where('class', $class->name)
                ->where('stream', $class->stream)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            // Preload existing marks: [student_id][subject] = result row
            $existing = Result::where('exam_id', $exam->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->groupBy('student_id')
                ->map(fn ($rows) => $rows->keyBy('subject'));
        } else {
            $existing = collect();
        }

        $exams   = Exam::orderByDesc('created_at')->get();
        $classes = Classroom::where('active', true)->orderBy('name')->get();

        return view('dos.exams.marks', compact('exam', 'class', 'students', 'subjects', 'exams', 'classes', 'existing'));
    }

    public function marksStore(Request $request)
    {
        $data = $request->validate([
            'exam_id'  => 'required|exists:exams,id',
            'class_id' => 'required|exists:classrooms,id',
            'marks'    => 'required|array',
            'marks.*'  => 'array',
            'marks.*.*'=> 'nullable|integer|min:0|max:100',
        ]);

        $exam  = Exam::findOrFail($data['exam_id']);
        $class = Classroom::findOrFail($data['class_id']);

        if (!in_array($exam->status, ['draft', 'open'])) {
            return back()->with('error', 'This exam is closed or published — cannot edit marks.');
        }

        DB::beginTransaction();
        try {
            foreach ($data['marks'] as $studentId => $subjectMarks) {
                foreach ($subjectMarks as $subject => $mark) {
                    if ($mark === null || $mark === '') continue;

                    $mark = (int) $mark;
                    $grade = $this->grade($mark);

                    Result::updateOrCreate(
                        [
                            'exam_id'    => $exam->id,
                            'student_id' => $studentId,
                            'subject'    => $subject,
                        ],
                        [
                            'marks'      => $mark,
                            'grade'      => $grade,
                            'teacher_id' => auth()->id(),
                        ]
                    );
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Save failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Marks saved. ' . count($data['marks']) . ' students updated.');
    }

    // ---------- MISSING MARKS ----------
    public function missing(Request $request)
    {
        $examId = $request->get('exam_id');
        $exam   = $examId ? Exam::find($examId) : null;
        $exams  = Exam::orderByDesc('created_at')->get();

        $report = [];

        if ($exam) {
            $classes = Classroom::where('active', true)->orderBy('name')->get();
            foreach ($classes as $c) {
                $studentIds = Student::where('class', $c->name)
                    ->where('stream', $c->stream)
                    ->where('status', 'active')
                    ->pluck('id');

                if ($studentIds->isEmpty()) continue;

                $subjectsEntered = Result::where('exam_id', $exam->id)
                    ->whereIn('student_id', $studentIds)
                    ->distinct('subject')
                    ->pluck('subject')
                    ->all();

                $studentsWithout = Student::whereIn('id', $studentIds)
                    ->whereDoesntHave('results', function ($q) use ($exam) {
                        $q->where('exam_id', $exam->id);
                    })
                    ->count();

                $report[] = [
                    'class'           => $c,
                    'students'        => count($studentIds),
                    'subjects_entered'=> count($subjectsEntered),
                    'subjects'        => $subjectsEntered,
                    'students_missing'=> $studentsWithout,
                ];
            }
        }

        return view('dos.exams.missing', compact('exams', 'exam', 'report'));
    }

    // ---------- PUBLISH + SMS ----------
    public function publish(Exam $exam)
    {
        if ($exam->status !== 'closed') {
            return back()->with('error', 'Close the exam first, then publish.');
        }

        $exam->update(['status' => 'published']);

        // Dispatch SMS job (queued)
        \App\Jobs\NotifyResultsPublished::dispatch($exam)->onQueue('default');

        return back()->with('success', "Exam '{$exam->name}' published. SMS notifications queued.");
    }

    // ---------- helpers ----------
    protected function grade(int $marks): string
    {
        return match(true) {
            $marks >= 80 => 'A',
            $marks >= 75 => 'A-',
            $marks >= 70 => 'B+',
            $marks >= 65 => 'B',
            $marks >= 60 => 'B-',
            $marks >= 55 => 'C+',
            $marks >= 50 => 'C',
            $marks >= 45 => 'C-',
            $marks >= 40 => 'D+',
            $marks >= 35 => 'D',
            default      => 'E',
        };
    }
}
