<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentQrController extends Controller
{
    // Public landing when QR is scanned
    public function show($token)
    {
        $student = Student::where('qr_token', $token)->first();
        if (!$student) {
            return view('public.student-qr-result', ['status' => 'invalid', 'student' => null]);
        }
        return view('public.student-qr-result', ['status' => 'valid', 'student' => $student]);
    }

    // Admin — printable card
    public function card(Student $student)
    {
        if (!$student->qr_token) {
            $student->update(['qr_token' => \Illuminate\Support\Str::random(48)]);
        }
        return view('student-qr.card', compact('student'));
    }

    // Bulk PDF for a class
    public function bulkPdf(\Illuminate\Http\Request $request)
    {
        $request->validate(['class' => 'required|string', 'stream' => 'nullable|string']);

        $q = Student::where('class', $request->class);
        if ($request->stream) $q->where('stream', $request->stream);
        $students = $q->orderBy('name')->get();

        // Ensure tokens
        foreach ($students as $s) {
            if (!$s->qr_token) $s->update(['qr_token' => \Illuminate\Support\Str::random(48)]);
        }

        $pdf = Pdf::loadView('student-qr.bulk-pdf', compact('students'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('Student-QR-Cards-' . $request->class . '.pdf');
    }

    // Parent view of their child QR
    public function parentCard(Student $student)
    {
        $phone = session('parent_phone');
        if (!$phone || $student->parent_phone !== $phone) {
            abort(403);
        }
        if (!$student->qr_token) {
            $student->update(['qr_token' => \Illuminate\Support\Str::random(48)]);
        }
        return view('public.parent-student-qr', compact('student'));
    }
}
