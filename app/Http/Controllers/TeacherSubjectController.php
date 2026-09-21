<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherSubjectController extends Controller
{
    const SUBJECTS = [
        'English', 'Kiswahili', 'Mathematics', 'Science', 'Integrated Science',
        'Social Studies', 'CRE', 'IRE', 'Agriculture', 'Business Studies',
        'Pre-Technical Studies', 'Creative Arts', 'ICT',
    ];

    public function index()
    {
        $teachers = User::role('teacher')->orderBy('name')->get();
        $classes  = Classroom::where('active', true)->orderBy('name')->orderBy('stream')->get();
        $assignments = TeacherSubject::with('teacher')
            ->orderBy('teacher_id')
            ->orderBy('subject')
            ->get()
            ->groupBy('teacher_id');

        return view('dos.teacher-subjects.index', [
            'teachers'    => $teachers,
            'classes'     => $classes,
            'assignments' => $assignments,
            'subjects'    => self::SUBJECTS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id'        => 'required|exists:users,id',
            'subject'           => 'required|string|max:60',
            'class'             => 'nullable|string|max:30',
            'stream'            => 'nullable|string|max:30',
            'periods_per_week'  => 'required|integer|min:1|max:12',
        ]);

        TeacherSubject::updateOrCreate(
            [
                'teacher_id' => $data['teacher_id'],
                'subject'    => $data['subject'],
                'class'      => $data['class'] ?? null,
                'stream'     => $data['stream'] ?? null,
            ],
            [
                'periods_per_week' => $data['periods_per_week'],
                'active'           => true,
            ]
        );

        return back()->with('success', 'Subject assigned.');
    }

    public function destroy(TeacherSubject $teacherSubject)
    {
        $teacherSubject->delete();
        return back()->with('success', 'Assignment removed.');
    }

    public function bulkAssign(Request $request)
    {
        $data = $request->validate([
            'teacher_id'  => 'required|exists:users,id',
            'subjects'    => 'required|array|min:1',
            'subjects.*'  => 'string|max:60',
            'classes'     => 'required|array|min:1',
            'classes.*'   => 'string|max:30',
            'periods_per_week' => 'required|integer|min:1|max:12',
        ]);

        $count = 0;
        foreach ($data['classes'] as $classLabel) {
            [$className, $stream] = array_pad(explode('|', $classLabel), 2, null);
            foreach ($data['subjects'] as $subj) {
                TeacherSubject::updateOrCreate(
                    [
                        'teacher_id' => $data['teacher_id'],
                        'subject'    => $subj,
                        'class'      => $className,
                        'stream'     => $stream,
                    ],
                    [
                        'periods_per_week' => $data['periods_per_week'],
                        'active'           => true,
                    ]
                );
                $count++;
            }
        }

        return back()->with('success', "Bulk assignment done: {$count} records.");
    }

    public function clearTeacher(User $user)
    {
        $n = TeacherSubject::where('teacher_id', $user->id)->delete();
        return back()->with('success', "Cleared {$n} assignments for {$user->name}.");
    }
}
