<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DosStudentController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $class  = $request->get('class');
        $status = $request->get('status');

        $query = Student::query();

        if ($q) {
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('adm_no', 'like', "%{$q}%")
                   ->orWhere('parent_phone', 'like', "%{$q}%")
                   ->orWhere('parent_name', 'like', "%{$q}%");
            });
        }
        if ($class)  $query->where('class', $class);
        if ($status) $query->where('status', $status);

        $students = $query->orderBy('class')->orderBy('name')->paginate(30)->withQueryString();
        $classes  = Student::select('class')->distinct()->orderBy('class')->pluck('class');

        return view('dos.students.index', compact('students', 'classes', 'q', 'class', 'status'));
    }

    public function create()
    {
        $nextAdm = $this->nextAdmNo();
        $classes = ['Baby Class','PP1','PP2','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9'];
        $streams = ['Blue','Green','Red','Yellow'];
        return view('dos.students.create', compact('nextAdm', 'classes', 'streams'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:120',
            'class'        => 'required|string|max:30',
            'stream'       => 'nullable|string|max:30',
            'parent_name'  => 'required|string|max:120',
            'parent_phone' => 'required|string|max:20',
            'parent_email' => 'nullable|email|max:120',
            'total_fee'    => 'required|numeric|min:0',
        ]);

        $data['parent_phone'] = $this->normalizePhone($data['parent_phone']);
        $data['adm_no']       = $this->nextAdmNo();
        $data['paid_amount']  = 0;
        $data['balance']      = $data['total_fee'];
        $data['status']       = 'active';

        $student = Student::create($data);

        DB::table('parent_student')->insertOrIgnore([
            'parent_phone' => $student->parent_phone,
            'student_id'   => $student->id,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('dos.students.show', $student)
            ->with('success', "Student {$student->name} registered as {$student->adm_no}.");
    }

    public function show(Student $student)
    {
        $payments = Payment::where('student_id', $student->id)->latest()->get();
        $results  = Result::where('student_id', $student->id)->orderBy('subject')->get();
        return view('dos.students.show', compact('student', 'payments', 'results'));
    }

    public function edit(Student $student)
    {
        $classes = ['Baby Class','PP1','PP2','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9'];
        $streams = ['Blue','Green','Red','Yellow'];
        return view('dos.students.edit', compact('student', 'classes', 'streams'));
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:120',
            'class'        => 'required|string|max:30',
            'stream'       => 'nullable|string|max:30',
            'parent_name'  => 'required|string|max:120',
            'parent_phone' => 'required|string|max:20',
            'parent_email' => 'nullable|email|max:120',
            'total_fee'    => 'required|numeric|min:0',
            'status'       => 'required|in:active,suspended,graduated',
        ]);

        $data['parent_phone'] = $this->normalizePhone($data['parent_phone']);
        $data['balance']      = max(0, (float) $data['total_fee'] - (float) $student->paid_amount);

        $student->update($data);

        return redirect()->route('dos.students.show', $student)
            ->with('success', 'Student updated.');
    }

    protected function nextAdmNo(): string
    {
        $year   = date('Y');
        $prefix = "MAR-{$year}-";
        $last   = Student::where('adm_no', 'like', "{$prefix}%")
            ->orderByDesc('adm_no')
            ->value('adm_no');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    protected function normalizePhone(string $phone): string
    {
        $p = preg_replace('/\D/', '', $phone);
        if (str_starts_with($p, '0')) $p = '254' . substr($p, 1);
        if (strlen($p) === 9)         $p = '254' . $p;
        return $p;
    }
}
