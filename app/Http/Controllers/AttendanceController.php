<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyParentAbsent;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // ============== TEACHER: Mark attendance ==============
    public function markForm(Request $request)
    {
        $teacher = auth()->user();

        // Classes this teacher can mark
        $classKeys = \App\Models\TeacherSubject::where('teacher_id', $teacher->id)
            ->select('class','stream')->distinct()->get();

        if ($classKeys->isEmpty()) {
            $classKeys = \App\Models\Timetable::where('teacher_id', $teacher->id)
                ->select('class','stream')->distinct()->get();
        }

        $selectedClass = $request->get('class');
        $selectedStream = $request->get('stream');
        $date = $request->get('date', today()->toDateString());

        $students = collect();
        $existing = collect();
        $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'sick' => 0, 'unmarked' => 0];

        if ($selectedClass) {
            $q = Student::where('class', $selectedClass)->where('status', 'active');
            if ($selectedStream) $q->where('stream', $selectedStream);
            $students = $q->orderBy('name')->get();

            $existing = Attendance::where('date', $date)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id');

            $summary['unmarked'] = $students->count() - $existing->count();
            foreach ($existing as $a) $summary[$a->status] = ($summary[$a->status] ?? 0) + 1;
        }

        return view('teacher.attendance.mark', compact(
            'classKeys', 'selectedClass', 'selectedStream', 'date', 'students', 'existing', 'summary'
        ));
    }

    public function markStore(Request $request)
    {
        $data = $request->validate([
            'class'   => 'required|string',
            'stream'  => 'nullable|string',
            'date'    => 'required|date|before_or_equal:today',
            'status'  => 'required|array',
            'status.*'=> 'required|in:present,absent,late,excused,sick',
            'reason'  => 'nullable|array',
        ]);

        $teacher = auth()->user();
        $class = $data['class'];
        $stream = $data['stream'] ?? null;
        $date = $data['date'];

        $q = Student::where('class', $class)->where('status', 'active');
        if ($stream) $q->where('stream', $stream);
        $studentIds = $q->pluck('id')->toArray();

        $notifyAbsent = [];

        DB::transaction(function () use ($data, $studentIds, $teacher, $date, &$notifyAbsent) {
            foreach ($data['status'] as $studentId => $status) {
                if (!in_array((int) $studentId, $studentIds, true)) continue;

                $existing = Attendance::where('student_id', $studentId)->where('date', $date)->first();
                $wasAbsent = $existing && $existing->status === 'absent';

                $record = Attendance::updateOrCreate(
                    ['student_id' => $studentId, 'date' => $date],
                    [
                        'status'         => $status,
                        'reason'         => $data['reason'][$studentId] ?? null,
                        'marked_by'      => $teacher->id,
                        'marked_by_name' => $teacher->name,
                        'arrival_time'   => $status === 'late' ? now()->format('H:i') : null,
                    ]
                );

                // Queue parent SMS if absent (and not already sent)
                if ($status === 'absent' && !$wasAbsent && !$record->sms_sent) {
                    $notifyAbsent[] = $record->id;
                }
            }
        });

        // Dispatch SMS job
        if (!empty($notifyAbsent)) {
            NotifyParentAbsent::dispatch($notifyAbsent)->onQueue('default');
        }

        $count = count($data['status']);
        return back()->with('success', "Attendance saved for {$count} students. " . count($notifyAbsent) . " parents notified.");
    }

    // ============== PARENT: Mark excused / sick ==============
    public function parentExcuse(Request $request)
    {
        $phone = session('parent_phone');
        if (!$phone) return redirect()->route('parent.login');

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'date'       => 'required|date|after_or_equal:today',
            'reason'     => 'required|string|max:200',
        ]);

        $student = Student::where('id', $data['student_id'])
            ->where('parent_phone', $phone)
            ->firstOrFail();

        Attendance::updateOrCreate(
            ['student_id' => $student->id, 'date' => $data['date']],
            [
                'status'         => 'excused',
                'reason'         => $data['reason'],
                'marked_by_name' => $student->parent_name . ' (parent)',
                'sms_sent'       => true,
            ]
        );

        return back()->with('success', 'Marked as excused. The school has been notified.');
    }

    // ============== DOS / Principal: Dashboard ==============
    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $class = $request->get('class');

        $query = Attendance::with('student')->where('date', $date);
        if ($class) {
            $query->whereHas('student', fn($q) => $q->where('class', $class));
        }

        $attendances = $query->latest()->paginate(40)->withQueryString();

        // Summary stats
        $totalActive = Student::where('status', 'active')->count();
        $present     = Attendance::where('date', $date)->where('status', 'present')->count();
        $absent      = Attendance::where('date', $date)->where('status', 'absent')->count();
        $late        = Attendance::where('date', $date)->where('status', 'late')->count();
        $excused     = Attendance::where('date', $date)->where('status', 'excused')->count();
        $sick        = Attendance::where('date', $date)->where('status', 'sick')->count();
        $marked      = $present + $absent + $late + $excused + $sick;
        $unmarked    = max(0, $totalActive - $marked);
        $rate        = $marked > 0 ? round((($present + $late) / $marked) * 100, 1) : 0;

        // By-class breakdown
        $byClass = Student::select('class', DB::raw('COUNT(*) as total'))
            ->where('status', 'active')
            ->groupBy('class')
            ->orderBy('class')
            ->get()
            ->map(function ($c) use ($date) {
                $marked = Attendance::where('date', $date)
                    ->whereHas('student', fn($q) => $q->where('class', $c->class))
                    ->count();
                $present = Attendance::where('date', $date)
                    ->where('status', 'present')
                    ->whereHas('student', fn($q) => $q->where('class', $c->class))
                    ->count();
                return [
                    'class'    => $c->class,
                    'total'    => $c->total,
                    'marked'   => $marked,
                    'present'  => $present,
                    'unmarked' => max(0, $c->total - $marked),
                ];
            });

        $classes = Student::select('class')->distinct()->orderBy('class')->pluck('class');

        return view('principal.attendance.index', compact(
            'attendances', 'date', 'class', 'classes',
            'totalActive', 'present', 'absent', 'late', 'excused', 'sick',
            'marked', 'unmarked', 'rate', 'byClass'
        ));
    }

    // ============== Chronic absentees ==============
    public function chronic(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $from = now()->subDays($days)->toDateString();

        $absentees = Attendance::select('student_id', DB::raw('COUNT(*) as absent_days'))
            ->where('date', '>=', $from)
            ->whereIn('status', ['absent', 'late'])
            ->groupBy('student_id')
            ->having('absent_days', '>=', 3)
            ->orderByDesc('absent_days')
            ->with('student')
            ->paginate(40);

        return view('principal.attendance.chronic', compact('absentees', 'days', 'from'));
    }

    // ============== Export ==============
    public function export(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

        $filename = 'Attendance-' . $date . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $rows = Attendance::with('student')->where('date', $date)->get();

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Date', 'ADM', 'Name', 'Class', 'Status', 'Reason', 'Marked By']);
            foreach ($rows as $a) {
                fputcsv($out, [
                    $a->date->format('Y-m-d'),
                    $a->student->adm_no ?? '',
                    $a->student->name ?? '',
                    $a->student->class ?? '',
                    strtoupper($a->status),
                    $a->reason ?? '',
                    $a->marked_by_name ?? '',
                ]);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
