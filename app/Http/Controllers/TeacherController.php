<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Homework;
use App\Models\Result;
use App\Models\Student;
use App\Models\Timetable;
use App\Models\TeacherSubject;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $teacher = auth()->user();
        $today   = strtolower(now()->format('D')); // mon, tue...

        // Normalize: our timetable uses Mon/Tue/Wed/Thu/Fri (capitalized first letter)
        $dayMap = ['mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat','sun'=>'Sun'];
        $today  = $dayMap[$today] ?? 'Mon';

        $todaySlots = Timetable::with('teacher')
            ->where('teacher_id', $teacher->id)
            ->where('day', $today)
            ->orderBy('period')
            ->get();

        // Unique classes this teacher teaches
        $classKeys = Timetable::where('teacher_id', $teacher->id)
            ->select('class','stream')->distinct()->get();

        $myClasses = $classKeys->map(function ($k) {
            $q = Student::where('class', $k->class);
            if ($k->stream) if ($k->stream !== null && $k->stream !== '') { $q->when($k->stream !== null && $k->stream !== '', fn($q) => $q->where('stream', $k->stream)); }
            return ['class'=>$k->class,'stream'=>$k->stream,'count'=>$q->count()];
        });

        return view('teacher.dashboard', compact('todaySlots','myClasses','today'));
    }

    public function classes()
    {
        $teacher = auth()->user();

        $classKeys = Timetable::where('teacher_id', $teacher->id)
            ->select('class','stream')->distinct()->get();

        $classes = $classKeys->map(function ($k) use ($teacher) {
            $q = Student::where('class', $k->class);
            if ($k->stream) if ($k->stream !== null && $k->stream !== '') { $q->when($k->stream !== null && $k->stream !== '', fn($q) => $q->where('stream', $k->stream)); }
            $students = $q->orderBy('name')->get();

            // Subjects this teacher teaches in this class
            $subjects = Timetable::where('teacher_id', $teacher->id)
                ->where('class', $k->class)
                ->when($k->stream !== null && $k->stream !== '', fn($q) => $q->where('stream', $k->stream))
                ->distinct('subject')
                ->pluck('subject')
                ->unique()
                ->values();

            return [
                'class'    => $k->class,
                'stream'   => $k->stream,
                'students' => $students,
                'subjects' => $subjects,
            ];
        });

        return view('teacher.classes', compact('classes'));
    }

    public function timetable()
    {
        $teacher = auth()->user();

        $rows = Timetable::where('teacher_id', $teacher->id)->get();
        $grid = [];
        foreach ($rows as $r) {
            $grid[$r->day][$r->period] = $r;
        }

        return view('teacher.timetable', compact('grid'));
    }

    public function marksForm(Request $request)
    {
        $teacher = auth()->user();

        $exams = Exam::whereIn('status', ['open'])->orderByDesc('created_at')->get();

        $classKeys = Timetable::where('teacher_id', $teacher->id)
            ->select('class','stream')->distinct()->get();

        $students = collect();
        $subjects = collect();
        $exam = null;
        $classKey = null;
        $existing = collect();

        if ($request->filled('exam_id') && $request->filled('class') && $request->filled('subject')) {
            $exam     = Exam::findOrFail($request->exam_id);
            $classKey = ['class' => $request->class, 'stream' => $request->stream];

            $q = Student::where('class', $request->class);
            if ($request->stream) $q->where('stream', $request->stream);
            $students = $q->orderBy('name')->get();

            $subjects = Timetable::where('teacher_id', $teacher->id)
                ->where('class', $request->class)
                ->where('stream', $request->stream)
                ->distinct('subject')
                ->pluck('subject')->unique()->values();

            $existing = Result::where('exam_id', $exam->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->where('subject', $request->subject)
                ->get()
                ->keyBy('student_id');
        }

        return view('teacher.marks', compact('exams','classKeys','students','subjects','exam','classKey','existing'));
    }

    public function marksStore(Request $request)
    {
        $data = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class'   => 'required|string',
            'stream'  => 'nullable|string',
            'subject' => 'required|string',
            'marks'   => 'required|array',
            'marks.*' => 'nullable|integer|min:0|max:100',
        ]);

        $exam = Exam::findOrFail($data['exam_id']);
        if ($exam->status !== 'open') {
            return back()->with('error', 'Exam is not open for marks entry.');
        }

        // Verify this specific class is open for this exam
        if (!\App\Http\Controllers\ExamControlController::isOpenFor($exam, $data['class'], $data['stream'] ?? null)) {
            return back()->with('error', 'Marks entry for this class is closed. Contact DOS.');
        }

        $teacher = auth()->user();

        foreach ($data['marks'] as $studentId => $mark) {
            if ($mark === null || $mark === '') continue;

            Result::updateOrCreate(
                [
                    'exam_id'    => $exam->id,
                    'student_id' => $studentId,
                    'subject'    => $data['subject'],
                ],
                [
                    'marks'      => (int) $mark,
                    'grade'      => $this->grade((int) $mark),
                    'teacher_id' => $teacher->id,
                ]
            );
        }

        return back()->with('success', 'Marks saved.');
    }

    public function homeworkIndex()
    {
        $teacher = auth()->user();
        $homework = Homework::where('teacher_id', $teacher->id)->orderByDesc('due_date')->paginate(20);

        $classKeys = Timetable::where('teacher_id', $teacher->id)
            ->select('class','stream')->distinct()->get();

        return view('teacher.homework', compact('homework','classKeys'));
    }

    public function homeworkStore(Request $request)
    {
        $data = $request->validate([
            'class'       => 'required|string',
            'stream'      => 'nullable|string',
            'subject'     => 'required|string',
            'title'       => 'required|string|max:180',
            'description' => 'nullable|string|max:2000',
            'due_date'    => 'required|date|after_or_equal:today',
        ]);

        $data['teacher_id'] = auth()->id();
        $data['published']  = true;

        $hw = Homework::create($data);

        // Optional: SMS parents
        try {
            $q = Student::where('class', $data['class']);
            if (!empty($data['stream'])) $q->where('stream', $data['stream']);
            $phones = $q->whereNotNull('parent_phone')->distinct()->pluck('parent_phone')->all();

            $msg = "MARELL ACADEMY\nNew homework: {$data['subject']} - {$data['title']}\nDue: " . date('d M Y', strtotime($data['due_date']));
            $sms = app(SmsService::class);
            foreach (array_slice($phones, 0, 100) as $p) {
                try { $sms->send($p, $msg); } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {}

        return back()->with('success', 'Homework posted. Parents notified via SMS.');
    }

    public function classListPdf(Request $request)
    {
        $request->validate(['class'=>'required|string','stream'=>'nullable|string']);

        $q = Student::where('class', $request->class);
        if ($request->stream) $q->where('stream', $request->stream);
        $students = $q->orderBy('name')->get();

        $title = 'Class List — ' . $request->class . ($request->stream ? ' ' . $request->stream : '');

        $pdf = Pdf::loadHTML($this->classListHtml($students, $title))->setPaper('A4','portrait');
        return $pdf->download('ClassList-' . str_replace(' ','-',$title) . '.pdf');
    }

    // ---------- helpers ----------
    protected function grade(int $m): string
    {
        return match(true){
            $m>=80=>'A',$m>=75=>'A-',$m>=70=>'B+',$m>=65=>'B',$m>=60=>'B-',
            $m>=55=>'C+',$m>=50=>'C',$m>=45=>'C-',$m>=40=>'D+',$m>=35=>'D',default=>'E',
        };
    }

    protected function classListHtml($students, string $title): string
    {
        $html = '<html><head><style>
            body{font-family:DejaVu Sans,sans-serif;font-size:11px;padding:20px}
            .header{border-bottom:3px solid #0B3D91;padding-bottom:10px;margin-bottom:15px}
            .logo{width:40px;height:40px;background:#D4AF37;color:#0B3D91;border-radius:50%;text-align:center;line-height:40px;font-weight:bold;font-size:18px;display:inline-block;vertical-align:middle}
            .school{display:inline-block;margin-left:10px;vertical-align:middle}
            .school-name{font-size:18px;font-weight:bold;color:#0B3D91}
            .school-tag{font-size:8px;letter-spacing:2px;color:#D4AF37;font-weight:bold}
            .title{text-align:right;font-size:14px;font-weight:bold;color:#0B3D91}
            table{width:100%;border-collapse:collapse;margin-top:15px}
            th{background:#0B3D91;color:white;padding:6px;font-size:10px;text-align:left}
            td{padding:6px;border-bottom:1px solid #e5e7eb}
            .footer{margin-top:20px;padding-top:8px;border-top:2px solid #D4AF37;text-align:center;color:#6b7280;font-size:8px}
        </style></head><body>';
        $html .= '<div class="header"><div style="float:left"><span class="logo">M</span><span class="school"><div class="school-name">MARELL ACADEMY</div><div class="school-tag">EMPOWERING TOMORROW\'S LEADERS</div></span></div><div style="float:right"><div class="title">'.e($title).'</div><div style="color:#6b7280;font-size:9px;">'.now()->format('d M Y').'</div></div><div style="clear:both"></div></div>';
        $html .= '<table><thead><tr><th style="width:40px">#</th><th>ADM</th><th>NAME</th><th>PARENT</th><th>PHONE</th></tr></thead><tbody>';
        foreach ($students as $i => $s) {
            $html .= '<tr><td>'.($i+1).'</td><td>'.e($s->adm_no).'</td><td>'.e($s->name).'</td><td>'.e($s->parent_name).'</td><td>'.e($s->parent_phone).'</td></tr>';
        }
        $html .= '</tbody></table>';
        $html .= '<div class="footer">Marell Academy · Kanduyi Road, Bungoma · +254 700 000 000 · Total: '.count($students).' students</div>';
        $html .= '</body></html>';
        return $html;
    }
}
