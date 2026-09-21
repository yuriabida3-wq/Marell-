<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrincipalResultsController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::withCount('results')->orderByDesc('created_at')->get();
        $examId = $request->get('exam_id', $exams->first()?->id);
        $exam = $examId ? Exam::find($examId) : null;

        $classBreakdown = [];
        $topStudents = [];
        $totalPublished = 0;

        if ($exam) {
            $classes = Classroom::where('active', true)->orderBy('name')->get();

            foreach ($classes as $c) {
                $studentIds = Student::where('class', $c->name)
                    ->where('stream', $c->stream)
                    ->pluck('id');

                if ($studentIds->isEmpty()) continue;

                $stats = Result::where('exam_id', $exam->id)
                    ->whereIn('student_id', $studentIds)
                    ->select(
                        DB::raw('COUNT(DISTINCT student_id) as students_entered'),
                        DB::raw('AVG(marks) as mean'),
                        DB::raw('MAX(marks) as highest'),
                        DB::raw('MIN(marks) as lowest')
                    )
                    ->first();

                $classBreakdown[] = [
                    'class'    => $c,
                    'total'    => $studentIds->count(),
                    'entered'  => $stats->students_entered ?? 0,
                    'mean'     => round((float) ($stats->mean ?? 0), 1),
                    'highest'  => (int) ($stats->highest ?? 0),
                    'lowest'   => (int) ($stats->lowest ?? 0),
                ];
            }

            $topStudents = Result::select('student_id', DB::raw('SUM(marks) as total'), DB::raw('AVG(marks) as mean'))
                ->where('exam_id', $exam->id)
                ->groupBy('student_id')
                ->orderByDesc('total')
                ->take(10)
                ->get()
                ->map(function ($r) {
                    $s = Student::find($r->student_id);
                    return [
                        'student' => $s,
                        'total'   => $r->total,
                        'mean'    => round((float) $r->mean, 1),
                    ];
                });

            $totalPublished = $exam->status === 'published' ? $exam->results()->distinct('student_id')->count('student_id') : 0;
        }

        return view('principal.results.index', compact(
            'exams', 'exam', 'classBreakdown', 'topStudents', 'totalPublished'
        ));
    }

    public function exportExam(Exam $exam)
    {
        $filename = 'Marell-Results-' . str_replace(' ', '-', $exam->name) . '-' . date('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($exam) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['ADM', 'Name', 'Class', 'Subject', 'Marks', 'Grade']);

            $results = Result::with('student')->where('exam_id', $exam->id)
                ->orderBy('student_id')->orderBy('subject')->get();

            foreach ($results as $r) {
                fputcsv($out, [
                    $r->student->adm_no ?? '',
                    $r->student->name ?? '',
                    $r->student->class ?? '',
                    $r->subject,
                    $r->marks,
                    $r->grade,
                ]);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
