<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Result;
use App\Models\Student;
use App\Models\TeacherSubject;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PrincipalTeacherController extends Controller
{
    public function index()
    {
        $teachers = User::role('teacher')->orderBy('name')->get();

        $stats = $teachers->map(function ($t) {
            return [
                'teacher'      => $t,
                'classes'      => TeacherSubject::where('teacher_id', $t->id)->distinct('class')->count('class'),
                'subjects'     => TeacherSubject::where('teacher_id', $t->id)->distinct('subject')->count('subject'),
                'slots'        => Timetable::where('teacher_id', $t->id)->count(),
                'assigned'     => TeacherSubject::where('teacher_id', $t->id)->count(),
            ];
        });

        $totalTeachers   = $teachers->count();
        $totalAssign     = TeacherSubject::count();
        $totalSlots      = Timetable::whereNotNull('teacher_id')->count();

        return view('principal.teachers.index', compact('stats', 'totalTeachers', 'totalAssign', 'totalSlots'));
    }

    public function show(User $teacher)
    {
        if (!$teacher->hasRole('teacher')) {
            abort(404);
        }

        $subjects  = TeacherSubject::where('teacher_id', $teacher->id)->get();
        $timetable = Timetable::where('teacher_id', $teacher->id)->orderBy('day')->orderBy('period')->get();

        // Student counts per class
        $classKeys = TeacherSubject::where('teacher_id', $teacher->id)
            ->select('class', 'stream')->distinct()->get();

        $myClasses = $classKeys->map(function ($k) {
            $q = Student::where('class', $k->class);
            if ($k->stream) $q->where('stream', $k->stream);
            return [
                'class'  => $k->class,
                'stream' => $k->stream,
                'count'  => $q->count(),
            ];
        });

        return view('principal.teachers.show', compact('teacher', 'subjects', 'timetable', 'myClasses'));
    }

    public function create()
    {
        return view('principal.teachers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
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

        return redirect()->route('principal.teachers.index')
            ->with('success', "Teacher {$user->name} registered.");
    }

    public function edit(User $teacher)
    {
        if (!$teacher->hasRole('teacher')) abort(404);
        return view('principal.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, User $teacher)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:120',
            'email'  => 'required|email|unique:users,email,' . $teacher->id,
            'phone'  => 'nullable|string|max:20|unique:users,phone,' . $teacher->id,
            'active' => 'boolean',
        ]);

        $teacher->update([
            'name'   => $data['name'],
            'email'  => $data['email'],
            'phone'  => $data['phone'] ? $this->normalizePhone($data['phone']) : null,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('principal.teachers.index')->with('success', 'Teacher updated.');
    }

    public function resetPassword(Request $request, User $teacher)
    {
        $data = $request->validate(['password' => 'required|string|min:6']);
        $teacher->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', 'Password reset.');
    }

    protected function normalizePhone(string $phone): string
    {
        $p = preg_replace('/\D/', '', $phone);
        if (str_starts_with($p, '0')) $p = '254' . substr($p, 1);
        if (strlen($p) === 9) $p = '254' . $p;
        return $p;
    }
}
