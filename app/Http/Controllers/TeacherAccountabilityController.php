<?php

namespace App\Http\Controllers;

use App\Models\{LessonPlan, TeacherCheckin, TeacherPerformance, User, Result, Student, Attendance};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherAccountabilityController extends Controller
{
    // ============== Teacher: Clock in/out ==============
    public function clockIn(Request $request)
    {
        $teacher = auth()->user();

        $today = TeacherCheckin::firstOrCreate(
            ['user_id' => $teacher->id, 'date' => today()->toDateString()],
            ['ip' => $request->ip()]
        );

        if ($today->clock_in) {
            return response()->json(['status' => 'already', 'clock_in' => $today->clock_in]);
        }

        $today->update(['clock_in' => now()->format('H:i:s')]);

        return response()->json(['status' => 'ok', 'clock_in' => $today->fresh()->clock_in]);
    }

    public function clockOut(Request $request)
    {
        $teacher = auth()->user();

        $today = TeacherCheckin::where('user_id', $teacher->id)
            ->where('date', today()->toDateString())
            ->first();

        if (!$today || !$today->clock_in) {
            return response()->json(['status' => 'error', 'message' => 'Not clocked in']);
        }

        $today->update(['clock_out' => now()->format('H:i:s')]);

        return response()->json(['status' => 'ok', 'clock_out' => $today->clock_out, 'hours' => $today->hoursWorked()]);
    }

    // ============== Teacher: Lesson Plans ==============
    public function myLessonPlans()
    {
        $teacher = auth()->user();
        $plans = LessonPlan::where('teacher_id', $teacher->id)
            ->orderByDesc('week_starting')
            ->paginate(20);

        $classKeys = \App\Models\TeacherSubject::where('teacher_id', $teacher->id)
            ->select('class','stream','subject')->distinct()->get();

        return view('teacher.lesson-plans.index', compact('plans', 'classKeys'));
    }

    public function submitLessonPlan(Request $request)
    {
        $data = $request->validate([
            'class'         => 'required|string|max:30',
            'stream'        => 'nullable|string|max:30',
            'subject'       => 'required|string|max:60',
            'week_starting' => 'required|date',
            'topic'         => 'required|string|max:200',
            'objectives'    => 'nullable|string|max:2000',
            'activities'    => 'nullable|string|max:2000',
            'submit'        => 'nullable|boolean',
        ]);

        $data['teacher_id'] = auth()->id();
        $data['status']     = $request->boolean('submit') ? 'submitted' : 'draft';

        if ($data['status'] === 'submitted') {
            $data['submitted_at'] = now();
        }

        LessonPlan::create($data);

        return back()->with('success', $data['status'] === 'submitted' ? 'Lesson plan submitted to DOS for approval.' : 'Draft saved.');
    }

    // ============== DOS: Review lesson plans ==============
    public function reviewIndex(Request $request)
    {
        $status = $request->get('status', 'submitted');
        $plans = LessonPlan::with('teacher')
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('submitted_at')
            ->paginate(30);

        $counts = [
            'draft'     => LessonPlan::where('status', 'draft')->count(),
            'submitted' => LessonPlan::where('status', 'submitted')->count(),
            'approved'  => LessonPlan::where('status', 'approved')->count(),
            'rejected'  => LessonPlan::where('status', 'rejected')->count(),
        ];

        return view('dos.lesson-plans.index', compact('plans', 'status', 'counts'));
    }

    public function review(Request $request, LessonPlan $plan)
    {
        $data = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes'  => 'nullable|string|max:500',
        ]);

        $plan->update([
            'status'        => $data['action'] === 'approve' ? 'approved' : 'rejected',
            'review_notes'  => $data['notes'] ?? null,
            'reviewed_by'   => auth()->id(),
            'reviewed_at'   => now(),
        ]);

        return back()->with('success', 'Lesson plan ' . $data['action'] . 'd.');
    }

    // ============== DOS/Principal: Teacher Performance ==============
    public function performanceIndex()
    {
        $month = now()->startOfMonth();

        $teachers = User::role('teacher')->orderBy('name')->get();

        $rows = $teachers->map(function ($t) use ($month) {
            $perf = TeacherPerformance::where('user_id', $t->id)
                ->whereDate('month', $month)
                ->first();

            if (!$perf) {
                $perf = $this->computePerformance($t, $month);
            }

            return ['teacher' => $t, 'perf' => $perf];
        })->sortByDesc(fn($r) => $r['perf']->score)->values();

        return view('dos.teacher-performance.index', compact('rows', 'month'));
    }

    public function computeAll()
    {
        $month = now()->startOfMonth();
        $teachers = User::role('teacher')->get();

        foreach ($teachers as $t) {
            $this->computePerformance($t, $month);
        }

        return back()->with('success', "Scores recomputed for {$teachers->count()} teachers.");
    }

    public function computePerformance(User $teacher, $month)
    {
        // Attendance score (based on check-ins this month)
        $checkins = TeacherCheckin::where('user_id', $teacher->id)
            ->whereBetween('date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->get();
        $workingDays = 22;
        $attendanceRate = min(100, ($checkins->count() / $workingDays) * 100);
        $attendanceScore = (int) round(($attendanceRate / 100) * 30); // max 30

        // Marks entry score (how fast they entered marks)
        $exams = \App\Models\Exam::where('status', 'open')->orWhere('status', 'closed')->get();
        $marksScore = 15; // default
        if ($exams->count() > 0) {
            $total = 0;
            foreach ($exams as $exam) {
                $studentIds = Student::whereIn('class',
                    \App\Models\TeacherSubject::where('teacher_id', $teacher->id)->pluck('class')
                )->pluck('id');
                if ($studentIds->isEmpty()) continue;
                $entered = Result::where('exam_id', $exam->id)
                    ->where('teacher_id', $teacher->id)
                    ->whereIn('student_id', $studentIds)
                    ->count();
                $total += $entered > 0 ? 1 : 0;
            }
            $marksScore = (int) round(($total / max(1, $exams->count())) * 20);
        }

        // Lesson plan score
        $plans = LessonPlan::where('teacher_id', $teacher->id)
            ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->get();
        $planScore = 0;
        if ($plans->count() > 0) {
            $approved = $plans->where('status', 'approved')->count();
            $submitted = $plans->where('status', 'submitted')->count();
            $planScore = (int) round((($approved * 1.0 + $submitted * 0.7) / max(1, $plans->count())) * 25);
        }

        // Class performance score (avg mean of students in their classes)
        $classKeys = \App\Models\TeacherSubject::where('teacher_id', $teacher->id)
            ->select('class','stream')->distinct()->get();
        $perfScore = 0;
        if ($classKeys->count() > 0) {
            $avg = 0;
            foreach ($classKeys as $ck) {
                $studentIds = Student::where('class', $ck->class)->pluck('id');
                $classAvg = Result::whereIn('student_id', $studentIds)->avg('marks') ?? 0;
                $avg += (float) $classAvg;
            }
            $avg = $avg / $classKeys->count();
            $perfScore = (int) round(($avg / 100) * 25);
        }

        $total = $attendanceScore + $marksScore + $planScore + $perfScore;

        $perf = TeacherPerformance::updateOrCreate(
            ['user_id' => $teacher->id, 'month' => $month->toDateString()],
            [
                'score'                    => $total,
                'attendance_score'         => $attendanceScore,
                'marks_entry_score'        => $marksScore,
                'lesson_plan_score'        => $planScore,
                'class_performance_score'  => $perfScore,
            ]
        );

        return $perf;
    }

    // ============== Teacher: My performance ==============
    public function myPerformance()
    {
        $teacher = auth()->user();
        $current = TeacherPerformance::where('user_id', $teacher->id)
            ->whereDate('month', now()->startOfMonth())
            ->first();

        if (!$current) {
            $current = $this->computePerformance($teacher, now()->startOfMonth());
        }

        $history = TeacherPerformance::where('user_id', $teacher->id)
            ->orderByDesc('month')
            ->take(6)
            ->get();

        $checkins = TeacherCheckin::where('user_id', $teacher->id)
            ->where('date', '>=', now()->subDays(30))
            ->orderByDesc('date')
            ->get();

        return view('teacher.performance', compact('current', 'history', 'checkins'));
    }
}
