<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamControl;
use App\Models\TeacherSubject;
use Illuminate\Http\Request;

class ExamControlController extends Controller
{
    public function index(Exam $exam)
    {
        $classes = Classroom::where('active', true)->orderBy('name')->orderBy('stream')->get();

        // Latest action per class for this exam
        $latest = [];
        foreach ($classes as $c) {
            $row = ExamControl::where('exam_id', $exam->id)
                ->where('class', $c->name)
                ->where('stream', $c->stream)
                ->latest()
                ->first();
            $latest[$c->id] = $row;
        }

        $audit = ExamControl::where('exam_id', $exam->id)
            ->with('actor')
            ->latest()
            ->take(20)
            ->get();

        return view('dos.exams.controls', compact('exam', 'classes', 'latest', 'audit'));
    }

    public function open(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'class_id' => 'required|exists:classrooms,id',
            'notes'    => 'nullable|string|max:500',
        ]);

        $class = Classroom::findOrFail($data['class_id']);

        ExamControl::create([
            'exam_id'  => $exam->id,
            'class'    => $class->name,
            'stream'   => $class->stream,
            'action'   => 'opened',
            'notes'    => $data['notes'] ?? null,
            'actor_id' => auth()->id(),
            'actor_ip' => $request->ip(),
        ]);

        // Also ensure the exam's overall status supports open
        if (in_array($exam->status, ['draft', 'closed'])) {
            $exam->update(['status' => 'open']);
        }

        return back()->with('success', "Opened marks entry for {$class->label()}.");
    }

    public function close(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'class_id' => 'required|exists:classrooms,id',
            'notes'    => 'nullable|string|max:500',
        ]);

        $class = Classroom::findOrFail($data['class_id']);

        ExamControl::create([
            'exam_id'  => $exam->id,
            'class'    => $class->name,
            'stream'   => $class->stream,
            'action'   => 'closed',
            'notes'    => $data['notes'] ?? null,
            'actor_id' => auth()->id(),
            'actor_ip' => $request->ip(),
        ]);

        return back()->with('success', "Closed marks entry for {$class->label()}.");
    }

    public function openAll(Request $request, Exam $exam)
    {
        $classes = Classroom::where('active', true)->get();
        foreach ($classes as $c) {
            ExamControl::create([
                'exam_id'  => $exam->id,
                'class'    => $c->name,
                'stream'   => $c->stream,
                'action'   => 'opened',
                'notes'    => 'Bulk open',
                'actor_id' => auth()->id(),
                'actor_ip' => $request->ip(),
            ]);
        }
        $exam->update(['status' => 'open']);

        return back()->with('success', "Opened marks entry for all " . $classes->count() . " classes.");
    }

    public function closeAll(Request $request, Exam $exam)
    {
        $classes = Classroom::where('active', true)->get();
        foreach ($classes as $c) {
            ExamControl::create([
                'exam_id'  => $exam->id,
                'class'    => $c->name,
                'stream'   => $c->stream,
                'action'   => 'closed',
                'notes'    => 'Bulk close',
                'actor_id' => auth()->id(),
                'actor_ip' => $request->ip(),
            ]);
        }
        $exam->update(['status' => 'closed']);

        return back()->with('success', "Closed marks entry for all classes. Ready to publish.");
    }

    /**
     * Check if a specific class is open for marks entry.
     * Returns true if last action for that class was 'opened'.
     */
    public static function isOpenFor(Exam $exam, string $className, ?string $stream): bool
    {
        $last = ExamControl::where('exam_id', $exam->id)
            ->where('class', $className)
            ->where('stream', $stream)
            ->latest()
            ->first();

        if ($last) return $last->action === 'opened';

        // No explicit control — fall back to exam status
        return in_array($exam->status, ['open']);
    }
}
