<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Result;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Hash;

class DosReportController extends Controller
{
    // ---------- REPORT CARDS ----------
    public function reportCardsIndex()
    {
        $exams   = Exam::orderByDesc('created_at')->get();
        $classes = Classroom::where('active', true)->orderBy('name')->get();
        return view('dos.report-cards.index', compact('exams', 'classes'));
    }

    public function reportCardsBulk(Request $request)
    {
        $data = $request->validate([
            'exam_id'  => 'required|exists:exams,id',
            'class_id' => 'required|exists:classrooms,id',
        ]);

        $exam  = Exam::findOrFail($data['exam_id']);
        $class = Classroom::findOrFail($data['class_id']);

        $students = Student::where('class', $class->name)
            ->where('stream', $class->stream)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No students in this class.');
        }

        $html = '<html><head><style>
            @page { margin: 12px; }
            body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
            .page { page-break-after: always; padding: 20px; }
            .page:last-child { page-break-after: auto; }
            .header { border-bottom: 3px solid #0B3D91; padding-bottom: 10px; margin-bottom: 12px; }
            .logo { width: 40px; height: 40px; background: #D4AF37; color: #0B3D91; border-radius: 50%; text-align: center; line-height: 40px; font-weight: bold; font-size: 18px; display: inline-block; vertical-align: middle; }
            .school { display: inline-block; margin-left: 10px; vertical-align: middle; }
            .school-name { font-size: 18px; font-weight: bold; color: #0B3D91; }
            .school-tag { font-size: 8px; letter-spacing: 2px; color: #D4AF37; font-weight: bold; }
            .title { text-align: right; font-size: 14px; font-weight: bold; color: #0B3D91; }
            .info { margin: 10px 0; padding: 10px; background: #f3f4f6; border-radius: 6px; }
            .info table { width: 100%; }
            .info td { padding: 3px 0; }
            .label { color: #6b7280; font-size: 9px; }
            .value { font-weight: bold; color: #0B3D91; font-size: 10px; }
            .marks-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .marks-table th { background: #0B3D91; color: white; padding: 5px 4px; font-size: 9px; text-align: left; }
            .marks-table td { padding: 5px 4px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
            .marks-table td.center { text-align: center; }
            .grade { font-weight: bold; }
            .grade-A, .grade-Am { color: #16a34a; }
            .grade-B { color: #2563eb; }
            .grade-C { color: #d97706; }
            .grade-D, .grade-E { color: #dc2626; }
            .summary { margin-top: 12px; padding: 10px; background: #fef3c7; border-left: 4px solid #d97706; border-radius: 4px; }
            .summary table { width: 100%; }
            .summary td { padding: 3px 0; font-size: 10px; }
            .big-number { font-size: 20px; font-weight: bold; color: #0B3D91; }
            .footer { margin-top: 20px; padding-top: 8px; border-top: 2px solid #D4AF37; text-align: center; color: #6b7280; font-size: 8px; }
            .stamp { display: inline-block; margin-top: 10px; padding: 5px 15px; border: 3px double #0B3D91; color: #0B3D91; font-weight: bold; font-size: 10px; transform: rotate(-5deg); }
            .sig { float: right; margin-top: 30px; border-top: 1px solid #1f2937; padding-top: 3px; font-size: 8px; width: 180px; text-align: center; }
        </style></head><body>';

        foreach ($students as $student) {
            $results = Result::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->orderBy('subject')
                ->get();

            if ($results->isEmpty()) continue;

            $total   = $results->sum('marks');
            $avg     = round($results->avg('marks'), 1);
            $meanGrade = $this->grade((int) round($avg));
            $position  = $this->classPosition($exam, $class, $student);

            $html .= '<div class="page">';
            $html .= '<div class="header">';
            $html .= '<div style="float:left;"><span class="logo">M</span><span class="school"><div class="school-name">MARELL ACADEMY</div><div class="school-tag">EMPOWERING TOMORROW\'S LEADERS</div></span></div>';
            $html .= '<div style="float:right;"><div class="title">REPORT CARD</div><div style="color:#6b7280;font-size:9px;">' . $exam->name . ' · ' . $exam->term . ' ' . $exam->year . '</div></div>';
            $html .= '<div style="clear:both;"></div></div>';

            $html .= '<div class="info"><table>';
            $html .= '<tr><td class="label">STUDENT</td><td class="value">' . e($student->name) . '</td><td class="label">ADM NO</td><td class="value">' . e($student->adm_no) . '</td></tr>';
            $html .= '<tr><td class="label">CLASS</td><td class="value">' . e($class->label()) . '</td><td class="label">TERM</td><td class="value">' . $exam->term . ' ' . $exam->year . '</td></tr>';
            $html .= '</table></div>';

            $html .= '<table class="marks-table"><thead><tr><th>SUBJECT</th><th style="text-align:center;">MARKS</th><th style="text-align:center;">GRADE</th></tr></thead><tbody>';
            foreach ($results as $r) {
                $gc = $this->gradeClass($r->grade);
                $html .= '<tr><td>' . e($r->subject) . '</td><td class="center">' . $r->marks . '</td><td class="center grade ' . $gc . '">' . $r->grade . '</td></tr>';
            }
            $html .= '</tbody></table>';

            $html .= '<div class="summary"><table>';
            $html .= '<tr><td class="label">TOTAL MARKS</td><td class="value">' . $total . '</td><td class="label">MEAN SCORE</td><td class="big-number">' . $avg . '</td></tr>';
            $html .= '<tr><td class="label">MEAN GRADE</td><td class="value">' . $meanGrade . '</td><td class="label">CLASS POSITION</td><td class="value">' . $position['position'] . ' of ' . $position['total'] . '</td></tr>';
            $html .= '</table></div>';

            $html .= '<div style="margin-top:20px;">';
            $html .= '<div class="stamp">OFFICIAL</div>';
            $html .= '<div class="sig">Class Teacher</div>';
            $html .= '<div style="clear:both;"></div></div>';

            $html .= '<div class="footer">Marell Academy · Kanduyi Road, Bungoma · +254 700 000 000<br>This report card is computer-generated. Verify at marell.ac.ke/verify</div>';
            $html .= '</div>';
        }

        $html .= '</body></html>';

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        return $pdf->download('Report-Cards-' . $class->label() . '-' . $exam->term . '-' . $exam->year . '.pdf');
    }

    // ---------- PROMOTE ----------
    public function promoteIndex()
    {
        $classes = Classroom::where('active', true)->orderBy('name')->get();
        return view('dos.promote.index', compact('classes'));
    }

    public function promote(Request $request)
    {
        $data = $request->validate([
            'from_class_id' => 'required|exists:classrooms,id',
            'to_class_id'   => 'required|exists:classrooms,id',
        ]);

        $from = Classroom::findOrFail($data['from_class_id']);
        $to   = Classroom::findOrFail($data['to_class_id']);

        if ($from->id === $to->id) {
            return back()->with('error', 'From and To classes must be different.');
        }

        $count = 0;
        DB::transaction(function () use ($from, $to, &$count) {
            $students = Student::where('class', $from->name)
                ->where('stream', $from->stream)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get();

            foreach ($students as $s) {
                $s->update([
                    'class'  => $to->name,
                    'stream' => $to->stream,
                ]);
                $count++;
            }
        });

        return back()->with('success', "Promoted {$count} students from {$from->label()} → {$to->label()}.");
    }

    // ---------- TEACHER REGISTRATION ----------
    public function teachersIndex()
    {
        $teachers = User::role('teacher')->withCount(['timetables'])->orderBy('name')->paginate(20);
        return view('dos.teachers.index', compact('teachers'));
    }

    public function teachersStore(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20|unique:users,phone',
            'password' => ['required', 'string', new StrongPassword],
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ? $this->normalizePhone($data['phone']) : null,
            'password'   => Hash::make($data['password']),
            'role_label' => 'teacher',
            'active'     => true,
        ]);

        $user->assignRole('teacher');

        return redirect()->route('dos.teachers.index')
            ->with('success', "Teacher {$user->name} registered.");
    }

    // ---------- helpers ----------
    protected function grade(int $marks): string
    {
        return match(true) {
            $marks >= 80 => 'A', $marks >= 75 => 'A-', $marks >= 70 => 'B+', $marks >= 65 => 'B',
            $marks >= 60 => 'B-', $marks >= 55 => 'C+', $marks >= 50 => 'C', $marks >= 45 => 'C-',
            $marks >= 40 => 'D+', $marks >= 35 => 'D', default => 'E',
        };
    }

    protected function gradeClass(string $grade): string
    {
        return match($grade) {
            'A','A-' => 'grade-A',
            'B+','B','B-' => 'grade-B',
            'C+','C','C-' => 'grade-C',
            default => 'grade-D',
        };
    }

    protected function classPosition(Exam $exam, Classroom $class, Student $student): array
    {
        $studentIds = Student::where('class', $class->name)
            ->where('stream', $class->stream)
            ->pluck('id');

        $totals = Result::select('student_id', DB::raw('SUM(marks) as total'))
            ->where('exam_id', $exam->id)
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->get();

        $pos   = 1;
        $mine  = 0;
        foreach ($totals as $i => $t) {
            if ((int) $t->student_id === (int) $student->id) {
                $pos  = $i + 1;
                $mine = $t->total;
                break;
            }
        }

        return ['position' => $pos, 'total' => $totals->count(), 'my_total' => $mine];
    }

    protected function normalizePhone(string $phone): string
    {
        $p = preg_replace('/\D/', '', $phone);
        if (str_starts_with($p, '0')) $p = '254' . substr($p, 1);
        if (strlen($p) === 9)         $p = '254' . $p;
        return $p;
    }
}
